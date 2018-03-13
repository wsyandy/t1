#!/bin/bash

echo 'websocket stop'
php cli.php websocket stop

echo "process num"
ps aux | grep websocket | wc -l