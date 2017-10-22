<?php
include_once 'config.php';

Log::_echo("PROJECT_WEB_NAME = ". PROJECT_WEB_NAME);
Log::_echo("IS_ON_LINE = ". IS_ON_LINE);
Log::_echo("ENV_NAME = ". ENV_NAME);
Log::_echo("PATH_ROOT = ". PATH_ROOT);
Log::_echo("PATH_LOG = ". PATH_LOG);
Log::_echo("PATH_LIB = ". PATH_LIB);
Log::_echo("HOST = ". HOST);
Log::_echo("USER = ". USER);
Log::_echo("PWD = ". PWD);
Log::_echo("DB_NAME = ". DB_NAME);
Log::_echo("WEIXIN_APPID = ". WEIXIN_APPID);
Log::_echo("WEIXIN_APPSECRET = ". WEIXIN_APPSECRET);
Log::_echo('');
testLog();
testDB();
Log::_echo('');
testWx();
Log::_echo('');
testWxSendCustMsg();

function testLog(){
	$result = Log::_write('test', 'create log file');
	Log::_echo($result ? 'log created is successful!':'log created is fail!');
}

function testDB(){
	$dbConfig = array(
			'host' => HOST,
			'user' => USER,
			'pass' => PWD,
			'dbname' => DB_NAME
	);
	$db = new DB($dbConfig);
	$row = $db->db_getRow("select * from jieqi_system_users order by uid desc limit 1;");

	Log::_echo($row ?  'databases connnection is successful!':'databases connnection is fail!');
}

function testWx(){
	$wxApi = new WechatApi(WEIXIN_APPID, WEIXIN_APPSECRET, PATH_LOG);
	$access_token = $wxApi->reget_access_token(array('errcode' => 40001), '');
	Log::_echo($access_token ? "access_token is {$access_token}":"get access token is fail!");
}

function testWxSendCustMsg(){
	$data = array(
			'touser' => WX_T_OPID,
			'msgtype' => "text",
			'text' => array(
					"content"=>"我在test.php `".(ENV_NAME)."` 进行 活动\n"	
			)
	);
	
	$wxApi = new WechatApi(WEIXIN_APPID, WEIXIN_APPSECRET, PATH_LOG);
	$result = $wxApi->send_custom_msg(_json_encode_ex($data));
	Log::_echo("openid = " .WX_T_OPID." send customer message result is :" . json_encode($result));
}





