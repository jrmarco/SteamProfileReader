<?php
# Php config:
ini_set('display_errors',1);
ini_set('error_reporting', E_ALL);
ini_set('error_log','/errors.log');
ini_set('log_errors','On');
# SQL Verbose debug log
define('DEBUG',1);

# Database configurations
define('DBDEAMON','mysql');
// PostgreSQL
//define('DBDEAMON','pgsql');
define('DBHOST',"<db_host>");
define('DBUSER',"<db_user>");
define('DBPSW',"<db_user_password>");
define('DBNAME',"<db_name");
define('TABLEPREFIX',"<table_prefix>");