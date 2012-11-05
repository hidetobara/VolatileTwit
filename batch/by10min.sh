#!/bin/sh
cd /home/baraoto/VolatileTwit/batch/
echo "" > min10.log
/usr/local/bin/php crawl_status.php >> min10.log
/usr/local/bin/php talk.php >> min10.log