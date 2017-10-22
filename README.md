微信推送消息功能，采用php cli模式部署和执行,php version 5.3.3

############参数配置############
程序根据服务器名称判断生产环境或开发测试环境，变量位于/src/config.php中 
define ( 'IS_ON_LINE', stripos ( php_uname (), "服务器名称" ) );
用以加载不同的数据库配置和公众号配置

############目录权限############
log日志目录 chmod -R 777 log/

############配置测试############
进入src目录，执行php test.php

###########测试推送客服消息配置############
cd /mnt/www/wx-push-msg/src; php WechatPush.class.php 1 test >> wp_test_1.log

############推送客服消息配置############
#查看是否有运行
service crond status
crontab -l
#进入编辑模式
crontab -e 
#加入以下行，每小时从8分开始执行，"1"代表页码，每页10000粉丝，"product"代表生产模式且(IS_ON_LINE为true)，另外还有"test","dev"
8 * * * * cd /mnt/www/wx-push-msg/src; php WechatPush.class.php 1 product >> wp_1.log
9 * * * * cd /mnt/www/wx-push-msg/src; php WechatPush.class.php 2 product >> wp_2.log
10 * * * * cd /mnt/www/wx-push-msg/src; php WechatPush.class.php 3 product >> wp_3.log
11 * * * * cd /mnt/www/wx-push-msg/src; php WechatPush.class.php 4 product >> wp_4.log
12 * * * * cd /mnt/www/wx-push-msg/src; php WechatPush.class.php 5 product >> wp_5.log

#保存退出
wq
#热重启crond服务
service crond reload
#查询执行日志
vim /var/log/cron
SHIFT+G 进入最后一行

###########测试推送签到模板消息配置############
cd /mnt/www/wxExtras/src; php QiandaoPush.class.php 1 test >> qd_test_1.log

############推送签到模板消息配置############
crontab -e
#加入以下行每晚20点发送
1 20 * * * cd /mnt/www/wxExtras/src; php QiandaoPush.class.php 1 product >> qd_1.log
2 20 * * * cd /mnt/www/wxExtras/src; php QiandaoPush.class.php 2 product >> qd_2.log
3 20 * * * cd /mnt/www/wxExtras/src; php QiandaoPush.class.php 3 product >> qd_3.log
4 20 * * * cd /mnt/www/wxExtras/src; php QiandaoPush.class.php 4 product >> qd_4.log
5 20 * * * cd /mnt/www/wxExtras/src; php QiandaoPush.class.php 5 product >> qd_5.log
6 20 * * * cd /mnt/www/wxExtras/src; php QiandaoPush.class.php 6 product >> qd_6.log
7 20 * * * cd /mnt/www/wxExtras/src; php QiandaoPush.class.php 7 product >> qd_7.log
8 20 * * * cd /mnt/www/wxExtras/src; php QiandaoPush.class.php 8 product >> qd_8.log
9 20 * * * cd /mnt/www/wxExtras/src; php QiandaoPush.class.php 9 product >> qd_9.log
#保存退出
wq
#热重启crond服务
service crond reload
