#!/bin/bash

maint.sh

cd ..;
git pull;
composer update;
chmod 777 logs;

echo > web/REVISION

cd scripts;
unmaint.sh