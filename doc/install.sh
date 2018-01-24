#!/bin/bash
yum install  -y gcc gcc-c++ git zip unzip readline-devel libevent-devel zlib-devel iptraf libmcrypt libmcrypt-devel mcrypt mhash openssl-devel curl-devel bison autoconf libxml2-devel libedit-devel ImageMagick re2c pcre-devel  pcre-devel libpng-devel libjpeg-devel freetype-devel psmisc

mkdir /usr/local/system/log

wget https://ftp.postgresql.org/pub/source/v9.6.0/postgresql-9.6.0.tar.gz

tar -zxvf postgresql-9.6.0.tar.gz
cd postgresql-9.6.0
./configure
make
make install
cd ..

wget https://pgbouncer.github.io/downloads/files/1.7.2/pgbouncer-1.7.2.tar.gz
tar zxvf pgbouncer-1.7.2.tar.gz
cd pgbouncer-1.7.2
./configure
make
make install
cd ..

wget http://nginx.org/download/nginx-1.12.2.tar.gz
tar -zxvf nginx-1.12.2.tar.gz
cd nginx-1.12.2
./configure --prefix=/usr/local/nginx --with-http_ssl_module --with-http_gzip_static_module --with-http_stub_status_module --with-http_sub_module
make
make install
cd ..

wget http://cn2.php.net/distributions/php-7.2.1.tar.gz
tar -zxvf php-7.2.1.tar.gz
cd php-7.2.1
./configure --with-pgsql --with-pdo-pgsql --with-openssl --with-mcrypt --enable-zip --enable-mbstring --enable-fpm --enable-opcache  --with-curl --with-readline --enable-pcntl   --enable-sysvmsg  --with-zlib --with-gd --with-jpeg-dir  --with-png-dir --with-freetype-dir
make
make install
cd ..

wget https://github.com/phalcon/cphalcon/archive/v3.1.2.tar.gz -O cphalcon-3.1.2.tar.gz
tar -zxvf cphalcon-3.1.2.tar.gz
cd cphalcon-3.1.2/build
./install
cd ../..


wget https://github.com/phpredis/phpredis/archive/3.1.2.tar.gz -O phpredis-3.1.2.tar.gz
tar -zxvf phpredis-3.1.2.tar.gz
cd phpredis-3.1.2
phpize
./configure
make
make install
cd ..

git clone https://github.com/jonnywang/phpssdb
cd phpssdb
git checkout php7
phpize
./configure
make
make install
cd ..


curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

wget --no-check-certificate https://github.com/ideawu/ssdb/archive/master.zip  -O ssdb.zip
unzip ssdb.zip
cd ssdb-master/
make
make install
cd ..

wget http://download.redis.io/releases/redis-3.2.4.tar.gz
tar -zxvf redis-3.2.4.tar.gz
cd redis-3.2.4
make
make install
