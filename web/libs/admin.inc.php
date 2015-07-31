<?php
/***********************************************************
	Filename: libs/common.inc.php
	Note	: 通用加载信息
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-16
***********************************************************/
//基本参数设置
require_once(APP.'config.inc.php');
if(defined("TIMEZONE") && function_exists("date_default_timezone_set"))
{
	date_default_timezone_set(TIMEZONE);
}
header("Content-type: text/html; charset=utf-8");
//取得Get或Post参数信息
//配置返回资料是否自动加入反斜线当溢出字符，设置为0关闭该功能
@set_magic_quotes_runtime(0);
$time = explode(" ",microtime());
$time_start = $time[0] + $time[1];
define("SYS_TIME_START",$time_start);
unset($time_start,$time);
//读取内存
if(function_exists("memory_get_usage") && !defined("SYS_MEMORY_START"))
{
	define("SYS_MEMORY_START",memory_get_usage());
}

//加载版本控制器
if(file_exists(ROOT."version.php"))
{
	include_once(ROOT."version.php");
}


if(defined("SYS_GZIP") && SYS_GZIP == true && function_exists("ob_gzhandler"))
{
	ob_start("ob_gzhandler");
}
else
{
	ob_start();
}
//判断是否有启用调试功能
if(defined("SYS_IF_DEBUG") && SYS_IF_DEBUG == true)
{
	//error_reporting(E_ALL);
	error_reporting(7);
}
else
{
	error_reporting(0);
}

require_once(LIBS.'control.sys.php');
//加载辅助函数，这里的辅助函数均不涉及到APP层上的信息
//即这里的辅助函数均可以单独运行
require_once(LIBS.'helper.sys.php');

//[格式化Get,Post及$_FILES参数]
@extract(sys_rgpc_safe($_POST));
@extract(sys_rgpc_safe($_GET));
if(!get_magic_quotes_gpc()) $_FILES = sys_rgpc_safe($_FILES);
//执行GET参数，以获取有效的控制文件
//如果没有检查到C层
$p_c = sys_get_cf($config['control_trigger']);
if(!$p_c) $p_c = "index";
$p_f = sys_get_cf($config['function_trigger']);
if(!$p_f) $p_f = "index";
$p_d = sys_get_d($config['dir_trigger']);
if($p_d)
{
	if(substr($p_d,-1) != '/')
	{
		$p_d .= '/';
	}
	$control_file = APP.'control/'.$p_d.$p_c.'.php';
}
else
{
	$control_file = APP.'control/'.$p_c.'.php';
}
if(!file_exists($control_file))
{
	exit('error: file '.$p_c.'.php no exists!');
}
//判断文件是否存在
$control_file = APP.'control/'.$p_d.$p_c.'.php';
require_once($control_file);
$control_name = strtolower($p_c)."_c";
$APP = new $control_name();
$APP->control_name = $p_c;//指定模块
$system_time = time() + (defined("TIMETUNING") ? TIMETUNING : 0);
$APP->system_time = $system_time;//系统时间
//执行session信息
$APP->session_lib->start($APP->db,$APP->db->prefix);
//运行以下参数，以实现在函数中使用Control操作
function sys_init()
{
	global $APP;
	return $APP;
}
//
function sys_app($var)
{
	$app = sys_init();
	return $app->$var;
}
//将数据存到URL类中
$APP->set_config($config);
$APP->url = $APP->url(array("c"=>$p_c,"f"=>$p_f,"d"=>$p_d));
$APP->tpl->assign("sys_app",$APP);
//加载公共函数
$dirlist = $APP->file_lib->ls(APP);
foreach($dirlist AS $key=>$value)
{
	$basename = strtolower(basename($value));
	if(substr($basename,-8) == "func.php")
	{
		include_once($value);
	}
}
unset($dirlist);
//判断自动载入的model配置信息
if(file_exists(APP."model.config.php"))
{
	include(APP."model.config.php");
	if($_model_config["autoload"] && is_array($_model_config["autoload"]) && count($_model_config["autoload"])>0)
	{
		foreach($_model_config["autoload"] AS $key=>$value)
		{
			$APP->load_model($value);
		}
	}
}
//判断是否有新的语言模块，有就重写
if($APP->langconfig_m)
{
	$_tmp_langid = $_SESSION["sys_lang_id"] ? $_SESSION["sys_lang_id"] : "";
	$_tmp_rs = $APP->langconfig_m->get_one($_tmp_langid);
	if($_tmp_rs)
	{
		$_SESSION["sys_lang_id"] = $_tmp_rs["langid"];
	}
	else
	{
		$_SESSION["sys_lang_id"] = defined("SYS_LANG") ? SYS_LANG : "zh";
	}
	//读取语言包数据，后台强制为中文
	$_lang = $APP->langconfig_m->get_list("zh");
	//$_lang = $APP->langconfig_m->get_list($_SESSION["sys_lang_id"]);
}
else
{
	$_SESSION["sys_lang_id"] = defined("SYS_LANG") ? SYS_LANG : "zh";
	$_lang = array();
}
//指定语言包
$_lang = $APP->lang($_SESSION["sys_lang_id"],$_lang);
$APP->langid = $_SESSION["sys_lang_id"];

//验证管理员
if(defined("SYS_IF_CHECKED") && SYS_IF_CHECKED == true)
{
	if(!defined("SYS_CHECKED_SESSION_ID") || SYS_CHECKED_SESSION_ID == "" || !defined("SYS_CHECKED_FALSE") || SYS_CHECKED_FALSE == "")
	{
		exit("error: setting false.");
	}
	if(!$_SESSION[SYS_CHECKED_SESSION_ID] && $p_c != SYS_CHECKED_FALSE)
	{
		$url = $APP->url(SYS_CHECKED_FALSE);
		sys_header($url);
	}
}

$function_name = strtolower($p_f)."_f";
unset($p_c,$p_f,$p_d,$control_file);
$APP->$function_name();
?>