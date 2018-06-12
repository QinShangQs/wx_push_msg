<?php
include_once 'config.php';

/**
 * 基类
 * @author liziqiang
 * @date 2017-06-26
 */
class Base {
	protected $_db = null;
	protected $_wxApi = null;
	
	protected $_console_domain = "http://mcc.hrwq.com/";
	
	public function __construct(){
		$dbConfig = array(
				'host' => HOST,
				'user' => USER,
				'pass' => PWD,
				'dbname' => DB_NAME,
                                'port' => defined('PORT') ? PORT : 3306
		);
		$this->_db = new DB($dbConfig);
		
		$this->_wxApi = new WechatApi(WEIXIN_APPID, WEIXIN_APPSECRET, PATH_LOG);
	}
	
	/**
	 * 获取对应的所有关注粉丝的openid
	 * @param string $mode dev test product
	 * @return multitype:
	 */
	public function getSubscribers($mode){
		$subscribers = array();
		if($mode == ENV_TEST){
			$subscribers = _mode_test_openids();
		}else if($mode == ENV_DEV ){
			$subscribers = _mode_dev_openids();
		}else{
			$i = 0;
			do {
				$subscribers = $this->_wxApi->get_all_user_openids ();
				if (count ( $subscribers ) > 0) {
					break;
				} else {
					$i ++;
					sleep ( 10 );
					$this->_wxApi->reget_access_token ( array (
							'errcode' => 40001 
					) );
					$access_token = $this->_wxApi->get_access_token (); // 保险
				}
			} while ( $i < 10 );
		}
	
		return $subscribers;
	}
	
	/**
	 * 分页数，和php cli执行任务数直接关联
	 * 若修改此处请求该定时任务中的php cli任务
	 * @return number
	 */
	protected function getPageSize() {
		return IS_ON_LINE ? 10000 : 2;
	}
	
	
}