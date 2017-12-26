#!/bin/bash

echo 'git pull'
git pull

echo 'delete cache file';
rm -f app/cache/volt/*.php

echo 'restart async'
./async_restart.sh

echo update_at_`date  +%Y%m%d+%H%M` >> update.log;