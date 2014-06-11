#!/bin/bash

maint.sh

cd ..;

wget https://github.com/jonathankart/sfcityfc/archive/master.zip;
unzip master.zip;
cp -rf sfcityfc-master/* .
mkdir logs;
chmod 777 logs;
date > web/REVISION;

rm master.zip;
rm -rf sfcityfc-master;

cd scripts;
unmaint.sh