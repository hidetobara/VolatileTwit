#!/bin/sh
# gzip status log
base=`pwd`
datestr=`date -v-1d +%Y%m%d`
dir=/home/baraoto/VolatileTwit/log/status/
cd $dir 
/usr/bin/gzip ${datestr}.log
cd $base
