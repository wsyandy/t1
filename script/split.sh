#!/bin/bash
source /etc/profile

# nginx
cd /usr/local/system/log
cat error.log > error_`date -d '-1 day' +%Y%m%d`.log
echo -n > error.log
rm -f error_`date -d '-7 day' +%Y%m%d`.log

#php
cd /usr/local/system/log

cat php_errors.log > php_errors_`date -d '-1 day' +%Y%m%d`.log
echo -n > php_errors.log
rm -f php_errors_`date -d '-7 day' +%Y%m%d`.log

#fpm
cd /usr/local/system/log

cat php-fpm.log > php-fpm_`date -d '-1 day' +%Y%m%d`.log
echo -n > php-fpm.log
rm -f php-fpm_`date -d '-7 day' +%Y%m%d`.log

cd /usr/local/system/chance_php/log

cat production.log > production_`date -d '-1 day' +%Y%m%d`.log
echo -n > production.log
rm -f production_`date -d '-15 day' +%Y%m%d`.log

cat async.log > async_`date -d '-1 day' +%Y%m%d`.log
echo -n > async.log
rm -f async_`date -d '-15 day' +%Y%m%d`.log

cat websocket_server.log > websocket_server_`date -d '-1 day' +%Y%m%d`.log
echo -n > websocket_server.log
rm -f websocket_server_`date -d '-15 day' +%Y%m%d`.log
