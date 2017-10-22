<?php
include_once 'Base.class.php';

/**
 * 发送推送消息
 * 读取定时发送信息，给所有微信关注者推送客服消息
 * @author liziqiang
 * @date 20170626
 */
class WechatPush extends Base {
	public function __construct() {
		parent::__construct ();
	}
	
	/**
	 * 发送消息
	 * @param unknown $page 页码
	 * @param unknown $mode 模式
	 */
	public function run($page, $mode) {
		$push_time = date ( "Y-m-d_H" );
		$task = $push_time."_{$page}";

		$dbMessage = $this->getDbMessage ($push_time);
		if (empty ( $dbMessage )) {
			Log::_write($task, 'push items empty');
			exit ();
		}
		
		$pageSize = parent::getPageSize(); 
		$subscribers = parent::getSubscribers($mode);
		// 数组分页
		$slices = array_slice ( $subscribers, ($page - 1) * $pageSize, $pageSize );
		$total_count = count ( $slices );
		$success_count = 0;
		for($i = 0; $i < $total_count; $i ++) {
			$openId = $slices[$i];
			
			$sendMsg = $this->forSendMsg($openId, $dbMessage, $push_time);
			$result = $this->_wxApi->send_custom_msg($sendMsg);
			Log::_write($task, "{$openId}\t".json_encode($result) );
			if($result['errcode'] == 0){
				$success_count ++ ;
			}
		}
		
		Log::_write($task, "===============================================================================");
		Log::_write($task, "总数：{$total_count}\t成功:{$success_count}\t失败:".($total_count-$success_count));
		Log::_write($task, "===============================================================================");
		
		$updateRusult = $this->updateDbMessageSended($dbMessage['id'], $total_count, $success_count);
		Log::_write($task, "更新数据库记录数：" .($updateRusult ? '成功': '失败'));
	}
	
	private function getDbMessage($push_time) {	
		$row = $this->_db->db_getRow ( "select * from `wechat_push` where `push_time` = '{$push_time}' limit 1" );
		return $row;
	}
	
	private function updateDbMessageSended($id, $sended_total, $sended_success){
		$sql = "update `wechat_push` set send_total = (send_total + {$sended_total}) , send_success = (send_success + {$sended_success})"
				. " where id = {$id}";
		return $this->_db->db_update($sql);
	}
	
	private function forSendMsg($openId, $dbMessage, $push_time) {
		$item = array();
		$item['title'] = html_entity_decode($dbMessage['title']);
		$item['url'] = (stripos ($dbMessage['url'], "?") ? $dbMessage['url'] : ($dbMessage['url']."?") ) ."&pid=ra&v=".$push_time;
		$item['picurl'] = $this->_console_domain . $dbMessage['picurl'];
		$item['description'] = $dbMessage['description'];
		
		$sendMsg = array (
				'touser' => $openId,
				'msgtype' => "news",
				'news' => array (
					'articles' => array ($item) 
				) 
		);
		
		return _json_encode_ex($sendMsg);
	}
}

//获取shell参数
$page = $argv[1];
$mode = $argv[2];

if(empty($page) || !is_numeric($page)){
	throw new Exception('请正确传入页码!');
}
// 模式验证
_mode_validate($mode);

//运行主函数
$object = new WechatPush ();
$object->run ($page, $mode);