<?php
include_once 'Base.class.php';

/**
 * 发送签到模板消息
 * 给今日未签到的微信公众号粉丝发送
 * @author liziqiang
 * @date 20170703
 */
class QiandaoPush extends Base {
	public function __construct(){
		parent::__construct();
	}
	
	/**
	 * 发送消息
	 * @param unknown $page 页码
	 * @param unknown $mode 模式
	 */
	public function run($page, $mode){
		$task = "qiandaopush_{$page}";
		
		$pageSize = parent::getPageSize();
		$tmpMsg = $this->getTemplateMsg($mode);
		$checkedOpenIds = $this->getTodayQdOpenids();
		$subscribers = parent::getSubscribers($mode);
		//删除已签到的openid
		$subscribers = array_diff($subscribers, $checkedOpenIds);
		// 数组分页
		$slices = array_slice ( $subscribers, ($page - 1) * $pageSize, $pageSize );
		$total_count = count($slices);
		$success_count = 0;
		for ($i = 0; $i < $total_count; $i ++){
			$openId = $slices[$i];
			$tmpMsg['touser'] = $openId;
			$result = $this->_wxApi->send_template_mssage(json_encode($tmpMsg));
			Log::_write($task, "{$openId}\t".json_encode($result) );
			if($result['errcode'] == 0){
				$success_count ++ ;
			}
		}
		
		Log::_write($task, "===============================================================================");
		Log::_write($task, "总数：{$total_count}\t成功:{$success_count}\t失败:".($total_count-$success_count));
		Log::_write($task, "===============================================================================");
	}
	
	/**
	 * 今天已签到的粉丝openid数组
	 * @return array
	 */
	private function getTodayQdOpenids(){
		$dateline = strtotime(date('Y-m-d'));		
		$sql = "select weixinid"
				." from `go123_plugin_qiandao` as qd"
				." left join `jieqi_system_userapi` as uapi on uapi.uid = qd.uid"
				." where qd.last_dateline  > {$dateline} and weixinid <> ''";
		
		$rows = $this->_db->db_getAll($sql);
		$datas = array();
		foreach ($rows as $k => $v){
			$datas[] = $v['weixinid'];
		}
		
		return $datas;
	}
	
	private function getTemplateMsg($mode){
		$templateId = '';
		if($mode == ENV_DEV ){
			$templateId = "y-xzsIUmf6MbfjJo7HYrBkGrMpswRcXidOGAi4Am9oE";
		}else if($mode == ENV_TEST ){
			$templateId = "PuRYHEkcNaphdyOBjyNbSNheOfe-FbVERcqiwgviWnM";
		}else if($mode == ENV_PRODUCT){
			$templateId = "PuRYHEkcNaphdyOBjyNbSNheOfe-FbVERcqiwgviWnM";
		}
		
		$data = array(
            'touser' => '',
            'template_id' => $templateId,
            'url' => "http://m.roaibook.com/api/weixin/login.php?jumpurl=http%3A%2F%2Fm.roaibook.com%2Fqiandao%2Fqiandao.php%3Fact%3Dqiandao",
            'topcolor' => '',
            'data' => array(
                'first' => array(
                    'value' => ("您好!\n\n"),
                    'color' => '#19ad17'
                ),
                'keyword1' => array(
                    'value' => ("\n签到领取书币，一定要记得每天签到吖，不然您可能会错过几亿免费书币\n"),
                    'color' => ''
                ),
                'keyword2' => array(
                    'value' => ("\n今晚24:00前，别忘了哦\n"),
                    'color' => ''
                ),
                'remark' => array(
                    'value' => ("点击下方「抢书币」\n".PROJECT_WEB_NAME."每天送你书币哦↓↓"),
                    'color' => '#19ad17'
                )
            )
        );
		
		return ($data);
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
$object = new QiandaoPush();
$object->run ($page, $mode);

