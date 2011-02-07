#!/bin/sh
cd /home/baraoto/volatile-twit-trunk/batch/
echo "" > min10.log
/usr/local/bin/php crawl_status.php >> min10.log
