<?php

error_reporting(E_ERROR);
date_default_timezone_set('America/Los_Angeles');

// add conf params as needed
// $SFCITY_CONFIG->some->thing = 'a value';

define('SFCITY_ROOT_PATH',realpath(__DIR__."/..")."/");

if(file_exists(SFCITY_ROOT_PATH."public_html/")){
	// this is the prod configuration
	$SFCITY_CONFIG->web_dir = SFCITY_ROOT_PATH."public_html/";
}else{
	$SFCITY_CONFIG->web_dir = SFCITY_ROOT_PATH."web/";
}


$SFCITY_CONFIG->log_dir = SFCITY_ROOT_PATH."logs/";
$SFCITY_CONFIG->cache_dir = SFCITY_ROOT_PATH."cache/";
$SFCITY_CONFIG->documents_dir = $SFCITY_CONFIG->web_dir."docs/";

$SFCITY_CONFIG->mailchimp->log = $SFCITY_CONFIG->log_dir."mailchimp.log";

$SFCITY_CONFIG->calendar->feed = 'http://www.google.com/calendar/feeds/sfcityfc.com_s0qunhs03spt448ukbr1n19cbg%40group.calendar.google.com/public/full-noattendees?futureevents=true';
$SFCITY_CONFIG->calendar->cache_time = "20 minutes";


