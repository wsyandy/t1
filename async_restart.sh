php cli.php async stop

sleep 3

echo "stop process num"
ps aux | grep 'async start' |grep -v grep | wc -l

php cli.php async start >> log/async.log

echo "start process num"
ps aux | grep 'async start' |grep -v grep | wc -l