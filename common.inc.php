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
define('CACHE_COMPRESSION_RATE', 9);
define('CACHE_COMPRESSED', true);

define('LOG_PATH',"log/");
define('EXCEPTION_GCG_REDIS_FAILURE', '90050');

require_once 'logger.class.php';
require_once 'db_redis.class.php';
require_once 'Threading.class.php';

?>