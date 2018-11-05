<?php
return array(
	//数据库配置
	'DB_TYPE'   => 'mysql', // 数据库类型
    'DB_HOST'   => '127.0.0.1', // 服务器地址
    'DB_NAME'   => 'ppc', // 数据库名
    'DB_USER'   => 'ppc', // 用户名
    'DB_PWD'    => 'W7b8S6R4', // 密码
    'DB_PORT'   => '3306', // 端口
    'DB_PREFIX' => 'ysk_', // 数据库表前缀

    'DEFAULT_MODULE' => 'Home',
    'URL_MODEL' => 2, //去掉index.php
    
    'SESSION_OPTIONS'         =>  array(
        'name'                =>  'BJYSESSION',                    //设置session名
        'expire'              =>  24*3600*15,                      //SESSION保存15天
        'use_trans_sid'       =>  1,                               //跨页传递
        'use_only_cookies'    =>  0,                               //是否只开启基于cookies的session的会话方式
    ),
	
	'app_begin' => array('Common\Behavior\CheckLangBehavior'),
	//注意这里，官方的文档解释感觉有误（大家自行分辨），TP3.2.3用Behavior\CheckLang会出错，提示：Class 'Behavior\CheckLang' not found
	'LANG_SWITCH_ON' => true,   // 开启语言包功能
	'LANG_AUTO_DETECT' => true,    // 自动侦测语言 开启多语言功能后有效
	'LANG_LIST'        => 'zh-cn,en-us', // 允许切换的语言列表 用逗号分隔
	'VAR_LANGUAGE'     => 'l', // 默认语言切换变量
);
