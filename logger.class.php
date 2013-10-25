<?php

if (!defined('IN_AUTH')) {
	exit('Access Denied');
}

define("INFO_LOG","INFO");
define("ERR_LOG","ERROR");

class T {

	public static function clearAll(){
		self::clear(INFO_LOG);
		self::clear(ERR_LOG);
	}
	
	public static function clear($type){
		if(file_exists($type)){
			unlink(LOG_PATH.$type);
		}
	}
	
	private static function log($tid,$info,$type){
		if($tid===null){
			$tid="MAIN";
		}
		file_put_contents(LOG_PATH.$type, date("[YmdHis]")."\t ".$tid."\t ".$info."\n",FILE_APPEND);
	}
	
	public static function i($tid,$info){
		self::log($tid, $info, INFO_LOG);
	}
	
	public static function e($tid,$info){
		self::log($tid, $info, ERR_LOG);
	}
	
}

?>