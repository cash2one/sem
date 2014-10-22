<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['baidu_server_host'] = array(
            '0'=>'http://172.16.34.145:22112',
            '1'=>'http://172.16.34.145:22112',
            '2'=>'http://172.16.34.145:22112',
            '3'=>'http://172.16.34.145:22112',
            '4'=>'http://172.16.34.145:22112',
        );
$config['auto_server_host'] = 'http://172.16.34.145:22300';

//检查用户是否处于更新状态白名单
$config['check_update_whitelist'] = array(
            '0'=>'user/info',
            '1'=>'user/statistics',
            '2'=>'autobid/taglist',
            '3'=>'unit/list',
            '4'=>'unit/stat',
            '5'=>'creative/feed',
            '6'=>'keyword/feed',
            '7'=>'plan/list',
            '8'=>'plan/stat',
        );

//统计相关配置
$config['stat_s_date'] = '2014-01-01';
$config['stat_threshold'] = '100';
$config['stat_cus_whitelist'] = array('5403759');
$config['stat_daily_whitelist'] = array('33752','33748');

//客户消耗邮件信息
$config['cus_consume_mail'] = array(
    'email_from'=>'sem_stat@haizhi.com',
    'email_to'=>'dingzhu@haizhi.com',
    'email_cc'=>'dingzhu@haizhi.com',
    'email_subject'=>'SEM未达标客户名单',
);

//产品用户日报邮件
$config['cus_daily_mail'] = array(
    'email_from'=>'sem_stat@haizhi.com',
    'email_to'=>'dingzhu@haizhi.com',
    'email_cc'=>'dingzhu@haizhi.com',
    'email_subject'=>'SEM产品用户使用日报',
);

//SEM客户端下载及安装日报邮件
$config['sem_client'] = array(
    'email_from'=>'sem_stat@haizhi.com',
    'email_to'=>'huangshitao@haizhi.com',
    'email_cc'=>'huangshitao@haizhi.com',
    'email_subject'=>'SEM客户端下载及安装统计日报@',
);

//客户端短信提示内容配置信息
$config['msg_content'] = array(
        '0' => '智投易提醒您竞价服务异常中止，请查看您的智投易客户端是否已异常退出或断开网络。如需帮助请开启24小时云服务。',
        '1' => '智投易网络连接断开，已进入竞价云端服务。',
        '2' => '智投易提醒您竞价服务中止，请立即启动智投易并连接百度恢复竞价服务。',
        '3' => '智投易检测到您的百度连接已经断开，并已暂停关键词竞价服务，请立即登录智投易，竞价服务将自动恢复。谢谢',
);

//短信通知未登录客户
$config['msg_notify'] = array(
        '2' => '您还在为关键词排名不稳定苦恼吧。即刻激活您的智投易账号，体验百秒极速智能竞价，锁定竞争对手的排名，24小时不间断竞价。立即打开电脑访问http://www.zhitouyi.com?m2 下载安装；登录账号为{username}，初始密码888888。如有疑问请致电4000639966。',
        '4' => '您可能已经被对手锁定排名，再不行动就错失商机。上万家企业已经开始使用智投易，享受极速智能竞价服务。立即打开电脑访问http://www.zhitouyi.com?m3 下载安装；登录账号为{username}，初始密码888888。如有疑问请致电4000639966。',
        //'11' => '第四条短信：您的智投易账号即将过期，请尽快下载安装智投易客户端登陆激活！下载地址：http://www.zhitouyi.com?m4 ，登录账号为{username}，初始密码888888。如有疑问请致电4000639966。',
);

//邮件通知未登录客户
$config['mail_notify'] = array(
        '5' => array('page'=>'edm2','title'=>'即刻激活智投易，体验百秒极速智能竞价'),
        '13' => array('page'=>'edm3','title'=>'您可能已经被对手锁定排名，再不行动就错失商机'),
        //'29' => array('page'=>'edm4','title'=>'您的智投易账号即将过期'),
);
