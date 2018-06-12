<?php
include_once 'Base.class.php';
/**
 * 预约成功提醒
 * @author qinshang
 * @date 20180110
 */
class YuyuePush extends Base {
	public function __construct(){
		parent::__construct();
	}
	
	public function run($page, $mode){
		$task = "yuyue_{$page}_{$mode}";
		
                $openids = parent::getSubscribers($mode);
		$tmpMsg = $this->getTemplateMsg($mode);
		$total_count = count($openids);
		$success_count = 0;
		
		for($i = 0; $i < $total_count; $i ++){
			$openId = $openids[$i];
			$tmpMsg['touser'] = $openId;
			$result = $this->_wxApi->send_template_mssage(json_encode($tmpMsg));
			Log::_write($task, "{$i}/{$total_count}:{$openId}\t".json_encode($result) );
			if($result['errcode'] == 0){
				$success_count ++ ;
			}
		}
                
                Log::_write($task, "===============================================================================");
		Log::_write($task, "总数：{$total_count}\t成功:{$success_count}\t失败:".($total_count-$success_count));
		Log::_write($task, "===============================================================================");
	}

	
	private function getTemplateMsg($mode){
		$templateId = null;
		if($mode == ENV_DEV ){
			$templateId = "PkByMzb2wJq5KN5q47BJCoZFPgxRgTxHdOAnKl53BHA";
		}else{
			$templateId = "QMzY4llcOtuzacTErWVgIKYVOHJ3OwK8eGwb8abspPQ";
		}
		
		$data = array(
				'touser' => '',
				'template_id' => $templateId,
				'url' => "http://m.hrwq.com/vcourse/detail/67",
				'topcolor' => '',
				'data' => array(
						'first' => array(
								'value' => ("本周和会员新课上线通知\n"),
								'color' => '#0000cc'
						),
						'keyword1' => array(
								'value' => ("和孩子有效约定的3项要点\n"),
								'color' => '#0000cc'
						),
						'keyword2' => array(
								'value' => ("范欣园\n"),
								'color' => '#0000cc'
						),
                                                'keyword3' => array(
								'value' => ("每次和孩子的约定都不了了之，拿他一点办法也没有。把握有效约定的三项要点，与孩子轻松、有效建立约定。\n"),
								'color' => '#0000cc'
						),
                                                'keyword4' => array(
								'value' => ("2018-6-11 19:00\n"),
								'color' => '#0000cc'
						),
						'remark' => array(
								'value' => ("点击进入课程页面，收听最新课程！"),
								'color' => '#0000cc'
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
$object = new YuyuePush();
$object->run ($page, $mode);