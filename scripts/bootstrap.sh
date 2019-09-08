DBNAME=bbcmicro
DBUSER=bbcmicro
PHPV="7.2"

PASSWORD=$(LC_ALL=C tr -dc 'A-Za-z0-9!#$%+,-.:;<=>?@^_~' </dev/urandom | head -c 13 )

echo "+++ Installing packages "

export DEBIAN_FRONTEND=noninteractive
apt-get update
apt-get install -y apache2 php mariadb-server php-mysql unzip

echo "Password is $PASSWORD"
echo "+++ Setting up the databse"
sudo mysql -u root <<-ENDSQL
drop user if exists ${DBUSER};
drop database if exists ${DBNAME};
create database bbcmicro;
grant all privileges on ${DBNAME}.* to ${DBUSER}@localhost identified by '$PASSWORD';
use ${DBNAME};
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

rm -rf /var/www/html
ln -s /vagrant/web /var/www/html

if [[ ! -f /etc/php/"$PHPV"/apache2/php.ini_orig ]]
then
sudo mv /etc/php/"$PHPV"/apache2/php.ini /etc/php/"$PHPV"/apache2/php.ini_orig
fi

sed 's/;extension=pdo_mysql/extension=pdo_mysql/' /etc/php/"$PHPV"/apache2/php.ini_orig > /etc/php/"$PHPV"/apache2/php.ini

sudo systemctl restart apache2.service

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
unzip -f ../BBCMicroFiles.zip
cd ../screens
unzip -f ../BBCMicroScShots.zip

echo "+++ Copy disc images and screenshots into place"
php scripts/copyfiles.php
echo "+++ Copy jsbeeb"
cd /vagrant/web
git clone https://github.com/mattgodbolt/jsbeeb.git
cp -pr jsb/* jsbeeb
echo "+++Done!"
