DBNAME=bbcmicro
DBUSER=bbcmicro
PHPV="7.3"

PASSWORD=$(LC_ALL=C tr -dc 'A-Za-z0-9!#$%+,-.:;<=>?@^_~' </dev/urandom | head -c 13 )

echo "+++ Installing packages "

sudo pkg install apache24

export ASSUME_ALWAYS_YES=yes

sudo pkg install -y wget git
sudo pkg install -y apache24
sudo sysrc apache24_enable="YES"
sudo service apache24 start
sudo service apache24 status
sudo pkg install -y mysql57-server
sudo sysrc mysql_enable="YES"
sudo service mysql-server start
sudo service mysql-server status
sudo pkg install -y php73 php73-mysqli mod_php73 php73-pdo_mysql php73-json
sudo cp /usr/local/etc/php.ini-production /usr/local/etc/php.ini
sudo cat > /usr/local/etc/apache24/modules.d/001_mod-php.conf <<-!
<IfModule dir_module>
    DirectoryIndex index.php index.html
    <FilesMatch "\.php$">
        SetHandler application/x-httpd-php
    </FilesMatch>
    <FilesMatch "\.phps$">
        SetHandler application/x-httpd-php-source
    </FilesMatch>
</IfModule>
!

sudo apachectl configtest
sudo apachectl restart

echo "Password is $PASSWORD"
echo "+++ Setting up the databse"
sudo service mysql-server stop
sudo sysrc mysql_args="--skip-grant-tables"
sudo service mysql-server start
mysql -u root <<-!
update mysql.user set authentication_string=password('') where user='root';
!
sudo service mysql-server stop
sudo sysrc -x mysql_args
sudo service mysql-server start
sudo mysql -u root <<-ENDSQL
alter user root@localhost identified by '';
drop user if exists ${DBUSER};
drop database if exists ${DBNAME};
create database bbcmicro;
grant all privileges on ${DBNAME}.* to ${DBUSER}@localhost identified by '$PASSWORD';
use ${DBNAME};
alter user ${DBUSER}@localhost identified by '$PASSWORD';
ENDSQL
echo "+++ Creating and loading tables. This takes a few minutes."
mysql -vu "$DBUSER" -D "$DBNAME" -p"$PASSWORD" < /vagrant/db/db.sql  > /vagrant/db/db.log
echo "+++ Creating and loading user table."
mysql -vu "$DBUSER" -D "$DBNAME" -p"$PASSWORD" < /vagrant/db/users.sql  > /vagrant/db/users.log

echo "+++ Setting up config"

sed -e "s/DB_NAME','bbc'/DB_NAME','$DBNAME'/" \
    -e "s/DB_USER','bbc'/DB_USER','$DBUSER'/" \
    -e "s/DB_PASS','password'/DB_PASS','$PASSWORD'/" \
    -e "s/WS_ROOT','http:\/\/localhost\/'/WS_ROOT','http:\/\/localhost:8080\/'/" \
    /vagrant/web/includes/config.php_templ > /vagrant/web/includes/config.php

rm -rf /usr/local/www/apache24/data
ln -s /vagrant/web /usr/local/www/apache24/data

if [[ ! -f /etc/php/"$PHPV"/apache2/php.ini_orig ]]
then
sudo mv /etc/php/"$PHPV"/apache2/php.ini /etc/php/"$PHPV"/apache2/php.ini_orig
fi

sed 's/;extension=pdo_mysql/extension=pdo_mysql/' /etc/php/"$PHPV"/apache2/php.ini_orig > /etc/php/"$PHPV"/apache2/php.ini

sudo systemctl restart apache2.service

cd /vagrant
if [[ -f BBCMicroFiles.zip ]] && [[ -f BBCMicroScShots.zip ]]
then
echo "++ Files already downloaded."
else
echo "+++ Getting files"
wget http://bbcmicro.co.uk/tmp/BBCMicroFiles.zip
wget http://bbcmicro.co.uk/tmp/BBCMicroScShots.zip
fi
echo "+++ Unzipping files"

[[ -d files ]] || mkdir files
[[ -d screens ]] || mkdir screens
cd files
unzip ../BBCMicroFiles.zip
cd ../screens
unzip ../BBCMicroScShots.zip

echo "+++ Copy disc images and screenshots into place"
php scripts/copyfiles.php
echo "+++ Copy jsbeeb"
cd /vagrant/web
rm -rf jsbeeb
git clone https://github.com/mattgodbolt/jsbeeb.git
cp -pr jsb/* jsbeeb
echo "+++Done!"
