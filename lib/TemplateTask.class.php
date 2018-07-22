<?php

class TemplateTask {

    private $_db = null;
    private $_table_name = 'wechat_template_task';

    const TASK_TYPE_ONLYONE = 1;
    const TASK_TYPE_EVERYDAY = 2;
    const TASK_STATUS_WAITING = 1;
    const TASK_STATUS_RUNNING = 2;
    const TASK_STATUS_STOPED = 3;
    const TASK_STATUS_FINISHED = 4;

    public function __construct() {
        $dbConfig = array(
            'host' => HOST,
            'user' => USER,
            'pass' => PWD,
            'port' => PORT,
            'dbname' => DB_NAME
        );
        $this->_db = new DB($dbConfig);
    }

    private function _getTask($wechat_appid, $task_type, $task_status, \stdClass $params) {
        $sql = "select * from {$this->_table_name} where wechat_appid = '{$wechat_appid}' and task_type = '{$task_type}'"
                . " and task_status in ({$task_status}) and task_run_time = '{$params->task_run_time}' limit 1;";

        return $this->_db->db_getRow($sql);
    }

    /**
     * 
     * @param type $wechat_appid
     * @param type $task_run_time date(Y-m-d H:i)
     * @return type
     */
    public function getOnlyoneTask($wechat_appid, $task_run_time) {
        $params = new \stdClass();
        $params->task_run_time = $task_run_time;

        $statuses = self::TASK_STATUS_WAITING . "," . self::TASK_STATUS_RUNNING;
        return $this->_getTask($wechat_appid, self::TASK_TYPE_ONLYONE, $statuses, $params);
    }

    public function changeStatus($id, $status, $remark) {
        $sql = "update {$this->_table_name} set task_status = '{$status}', remark = '{$remark}' where id = {$id}";
        return $this->_db->db_update($sql);
    }
    
    public function finishUpdate($id, $finish_time, $send_total_num, $send_success_num){
        $status = self::TASK_STATUS_FINISHED;
        $send_fail_num = intval($send_total_num) - intval($send_success_num);
        $sql = "update {$this->_table_name} set task_status = '{$status}', finish_time = '{$finish_time}'"
        . " , send_total_num = {$send_total_num} , send_success_num = {$send_success_num}, send_fail_num = {$send_fail_num}"
        . " where id = {$id}";
        return $this->_db->db_update($sql);
    }

}
