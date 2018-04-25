#!/bin/bash
source /etc/profile

cd /usr/local/system/chance_php

/usr/local/bin/php cli.php good_no generate_no user

/usr/local/bin/php cli.php good_no generate_no room
