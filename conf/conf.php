<?php

// add conf params as needed
// $SFCITY_CONFIG->some->thing = 'a value';

define('SFCITY_ROOT_PATH',realpath(__DIR__."/..")."/");


$SFCITY_CONFIG->mailchimp->log = SFCITY_ROOT_PATH."logs/mailchimp.log";


