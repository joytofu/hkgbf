<?php
/***********************************************************
	Filename: app/www/config.inc.php
	Note	: 前台基本参数设置
	Version : 3.0
	Author  : qinggan
	Update  : 2009-12-22
***********************************************************/
define("HOME_PAGE","index.php"); //请不要改动此设置，如果要改动，请同时更改后台网站配置index.php

//以下触发参数是系统必须的
//Control触发参数
$config["control_trigger"] = "c";
//Control应用函数触发参数
$config["function_trigger"] = "f";
//Control应用目录触发参数
$config["dir_trigger"] = "d";

define("SYS_DOMAIN","phpok.com");

//是否需要验证，验证为空时执行的参数
define("SYS_IF_CHECKED",false);
//要验证哪些字段，多个字段用英文逗号隔开
define("SYS_CHECKED_SESSION_ID","web_md5");
//验证为空时执行哪一个脚本
define("SYS_CHECKED_FALSE","login");

//判断是否要压缩缩出
define("SYS_GZIP",false);

//判断是否启用验证码功能
define("SYS_VCODE_USE",true);
define("SYS_VCODE_VAR","phpok_login_chk");

//定义前台语言包默认标识（如果系统检测不到语言包时）
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
define("PLUGIN_NAME","www");


//时间调节，Timezone指的是时区，Timetuning是指时间微调，支持负数
//如果您的系统不支持date_default_timezone_set函数（PHP5.1版本以上），请直接改动微调数据
//微调数据单位是：秒
define("TIMEZONE","Asia/Shanghai");
define("TIMETUNING",0);

//设置是否启用缓存
define("DB_CACHE",true);

//订单编号规则
//支持的字符串有
//year年（4位）
//month月（2位）
//date日（2位）
//id订单自增ID（5位）
//rand 随机数（2位）
//usertype 订单用户类型C会员G游客(1位）
//otype 订单类型C指购买车购物U指客户填表（1位）
//count 指今日订单数（3位）
//注意，订单编号最长为20个字符
//不设置使用 year-month-date-id
define("ORDER_SN","usertype-year-month-date-otype-count");
?>