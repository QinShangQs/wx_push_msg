<?php

include_once 'Base.class.php';
/**
 * 办理成功通知，只用一次
 * @author qinshang
 * @date 20180110
 */
class BlcgPush extends Base {

    private $task = "";
    public function __construct() {
        parent::__construct();
    }

    const AWARD_DAY = 21;
    const AWARD_SOURCE = 6;

    public function run($page, $mode) {
        $this->task = "blcg_{$page}_{$mode}";

        $openids = $this->getOpenIds($mode);
        $tmpMsg = $this->getTemplateMsg($mode);
        $total_count = count($openids);
        $success_count = 0;

        for ($i = 0; $i < $total_count; $i ++) {
            $openId = $openids[$i];
            $tmpMsg['touser'] = $openId;
            $result = $this->_wxApi->send_template_mssage(json_encode($tmpMsg));
            Log::_write($this->task, "{$openId}\t" . json_encode($result));
            if ($result['errcode'] == 0) {
                $success_count ++;
            }

            Log::_write($this->task, "===============================================================================");
            Log::_write($this->task, "总数：{$total_count}\t成功:{$success_count}\t失败:" . ($total_count - $success_count));
            Log::_write($this->task, "===============================================================================");
        }
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

            $isql = "insert into user_point_vip(user_id,point_value,source,created_at) "
                    . " values('{$v['id']}'," . self::AWARD_DAY . "," . self::AWARD_SOURCE . ",'".date('Y-m-d H:i:s')."');";
            $ires = $this->_db->db_insert($isql);
            if($ires){
                $usql = "update `user` set vip_left_day = date_add(vip_left_day, interval " . self::AWARD_DAY . " day) where id = {$v['id']};";
                $this->_db->db_update($usql);
            }else{
                Log::_write($this->task,  "{$k} is 插入失败".$isql);
                exit;
            }
            
            Log::_write($this->task,  "{$k} is ". json_encode($v, JSON_UNESCAPED_UNICODE));
        }

        return $datas;
    }

    private function getTemplateMsg($mode) {
        $templateId = null;
        if ($mode == ENV_DEV) {
            $templateId = "4gUv9ztGaRfm3WXJ6XeC5_wRk_gbyelouweuP1GKfjM";
        } else {
            $templateId = "GG3R3N26F5bTtSAz0E8Wk1Q_KZPQAOaoW0LXpnAs5X0";
        }

        $data = array(
            'touser' => '',
            'template_id' => $templateId,
            'url' => "http://m.hrwq.com/vip/records?from=singlemessage",
            'topcolor' => '',
            'data' => array(
                'first' => array(
                    'value' => ("亲爱的和会员，因服务器升级导致本周一的课程更新延迟，特送您" . self::AWARD_DAY . "天会员补偿，目前会员补偿已到账！\n"),
                    'color' => ''
                ),
                'keyword1' => array(
                    'value' => ("VIP和会员\n"),
                    'color' => ''
                ),
                'keyword2' => array(
                    'value' => (date('Y-m-d') . "\n"),
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

if (empty($page) || !is_numeric($page)) {
    throw new Exception('请正确传入页码!');
}
// 模式验证
_mode_validate($mode);

//运行主函数
$object = new BlcgPush();
$object->run($page, $mode);
