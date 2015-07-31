<?php
/***********************************************************
	Filename: app/admin/config.inc.php
	Note	: 后台基本参数设置
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-16
***********************************************************/
define("HOME_PAGE","websitesystem.php");
define("HOME_WWW","index.php");

//以下触发参数是系统必须的
//Control触发参数
$config["control_trigger"] = "c";
//应用函数触发参数
$config["function_trigger"] = "f";
//应用目录触发参数
$config["dir_trigger"] = "d";

//是否需要验证，验证为空时执行的参数
define("SYS_IF_CHECKED",true);
//要验证哪些字段，多个字段用英文逗号隔开
define("SYS_CHECKED_SESSION_ID","admin_md5");
//验证为空时执行哪一个脚本
define("SYS_CHECKED_FALSE","login");

//判断是否要压缩缩出
define("SYS_GZIP",true);

//判断是否启用验证码功能
define("SYS_VCODE_USE",true);
define("SYS_VCODE_VAR","phpok_login_chk");

//定义后台语言包标识
define("SYS_LANG","zh");

//默认分页数量
define("SYS_PSIZE",20);
//分页变量
define("SYS_PAGEID","pageid");

//判断是否启用debug
define("SYS_IF_DEBUG",true);

//定义session id，默认使用PHPSESSID
define("SYS_SESSION_ID","PHPSESSID");

//定义附件路径
define("SYS_UP_PATH","upfiles");

//定义插件界定符，也是插件名称文件，如未定义，则使用APP_NAME
define("PLUGIN_NAME","admin");

//时间调节，Timezone指的是时区，Timetuning是指时间微调，支持负数
//如果您的系统不支持date_default_timezone_set函数（PHP5.1版本以上），请直接改动微调数据
//微调数据单位是：秒
define("TIMEZONE","Asia/Shanghai");
define("TIMETUNING",0);

//设置是否启用缓存
define("DB_CACHE",false);
?>