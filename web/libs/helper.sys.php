<?php
/***********************************************************
	Filename: libs/helper.sys.php
	Note	: 辅助函数，均以sys_开头，在任何应用中都会载入
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-19
***********************************************************/
//自动跳转
if(!function_exists("sys_header"))
{
	function sys_header($url)
	{
		$url = str_replace("&amp;","&",$url);
		if(!headers_sent())
		{
			header("Location:".$url);
		}
		else
		{
			echo '<script type="text/javascript">';
			echo 'window.location.href="'.$url.'";';
			echo '</script>';
		}
		exit;
	}
}

//安全数据传输
if(!function_exists("sys_rgpc_safe"))
{
	function sys_rgpc_safe($msg,$f=false)
	{
		$status = get_magic_quotes_gpc();
		if(!$status || $f)
		{
			if(is_array($msg))
			{
				foreach($msg AS $key=>$value)
				{
					$msg[$key] = sys_rgpc_safe($value,$f);
				}
			}
			else
			{
				$msg = addslashes($msg);
			}
		}
		return $msg;
	}
}

//统计运行时间
if(!function_exists("sys_time_used"))
{
	function sys_time_used()
	{
		if(!defined("SYS_TIME_START"))
		{
			return false;
		}
		$time = explode(" ",microtime());
		$end_time = $time[0] + $time[1];
		return round(($end_time-SYS_TIME_START),5);
	}
}

//内存应用统计
if(!function_exists("sys_memory_used"))
{
	function sys_memory_used()
	{
		if(!defined("SYS_MEMORY_START") || !function_exists("memory_get_usage"))
		{
			return false;
		}
		$end = memory_get_usage();
		$used = $end - SYS_MEMORY_START;
		if($used <= 1024)
		{
			$used = "1 KB";
		}
		elseif($used>1024 && $used<(1024*1024))
		{
			$used = round(($used/1024),4)." KB";
		}
		else
		{
			$used = round(($used/(1024*1024)),4)." MB";
		}
		return $used;
	}
}


//获取控制层的三个参数
if(!function_exists("sys_get_cf"))
{
	function sys_get_cf($var)
	{
		$val = $_GET[$var];
		if(!$val)
		{
			return false;
		}
		//判断格式是否正确
		if(!ereg('^[a-z][a-z\_0-9]+$',$val))
		{
			exit('error: Only letters, numbers and underscores!');
		}
		return $val;
	}
}


if(!function_exists("sys_get_d"))
{
	function sys_get_d($var)
	{
		$val = $_GET[$var];
		if(!$val)
		{
			return false;
		}
		//判断格式是否正确
		if(!ereg('^[a-z][a-z\_0-9\/]+$',$val))
		{
			exit('error: Only letters, numbers and underscores!');
		}
		return $val;
	}
}

//用于调试用的信息
if(!function_exists("sys_debug"))
{
	function sys_debug($ifarray = false)
	{
		$app = sys_init();
		$time_used = sys_time_used();
		$mysql_count = $app->db_count();
		$mysql_times = $app->db_times();
		$memory = sys_memory_used();
		if($ifarray)
		{
			$array = array();
			$array["php_time"] = $time_used;
			$array["mysql_count"] = $mysql_count;
			$array["mysql_time"] = $mysql_times;
			if($memory)
			{
				$array["memory"] = $memory;
			}
			return $array;
		}
		else
		{
			$msg = "Processed in ".$time_used." second(s), mysql: ".$mysql_count." queries";
			$msg.= ", ".$mysql_times." second(s)";
			if($memory)
			{
				$msg.= ", memory: ".$memory;
			}
			return $msg.".";
		}
	}
}

//根据得到的数据执行二次MD5加密方案
//参数最多只支持一维数据（多维数据将会出错）
if(!function_exists("sys_md5"))
{
	function sys_md5($rs)
	{
		if(!$rs)
		{
			return false;
		}
		$rs = is_array($rs) ? implode("[:phpok:]",$rs) : $rs;
		return md5(md5($rs));
	}
}

//执行变量替换，以实现在语言包中使用变量
if(!function_exists("sys_eval"))
{
	function sys_eval($string,$val="")
	{
		if(!$val)
		{
			return $string;
		}
		if(is_array($val))
		{
			foreach($val AS $key=>$value)
			{
				$string = str_replace("{".$key."}",$value,$string);
			}
		}
		else
		{
			$string = preg_replace("/\{(.*)\}/isU",$val,$string);
		}
		return $string;
	}
}

if(!function_exists("sys_id_list"))
{
	function sys_id_list($id,$type="strval",$implode_code=",")
	{
		if(!in_array($type,array("strval","intval","floatval"))) $type = "strval";
		if(!$id || (!is_array($id) && !is_string($id) && !is_int($id)))
		{
			return false;
		}
		if(is_string($id) || is_int($id))
		{
			$new_list = array();
			$idlist = explode($implode_code,$id);
			foreach($idlist AS $key=>$value)
			{
				$value = trim($value);
				if($value)
				{
					$new_list[] = $type($value);
				}
			}
		}
		else
		{
			$new_list = array();
			foreach($id AS $key=>$value)
			{
				$value = trim($value);
				if($value)
				{
					$new_list[] = $type($value);
				}
			}
		}
		$new_list = array_unique($new_list);
		if(count($new_list)>0)
		{
			return $new_list;
		}
		else
		{
			return false;
		}
	}
}

//指格式化ID
if(!function_exists("sys_id_string"))
{
	function sys_id_string($id,$implode_code = ",",$type="strval")
	{
		if(!in_array($type,array("strval","intval","floatval"))) $type = "strval";
		$id = sys_id_list($id,$type,$implode_code);
		if($id)
		{
			return implode($implode_code,$id);
		}
		else
		{
			return false;
		}
	}
}

if(!function_exists("sys_ip"))
{
	function sys_ip()
	{
		$cip = (isset($_SERVER['HTTP_CLIENT_IP']) AND $_SERVER['HTTP_CLIENT_IP'] != "") ? $_SERVER['HTTP_CLIENT_IP'] : FALSE;
		$rip = (isset($_SERVER['REMOTE_ADDR']) AND $_SERVER['REMOTE_ADDR'] != "") ? $_SERVER['REMOTE_ADDR'] : FALSE;
		$fip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND $_SERVER['HTTP_X_FORWARDED_FOR'] != "") ? $_SERVER['HTTP_X_FORWARDED_FOR'] : FALSE;
		$ip = "0.0.0.0";
		if($cip && $rip)
		{
			$ip = $cip;
		}
		elseif($rip)
		{
			$ip = $rip;
		}
		elseif($cip)
		{
			$ip = $cip;
		}
		elseif($fip)
		{
			$ip = $fip;
		}

		if (strstr($ip, ','))
		{
			$x = explode(',', $ip);
			$ip = end($x);
		}

		if ( ! preg_match( "/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $ip))
		{
			$ip = '0.0.0.0';
		}

		unset($cip);
		unset($rip);
		unset($fip);

		return $ip;
	}
}

//读取当前分类下的子分类ID，递归法
if(!function_exists("sys_son_cateid"))
{
	function sys_son_cateid(&$cate_array,$id=0)
	{
		$sys_app = sys_init();
		$sys_app->load_model("category_model",true);
		$sys_app->category_model->langid($_SESSION["sys_lang_id"]);
		$rslist = $sys_app->category_model->get_son_cateid($id);
		if($rslist && is_array($rslist) && count($rslist))
		{
			foreach($rslist AS $key=>$value)
			{
				$cate_array[] = $value["id"];
				sys_son_cateid($cate_array,$value["id"]);
			}
		}
	}
}

//格式化价格
if(!function_exists("sys_format_price"))
{
	//val是原始值
	//currency是原始值的货币类型
	//ifval是否转化成可计算的货币（即只存在数值，不带前缀和后缀）
	function sys_format_price($val="0.00",$currency="CNY",$ifval=false)
	{
		$app = sys_init();
		$app->load_model("currency_model",true);
		//原始的货币值信息
		$rs = $app->currency_model->get_one($currency);
		if(!$rs)
		{
			$rs["val"] = "1.0000";
			$rs["title"] = "人民币";
			$rs["code"] = "CNY";
			$rs["symbol_left"] = "RMB￥";
			$rs["symbol_right"] = "";
		}
		//判断是否默认货币
		if($_SESSION["currency_default"] && is_array($_SESSION["currency_default"]))
		{
			$default_currency = $_SESSION["currency_default"];
		}
		else
		{
			$default_currency = $app->currency_model->get_default();
			$_SESSION["currency_default"] = $default_currency;
		}
		if(floatval($rs["val"])<0.0001)
		{
			$val = number_format(round($val,2),2,".","");
			if($ifval)
			{
				return "0.00";
			}
			else
			{
				if($default_currency["symbol_left"]) $default_currency["symbol_left"] .= " ";
				if($default_currency["symbol_right"]) $default_currency["symbol_right"] = " ".$default_currency["symbol_right"];
				return $default_currency["symbol_left"]."0.00".$default_currency["symbol_right"];
			}
		}
		else
		{
			$val = number_format(round($val*($default_currency["val"]/$rs["val"]),2),2,".","");
			if($ifval)
			{
				return $val;
			}
			else
			{
				if($default_currency["symbol_left"]) $default_currency["symbol_left"] .= " ";
				if($default_currency["symbol_right"]) $default_currency["symbol_right"] = " ".$default_currency["symbol_right"];
				return $default_currency["symbol_left"].$val.$default_currency["symbol_right"];
			}
		}
	}
}

//格式化已经完成的订单价格
if(!function_exists("sys_format_order_price"))
{
	function sys_format_order_price($val="0.00",$currency="CNY",$other_currency="CNY",$ifval=false)
	{
		$app = sys_init();
		$app->load_model("currency_model",true);
		//原始的货币值信息
		$rs = $app->currency_model->get_one($currency);
		if(!$rs)
		{
			$rs["val"] = "1.0000";
			$rs["title"] = "人民币";
			$rs["code"] = "CNY";
			$rs["symbol_left"] = "RMB￥";
			$rs["symbol_right"] = "";
		}
		$o_rs = $app->currency_model->get_one($other_currency);
		if(!$o_rs)
		{
			$o_rs["val"] = "1.0000";
			$o_rs["title"] = "人民币";
			$o_rs["code"] = "CNY";
			$o_rs["symbol_left"] = "RMB￥";
			$o_rs["symbol_right"] = "";
		}
		$default_currency = $o_rs;
		unset($o_rs);
		if(floatval($rs["val"])<0.0001)
		{
			$val = number_format(round($val,2),2,".","");
			if($ifval)
			{
				return "0.00";
			}
			else
			{
				if($default_currency["symbol_left"]) $default_currency["symbol_left"] .= " ";
				if($default_currency["symbol_right"]) $default_currency["symbol_right"] = " ".$default_currency["symbol_right"];
				return $default_currency["symbol_left"]."0.00".$default_currency["symbol_right"];
			}
		}
		else
		{
			$val = number_format(round($val*($default_currency["val"]/$rs["val"]),2),2,".","");
			if($ifval)
			{
				return $val;
			}
			else
			{
				if($default_currency["symbol_left"]) $default_currency["symbol_left"] .= " ";
				if($default_currency["symbol_right"]) $default_currency["symbol_right"] = " ".$default_currency["symbol_right"];
				return $default_currency["symbol_left"].$val.$default_currency["symbol_right"];
			}
		}
	}
}

//格式化字符串
if(!function_exists("sys_cutstring"))
{
	function sys_cutstring($title,$length=255,$ext="")
	{
		if(!$title)
		{
			return false;
		}
		$app = sys_init();
		return $app->trans_lib->cut($title,$length,$ext);
	}
}

if(!function_exists("sys_fckeditor"))
{
	function sys_fckeditor($var="",$defaultvalue="",$height="370px",$width="690px",$show_html=false)
	{
		$app = sys_init();
		if(intval($width)<562)
		{
			$width = "562px";
		}
		if(intval($height)<80)
		{
			$height = "80px";
		}
		if(substr($height,-2) != "px") $height .= "px";
		if(substr($width,-2) != "px") $width .= "px";
		if(intval($height)<150)
		{
			$width = "680px";
			$editor_html = $app->file_lib->cat("libs/xheditor/mini_edit.html");
		}
		else
		{
			$editor_html = $app->file_lib->cat("libs/xheditor/edit.html");
		}
		$editor_html = str_replace("{var}",$var,$editor_html);
		$editor_html = str_replace("{defaultvalue}",$defaultvalue,$editor_html);
		$editor_html = str_replace("{height}",$height,$editor_html);
		$editor_html = str_replace("{width}",$width,$editor_html);
		$editor_html = str_replace("{show_html}",($show_html ? "true" : "false"),$editor_html);
		return $editor_html;
	}
}

if(!function_exists("base_url"))
{
	function base_url()
	{
		$myurl = "http://".str_replace("http://","",$_SERVER["SERVER_NAME"]);
		$docu = $_SERVER["PHP_SELF"];
		$array = explode("/",$docu);
		$count = count($array);
		if($count>1)
		{
			foreach($array AS $key=>$value)
			{
				$value = trim($value);
				if($value)
				{
					if(($key+1) < $count)
					{
						$myurl .= "/".$value;
					}
				}
			}
		}
		$myurl .= "/";
		return $myurl;
	}
}

if(!function_exists("sys_write_cache_txt"))
{
	function sys_write_cache_txt()
	{
		$app = sys_init();
		$rslist = $this->cache_lib->cache_rs;
		$rslist = $app->cache_save();
		//echo "<pre>".print_r($rslist,true)."</pre>";
		$app->db->cache_write_txt($rslist);
		return true;
	}
}


if(!function_exists("sys_is_utf8"))
{
	function sys_is_utf8($string)
	{
	   $app = sys_init();
	   return $app->trans_lib->is_utf8($string);
	}
}

if(!function_exists("sys_tpl_setting"))
{
	function sys_tpl_setting($tplid=0)
	{
		$app = sys_init();
		if(!$tplid || $tplid == intval($app->tpl->tplid))
		{
			return false;
		}
		$app->load_model("tpl");
		$rs = $app->tpl_m->getone($tplid);
		if(!$rs)
		{
			return false;
		}
		$app->tpl->tplid = $tplid;
		$app->tpl->tpldir = ROOT."templates/".$rs["folder"];
		$app->tpl->cache = ROOT."data/tpl_c";
		$app->tpl->ext = $rs["ext"] ? $rs["ext"] : "html";
		$app->tpl->autoimg = $rs["autoimg"];
	}
}

if(!function_exists("sys_numformat"))
{
	function sys_numformat($a,$ext=2)
	{
		$app = sys_init();
		return $app->trans_lib->num_format($a,$ext);
	}
}

if(!function_exists("sys_html2js"))
{
	function sys_html2js($msg)
	{
		$msg = str_replace("\r","",$msg);
		$msg = str_replace("\n","",$msg);
		$msg = str_replace("'","\'",$msg);
		exit("var phpok_data='".$msg."';");
	}
}

if(!function_exists("sys_json_encode"))
{
	function sys_json_encode($msg,$exit=true)
	{
		$app = sys_init();
		if($exit)
		{
			exit($app->json_lib->encode($msg));
		}
		else
		{
			return $app->json_lib->encode($msg);
		}
	}
}

if(!function_exists("sys_format_list"))
{
	function sys_format_list($val,$input)
	{
		$app = sys_init();
		$app->load_model("format_model",true);
		return $app->format_model->format($val,$input);
	}
}

if(!function_exists("load_plugin"))
{
	//string：插件应用标识串，勾子
	//rs：传递的参数，可以是数组也可以是文本字符串等
	//is_return：是否返回结果
	function load_plugin($string,$rs="",$is_return=false)
	{
		if(!$string)
		{
			return false;
		}
		$string = "[".PLUGIN_NAME."]".$string."[/".PLUGIN_NAME."]";
		$app = sys_init();
		$app->load_model("plugin_model",true);
		$rslist = $app->plugin_model->get_all($string);
		if(!$rslist)
		{
			return false;
		}
		$tmplist = array();
		$is_html = false;
		foreach($rslist AS $key=>$value)
		{
			//获取扩展插件
			$hooks = str_replace(array("\t","\r"),"",$value["hooks"]);
			$hooks_array = explode("\n",$hooks);
			$action = "content";
			foreach($hooks_array AS $k=>$v)
			{
				$tmp_v = str_replace($string,"",$v);
				if($tmp_v != $v)
				{
					$action = $tmp_v ? $tmp_v : "content";
					//break;
				}
			}
			$myplugin = $app->plugin($value["identifier"]);
			$methods = get_class_methods($myplugin);
			if(!in_array($action,$methods))
			{
				$action = "content";
			}
			$mytmp = $myplugin->$action($rs);
			if(!is_int($mytmp) && !is_bool($mytmp))
			{
				$is_html = true;
				$tmplist[] = $mytmp;
			}
		}
		$content = "";
		if(count($tmplist)>0)
		{
			$content = implode("",$tmplist);
		}
		if($is_return)
		{
			return $content;
		}
		else
		{
			$app->tpl->assign("plugin_html",$content);
		}
		return true;
	}
}
?>