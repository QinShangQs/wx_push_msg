<?php

/**
 * 
 * 会员开通测试通知，只用一次
 * php VipSuccess.class.php dev
 * @author qyd
 */
die('注销本行后运行' . PHP_EOL);

include_once 'Base.class.php';

class VipSuccess extends Base {

    public function run($mode) {
        $task = "vipSuccess_{$mode}";

        $users = $this->_getUsers();
        $count = count($users);
        $tmpMsg = $this->getTemplateMsg($mode);

        for ($i = 0; $i < $count; $i++) {
            $openId = $users[$i];
            $tmpMsg['touser'] = $openId;
            $result = $this->_wxApi->send_template_mssage(json_encode($tmpMsg));
            
            Log::_write($task, "{$i} / {$count} \t" . json_encode($result, JSON_UNESCAPED_UNICODE));
        }
    }

    private function _getUsers() {
        return array('ot3XZt41_M-OX9ihvC0_w05DU68Q', 'ot3XZtyEcBJWjpXJxxyqAcpBCdGY','obpqNs_GdrHPLOGJig50qNcFZRGk');
    }

    private function getTemplateMsg($mode) {
        $templateId = '';
        if ($mode == ENV_DEV) {
            $templateId = "FIR7wZ8gw5CoxwhIiKNXl1xXlNzZntUh5n-ngV0xFWs";
        } else if ($mode == ENV_TEST) {
            $templateId = "7hXsOVA4WE3nGyta1UQRqUOtDP6C1D5ymR-E46_X1Ts";
        } else if ($mode == ENV_PRODUCT) {
            $templateId = "7hXsOVA4WE3nGyta1UQRqUOtDP6C1D5ymR-E46_X1Ts";
        }

        $data = array(
            'touser' => '',
            'template_id' => $templateId,
            'url' => "http://m.hrwq.com/user/profile/edit",
            'topcolor' => '#f7f7f7',
            'data' => array(
                'first' => array(
                    'value' => ("和会员开通提示!"),
                    'color' => ''
                ),
                'keyword1' => array(
                    'value' => ("和会员"),
                    'color' => ''
                ),
                'keyword2' => array(
                    'value' => (date('Y-m-d H:i:s')),
                    'color' => ''
                ),
                'keyword3' => array(
                    'value' => ("恭喜你成功加入和润万青父母学院"),
                    'color' => ''
                ),
                'remark' => array(
                    'value' => ("点击此处立即完善个人信息，客服老师会在1-2个工作日内，联系您开通直播权限" ),
                    'color' => ''
                )
            )
        );

        return ($data);
    }

}


//获取shell参数
$mode = $argv[1];

// 模式验证
_mode_validate($mode);

//运行主函数
$object = new VipSuccess();
$object->run($mode);

