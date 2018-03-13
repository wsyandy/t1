#!/bin/bash

echo 'websocket start'
php cli.php websocket start

echo "process num"
ps aux | grep websocket | wc -l