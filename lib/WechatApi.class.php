<?php
class WechatApi {
	private $appid = "";
	private $appsecret = "";
	private $tokenLog = "";
	
	/**
	 * 
	 * @param unknown $_appid
	 * @param unknown $_appsecret
	 * @param unknown $_token_log_dir 必须以“/“结尾
	 * @throws Exception
	 */
	public function __construct($_appid, $_appsecret, $_token_log_dir) {
		$this->appid = $_appid;
		$this->appsecret = $_appsecret;
		$this->tokenLog = $_token_log_dir ."access_token.json";
		
		if (empty ( $this->appid ) || empty ( $this->appsecret )) {
			throw new Exception ( 'Appid或Appsecret为空', - 100 );
			exit ();
		}
	}

	private function http_request($url, $data = null) {
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
		if (! empty ( $data )) {
			curl_setopt ( $ch, CURLOPT_POST, 1 );
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
		}
		
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		$output = curl_exec ( $ch );
		curl_close ( $ch );
		$jsoninfo = json_decode ( $output, true );
		return $jsoninfo;
	}
	
	/**
	 * 获取access_token
	 *
	 * @return mixed
	 */
	public function get_access_token() {
		$json_file = $this->tokenLog;
		$data = file_exists ( $json_file ) ? json_decode ( file_get_contents ( $json_file ) ) : json_decode ( '{"expire_time":0,"access_token":""}' );
		$access_token = '';
		if ($data->expire_time < time ()) {
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $this->appid . "&secret=" . $this->appsecret;
			$res = $this->http_request ( $url );
			$access_token = $res ['access_token'];
			if ($access_token) {
				$data->expire_time = time () + 7000;
				$data->access_token = $access_token;
				$fp = fopen ( $json_file, "w" );
				fwrite ( $fp, json_encode ( $data ) );
				fclose ( $fp );
			}
		} else {
			$access_token = $data->access_token;
		}
		return $access_token;
	}
	
	public function unlink_access_token(){
		@unlink($this->tokenLog);
	}
	
	/**
	 * 获取用户分组
	 *
	 * @param unknown $access_token        	
	 * @return mixed
	 */
	public function get_groups($access_token) {
		$url = "https://api.weixin.qq.com/cgi-bin/groups/get?access_token={$access_token}";
		return $this->http_request ( $url, $data );
	}
	
	/**
	 * 判断是否重新获取access token
	 *
	 * @param unknown $jsonResult
	 *        	执行接口返回的json对象
	 * @param unknown $old_access_token
	 *        	老的access token
	 * @return access token
	 */
	public function reget_access_token($jsonResult, $old_access_token) {
		if ($jsonResult ['errcode'] == 40001 || $jsonResult ['errcode'] == 42001) {
			$this->unlink_access_token();
			$old_access_token = $this->get_access_token ();
		}
		return $old_access_token;
	}
	
	/**
	 * 发送模板消息
	 *
	 * @param unknown $data
	 *        	需要经过json_encode传递
	 * @param unknown $access_token
	 *        	来自get_access_token()
	 * @return json对象
	 */
	function send_template_mssage($data) {
		$access_token = $this->get_access_token();
		$url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$access_token}";
		$result = $this->http_request ( $url, $data );
                $this->reget_access_token($result, $access_token);
                return $result;
	}
	
	/**
	 * 获取所有已关注用户的openid
	 *    	
	 * @return array
	 */
	function get_all_user_openids() {
		$access_token = $this->get_access_token();
		$subscribers = array ();
		$next_openid = '';
		while ( true ) {
			$url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token={$access_token}&next_openid={$next_openid}";
			$result = $this->http_request ( $url );
			if (empty ( $result ) || empty ( $result ['count'] ) || empty ( $result ['data'] ['openid'] )) {
				break;
			}
			$subscribers = array_merge ( $subscribers, $result ['data'] ['openid'] );
			$next_openid = $result ['next_openid'];
		}
		return $subscribers;
	}
	/**
	 * 获取用户信息
	 * @param unknown $openid
	 * @return mixed
	 */
	public function get_user_info($openid) {
		$access_token = $this->get_access_token();
		$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$access_token}&openid={$openid}&lang=zh_CN";
		$result = $this->http_request ( $url );
		return $result;
	}
	
	/**
	 * 发送客服消息
	 *
	 * @param string $data
	 *        	经过json_encode
	 * @return 返回发送结果
	 */
	public function send_custom_msg($data) {
		$access_token = $this->get_access_token ();
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $access_token;
		$result = $this->http_request ( $url, $data );
                $this->reget_access_token($result, $access_token);
                return $result;
	}
	
}