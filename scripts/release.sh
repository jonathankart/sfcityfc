#!/bin/bash

maint.sh

cd ..;

wget --no-check-certificate https://github.com/jonathankart/sfcityfc/archive/master.zip;
unzip master.zip;
cp -rf sfcityfc-master/* .
mkdir logs;
chmod 777 logs;
date > web/REVISION;

# this effectively unmaint's the site
cp -rf web/* public_html/;

rm -rf web;
rm master.zip;
rm -rf sfcityfc-master;

cd scripts;
