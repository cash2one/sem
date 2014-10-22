<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//url访问权限配置

$config['access_control'] = array(
            
        //智能竞价
        '1' => array(
                //读的api
                '1' => array(
                        'page/smart_bid',
                        'autobid/list',
                        'autobid/monitor',
                        'autobid/taglist',
                    ),
                //写的api
                '2' => array(
                        'user/update',
                        'autobid/add',
                        'autobid/add_tag',
                        'autobid/del_tag',
                        'autobid/modify_bid_status',
                        'autobid/modify',
                        'autobid/modify_tag',
                        'autobid/monitor_set',
                    )
            ),    
        //出价参考
        '2' => array(
                //读的api
                '1' => array(
                        'page/ref_bid',
                        'refbid/feed',
                    ),
                //写的api
                '2' => array(
                        'user/update',
                        'refbid/calc',
                    )
            ),    
        //对手跟踪
        '3' => array(
                //读的api
                '1' => array(
                        'page/competitor',
                        'competitor/feed',
                        'competitor/rank',
                    ),
                //写的api
                '2' => array(
                        'user/update',
                        'competitor/add',
                        'competitor/modify',
                    )
            ),    
        //白名单模块/公共
        'white' => array(
                //读的api
                '1' => array(
                        'page/help',
                        'hzuser/info',
                        'hzuser/logout',
                        'hzuser/module_access',
                        'user/level_tree',
                        'user/info',
                    ),
                //写的api
                '2' => array(
                    )
            ),  
        //搜索管理模块,废弃
        /*'4' => array(
                //读的api
                '1' => array(
                        'page/search_manage',
                        'user/info',
                        'user/level_tree',
                        'user/statistics',
                        'plan/list',
                        'plan/stat',
                        'unit/list',
                        'unit/stat',
                        'creative/feed',
                        'keyword/feed',
                    ),
                //写的api
                '2' => array(
                        'user/add_whitelist',
                        'user/del_whitelist',
                        'user/init',
                        'user/mod_area',
                        'user/mod_budget',
                        'user/mod_ip',
                        'user/reset_pwd',
                        'user/stat_fix',
                        'user/update',
                        'plan/add',
                        'plan/del',
                        'plan/modify',
                        'plan/modify_schedule',
                        'unit/add',
                        'unit/del',
                        'unit/modify',
                        'creative/add',
                        'creative/del',
                        'creative/modify',
                        'keyword/add',
                        'keyword/del',
                        'keyword/modify',
                    )
            ),*/
    );

