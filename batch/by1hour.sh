#!/bin/sh
cd /home/baraoto/volatile-twit-trunk/batch/
cat "by1hour\n" > hour.log
/usr/local/bin/php talk.php >> hour.log
