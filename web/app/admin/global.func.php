<?php
/***********************************************************
	Filename: app/admin/global.func.php
	Note	: 后台公共函数
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-17
***********************************************************/
if(!function_exists("error"))
{
	function error($msg="",$url="",$time=2)
	{
		$app = sys_init();
		//哪果没有内容
		if(!$msg && !$url)
		{
			exit("error: false");
		}
		//如果没有内容提示，则直接跳转
		if(!$msg)
		{
			sys_header($url);
		}
		//如果有内容提示跳转
		$app->tpl->assign("msg",$msg);
		$app->tpl->assign("error_url",$url);
		if($url)
		{
			$error_note = sys_eval($app->lang["error_note"],$time);
			$app->tpl->assign("error_note",$error_note);
		}
		$app->tpl->assign("time",$time);
		//毫秒级，在JS中应用
		$app->tpl->assign("micro_time",$time*1000);
		$app->tpl->p("error");
		exit();
	}
}

if(!function_exists("error_open"))
{
	function error_open($msg="",$url="",$time=2)
	{
		$app = sys_init();
		//哪果没有内容
		if(!$msg && !$url)
		{
			exit("error: false");
		}
		//如果没有内容提示，则直接跳转
		if(!$msg)
		{
			sys_header($url);
		}
		//如果有内容提示跳转
		$app->tpl->assign("msg",$msg);
		$app->tpl->assign("error_url",$url);
		if($url)
		{
			$error_note = sys_eval($app->lang["error_note"],$time);
			$app->tpl->assign("error_note",$error_note);
		}
		$app->tpl->assign("time",$time);
		//毫秒级，在JS中应用
		$app->tpl->assign("micro_time",$time*1000);
		$app->tpl->p("open_error");
		exit();
	}
}

if(!function_exists("site_url"))
{
	function site_url($value="",$extend="",$format_type="&amp;")
	{
		$app = sys_init();
		$url = $app->url($value,$extend,$format_type);
		return $url;
	}
}

//判断是否有权限操作
if(!function_exists("sys_popedom"))
{
	//string：权限字符串，格式为“模块标识串或ID:权限标识串”
	//rtype：返回信息，支持参数有：tpl,ajax及空值，默认为空值
	function sys_popedom($string,$rtype="")
	{
		$app = sys_init();
		$admin_id = $_SESSION["admin_id"];
		if(!$admin_id)
		{
			return sys_rtype($rtype);
		}
		$app->load_model("admin");
		$rs = $app->admin_m->get_one($admin_id);
		//系统管理员，返回有权限操作
		if($rs["if_system"])
		{
			return true;
		}
		//被锁定的管理员没有权限，返回否
		if(!$rs["status"])
		{
			return sys_rtype($rtype);
		}
		//未设置用户权限的返回否
		if(!$rs["popedom"])
		{
			return sys_rtype($rtype);
		}
		$popedom = explode(",",$rs["popedom"]);
		//无法取得参数的返回否
		if(!$string)
		{
			return sys_rtype($rtype);
		}
		//分割字符串
		$array = explode(":",$string);
		if(!$array[0] || !$array[1])
		{
			return sys_rtype($rtype);
		}
		//取得模块的内容
		$app->load_model("identifier");
		$popedom_id = $app->identifier_m->popedom_id($array[1]);
		if(!$popedom_id)
		{
			return sys_rtype($rtype);
		}
		$app->load_model("module");
		$module_id = intval($array[0]) ? $array[0] : $app->module_m->module_id($array[0]);
		if(!$module_id)
		{
			return sys_rtype($rtype);
		}
		$chk_popedom = $module_id.":".$popedom_id;
		if(in_array($chk_popedom,$popedom))
		{
			return true;
		}
		else
		{
			return sys_rtype($rtype);
		}
	}
}

if(!function_exists("sys_rtype"))
{
	function sys_rtype($type="")
	{
		$app = sys_init();
		if($type == "tpl")
		{
			error($app->lang["no_popedom"]);
		}
		elseif($type == "ajax")
		{
			exit($app->lang["no_popedom"]);
		}
		else
		{
			return false;
		}
	}
}


?>