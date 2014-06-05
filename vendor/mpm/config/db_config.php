<?php


require_once __DIR__."/../../../conf/dev/db.php";

$db_config = (object) array();
$db_config->host = $sfcityconfig->db['host'];
$db_config->port = $sfcityconfig->db['port'];;
$db_config->user = $sfcityconfig->db['user'];;
$db_config->pass = $sfcityconfig->db['password'];
$db_config->name = $sfcityconfig->db['dbname'];;
$db_config->db_path = __DIR__."/../../../sql/";
$db_config->method = 2;
$db_config->migrations_table = 'mpm_migrations';

?>