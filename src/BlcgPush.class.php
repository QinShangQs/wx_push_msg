<?php
include_once 'Base.class.php';
/**
 * 办理成功通知，只用一次
 * @author qinshang
 * @date 20180110
 */
class BlcgPush extends Base {
	public function __construct(){
		parent::__construct();
	}
	
	public function run($page, $mode){
		$task = "blcg_{$page}_{$mode}";
		
		$openids = $this->getOpenIds($mode);
		$tmpMsg = $this->getTemplateMsg($mode);
		$total_count = count($openids);
		$success_count = 0;
		
		for($i = 0; $i < $total_count; $i ++){
			$openId = $openids[$i];
			$tmpMsg['touser'] = $openId;
			$result = $this->_wxApi->send_template_mssage(json_encode($tmpMsg));
			Log::_write($task, "{$openId}\t".json_encode($result) );
			if($result['errcode'] == 0){
				$success_count ++ ;
			}
			
			Log::_write($task, "===============================================================================");
			Log::_write($task, "总数：{$total_count}\t成功:{$success_count}\t失败:".($total_count-$success_count));
			Log::_write($task, "===============================================================================");
		}
	}
	
	private function getOpenIds($mode){
		if($mode == ENV_DEV || $mode == ENV_TEST){
			return array(WX_T_OPID);
		}
		
		$sql = "select openid from `user` where vip_flg = 2;";
		$rows = $this->_db->db_getAll($sql);
		$datas = array();
		foreach ($rows as $k => $v){
			$datas[] = $v['openid'];
		}
		
		return $datas;
	}
	
	private function getTemplateMsg($mode){
		$templateId = null;
		if($mode == ENV_DEV ){
			$templateId = "4gUv9ztGaRfm3WXJ6XeC5_wRk_gbyelouweuP1GKfjM";
		}else{
			$templateId = "GG3R3N26F5bTtSAz0E8Wk1Q_KZPQAOaoW0LXpnAs5X0";
		}
		
		$data = array(
				'touser' => '',
				'template_id' => $templateId,
				'url' => "http://m.hrwq.com/vip/records?from=singlemessage",
				'topcolor' => '',
				'data' => array(
						'first' => array(
								'value' => ("亲爱的和会员，因服务器升级导致本周一的课程更新延迟，特送您30天会员补偿，目前会员补偿已到账！\n"),
								'color' => ''
						),
						'keyword1' => array(
								'value' => ("VIP和会员\n"),
								'color' => ''
						),
						'keyword2' => array(
								'value' => ("2018-1-10\n"),
								'color' => ''
						),
						'remark' => array(
								'value' => ("点此查看我的会员天数！"),
								'color' => ''
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
$object = new BlcgPush();
$object->run ($page, $mode);
