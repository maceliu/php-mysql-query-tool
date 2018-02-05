<?php
error_reporting(E_ALL);
ini_set('date.timezone','Asia/Shanghai');  //设置时区
//数据库连接配置信息数组
$database_config_arr = array();
$database_config_arr['localhost']['php_test']['host'] 	 = '127.0.0.1';
$database_config_arr['localhost']['php_test']['port'] 	 = '';
$database_config_arr['localhost']['php_test']['user'] 	 = 'sdb_user';
$database_config_arr['localhost']['php_test']['pass'] 	 = '';
$database_config_arr['localhost']['php_test']['db'] 	 = 'db_name';
$database_config_arr['localhost']['php_test']['charset'] = 'utf8';



?>