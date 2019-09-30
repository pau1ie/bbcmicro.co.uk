var binaryDiskBlob = null;
var diskTitle = null;
var catalog = null;
var diskWrites = 0;
var catalogEntryCount = 0;
var sectorCount = 0;
var bootOptions = 0;
var gFileId = String("0");
var ddd=false;
var bootOptTab = {
  0: "0: *None",
  1: "1: *Load $.!BOOT",
  2: "2: *Run $.!BOOT",
  3: "3: *Exec $.!BOOT"
}

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
	  checkDiskType();
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

function checkDiskType()
{
	checklocs = Array(parseInt('0x001',16),parseInt('0x002',16),parseInt('0x003',16),parseInt('0x004',16),
					  parseInt('0x005',16),parseInt('0x006',16),parseInt('0x007',16),parseInt('0x008',16),
					  parseInt('0x009',16),parseInt('0x00A',16),parseInt('0x100',16),parseInt('0x101',16),
					  parseInt('0x102',16),parseInt('0x103',16));
	var dfs=true;
	var imgsz;
	var j,k,l,m,o,p;
	var offset=parseInt('0xA00',16);
	ddd=true;
	for (i=0; i<checklocs.length; i++) {
		j=getEightBitValueFromBinary(checklocs[i]);
		if (j > 127 || ( j > 0 && j < 31 )) {
			dfs=false;
		}
		j=getEightBitValueFromBinary(checklocs[i]+offset);
		if (j > 127 || ( j > 0 && j < 31 )) {
			ddd=false;
		}
	}
	j=getEightBitValueFromBinary(parseInt('0x105',16));
	k=getEightBitValueFromBinary(parseInt('0x106',16));
	if (j&7 || k&parseInt('0xCC',16)) 
	{
		dfs=false;
		ddd=false;
	}
	l=getEightBitValueFromBinary(parseInt('0x105',16)+offset);
	m=getEightBitValueFromBinary(parseInt('0x106',16)+offset);
	o=getEightBitValueFromBinary(parseInt('0x107',16));
	p=parseInt('0x100',16);
	if (l&7 || m&parseInt('0xCC',16) ) 
	{
		ddd=false;
	}
	imgsz=(o+256*(k&3))*p;
	console.log("Image size: "+ binaryDiskBlob.byteLength + "; Sectors*Tracks: "+imgsz);
	if (dfs) console.log("DFS Disc");
	if (ddd) console.log("Double sided disc");
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

  $('#bootOption').html(bootOptTab[bootOptions]);

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
      "</td><td>" +
      '<button id="bdis'+catCount+'" type="button" class="btn btn-default btn-xs" onclick="displaySingleFile(\'' + catCount + '\')">Display</button>' +
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

function getFileData(catalogIndex)
{
	var fileData;
	var fileItem = catalog[catalogIndex];
	var track=Math.floor(fileItem.startSector/10);
	var tracksect=fileItem.startSector%10;
	var startOffset;

	fileData = new Uint8Array(fileItem.fileLength);
	
	if (ddd)
	{
		var currTrack = track;
		var currSect=tracksect;
		var bytesLeft=fileItem.fileLength;
		var hwm=0;
		while (bytesLeft>0) {
			if (bytesLeft > 2560-currSect*256) {
				l = 2560-currSect*256;
			} else {
				l=bytesLeft;
			}
			bytesLeft = bytesLeft - l;
			startOffset = 256*(currTrack*2*10+currSect);
			console.log(startOffset,l);
			fileData.set(new Uint8Array(binaryDiskBlob, startOffset, l),hwm);
			hwm=hwm+l;
			currSect = 0; // Next time we start at the first sector
			currTrack++;
		}
	} else {
		startOffset = 256*(track*10+tracksect);
		fileData.set(new Uint8Array(binaryDiskBlob, startOffset, fileItem.fileLength));
	}
	console.log("Double sided: "+ ddd+" Start Sector: "+fileItem.startSector+" Track: "+track+" Sector in track: "+tracksect+" Start Offset: "+startOffset+" Length: "+fileItem.fileLength);
	return fileData;
}

function downloadSingleFile(catalogIndex)
{
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

function setDispBtn(num)
{
  $('#bdis'+num).removeClass('btn-default');
  $('#bdis'+num).addClass('btn-primary');
}

function resetDispBtn(num)
{
  $('#bdis'+num).removeClass('btn-primary');
  $('#bdis'+num).addClass('btn-default');
}

function displaySingleFile(catalogIndex)
{
  var oldfileid=gFileId;
  var b = false, d=false, t=false;
  gFileId=catalogIndex;
  resetDispBtn(oldfileid);
  setDispBtn(catalogIndex);
  $('#fn').text(catalog[catalogIndex]['fileName']);
  d=d_dis(catalogIndex);
  t=d_text(catalogIndex);
  b=d_basic(catalogIndex);
  s=d_scr(catalogIndex);
  d_hex(catalogIndex);

  if (b) {
    $("#pbas").click();
  } else if (t) {
    $("#ptxt").click();
  } else if (s) {
    $("#pscr").click();
  } else if (d) {
    $("#pdis").click();
  } else {
    $("#phex").click();
  }
}

function d_text(catalogIndex)
{
  var fileItem = catalog[catalogIndex];
  var fileData = getFileData(catalogIndex);
  var text = new String;
  var good=0;

  for ( var i=0; i<=fileItem.fileLength; i++) {
    if ( fileData[i] < 31 || fileData[i] > 127 ) {
      if (fileData[i]==13) {
        text+="\n";
        good++;
      } else {
        text += '[' + String("00" + fileData[i].toString(16)).slice(-2)+']';
      }
    } else {
      text+=String.fromCharCode(fileData[i]);
      good++;
    }
  }
  $('#contentstxt').text(text);
  if (good > 0.95*fileItem.fileLength ) {
    return true;
  } else {
    return false;
  }
}

function d_hex(catalogIndex)
{
  var fileItem = catalog[catalogIndex];
  var fileData = getFileData(catalogIndex);

  var c = new String;
  var d = new String;
  var e = new String;
  var hexd = new String;

  var k = 0;
  for ( var i=0; i<=fileItem.fileLength; i+=16) {
    c=String("00000"+i.toString(16)).slice(-5);
    d = "";
    e = "";
    for ( var j=0; j<16; j++ ) {
      k=i+j;
      if ( k < fileItem.fileLength ) {
        d += String("00" + fileData[k].toString(16)).slice(-2);
        if ( j % 2 == 1 ) {
          d += " ";
        }
        if ( fileData[k] > 31 && fileData[k] < 127 ) {
          e += String.fromCharCode(fileData[k]);
        } else {
          e += '.';
        }
      } else {
        d+="  ";
        if ( j % 2 == 1 ) {
          d += " ";
        }
      }
    }
    hexd+=c+" "+d+" "+e+"\n";
    //$('#contents').append(c+" "+d+" "+e+"\n");
  }
  $('#contentshex').text(hexd);
}

function d_basic(catalogIndex)
{
  var fileItem = catalog[catalogIndex];
  var fileData = getFileData(catalogIndex);
  /* Thanks to https://www.sweharris.org for letting me convert the code in
  *  list.pl part of his MMB_Utils https://github.com/sweharris/MMB_Utils
  *  to javascript and relicense it for use in this code.
  */

  var c = new String;
  var d = new String;
  var e = new String;
  var line = 0;
  var llen = 0;
  var raw = 0;
  var decode = new String;
  var prevchar = new String;
  var listing = new String;
  var lend = 0;
  var lno;
  var n1=0;
  var n2=0;
  var n3=0;
  var low=0;
  var high=0;
  var tokens = new Array();
  var ret=true;
  tokens[128] = 'AND';     tokens[192] = 'LEFT$(';
  tokens[129] = 'DIV';     tokens[193] = 'MID$(';
  tokens[130] = 'EOR';     tokens[194] = 'RIGHT$(';
  tokens[131] = 'MOD';     tokens[195] = 'STR$';
  tokens[132] = 'OR';      tokens[196] = 'STRING$(';
  tokens[133] = 'ERROR';   tokens[197] = 'EOF';
  tokens[134] = 'LINE';    tokens[198] = 'AUTO';
  tokens[135] = 'OFF';     tokens[199] = 'DELETE';
  tokens[136] = 'STEP';    tokens[200] = 'LOAD';
  tokens[137] = 'SPC';     tokens[201] = 'LIST';
  tokens[138] = 'TAB(';    tokens[202] = 'NEW';
  tokens[139] = 'ELSE';    tokens[203] = 'OLD';
  tokens[140] = 'THEN';    tokens[204] = 'RENUMBER';
  tokens[142] = 'OPENIN';  tokens[205] = 'SAVE';
  tokens[143] = 'PTR';     tokens[207] = 'PTR';
  tokens[144] = 'PAGE';    tokens[208] = 'PAGE';
  tokens[145] = 'TIME';    tokens[209] = 'TIME';
  tokens[146] = 'LOMEM';   tokens[210] = 'LOMEM';
  tokens[147] = 'HIMEM';   tokens[211] = 'HIMEM';
  tokens[148] = 'ABS';     tokens[212] = 'SOUND';
  tokens[149] = 'ACS';     tokens[213] = 'BPUT';
  tokens[150] = 'ADVAL';   tokens[214] = 'CALL';
  tokens[151] = 'ASC';     tokens[215] = 'CHAIN';
  tokens[152] = 'ASN';     tokens[216] = 'CLEAR';
  tokens[153] = 'ATN';     tokens[217] = 'CLOSE';
  tokens[154] = 'BGET';    tokens[218] = 'CLG';
  tokens[155] = 'COS';     tokens[219] = 'CLS';
  tokens[156] = 'COUNT';   tokens[220] = 'DATA';
  tokens[157] = 'DEG';     tokens[221] = 'DEF';
  tokens[158] = 'ERL';     tokens[222] = 'DIM';
  tokens[159] = 'ERR';     tokens[223] = 'DRAW';
  tokens[160] = 'EVAL';    tokens[224] = 'END';
  tokens[161] = 'EXP';     tokens[225] = 'ENDPROC';
  tokens[162] = 'EXT';     tokens[226] = 'ENVELOPE';
  tokens[163] = 'FALSE';   tokens[227] = 'FOR';
  tokens[164] = 'FN';      tokens[228] = 'GOSUB';
  tokens[165] = 'GET';     tokens[229] = 'GOTO';
  tokens[166] = 'INKEY';   tokens[230] = 'GCOL';
  tokens[167] = 'INSTR(';  tokens[231] = 'IF';
  tokens[168] = 'INT';     tokens[232] = 'INPUT';
  tokens[169] = 'LEN';     tokens[233] = 'LET';
  tokens[170] = 'LN';      tokens[234] = 'LOCAL';
  tokens[171] = 'LOG';     tokens[235] = 'MODE';
  tokens[172] = 'NOT';     tokens[236] = 'MOVE';
  tokens[173] = 'OPENUP';  tokens[237] = 'NEXT';
  tokens[174] = 'OPENOUT'; tokens[238] = 'ON';
  tokens[175] = 'PI';      tokens[239] = 'VDU';
  tokens[176] = 'POINT(';  tokens[240] = 'PLOT';
  tokens[177] = 'POS';     tokens[241] = 'PRINT';
  tokens[178] = 'RAD';     tokens[242] = 'PROC';
  tokens[179] = 'RND';     tokens[243] = 'READ';
  tokens[180] = 'SGN';     tokens[244] = 'REM';
  tokens[181] = 'SIN';     tokens[245] = 'REPEAT';
  tokens[182] = 'SQR';     tokens[246] = 'REPORT';
  tokens[183] = 'TAN';     tokens[247] = 'RESTORE';
  tokens[184] = 'TO';      tokens[248] = 'RETURN';
  tokens[185] = 'TRUE';    tokens[249] = 'RUN';
  tokens[186] = 'USR';     tokens[250] = 'STOP';
  tokens[187] = 'VAL';     tokens[251] = 'COLOUR';
  tokens[188] = 'VPOS';    tokens[252] = 'TRACE';
  tokens[189] = 'CHR$';    tokens[253] = 'UNTIL';
  tokens[190] = 'GET$';    tokens[254] = 'WIDTH';
  tokens[191] = 'INKEY$';  tokens[255] = 'OSCLI';


  var i = 0;
  while ( i < fileItem.fileLength ) {
    if ( fileData[i] != 13 ) {
      listing+="Bad Program (expected ^M at start of line).";
      ret=false;
      break;
    }
    i++;
    // Line number high
    if ( fileData[i] == 255 ) {
      break;
    }
    if ( fileItem.fileLength < i+2 ) {
      listing+="Bad Program (Line finishes before metadata).";
      ret=false;
      break;
    }
    line = fileData[i]*256;
    i++;
    // Line number low
    line = line + fileData[i];
    i++;
    // Line length
    llen = fileData[i]-4;
    if ( llen < 0 ) {
      listing+="Bad Program (Line length too short)";
      ret=false;
      break;
    }
    raw=0;  // Set to 1 if in quotes
    decode="";
    prevchar="";
    lend=i+llen;
    if (lend > fileItem.fileLength ) {
      listing+="Bad Program (Line truncated)";
      ret=false;
      break;
    }
    // Read rest of line
    while ( i++ < lend ) {
      if (raw == 1) {
        d = String.fromCharCode(fileData[i]);
      } else {
        if (fileData[i] == parseInt("8D",16)) {
          // Line token
          i++;
          n1=fileData[i];
          i++;
          n2=fileData[i];
          i++;
          n3=fileData[i];
          // This comes from page 41 of "The BASIC ROM User Guide"
          n1=(n1*4)&255;
          low=(n1 & 192) ^ n2;
          n1=(n1*4)&255;
          high=n1 ^ n3;
          lno=high*256+low;
          d=lno;
        } else {
          if ( fileData[i] in tokens ) {
            d=tokens[fileData[i]];
          } else {
            d=String.fromCharCode(fileData[i]);
          }
        }
      }
      if (String.fromCharCode(fileData[i]) == '"' ) { raw=1-raw; }
      if ( d == '<' ) {
        decode += "&lt;";
      } else {
        decode += d;
      }
    }
    listing+=String("     "+line).slice(-6)+decode+"\n";
  }
  $('#contentsbas').html(listing);
  return ret;
}

function d_dis(catalogIndex)
{
  var fileItem = catalog[catalogIndex];
  var fileData = getFileData(catalogIndex);
	
  var l = new String;
  var text = new String;

  var ops = new Array();
  var amode = new Array();

  var a = 0;
  var addr = 0;
  var i = 0;
  var j = 0;

  ops[0x00] = ['BRK   ', 5];
  ops[0x01] = ['ORA X,', 7];
  ops[0x05] = ['ORA (' ,10];
  ops[0x06] = ['ASL (' ,10];
  ops[0x08] = ['PHP '  , 5];
  ops[0x09] = ['ORA '  , 4];
  ops[0x0A] = ['ASL A' , 0];
  ops[0x0D] = ['ORA '  , 1];
  ops[0x0E] = ['ASL '  , 1];

  ops[0x10] = ['BPL '  , 9];
  ops[0x11] = ['ORA (' , 8];
  ops[0x15] = ['ORA '  ,11];
  ops[0x16] = ['ASL '  ,11];
  ops[0x18] = ['CLC '  , 5];
  ops[0x19] = ['ORA '  , 3];
  ops[0x1D] = ['ORA '  , 2];
  ops[0x1E] = ['ORA '  , 1];

  ops[0x20] = ['JSR '  , 1];
  ops[0x21] = ['AND X,', 7];
  ops[0x24] = ['BIT (' ,10];
  ops[0x25] = ['AND (' ,10];
  ops[0x26] = ['ROL (' ,10];
  ops[0x28] = ['PLP '  , 5];
  ops[0x29] = ['AND '  , 4];
  ops[0x2A] = ['ROL A' , 0];
  ops[0x2C] = ['BIT '  , 1];
  ops[0x2D] = ['AND '  , 1];
  ops[0x2E] = ['ROL '  , 1];

  ops[0x30] = ['BMI '  , 9];
  ops[0x31] = ['AND (' , 8];
  ops[0x35] = ['AND '  ,11];
  ops[0x36] = ['ROL '  ,11];
  ops[0x38] = ['SEC '  , 5];
  ops[0x39] = ['AND '  , 3];
  ops[0x3D] = ['AND '  , 2];
  ops[0x3E] = ['ROL '  , 2];

  ops[0x40] = ['RTI '  , 5];
  ops[0x41] = ['EOR X,', 7];
  ops[0x45] = ['EOR (' ,10];
  ops[0x46] = ['LSR (' ,10];
  ops[0x48] = ['PHA '  , 5];
  ops[0x49] = ['EOR #' , 4];
  ops[0x4A] = ['LSR A' , 0];
  ops[0x4C] = ['JMP '  , 1];
  ops[0x4D] = ['EOR '  , 1];
  ops[0x4E] = ['LSR '  , 1];

  ops[0x50] = ['BVC '  , 9];
  ops[0x51] = ['EOR (' , 8];
  ops[0x55] = ['EOR '  ,11];
  ops[0x56] = ['LSR '  ,11];
  ops[0x58] = ['CLI '  , 5];
  ops[0x59] = ['EOR '  , 3];
  ops[0x5D] = ['EOR '  , 2];
  ops[0x5E] = ['LSR '  , 2];

  ops[0x60] = ['RTS '  , 5];
  ops[0x61] = ['ADC X,', 7];
  ops[0x65] = ['ADC (' ,10];
  ops[0x66] = ['ROR (' ,10];
  ops[0x68] = ['PLA '  , 5];
  ops[0x69] = ['ADC #' , 4];
  ops[0x6A] = ['ROR A' , 0];
  ops[0x6C] = ['JMP (' , 6];
  ops[0x6D] = ['ADC '  , 1];
  ops[0x6E] = ['ROR '  , 1];

  ops[0x70] = ['BVS '  , 9];
  ops[0x71] = ['ADC (' , 8];
  ops[0x75] = ['ROR '  ,11];
  ops[0x76] = ['ROR '  ,11];
  ops[0x78] = ['SEI '  , 5];
  ops[0x79] = ['ADC '  , 3];
  ops[0x7D] = ['ADC '  , 2];
  ops[0x7E] = ['ROR '  , 2];

  ops[0x81] = ['STA '  , 7];
  ops[0x84] = ['STY (' ,10];
  ops[0x85] = ['STA (' ,10];
  ops[0x86] = ['STX (' ,10];
  ops[0x88] = ['DEY '  , 5];
  ops[0x8A] = ['TXA '  , 5];
  ops[0x8C] = ['STY '  , 1];
  ops[0x8D] = ['STA '  , 1];
  ops[0x8E] = ['STX '  , 1];

  ops[0x90] = ['BCC '  , 9];
  ops[0x91] = ['STA (' , 8];
  ops[0x94] = ['STY '  ,11];
  ops[0x95] = ['STA '  ,11];
  ops[0x96] = ['STX '  ,12];
  ops[0x98] = ['TYA '  , 5];
  ops[0x99] = ['STA '  , 3];
  ops[0x9A] = ['TXS '  , 5];
  ops[0x9D] = ['STA '  , 2];

  ops[0xA0] = ['LDY #' , 4];
  ops[0xA1] = ['LDA '  , 7];
  ops[0xA2] = ['LDX #' , 4];
  ops[0xA4] = ['LDY (' ,10];
  ops[0xA5] = ['LDA (' ,10];
  ops[0xA6] = ['LDX (' ,10];
  ops[0xA8] = ['TAY '  , 5];
  ops[0xA9] = ['TAY #' , 4];
  ops[0xAA] = ['TAX '  , 5];
  ops[0xAC] = ['LDY '  , 1];
  ops[0xAD] = ['LDA '  , 1];
  ops[0xAE] = ['LDX '  , 1];

  ops[0xB0] = ['BCS '  , 9];
  ops[0xB1] = ['LDA (' , 8];
  ops[0xB4] = ['LDY '  ,11];
  ops[0xB5] = ['LDA '  ,11];
  ops[0xB6] = ['LDX '  ,12];
  ops[0xB8] = ['CLV '  , 5];
  ops[0xB9] = ['LDA '  , 3];
  ops[0xBA] = ['TSX '  , 5];
  ops[0xBC] = ['LDY '  , 2];
  ops[0xBD] = ['LDA '  , 2];
  ops[0xBE] = ['LDX '  , 3];

  ops[0xC0] = ['CPY #' , 4];
  ops[0xC1] = ['CMP X,', 7];
  ops[0xC4] = ['CPY (' ,10];
  ops[0xC5] = ['CMP (' ,10];
  ops[0xC6] = ['DEC (' ,10];
  ops[0xC8] = ['INY '  , 5];
  ops[0xC9] = ['CMP #' , 4];
  ops[0xCA] = ['DEX '  , 5];
  ops[0xCC] = ['CPY '  , 1];
  ops[0xCD] = ['CMP '  , 1];
  ops[0xCE] = ['DEC '  , 1];

  ops[0xD0] = ['BNE '  , 9];
  ops[0xD1] = ['CMP (' , 8];
  ops[0xD5] = ['CMP '  ,11];
  ops[0xD6] = ['DEC '  ,11];
  ops[0xD8] = ['CLD '  , 5];
  ops[0xD9] = ['CMP '  , 3];
  ops[0xDD] = ['CMP '  , 2];
  ops[0xDE] = ['DEC '  , 3];

  ops[0xE0] = ['CPX #' , 4];
  ops[0xE1] = ['SBC X,', 7];
  ops[0xE4] = ['CPX (' ,10];
  ops[0xE5] = ['SBC (' ,10];
  ops[0xE6] = ['INC (' ,10];
  ops[0xE8] = ['INX '  , 5];
  ops[0xE9] = ['SBC #' , 4];
  ops[0xEA] = ['NOP '  , 5];
  ops[0xEC] = ['CPX '  , 1];
  ops[0xED] = ['SBC '  , 1];
  ops[0xEE] = ['INC '  , 1];

  ops[0xF0] = ['BEQ '  , 9];
  ops[0xF1] = ['SBC '  , 9];
  ops[0xF5] = ['SBC '  ,11];
  ops[0xF6] = ['INC '  ,11];
  ops[0xF8] = ['SED '  , 5];
  ops[0xFD] = ['SBC '  , 2];
  ops[0xFE] = ['INC '  , 2];

  xbytes = [0,2,2,2,1,0,2,2,2,1,1,1,1];
/*
 0 A
 1 abs
 2 abs,X
 3 abs,Y
 4 #
 5 impl
 6 ind
 7 X,ind
 8 ind,Y
 9 rel
10 zpg
11 zpg,X
12 zpg,Y
*/
  while ( i < fileItem.fileLength ) {
    addr = fileItem.fileLoad+i;
    l += ('0000'+Number(addr).toString(16)).slice(-4)+'   ';
    if (fileData[i] in ops) {
      ins=ops[fileData[i]];
      if (i+xbytes[ins[0]] > fileItem.fileLength ) {
        l += "Premature end of file";
        break;
      }
      for ( j=0; j<=xbytes[ins[1]]; j++) {
        l+=('0'+Number(fileData[i+j]).toString(16)).slice(-2)+' ';
      }
      l+= '         '.slice(0,3*(3-xbytes[ins[1]]));
      l+= ins[0];
      i++;
      function abs(a,b) {
        return '&'+('0000' + Number(a+b*0x100).toString(16)).slice(-4);
      }
      function twoc(a) {
        if ( a > 127 ) {
          return a|0xFFFFFF00;
        } else {
          return a
        }
      }
      switch ( ins[1] ) {
        case 0:  // A
        case 5:  // Implied
          // No more arguments needed.
          break;
        case 1: // Absolute addressing
          l=l+abs(fileData[i],fileData[i+1]);
          break;
        case 2: // Absolute addressing
          l=l+abs(fileData[i],fileData[i+1])+',X';
          break;
        case 3: // Absolute addressing
          l=l+abs(fileData[i],fileData[i+1])+',Y';
          break;
        case 4: // Immediate addressing
          l+='&'+('00'+Number(fileData[i]).toString(16)).slice(-2);
          break;
        case 6: // Indirect
          l=l+abs(fileData[i],fileData[i+1])+')';
          break;
        case 7: // Indirect X
          l=l+abs(fileData[i],fileData[i+1]);
          break;
        case 8: // Indirect Y
          l=l+abs(fileData[i],fileData[i+1])+',Y)';
          break;
        case 9: // Relative
          a=twoc(fileData[i]);
          l+=('   '+Number(a)).slice(-3)+'       '+Number(addr+1+xbytes[ins[1]]+a).toString(16);
          break;
        case 10: // Zero page
          l+='&'+('00'+Number(fileData[i]).toString(16)).slice(-2)+')';
          break;
        case 11: // Zero page X
          l+='&'+('00'+Number(fileData[i]).toString(16)).slice(-2)+',X';
          break;
        case 12: // Zero page Y
          l+='&'+('00'+Number(fileData[i]).toString(16)).slice(-2)+',Y';
          break;
      }
      i+=xbytes[ins[1]];
    } else {
      l += ('00'+Number(fileData[i]).toString(16)).slice(-2)+'          ???';
      i++;
    }
    text += l + "\n";
    l="";
  }
  $('#contentsdis').html(text);

  if  (fileItem.fileExec == 0 || fileItem.fileExec < fileItem.fileLoad || fileItem.fileExec > fileItem.fileLoad + fileItem.fileLength || !( fileData[ fileItem.fileExec - fileItem.fileLoad ] in ops) || fileData[ fileItem.fileExec - fileItem.fileLoad ] == 0 ) {
    return false;
  } else {
    return true;
  }
}

// Globals for screen display
var modeTable = {
  // Note: the colours entry maps to the hash entry in the palettes table
  // NOTE2: MODE7 is Not decoded by this script, only graphics modes.
  0: { width: 640, height: 256, colors: 2, charsX: 80, charsY: 32, bits: 1 },
  1: { width: 320, height: 256, colors: 4, charsX: 40, charsY: 32, bits: 2 },
  2: { width: 160, height: 256, colors: 16, charsX: 20, charsY: 32, bits: 4 },
  4: { width: 320, height: 256, colors: 2, charsX: 40, charsY: 32, bits: 1 },
  5: { width: 160, height: 256, colors: 4, charsX: 20, charsY: 32, bits: 2 },
};

var colorPalettes = { // Arrays are RGBA format
  2: [
    [ 0, 0, 0, 255 ],      // 0 = Black
    [ 255, 255, 255, 255 ] // 1 = White
  ],
  4: [
    [ 0, 0, 0, 255 ],      // 0 = Black
    [ 255, 0, 0, 255 ],    // 1 = Red
    [ 255, 255, 0, 255 ],  // 2 = Yellow
    [ 255, 255, 255, 255 ] // 3 = White
  ],
  16: [
    [ 0, 0, 0, 255 ],       // 0 = Black
    [ 255, 0, 0, 255 ],     // 1 = Red
    [ 0, 255, 0, 255 ],     // 2 = Green
    [ 255, 255, 0, 255 ],   // 3 = Yellow
    [ 0, 0, 255, 255 ],     // 4 = Blue
    [ 255, 0, 255, 255 ],   // 5 = Magenta
    [ 0, 255, 255, 255 ],   // 6 = Cyan
    [ 255, 255, 255, 255 ], // 7 = White

    // Can't support flashing colours in a 24bpp pixel map, so we just repeat the first 16 colours
    [ 0, 0, 0, 255 ],       // 0 = Black
    [ 255, 0, 0, 255 ],     // 1 = Red
    [ 0, 255, 0, 255 ],     // 2 = Green
    [ 255, 255, 0, 255 ],   // 3 = Yellow
    [ 0, 0, 255, 255 ],     // 4 = Blue
    [ 255, 0, 255, 255 ],   // 5 = Magenta
    [ 0, 255, 255, 255 ],   // 6 = Cyan
    [ 255, 255, 255, 255 ], // 7 = White
  ]
}

function decodeOneBitByte(blockData, blockIndex, modeInfo)
{
  var result = 
    colorPalettes[modeInfo.colors][(blockData[blockIndex] & 0x80) >> 7]
    .concat(colorPalettes[modeInfo.colors][(blockData[blockIndex] & 0x40) >> 6])
    .concat(colorPalettes[modeInfo.colors][(blockData[blockIndex] & 0x20) >> 5])
    .concat(colorPalettes[modeInfo.colors][(blockData[blockIndex] & 0x10) >> 4])
    .concat(colorPalettes[modeInfo.colors][(blockData[blockIndex] & 0x08) >> 3])
    .concat(colorPalettes[modeInfo.colors][(blockData[blockIndex] & 0x04) >> 2])
    .concat(colorPalettes[modeInfo.colors][(blockData[blockIndex] & 0x02) >> 1])
    .concat(colorPalettes[modeInfo.colors][(blockData[blockIndex] & 0x01)]);
  return result;
}

function decodeTwoBitByte(blockData, blockIndex, modeInfo)
{
  var bottomNibble = blockData[blockIndex] & 0x0F;
  var topNibble = (blockData[blockIndex] & 0xF0) >> 4;

  var pix1 = ((topNibble & 0x01) << 1) + (bottomNibble & 0x01);
  var pix2 = (topNibble & 0x02) + ((bottomNibble & 0x02) >> 1);
  var pix3 = ((topNibble & 0x04) >> 1) + ((bottomNibble & 0x04) >> 2);
  var pix4 = ((topNibble & 0x08) >> 2) + ((bottomNibble & 0x08) >> 3);

  var result = 
    colorPalettes[modeInfo.colors][pix4]
    .concat(colorPalettes[modeInfo.colors][pix3])
    .concat(colorPalettes[modeInfo.colors][pix2])
    .concat(colorPalettes[modeInfo.colors][pix1])
  return result;
}

function decodeFourBitByte(blockData, blockIndex, modeInfo)
{
  var bottomFirstHalfNibble = (blockData[blockIndex] & 0x0C) >> 2;
  var bottomSecondHalfNibble = blockData[blockIndex] & 0x03;

  var topFirstHalfNibble = (blockData[blockIndex] & 0xC0) >> 6;
  var topSecondHalfNibble = (blockData[blockIndex] & 0x30) >> 4;

  var pix1 = ((topFirstHalfNibble & 0x02) + ((topSecondHalfNibble & 0x02) >> 1) << 2) + (bottomFirstHalfNibble & 0x02) + ((bottomSecondHalfNibble & 0x02) >> 1);
  var pix2 = ((((topFirstHalfNibble & 0x01) << 1) + (topSecondHalfNibble & 0x01)) << 2) + (((bottomFirstHalfNibble & 0x01) << 1) + (bottomSecondHalfNibble & 0x01));

  var result = 
    colorPalettes[modeInfo.colors][pix1]
    .concat(colorPalettes[modeInfo.colors][pix2])
  return result;
}

function unpackOneBitColor(packedInputData, screenMode, length)
{
  var modeInfo = modeTable[screenMode];
  var buffer = new Uint8ClampedArray(modeInfo.height*modeInfo.width*4);
  var lineWidth = modeInfo.width;

  // Check length for input array, and height*width for output array
  for (var i=0; i<length && i<modeInfo.height*modeInfo.width/8; i+=8) {
    // Deal with 1 char at a time - 8x8 pixels
    // Each byte has 8 pixels going down the screen.
    var charBytes = packedInputData.slice(i,i+8)

    // 8 rows of pixels dealt with at a time.
    var lineNo=Math.trunc(i/lineWidth);
    var linePos=i-(lineWidth*lineNo);
    // 8 lines are done at a time
    // 4 bytes per pixel 
    // but i is incremented by 8, so reflected in linePos.
    var destinationOffset = lineNo*8*lineWidth*4+linePos*4;
    //console.log("I: "+i+"  Dest: "+destinationOffset);
    var row1 = decodeOneBitByte(charBytes, 0, modeInfo);
    var row2 = decodeOneBitByte(charBytes, 1, modeInfo);
    var row3 = decodeOneBitByte(charBytes, 2, modeInfo);
    var row4 = decodeOneBitByte(charBytes, 3, modeInfo);
    var row5 = decodeOneBitByte(charBytes, 4, modeInfo);
    var row6 = decodeOneBitByte(charBytes, 5, modeInfo);
    var row7 = decodeOneBitByte(charBytes, 6, modeInfo);
    var row8 = decodeOneBitByte(charBytes, 7, modeInfo);

    buffer.set(row1, (lineWidth * 4 * 0) + destinationOffset);
    buffer.set(row2, (lineWidth * 4 * 1) + destinationOffset);
    buffer.set(row3, (lineWidth * 4 * 2) + destinationOffset);
    buffer.set(row4, (lineWidth * 4 * 3) + destinationOffset);
    buffer.set(row5, (lineWidth * 4 * 4) + destinationOffset);
    buffer.set(row6, (lineWidth * 4 * 5) + destinationOffset);
    buffer.set(row7, (lineWidth * 4 * 6) + destinationOffset);
    buffer.set(row8, (lineWidth * 4 * 7) + destinationOffset);
  }
  return buffer;
}

function unpackTwoBitColor(packedInputData, screenMode, length)
{
  var modeInfo = modeTable[screenMode];
  var buffer = new Uint8ClampedArray(modeInfo.height*modeInfo.width*4);
  var lineWidth = modeInfo.width;

  for(var i = 0; i < length && i<modeInfo.height*modeInfo.width*modeInfo.bits/8; i+=8 )
  {
    var charBytes = packedInputData.slice(i, i+8);
    // 8 rows of pixels dealt with at a time.
    var lineNo=Math.trunc(i/(2*lineWidth));
    var linePos=i-(lineWidth*lineNo*2);
    // 8 bytes cover 4 columns
    // 4 bytes per pixel 
    // but i is incremented by 8, so reflected in linePos.
    var destinationOffset = lineNo*8*lineWidth*4+linePos*4/2;
    //console.log("I: "+i+"  Dest: "+destinationOffset);

    var row1 = decodeTwoBitByte(charBytes, 0, modeInfo);
    var row2 = decodeTwoBitByte(charBytes, 1, modeInfo);
    var row3 = decodeTwoBitByte(charBytes, 2, modeInfo);
    var row4 = decodeTwoBitByte(charBytes, 3, modeInfo);
    var row5 = decodeTwoBitByte(charBytes, 4, modeInfo);
    var row6 = decodeTwoBitByte(charBytes, 5, modeInfo);
    var row7 = decodeTwoBitByte(charBytes, 6, modeInfo);
    var row8 = decodeTwoBitByte(charBytes, 7, modeInfo);
      
    buffer.set(row1, (lineWidth * 4 * 0) + destinationOffset);
    buffer.set(row2, (lineWidth * 4 * 1) + destinationOffset);
    buffer.set(row3, (lineWidth * 4 * 2) + destinationOffset);
    buffer.set(row4, (lineWidth * 4 * 3) + destinationOffset);
    buffer.set(row5, (lineWidth * 4 * 4) + destinationOffset);
    buffer.set(row6, (lineWidth * 4 * 5) + destinationOffset);
    buffer.set(row7, (lineWidth * 4 * 6) + destinationOffset);
    buffer.set(row8, (lineWidth * 4 * 7) + destinationOffset);
  }
  return buffer;
}

function unpackFourBitColor(packedInputData, screenMode, length)
{
  var modeInfo = modeTable[screenMode];
  var buffer = new Uint8ClampedArray(modeInfo.height*modeInfo.width*4);
  var lineWidth = modeInfo.width;

  for(var i = 0; i < length && i<modeInfo.height*modeInfo.width*modeInfo.bits/8; i+=8 )
  {
    var charBytes = packedInputData.slice(i, i+8);
    // 8 rows of pixels dealt with at a time.
    var lineNo=Math.trunc(i/(4*lineWidth));
    var linePos=i-(lineWidth*lineNo*4);
    // 8 bytes cover 4 columns
    // 4 bytes per pixel 
    // but i is incremented by 8, so reflected in linePos.
    var destinationOffset = lineNo*8*lineWidth*4+linePos*4/4;
    //console.log("I: "+i+"  Dest: "+destinationOffset);

    var row1 = decodeFourBitByte(charBytes, 0, modeInfo);
    var row2 = decodeFourBitByte(charBytes, 1, modeInfo);
    var row3 = decodeFourBitByte(charBytes, 2, modeInfo);
    var row4 = decodeFourBitByte(charBytes, 3, modeInfo);
    var row5 = decodeFourBitByte(charBytes, 4, modeInfo);
    var row6 = decodeFourBitByte(charBytes, 5, modeInfo);
    var row7 = decodeFourBitByte(charBytes, 6, modeInfo);
    var row8 = decodeFourBitByte(charBytes, 7, modeInfo);
     
    buffer.set(row1, (lineWidth * 4 * 0) + destinationOffset);
    buffer.set(row2, (lineWidth * 4 * 1) + destinationOffset);
    buffer.set(row3, (lineWidth * 4 * 2) + destinationOffset);
    buffer.set(row4, (lineWidth * 4 * 3) + destinationOffset);
    buffer.set(row5, (lineWidth * 4 * 4) + destinationOffset);
    buffer.set(row6, (lineWidth * 4 * 5) + destinationOffset);
    buffer.set(row7, (lineWidth * 4 * 6) + destinationOffset);
    buffer.set(row8, (lineWidth * 4 * 7) + destinationOffset);
  }
  return buffer;
}

function d_scr(catalogIndex)
{
  var fileItem = catalog[catalogIndex];
  var fileData = getFileData(catalogIndex);  var canvas, ctx;
  var buffer = "";

  for (mode in modeTable) {
    switch (modeTable[mode].bits)
    {
      case 1:
        buffer = unpackOneBitColor(fileData, mode, fileItem.fileLength);
        break;
      case 2:
        buffer = unpackTwoBitColor(fileData, mode, fileItem.fileLength);
        break;
      case 4:
        buffer = unpackFourBitColor(fileData, mode, fileItem.fileLength);
        break;
    }
    canvas = document.getElementById('beebScreen'+mode);
    ctx = canvas.getContext("2d");
    canvas.width = modeTable[mode].width;
    canvas.height = modeTable[mode].height;
    canvas.style.width = "640px";
    canvas.style.height = "512px";
    var idata = ctx.createImageData(modeTable[mode].width, modeTable[mode].height);
    idata.data.set(buffer.slice(0,4*modeTable[mode].width*modeTable[mode].height));
    ctx.putImageData(idata, 0, 0);
  }
  if ( catalog[catalogIndex].fileLoad >= 12288 && catalog[catalogIndex].fileLoad + catalog[catalogIndex].fileLength <= 32768 && catalog[catalogIndex].fileLength % 8 == 0 ) {
    return true;
  } else { 
    return false;
  }
}
