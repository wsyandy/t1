#!/bin/bash

echo "Reloading..."
cmd=$(pidof reload_master)
echo $cmd
kill -USR1 $cmd
echo "Reloaded"
echo "process num"
ps aux | grep websocket | wc -l