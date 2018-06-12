<?php

include 'Base.class.php';

//获取shell参数
$mode = $argv[1];

// 模式验证
_mode_validate($mode);

$base = new Base();
$funs = $base->getSubscribers($mode);
echo count($funs);
