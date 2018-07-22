<?php

include_once 'Base.class.php';
include_once '../lib/TemplateTask.class.php';
include_once '../lib/Helper.class.php';

/**
 * php TaskPush.class.php 1 dev
 * 定时发送模版消息
 */
class TaskPush extends Base {

    private $_templateTaskModel = null;
    private $_task_prefix = "task_push";

    public function __construct() {
        parent::__construct();
        $this->_templateTaskModel = new TemplateTask ();
    }

    /**
     * 发送消息
     * @param integer $page
     * @param string $mode
     */
    public function run($page, $mode) {
        $wechat_appid = WEIXIN_APPID;
        $task_run_time = date('Y-m-d H:i');

        $taskInfo = $this->_templateTaskModel->getOnlyoneTask($wechat_appid, $task_run_time);
        if (empty($taskInfo)) {
            Log::_write($this->_task_prefix, $task_run_time . ' no task');
            exit();
        }
        try {
            $task = $this->_task_prefix . "_" . $taskInfo['id'];

            $subscribers = parent::getUsers($mode, $taskInfo ['user_type']);
            $total_count = count($subscribers);
            $success_count = 0;

            $tmpMsg = $this->_createTemplateMsg($taskInfo);
            for ($i = 0; $i < $total_count; $i ++) {
                $openId = $subscribers [$i];
                $tmpMsg ['touser'] = $openId;
                $result = $this->_wxApi->send_template_mssage(json_encode($tmpMsg));
                Log::_write($task, $taskInfo['user_type'] . "{$i}/{$total_count} {$openId}\t" . json_encode($result));
                if ($result ['errcode'] == 0) {
                    $success_count ++;
                } 
            }

            $taskEndTime = Helper::getTimeStampString();
            $this->_templateTaskModel->finishUpdate($taskInfo['id'], $taskEndTime, $total_count, $success_count);
            Log::_write($task, "===============================================================================");
            Log::_write($task, "总数：{$total_count}\t成功:{$success_count}\t失败:" . ($total_count - $success_count));
            Log::_write($task, "===============================================================================");
        } catch (Exception $ex) {
            Log::_write($this->_task_prefix, "Error " . $ex->getMessage() . PHP_EOL . json_encode($ex));
            exit();
        }
    }

    private function _createTemplateMsg($taskInfo) {
        $subData = base64_decode($taskInfo['content']);
        $subData = json_decode($subData, true);
        $data = array(
            'touser' => '',
            'template_id' => $taskInfo['template_id'],
            'url' => $taskInfo['url'],
            'topcolor' => $taskInfo['topcolor'],
            'data' => $subData
        );
        return $data;
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
$object = new TaskPush();
$object->run($page, $mode);
