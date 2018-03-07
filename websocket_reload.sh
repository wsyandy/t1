#!/bin/bash

echo 'websocket reload'
php cli.php websocket reload

echo "process num"
ps aux | grep websocket | wc -l