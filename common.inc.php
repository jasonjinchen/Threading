<?php

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-type: text/html; charset=utf-8");

ini_set('display_errors', true);
error_reporting(E_ERROR);
ignore_user_abort(true);
set_time_limit(0);

define('IN_AUTH', true);

define("DEFAULT_SLICE_SIZE",32);
define("DEFAULT_MAX_THREAD",128);
define("DEFAULT_PHRASE_NAME","COMMON_PHRASE");

define("LOG_DISABLED",false);
define('LOG_PATH',"log/");
define("INFO_LOG","INFO");
define("ERR_LOG","ERROR");

require_once 'logger.class.php';
require_once 'threading.class.php';
require_once 'mapreduce.class.php';

?>