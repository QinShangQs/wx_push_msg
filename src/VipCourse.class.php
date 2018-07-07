<?php


/**
 * 
 * 给VIP用户增加课程7，只用一次
 * php VipCourse.class.php dev
 * @author qyd
 */

die('注销本行后运行' . PHP_EOL);

include_once 'Base.class.php';
class VipCourse extends Base {

    const COURSE_ID = 7;

    public function run($mode) {
        $task = "vipcourse_{$mode}";
        
        $users = $this->_getVipUsers();
        $count = count($users);
        
        for($i = 0; $i < $count; $i++){
            $user = $users[$i];
            $uid = $user['id'];
            $order = $this->_getCourseOrder($uid, static::COURSE_ID);
            $msg = "";
            if(empty($order)){
                $this->_addCourseOrder($uid, static::COURSE_ID);
                $msg = "新增订单";
            }

            Log::_write($task, "{$i} / {$count} {$msg} \t" . json_encode($user, JSON_UNESCAPED_UNICODE));
            
            if($mode == ENV_DEV && $i == 5){
                exit;
            }
        }
        
    }

    private function _addCourseOrder($uid, $course_id) {
        $sql = "insert into `order` (user_id, pay_id,pay_type,order_type,order_name,pay_method,pay_remark)
                value ({$uid}, {$course_id} ,1, 2 , '百万家庭幸福工程·招募简章', 2,'VIP批量添加');";
        $this->_db->db_insert($sql);
    }

    private function _getVipUsers() {
        $sql = "select openid, id, nickname,mobile,realname from `user` where vip_flg = 2";
        $rows = $this->_db->db_getAll($sql);
        return $rows;
    }
    
    private function _getCourseOrder($uid, $course_id){
        $sql = "select * from `order` where user_id = {$uid} and pay_id = {$course_id} limit 1";
        return $this->_db->db_getRow($sql);
    }

}


//获取shell参数
$mode = $argv[1];

// 模式验证
_mode_validate($mode);

//运行主函数
$object = new VipCourse();
$object->run($mode);