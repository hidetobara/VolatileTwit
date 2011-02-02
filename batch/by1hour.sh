#!/bin/sh
cd /home/baraoto/volatile-twit-trunk/batch/
echo "" > hour.log
/usr/local/bin/php talk.php >> hour.log
