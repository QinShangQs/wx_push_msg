<?php

include_once 'Base.class.php';

/**
 * 预约成功提醒
 * php YuyuePush.class.php 1 dev
 * @author qinshang
 * @date 20180110
 */
class YuyuePush extends Base {

    public function __construct() {
        parent::__construct();
    }

    public function run($page, $mode) {
        $task = "yuyue_{$page}_{$mode}";

        $openids = $this->getOpenIds($mode);
        $tmpMsg = $this->getTemplateMsg($mode);
        $total_count = count($openids);
        $success_count = 0;

        for ($i = 0; $i < $total_count; $i ++) {
            $openId = $openids[$i];
            $tmpMsg['touser'] = $openId;
            $result = $this->_wxApi->send_template_mssage(json_encode($tmpMsg));
            Log::_write($task, "{$i}/{$total_count}:{$openId}\t" . json_encode($result));
            if ($result['errcode'] == 0) {
                $success_count ++;
            }
        }

        Log::_write($task, "===============================================================================");
        Log::_write($task, "总数：{$total_count}\t成功:{$success_count}\t失败:" . ($total_count - $success_count));
        Log::_write($task, "===============================================================================");
    }
    
    private function getOpenIds($mode) {
        $sql = "select openid, id, nickname,mobile,realname from `user` where vip_flg = 2";
        if($mode != ENV_PRODUCT){
            $sql .= " and openid in ('ot3XZtyEcBJWjpXJxxyqAcpBCdGY','obpqNs_GdrHPLOGJig50qNcFZRGk')";
        }
        $rows = $this->_db->db_getAll($sql);
        $datas = array();
        foreach ($rows as $k => $v) {
            $datas[] = $v['openid'];
        }
        return $datas;
    }

    private function getTemplateMsg($mode) {
        $templateId = null;
        if ($mode != ENV_PRODUCT) {
            $templateId = "_xREyt32ZoWlQjbPIKlNIGjKh8CmdwLiJ0DBRoPpYos";
        } else {
            $templateId = "tU28-BPSh2lkO787wsd071N4L_pMpHHH1XWvQOcgmhM";
        }

        $data = array(
            'touser' => '',
            'template_id' => $templateId,
            'url' => "http://m.hrwq.com/vcourse/detail/70",
            'topcolor' => '',
            'data' => array(
                'first' => array(
                    'value' => ("本周和会员新课即将上线\n"),
                    'color' => '#0000cc'
                ),
                'keyword1' => array(
                    'value' => ("如何走近孩子的心？\n"),
                    'color' => '#0000cc'
                ),
                'keyword2' => array(
                    'value' => ("走进孩子的心，指导思想+特别强调，距离不再是距离。\n"),
                    'color' => '#0000cc'
                ),
                'keyword3' => array(
                    'value' => ("范欣园老师\n"),
                    'color' => '#0000cc'
                ),
                'keyword4' => array(
                    'value' => ("2018-7-2\n"),
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

if (empty($page) || !is_numeric($page)) {
    throw new Exception('请正确传入页码!');
}
// 模式验证
_mode_validate($mode);

//运行主函数
$object = new YuyuePush();
$object->run($page, $mode);
