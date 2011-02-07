#!/bin/sh
cd /home/baraoto/volatile-twit-trunk/batch/
echo "" > hour1.log
/usr/local/bin/php talk.php >> hour1.log
