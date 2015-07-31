<?php
/***********************************************************
	Filename: websitesystem.php
	Note	: 后台首页，以下常量参数是必须的
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-16
***********************************************************/
//根目录
//define("ROOT",str_replace("\\","/",dirname(__FILE__))."/");
define("ROOT","./");
//核心库文件
define("LIBS",ROOT."libs/");
//应用层文件
define("APP_ROOT",ROOT."app/");
//应用模块名称，模块目录，可供应各个插件调用
define("APP_NAME","admin");
//管理
define("APP",APP_ROOT.APP_NAME."/");
//前台管理
define("APP_WWW",APP_ROOT."www/");
//文本文件存储目标
define("ROOT_DATA",ROOT."data/");

//插件目录
define("ROOT_PLUGIN",ROOT."plugins/");
//配置标识参数
define("PHPOK_SET",true);
//加载通用信息管理
require_once(LIBS."admin.inc.php");
?>