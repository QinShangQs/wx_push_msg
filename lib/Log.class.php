<?php
final class Log {
	public static function _echo($msg) {
		echo date ( "Y-m-d H:i:s" ) . " " . $msg . "\n";
	}
	
	public static function _write($task, $msg) {
		$logDir = static::_dirName();
		if (! file_exists ( $logDir )) {
			mkdir ( $logDir );
		}
		$logName = $logDir.$task.".log";
		return file_put_contents ( $logName, date ( "Y-m-d H:i:s" ) . " " . $msg . "\n", FILE_APPEND );
	}
	
	public static function _dirName(){
		return PATH_LOG . date ( "Y-m-d" ) . "/";
	}
}