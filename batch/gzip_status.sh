#!/bin/sh
# gzip status log
base=`pwd`
datestr=`date -d '1 days ago' +%Y%m%d`
dir=/home/baraoto/VolatileTwit/log/status/
cd $dir
/usr/bin/gzip ${datestr}.json
cd $base
