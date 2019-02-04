var binaryDiskBlob = null;
var diskTitle = null;
var catalog = null;
var diskWrites = 0;
var catalogEntryCount = 0;
var sectorCount = 0;
var bootOptions = 0;

function hideElement(element)
{
  $('#' + element).hide();
}

function showElement(element)
{
  $('#' + element).show();
}

function loadDiskTable()
{
  hideElement('diskTable');
  showElement('disksLoading');
  $('#diskTable tbody').empty();
  networkGet("disks.json")
    .then(function (data)
    {

      data.forEach(function (dataItem)
      {

        var newTableLine = "<tr><td>" +
          dataItem.name +
          "</td><td>" +
          dataItem.diskTitle +
          "</td><td>" +
          dataItem.diskType +
          "</td><td>" +
          '<button type="button" class="btn btn-primary btn-xs" onclick="openDisk(\'' + dataItem.name + '\')">Open</button>&nbsp;' +
          '<button type="button" class="btn btn-success btn-xs" onclick="downloadDisk(\'' + dataItem.name + '\')">Download</button>' +
          "</td></tr>";

        $('#diskTable tbody').append(newTableLine);

      });
      showElement('diskTable');
      hideElement('disksLoading');

    })
    .fail(function (xhr)
    {
      alert("ERROR: Failed to load 'disks.json' with error code " + xhr.status);
    });
}

function networkGet(url)
{
  return $.ajax({ method: "GET", url: url, cache: false });
};

function openDisk(diskName)
{
  $('#diskContentName').html(diskName.replace(/\\/g,'/').replace( /.*\//, '' ));
  hideElement('availableDisks');
  showElement('diskContents');

  var diskPath = diskName;

  networkGetBinaryBlob(diskPath)
    .then(function ()
    {
      decodeDiskCatalog();
      displayDiskCatalog();
    });
}

function downloadDisk(diskName)
{
  var diskPath = diskName;

  networkGetBinaryBlob(diskPath)
    .then(function () {
      downloadBinaryData(diskName, binaryDiskBlob);
    });
}

function decodeDiskCatalog()
{
  var catalogOffset = 0; // Sector one

  diskTitle = getStringFromBinary(catalogOffset, 8);
  catalog = new Array();

  catalogOffset += 8;
  for(var sectOneCount = 0; sectOneCount < 31; sectOneCount++)
  {
    var catalogEntry = {
      fileName: getStringFromBinary(catalogOffset, 7).trim().toUpperCase(),
      fileDirectory: getSevenBitCharFromBinary(catalogOffset + 7),
      fileLocked: getTopBitSetFromBinary(catalogOffset + 7),
      fileLoad: 0,
      fileExec: 0,
      fileLength: 0,
      startSector: 0
    };
    catalog.push(catalogEntry);
    catalogOffset += 8;
  }

  catalogOffset = 256; // Sector two

  diskTitle = diskTitle + getStringFromBinary(catalogOffset, 4);
  catalogOffset += 4;

  diskWrites = getEightBitValueFromBinary(catalogOffset);
  catalogOffset++;

  catalogEntryCount = getEightBitValueFromBinary(catalogOffset) / 8;
  catalogOffset++;

  sectorCount = ((getEightBitValueFromBinary(catalogOffset) & 0x03) << 8) + getEightBitValueFromBinary(catalogOffset + 1);
  bootOptions = ((getEightBitValueFromBinary(catalogOffset) & 0xF0) >> 4);
  catalogOffset += 2;

  for(var sectTwoCount = 0; sectTwoCount < 31; sectTwoCount++)
  {
    // Full Tube Compatible Addressing
    //catalog[sectTwoCount].fileLoad = (((getEightBitValueFromBinary(catalogOffset + 0x06) & 0x0C) >> 2) << 16) + getSixteenBitValueFromBinary(catalogOffset);
    //catalog[sectTwoCount].fileExec = (((getEightBitValueFromBinary(catalogOffset + 0x06) & 0xC0) >> 6) << 16) + getSixteenBitValueFromBinary(catalogOffset + 0x02);
    //catalog[sectTwoCount].fileLength = (((getEightBitValueFromBinary(catalogOffset + 0x06) & 0x30) >> 4) << 16) + getSixteenBitValueFromBinary(catalogOffset + 0x04);
    //catalog[sectTwoCount].startSector = ((getEightBitValueFromBinary(catalogOffset + 0x06) & 0x03) << 8) + getEightBitValueFromBinary(catalogOffset + 0x07);

    // Normal Addressing (That everyones used too)
    catalog[sectTwoCount].fileLoad = getSixteenBitValueFromBinary(catalogOffset);
    catalog[sectTwoCount].fileExec = getSixteenBitValueFromBinary(catalogOffset + 0x02);
    catalog[sectTwoCount].fileLength = (((getEightBitValueFromBinary(catalogOffset + 0x06) & 0x30) >> 4) << 16) + getSixteenBitValueFromBinary(catalogOffset + 0x04);
    catalog[sectTwoCount].startSector = ((getEightBitValueFromBinary(catalogOffset + 0x06) & 0x03) << 8) + getEightBitValueFromBinary(catalogOffset + 0x07);

    catalogOffset += 8;
  }
}

function displayDiskCatalog()
{
  $('#diskTitle').html(diskTitle);
  $('#diskWrites').html(diskWrites);

  var sizeInKb = (sectorCount * 256) / 1024;
  var tracks = (sectorCount > 400) ? 80 : 40;
  $('#diskSize').html(sizeInKb + "K (" + tracks + " track)");

  $('#bootOption').html(bootOptions);

  $('#fileTable tbody').empty();
  for (var catCount = 0; catCount < catalogEntryCount; catCount++)
  {
    var fileItem = catalog[catCount];

    var newTableLine = "<tr><td>" +
      fileItem.fileDirectory +
      "</td><td>" +
      fileItem.fileName +
      "</td><td>" +
      decimalToHex(fileItem.fileLoad, 4) +
      "</td><td>" +
      decimalToHex(fileItem.fileExec, 4) +
      "</td><td>" +
      decimalToHex(fileItem.fileLength, 4) +
      "</td><td>" +
      ((fileItem.fileLocked === true) ? "L" : "") +
      "</td><td>" +
      '<button type="button" class="btn btn-success btn-xs" onclick="downloadSingleFile(\'' + catCount + '\')">Download</button>' +
      "</td></tr>";

    $('#fileTable tbody').append(newTableLine);

  }

}

function goBackToAvailableDisks()
{
  $('#diskContentName').html("");
  showElement('availableDisks');
  hideElement('diskContents');
  loadDiskTable();
}

function networkGetBinaryBlob(url)
{
  var loadComplete = $.Deferred();

  var request = new XMLHttpRequest();
  request.open("GET", url, true);
  request.responseType = "arraybuffer";

  request.onload = function (loadEvent)
  {
    //binaryDiskBlob = new Blob([request.response], { type: "application/octet-stream" });
    binaryDiskBlob = request.response;
    loadComplete.resolve();
  }

  request.send();

  return loadComplete.promise();
}

function getSixteenBitValueFromBinary(startPos)
{
  var tempArray = new Uint8Array(binaryDiskBlob, startPos, 2);
  var result = (tempArray[1] << 8) + tempArray[0];
  return result;
}

function getEightBitValueFromBinary(startPos)
{
  var result = new Uint8Array(binaryDiskBlob, startPos, 1)[0];
  return result;
}

function getSevenBitValueFromBinary(startPos)
{
  var result = getEightBitValueFromBinary(startPos);
  return  result & 0x7F;
}

function getSevenBitCharFromBinary(startPos)
{
  var result = getSevenBitValueFromBinary(startPos);
  return String.fromCharCode(result);
}

function getTopBitSetFromBinary(startPos)
{
  var result = new Uint8Array(binaryDiskBlob, startPos, 1)[0] & 0x80;
  return (result === 128);
}

function getStringFromBinary(startPos, length)
{
  var tempBuffer = new Uint8Array(binaryDiskBlob, startPos, length);
  return (String.fromCharCode.apply(null, tempBuffer)).trim();
}

function decimalToHex(d, padding)
{
  var hex = Number(d).toString(16).toUpperCase();
  padding = typeof (padding) === "undefined" || padding === null ? padding = 2 : padding;

  while (hex.length < padding)
  {
    hex = "0" + hex;
  }

  return hex;
}

function downloadSingleFile(catalogIndex)
{
  console.log(catalog[catalogIndex]);

  var fileItem = catalog[catalogIndex];

  var startOffset = fileItem.startSector * 256; // DFS Disks have 256 bytes per sector
  var fileData = new Uint8Array(binaryDiskBlob, startOffset, fileItem.fileLength);

  var fileName = ((fileItem.fileDirectory === '$') ? '' : fileItem.fileDirectory + "_") +
    fileItem.fileName +
    "_" +
    decimalToHex(fileItem.fileLoad) +
    "_" +
    decimalToHex(fileItem.fileExec) +
    ".bin";

  downloadBinaryData(fileName, fileData);
}

function downloadBinaryData(fileName, fileData)
{
  var blob = new Blob([fileData], { type: "application/octet-stream" });
  var b64Url = URL.createObjectURL(blob);

  var anchorTag = document.createElement("a");
  anchorTag.download = fileName;
  anchorTag.href = b64Url;
  anchorTag.id = 'downloadLink';
  anchorTag.style.display = "none";
  document.body.appendChild(anchorTag);
  anchorTag.click();

  // Wait 2 seconds then remove the link
  setTimeout(function() {
      var aTag = document.getElementById("downloadLink");
      aTag.parentNode.removeChild(aTag);
    },
    2000);
}
