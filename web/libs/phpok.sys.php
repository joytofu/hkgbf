<?php
/***********************************************************
	Filename: phpok.sys.php
	Note	: 参数调用涉及到的函数
	Version : 3.0
	Author  : qinggan
	Update  : 2010-01-07
***********************************************************/
if(!function_exists("phpok"))
{
	//标识串
	//vartext，数组，将合并到后台的参数调用中
	//格式如：array("id"=>1) 这样子的形式 或 cs=1,cid=1这样子
	function phpok($var,$vartext="")
	{
		if(!$var)
		{
			return false;
		}
		$app = sys_init();
		$app->load_lib("phpok");
		$app->phpok_lib->langid($_SESSION["sys_lang_id"]);
		$app->load_model("phpok");
		$app->phpok_m->langid($_SESSION["sys_lang_id"]);
		$rs = $app->phpok_m->get_one_sign($var);
		if(!$rs)
		{
			return false;
		}
		$in_var = array();
		if($rs["vartext"])
		{
			$varlist = explode(",",$rs["vartext"]);
			foreach($varlist AS $key=>$value)
			{
				$in_var[$value] = $app->trans_lib->safe($value);
			}
		}
		$app->phpok_lib->set_rs($rs);
		$app->phpok_lib->thumbtype($rs["inpic"]);//指定小图类型
		//获取所有字段信息
		if($rs["mid"])
		{
			$mid = $rs["mid"];
			$in_var["mid"] = $rs["mid"];
		}
		if($rs["cid"])
		{
			$cid = $rs["cid"];
			$in_var["cid"] = $rs["cid"];
		}
		//如果系统有设置主题标签
		if($rs["extsign"] && $rs["maxcount"] == 1)
		{
			$in_var["ts"] = $rs["extsign"];
		}
		//合并传过来的数组
		if($vartext)
		{
			if(is_array($vartext) && count($vartext)>0)
			{
				$in_var = array_merge($in_var,$vartext);
			}
			else
			{
				$varlist = explode("&",$vartext);
				$v_list = array();
				foreach($varlist AS $key=>$value)
				{
					$v = explode("=",$value);
					$v_list[$v[0]] = $v[1];
				}
				$in_var = array_merge($in_var,$v_list);
			}
		}
		//执行内容信息
		if($rs["intype"] == "sql")
		{
			if(!$rs["typetext"])
			{
				return false;
			}
			$get_type = $rs["maxcount"] == 1 ? "get_one" : "get_all";
			$sql = sys_eval($rs["typetext"],$in_var);//参数替换值
			//过滤html代码
			$sql = str_replace("<div>","",$sql);
			$sql = str_replace("</div>","",$sql);
			return $app->phpok_lib->exec_sql($sql,$get_type);
		}
		elseif($rs["intype"] == "sign")
		{
			//增加父级缓存信息数
			if($rs["datatype"] == "cate")
			{
				$cache_key = md5("cate:".serialize($in_var));
				$rslist = $app->cache_lib->cache_read($cache_key);
				if(!$rslist)
				{
					$rslist = $app->phpok_lib->cate_sql($in_var);
					if($rslist)
					{
						$app->cache_lib->cache_write($cache_key,$rslist);
					}
				}
			}
			else
			{
				$cache_key = md5("list:".serialize($in_var)."-phpok-".serialize($rs));
				$rslist = $app->cache_lib->cache_read($cache_key);
				if(!$rslist)
				{
					$rslist = $app->phpok_lib->list_sql($in_var,$rs["maxcount"],$rs["orderby"]);
					if($rslist)
					{
						$app->cache_lib->cache_write($cache_key,$rslist);
					}
				}
			}
			return $rslist;
		}
		else
		{
			if(!$rs["typetext"])
			{
				return false;
			}
			$content = sys_eval($rs["typetext"],$in_var);
			$content = sys_format_content($content);//格式化内容代码信息
			return array("title"=>$rs["title"],"content"=>$content);
		}
	}
}

//上下主题
if(!function_exists("phpok_next_prev"))
{
	//id:主题ID
	//cateid：主题所属分类ID，如果分类ID为0，将读取模块ID信息
	//pictype：关联的图片类型，不关联为空
	//num：读取数量
	function phpok_next_prev($id,$cateid=0,$pictype="",$num=1)
	{
		$app = sys_init();
		$app->load_model("np_model",true);
		$app->np_model->langid($_SESSION["sys_lang_id"]);
		$rs = array();
		$next_list = $app->np_model->get_next($id,$cateid,$pictype,$num);
		if($next_list)
		{
			$rs["next"] = $next_list;
		}
		$prev_list = $app->np_model->get_prev($id,$cateid,$pictype,$num);
		if($prev_list)
		{
			$rs["prev"] = $prev_list;
		}
		if($rs["next"] || $rs["prev"])
		{
			return $rs;
		}
		else
		{
			return false;
		}
	}
}

//读取语言包
if(!function_exists("phpok_lang"))
{
	function phpok_lang($format=true)
	{
		$app = sys_init();
		return $app->langconfig_m->get_all($format);
	}
}

//读取导航菜单
//id：主题ID
//cid：分类ID
//mid：模块ID
//在模板中，调用可以直接编写： <!-- run:$menulist = phpok_menu($id,$cid,$mid) -->
if(!function_exists("phpok_menu"))
{
	function phpok_menu($id=0,$cid=0,$mid=0)
	{
		$app = sys_init();
		$app->load_model("menu");
		$tmplist = $app->menu_m->get_all();
		if(!$tmplist)
		{
			return false;
		}
		$rslist = array();
		$site_html = $app->sys_config["sitehtml"] ? $app->sys_config["sitehtml"] : $app->sys_config["siteurl"]."html/".$app->langid."/";
		$index_php = $app->sys_config["indexphp"] ? $app->sys_config["indexphp"] : "index.php";
		$site_url = $app->sys_config["siteurl"] ? $app->sys_config["siteurl"] : base_url();
		foreach($tmplist AS $key=>$value)
		{
			if(!$value["link_html"]) $value["link_html"] = $value["link"];
			if(!$value["link_rewrite"]) $value["link_rewrite"] = $value["link"];
			if($app->sys_config["site_type"] == "html")
			{
				$value["link"] = $value["link_html"];
			}
			elseif($app->sys_config["site_type"] == "rewrite")
			{
				$value["link"] = $value["link_rewrite"];
			}
			$value["link"] = str_replace("{control_trigger}",$app->config->c,$value["link"]);
			$value["link"] = str_replace("{site_html}",$site_html,$value["link"]);
			$value["link"] = str_replace("{index_php}",$index_php,$value["link"]);
			$value["link"] = str_replace("{site_url}",$site_url,$value["link"]);
			$value["link"] = str_replace("&","&amp;",$value["link"]);
			unset($value["link_html"],$value["link_rewrite"]);
			//判断是否有
			$value["my_highlight"] = false;
			$control_c = $app->trans_lib->safe($app->config->c);
			if($id && $control_c == "msg")
			{
				if($value["highlight"] == "subject" && $value["highlight_id"] == $id)
				{
					$value["my_highlight"] = true;
				}
				else
				{
					if($value["highlight"] == "cate" && $value["highlight_id"] && $cid && in_array($cid,sys_id_list($value["highlight_id"])))
					{
						$value["my_highlight"] = true;
					}
					else
					{
						if($value["highlight"] == "module" && $value["highlight_id"] && $mid && $mid == $value["highlight_id"])
						{
							$value["my_highlight"] = true;
						}
					}
				}
			}
			elseif($cid && $control_c == "list")
			{
				if($value["highlight"] == "cate" && $value["highlight_id"] && in_array($cid,sys_id_list($value["highlight_id"])))
				{
					$value["my_highlight"] = true;
				}
				else
				{
					if($value["highlight"] == "module" && $value["highlight_id"] && $mid && $mid == $value["highlight_id"])
					{
						$value["my_highlight"] = true;
					}
				}
			}
			elseif($mid && $control_c == "list")
			{
				if($value["highlight"] == "module" && $value["highlight_id"] && $mid == $value["highlight_id"])
				{
					$value["my_highlight"] = true;
				}
			}
			else
			{
				if(!$control_c) $control_c = "index";
				if($control_c && $control_c == $value["highlight"])
				{
					$value["my_highlight"] = true;
				}
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}
}

//读取底部导航菜单
if(!function_exists("phpok_nav"))
{
	function phpok_nav()
	{
		$app = sys_init();
		$app->load_model("nav");
		return $app->nav_m->get_all();
	}
}

//调用搜索支持的模块
if(!function_exists("phpok_module"))
{
	function phpok_module()
	{
		$app = sys_init();
		$app->load_model("module");
		return $app->module_m->get_all_module();
	}
}

//调用某个模块信息
//sign 模块标识符
if(!function_exists("phpok_m"))
{
	function phpok_m($sign="")
	{
		if(!$sign)
		{
			return false;
		}
		$app = sys_init();
		$app->load_model("module");
		return $app->module_m->get_one_from_code($sign);
	}
}

//调用某个模块下的主题
//ms 模块标识符
//limit 数量
//ifpic 0不需要 1需要
//order_by 排序，支持类型，请登录官方网站查看相关帮助
//attr：属性，仅支持 空，istop，isvouch，isbest
if(!function_exists("phpok_m_list"))
{
	function phpok_m_list($ms,$limit=10,$ifpic=0,$order_by="post_desc",$attr="")
	{
		//没有指定模块标识，返回为空！
		if(!$ms) return false;
		$app = sys_init();
		$app->load_lib("phpok");
		$app->phpok_lib->langid($_SESSION["sys_lang_id"]);
		$tmp_rs = array();
		$tmp_rs["pic_required"] = $ifpic;
		$tmp_rs["attr"] = $attr;
		$tmp_rs["maxcount"] = $limit;
		$app->phpok_lib->set_rs($tmp_rs);
		$in_var = array();
		$in_var["ms"] = $ms;
		$cache_key = "list_".md5("list:".serialize($in_var)."-phpok-".$order_by."-".$limit);
		$rslist = $app->cache_lib->cache_read($cache_key);
		if(!$rslist)
		{
			$rslist = $app->phpok_lib->list_sql($in_var,$limit,$order_by);
			if($rslist)
			{
				$app->cache_lib->cache_write($cache_key,$rslist);
			}
		}
		return $rslist;
	}
}

//调用某个分类下的主题
//cs 分类标识符
//limit 数量
//ifpic 0不需要 1需要
//order_by 排序，支持类型，请登录官方网站查看相关帮助
//attr：属性，仅支持 空，istop，isvouch，isbest
if(!function_exists("phpok_c_list"))
{
	function phpok_c_list($cs,$limit=10,$ifpic=0,$order_by="post_desc",$attr="")
	{
		//没有指定模块标识，返回为空！
		if(!$cs) return false;
		$app = sys_init();
		$app->load_lib("phpok");
		$app->phpok_lib->langid($_SESSION["sys_lang_id"]);
		$tmp_rs = array();
		$tmp_rs["pic_required"] = $ifpic;
		$tmp_rs["attr"] = $attr;
		$tmp_rs["maxcount"] = $limit;
		$app->phpok_lib->set_rs($tmp_rs);
		$in_var = array();
		$in_var["cs"] = $cs;
		$cache_key = "list_".md5("list:".serialize($in_var)."-phpok-".$order_by."-".$limit);
		$rslist = $app->cache_lib->cache_read($cache_key);
		if(!$rslist)
		{
			$rslist = $app->phpok_lib->list_sql($in_var,$limit,$order_by);
			if($rslist)
			{
				$app->cache_lib->cache_write($cache_key,$rslist);
			}
		}
		return $rslist;
	}
}

//调用分类，显示两级
//cid 当前分类ID
//mid 当前模块ID，可以为空，适用于分类ID为空时使用
if(!function_exists("phpok_catelist"))
{
	function phpok_catelist($cid)
	{
		if(!$cid) return false;
		$app = sys_init();
		$app->load_lib("phpok");
		$app->phpok_lib->langid($_SESSION["sys_lang_id"]);
		$tmp_rs = array();
		$tmp_rs["maxcount"] = 999;
		$app->phpok_lib->set_rs($tmp_rs);
		$rs = $app->cate_m->get_one($cid);
		$in_var = array();
		$in_var["cid"] = $cid;
		$cache_key = "catelist_".md5("cate:".serialize($in_var)."-phpok-".$cid);
		$rslist = $app->cache_lib->cache_read($cache_key);
		if(!$rslist)
		{
			$rslist = $app->phpok_lib->cate_sql($in_var);
			if($rslist)
			{
				$app->cache_lib->cache_write($cache_key,$rslist);
			}
		}
		return $rslist;
	}
}

//调用一个主题
//ts：内容标签，必填
//ifpic：是否包括图片0不限制，1包含
if(!function_exists("phpok_msg"))
{
	function phpok_msg($ts,$ifpic=0,$attr="")
	{
		if(!$ts) return false;
		$app = sys_init();
		$app->load_lib("phpok");
		$app->phpok_lib->langid($_SESSION["sys_lang_id"]);
		$tmp_rs = array();
		$tmp_rs["pic_required"] = $ifpic;
		$tmp_rs["attr"] = $attr;
		$tmp_rs["maxcount"] = 1;
		$app->phpok_lib->set_rs($tmp_rs);
		$in_var = array();
		$in_var["ts"] = $ts;
		$cache_key = "msg_".md5("list:".serialize($in_var)."-phpok");
		$rslist = $app->cache_lib->cache_read($cache_key);
		if(!$rslist)
		{
			$rslist = $app->phpok_lib->list_sql($in_var,1);
			if($rslist)
			{
				$app->cache_lib->cache_write($cache_key,$rslist);
			}
		}
		return $rslist;
	}
}

//简单分类列表，即不判断是否有父级分类，也不判断是否有子分类，只是根据标识串或ID，罗列相应的子分类
//id ：标识串或ID
//type 类型，默认是ID，支持 id 和 sign 两种类型
if(!function_exists("phpok_s_catelist"))
{
	function phpok_s_catelist($id,$type="id")
	{
		$app = sys_init();
		$app->load_model("list_model",true);
		return $app->list_model->get_s_catelist($id,$type,$_SESSION["sys_lang_id"]);
	}
}

//获取联动信息
//groupname 组名称
if(!function_exists("phpok_datalink"))
{
	function phpok_datalink($groupname="")
	{
		if(!$groupname)
		{
			return false;
		}
		$app = sys_init();
		$app->load_model("datalink_model",true);
		$tmplist = $app->datalink_model->get_list($groupname);
		if(!$tmplist)
		{
			return false;
		}
		$rslist = $sonlist = array();
		foreach($tmplist AS $key=>$value)
		{
			if(!$value["pid"])
			{
				$rslist[] = $value;
			}
			else
			{
				$sonlist[$value["pid"]][] = $value;
			}
		}
		if(!$rslist || count($rslist)<1 || !is_array($rslist))
		{
			return false;
		}
		foreach($rslist AS $key=>$value)
		{
			if($sonlist[$value["id"]] && is_array($sonlist[$value["id"]]))
			{
				$rslist[$key]["sonlist"] = $sonlist[$value["id"]];
			}
		}
		return $rslist;
	}
}

if(!function_exists("phpok_video"))
{
	function phpok_video($rs)
	{
		if(!$rs) return false;
		if(!is_array($rs))
		{
			$app = sys_init();
			$app->load_model("upfile");
			$rs = $app->upfile_m->get_one($rs);
		}
		$width = $app->sys_config["video_width"] ? $app->sys_config["video_width"] : "500";
		$height = $app->sys_config["video_height"] ? $app->sys_config["video_height"] : "400";
		$pre_image = $rs["flv_pic"] ? $rs["flv_pic"] : $app->sys_config["video_image"];
		$n_msg = "<script type='text/javascript'>";
		$n_msg.= 'var htmlmsg = Media.init("'.$rs["filename"].'","'.$width.'","'.$height.'","'.$pre_image.'");';
		$n_msg.= "document.write(htmlmsg);</script>";
		return $n_msg;
	}
}

//plugin_identifier，插件标识串
//function，执行函数
//ext，传递参数，数组或字符串，受插件影响，一般是传递字符串
if(!function_exists("phpok_plugin"))
{
	function phpok_plugin($plugin_identifier,$function="phpok",$ext="")
	{
		if(!$plugin_identifier)
		{
			return false;
		}
		$app = sys_init();
		$pt = $app->plugin($plugin_identifier);
		$pt->$function($ext);
	}
}

if(!function_exists("phpok_c"))
{
	function phpok_c($id,$field="content",$pageid=1)
	{
		if(!$id || !$field) return false;
		$app = sys_init();
		$app->load_model("msg");
		if(is_array($id))
		{
			$rs = $id;
		}
		else
		{
			$rs = $app->msg_m->get_one($id);
		}
		$content = $app->msg_m->get_c($rs["id"],$field);
		if(!$content) return false;
		$content = preg_replace("/<div/isU","<p",$content);
		$content = preg_replace("/<\/div>/isU","</p>",$content);
		//格式化内容
		$rslist = explode("[:page:]",$content);
		$content_count = count($rslist);
		if($content_count < 2)
		{
			return $content;
		}
		unset($content);
		//判断网址类型

		$html = '<div class="content-page">';
		$html.= "<table cellpadding='0' cellspacing='0'><tr>";
		foreach($rslist AS $key=>$value)
		{
			$html .= '<td>';
			$html .= '<a href="'.msg_url($rs,true,($key+1)).'" title="'.$rs["title"].'"';
			if(($key+1) == $pageid)
			{
				$html .= ' class="now"';
			}
			$html .= '>'.($key+1)."</a>";
			$html .= "</td>";
		}
		$html .= "</tr></table>";
		$html .= "</div>";
		$keyid = $pageid-1;
		$content = $rslist[$keyid] ? $rslist[$keyid] : $rslist[($content_count-1)];
		return $content.$html;
	}
}

//展示五个星星数统计
if(!function_exists("phpok_star"))
{
	function phpok_star($id)
	{
		if(!$id) return false;
		$app = sys_init();
		$app->load_model("reply");
		$rslist = $app->reply_m->getlist_star($id);
		$rs = array(0,1,2,3,4,5);
		$mylist = array();
		foreach($rs AS $key=>$value)
		{
			if($rslist[$value])
			{
				$mylist[$value] = $rslist[$value]["mycount"];
			}
			else
			{
				$mylist[$value] = 0;
			}
		}
		return $mylist;
	}
}

?>