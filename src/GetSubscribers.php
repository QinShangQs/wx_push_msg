<?php

include 'Base.class.php';

//php GetSubscribers.php all test
//php GetSubscribers.php vip test

//获取shell参数
$user_type = $argv[1];
$mode = $argv[2];

// 模式验证
_mode_validate($mode);

$base = new Base();
$funs = $base->getUsers($mode, $user_type);
echo count($funs).PHP_EOL;

//test
