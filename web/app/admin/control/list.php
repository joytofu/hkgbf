<?php
/***********************************************************
	Filename: app/admin/control/list.php
	Note	: 内容管理
	Version : 3.0
	Author  : qinggan
	Update  : 2011-12-03 20:44
***********************************************************/
class list_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("list");
		$this->load_model("module");
		$this->load_model("cate");
		$this->load_lib("phpok_input");
		$this->load_model("currency_model",true);
	}

	function list_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		load_plugin("list:index:prev");
		$page_url = $this->url("list,index");
		$module_id = $this->trans_lib->int("module_id");
		if(!$module_id)
		{
			error("没有指定模块ID");
		}
		//判断是否有此操作权限
		sys_popedom($module_id.":list","tpl");
		$page_url .= "module_id=".$module_id."&";
		//取得模块列表
		$m_rs = $this->_load_module($module_id);
		load_plugin("module:".$m_rs["identifier"].":index:prev",$m_rs);//执行模块前的操作
		$this->tpl->assign("m_rs",$m_rs);
		//取得模块的扩展字段
		$m_key_list = $this->module_m->fields_index($module_id,1,"input IN('text','radio','textarea','select','opt','module','checkbox')");
		$this->tpl->assign("m_key_list",$m_key_list);
		//
		$ifcate = $m_rs["if_cate"] ? true : false;
		$ifbiz = $m_rs["if_biz"] ? true : false;
		$ifthumb = $m_rs["if_thumb"] ? true : false;
		if($ifbiz)
		{
			$this->load_model("currency");
			$currency_list = $this->currency_m->get_list();
			$this->tpl->assign("currency_list",$currency_list);
			if($_SESSION["currency_default"] && is_array($_SESSION["currency_default"]))
			{
				$default_currency = $_SESSION["currency_default"];
			}
			else
			{
				$default_currency = $this->currency_model->get_default();
				$_SESSION["currency_default"] = $default_currency;
			}
			$this->tpl->assign("default_currency",$default_currency);
		}
		$this->tpl->assign("ifcate",$ifcate);
		$pageid = $this->trans_lib->int(SYS_PAGEID);
		$this->list_m->set_condition("m.langid='".$_SESSION["sys_lang_id"]."'");//区分语言
		$this->list_m->set_condition("m.module_id='".$module_id."'");
		if($ifcate)
		{
			$cate_id = $this->trans_lib->int("cate_id");
			if($cate_id>0)
			{
				$page_url .= "cate_id=".$cate_id."&";
				//取得子类
				$cate_array = array($cate_id);
				$this->cate_m->get_sonid_array($cate_array,$cate_id);
				$this->list_m->set_condition("m.cate_id IN(".implode(",",$cate_array).")");
			}
		}
		$status = $this->trans_lib->int("status");
		if($status)
		{
			$page_url .= "status=".$status."&";
			$this->list_m->set_condition("m.status='".($status == 1 ? 1 : 0)."'");
		}
		$keywords = $this->trans_lib->safe("keywords");
		$keytype = $this->trans_lib->safe("keytype");
		$isbest = $this->trans_lib->safe("isbest");	
	    $this->tpl->assign("isbest",$isbest);
		//echo $keytype;		
		//代理商		
		//$note = $this->trans_lib->safe("note");		
		if($keytype || $isbest)
		{
			$this->list_m->set_keywords($keytype,$keywords,$module_id,$isbest);
			$this->tpl->assign("keytype",$keytype);
			$page_url .= "keywords=".rawurlencode($keywords)."&keytype=".rawurlencode($keytype)."&";
		}
		$rslist = $this->list_m->get_list($pageid,$ifcate,$ifthumb);
		$this->tpl->assign("rslist",$rslist);
		$total_count = $this->list_m->get_count();//取得总数
		$pagelist = $this->page_lib->page($page_url,$total_count);
		$this->tpl->assign("pagelist",$pagelist);
		//如果有分类
		if($ifcate)
		{
			$this->_load_cate($module_id,$cate_id,true,true);
		}
		//加载模块配置字段
		$this->layout($module_id,$m_rs);
		load_plugin("module:".$m_rs["identifier"].":index:next",$m_rs);//执行模块前的操作
		load_plugin("list:index:next",$rslist);//全局插件
		$this->tpl->display("list/list.html");
	}

	function layout($mid,$m_rs)
	{
		$layout = array();
		$layout["subtitle"] = $m_rs["subtitle_nickname"] ? $m_rs["subtitle_nickname"] : "副主题";
		$layout["hits"] = "查看次数";
		$layout["good_hits"] = "好评";
		$layout["bad_hits"] = "差评";
		$layout["author"] = "发布人";
		$layout["link_url"] = "链接地址";
		//取得扩展字段的名字
		$mlist = $this->module_m->fields_index_identifier($mid);
		if($mlist)
		{
			foreach($mlist AS $key=>$value)
			{
				$layout[$key] = $value["title"];
			}
			unset($mlist);
		}
		if(!$m_rs["layout"]) return false; //如果未设置，直接返空
		//合并
		$keylist = sys_id_list($m_rs["layout"]);
		$mlist = array();
		foreach($keylist AS $key=>$value)
		{
			$mlist[$value] = $layout[$value];
		}
		$this->tpl->assign("mlist",$mlist);
		return $mlist;
	}

	function chkone_f()
	{
		$id = $this->trans_lib->int("id");
		$sign = $this->trans_lib->safe("sign");
		if(!$sign)
		{
			exit("error: 没有指定标识串");
		}
		$rs = $this->list_m->chk_sign($sign,$id,$_SESSION["sys_lang_id"]);
		if($rs)
		{
			exit("error: 标识串已被使用！");
		}
		exit("ok");
	}

	function ajax_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("没有指定ID！");
		}
		$rs = $this->list_m->get_one($id);
		$this->tpl->assign("id",$id);
		$mid = $rs["module_id"];
		$cid = $rs["cate_id"];
		$currency = $rs["price_currency"];

		$this->tpl->assign("currency",$currency);
		$this->tpl->assign("rs",$rs);
		$this->tpl->assign("mid",$mid);
		$this->tpl->assign("cid",$cid);
		$m_rs = $this->_load_module($mid);
		if($cid)
		{
			$cate_rs = $this->cate_m->get_one($cid);
			$this->tpl->assign("cate_rs",$cate_rs);
			$ext_cid_list = $this->list_m->ext_catelist($id,$cid);
			if($ext_cid_list)
			{
				$ext_catelist = $this->cate_m->get_list_idstring(sys_id_string($ext_cid_list));
				$this->tpl->assign("ext_catelist",$ext_catelist);
			}
		}
		$ext_list = $this->_load_ext_fields($mid);//获取扩展信息
		if($ext_list && is_array($ext_list) && count($ext_list)>0)
		{
			$optlist = array();
			foreach($ext_list AS $key=>$value)
			{
				$_field_name = $value["identifier"];
				$value["default_val"] = $rs[$_field_name] ? $this->trans_lib->cut($rs[$_field_name],80,'…') : $value["default_val"];
				$ext_list[$key] = $value;
			}
			$this->tpl->assign("extlist",$ext_list);
		}
		$this->tpl->display("list/alt.html");
	}

	function set_f()
	{
		load_plugin("list:set:prev");
		$id = $this->trans_lib->int("id");
		if($id)
		{
			$rs = $this->list_m->get_one($id);
			$this->tpl->assign("id",$id);
			$module_id = $rs["module_id"];
			sys_popedom($module_id.":modify","tpl");
			$cate_id = $rs["cate_id"];
			$currency = $rs["price_currency"];
			//取得扩展分类
			$ext_catelist = $this->list_m->ext_catelist($id,$rs["cate_id"]);
			if($ext_catelist)
			{
				$this->tpl->assign("cate_string",sys_id_string($ext_catelist));
			}
		}
		else
		{
			$cate_id = $this->trans_lib->int("cateid");
			$module_id = $this->trans_lib->int("module_id");
			if(!$module_id && $cate_id)
			{
				$cate_rs = $this->cate_m->get_one($cate_id);
				if(!$cate_rs)
				{
					error("没有指定模块ID！且没有指定分类ID");
				}
				$module_id = $rs["module_id"];
			}
			sys_popedom($module_id.":add","tpl");
			$rs["post_date"] = $this->system_time;//系统时间
			$rs["ip"] = sys_ip();
		}
		if(!$module_id)
		{
			error("没有指定模块ID");
		}
		if(!$currency)
		{
			$currency = $this->currency_model->get_default_currency();
		}
		$this->tpl->assign("currency",$currency);
		$this->tpl->assign("rs",$rs);
		$this->tpl->assign("module_id",$module_id);
		$m_rs = $this->_load_module($module_id);
		//判断是否使用电子商务
		if($m_rs["if_biz"])
		{
			$this->load_model("currency");
			$curlist = $this->currency_m->get_list();
			$this->tpl->assign("curlist",$curlist);
		}
		load_plugin("module:".$m_rs["identifier"].":set:prev",$m_rs);//执行模块前的操作
		//读取内容
		$ifcate = $m_rs["if_cate"] ? true : false;
		$this->tpl->assign("ifcate",$ifcate);
		if($ifcate)
		{
			if($cate_id)
			{
				$cate_rs = $this->cate_m->get_one($cate_id);
				$this->tpl->assign("cate_rs",$cate_rs);
			}
			$this->tpl->assign("cate_id",$cate_id);
			$this->_load_cate($module_id,$cate_id);
		}
		$ext_list = $this->_load_ext_fields($module_id);//获取扩展信息
		if($ext_list && is_array($ext_list) && count($ext_list)>0)
		{
			$optlist = array();
			$extlist_must = $extlist_need = array();
			foreach($ext_list AS $key=>$value)
			{
				$_field_name = $value["identifier"];
				$value["default_val"] = $rs[$_field_name] ? $rs[$_field_name] : $value["default_val"];
				$extlist = $this->phpok_input_lib->get_html($value);
				$extlist_must[] = $extlist;
				if($value["input"] == "opt")
				{
					$optlist[] = $value;
				}
				$ext_list[$key] = $value;
			}
			$this->tpl->assign("extlist_must",$extlist_must);
			$this->tpl->assign("optlist",$optlist);
			$this->tpl->assign("extlist",$ext_list);
		}
		load_plugin("module:".$m_rs["identifier"].":set:next",$m_rs);//执行模块前的操作
		load_plugin("list:set:next");
		$_hotid = $this->trans_lib->int("_hotid");
		if($_hotid)
		{
			$this->tpl->assign("_hotid",$_hotid);
		}
		$this->tpl->display("list/set.html");
	}

	function setok_f()
	{
		load_plugin("list:setok:prev");
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			$module_id = $this->trans_lib->int("module_id");
			if(!$module_id)
			{
				error("对不起，您的操作错误，没有指定应用模块");
			}
			sys_popedom($module_id.":add","tpl");
		}
		else
		{
			$rs = $this->list_m->get_one($id);
			$module_id = $rs["module_id"];
			sys_popedom($module_id.":modify","tpl");
		}
		$m_rs = $this->module_m->get_one($module_id);
		//获取核心数据
		$array_sys = array();
		if(!$id)
		{
			$array_sys["module_id"] = $module_id;
		}
		$array_sys["cate_id"] = $this->trans_lib->int("cate_id");
		$cateid = $array_sys["cate_id"];
		$array_sys["title"] = $this->trans_lib->safe("subject");
		$array_sys["s_rg"] = $this->trans_lib->safe("s_rg");
		$array_sys["s_qx"] = $this->trans_lib->safe("s_qx");
		$array_sys["s_ns"] = $this->trans_lib->safe("s_ns");
		$array_sys["rg"] = $this->trans_lib->safe("rg");
		$array_sys["qx"] = $this->trans_lib->safe("qx");
		$array_sys["ns"] = $this->trans_lib->safe("ns");
		$array_sys["hy"] = $this->trans_lib->safe("hy");
		
		$array_sys["style"] = $this->trans_lib->safe("style");
		$array_sys["hidden"] = $this->trans_lib->int("hidden");
		$array_sys["link_url"] = $this->trans_lib->safe("link_url");
		$array_sys["target"] = $this->trans_lib->int("target");
		$array_sys["author"] = $this->trans_lib->safe("author");
		$array_sys["author_type"] = $this->trans_lib->safe("author_type");
		$array_sys["ip"] = $this->trans_lib->safe("ip");
		if(!$array_sys["ip"])
		{
			$array_sys["ip"] = sys_ip();
		}
		$array_sys["seotitle"] = $this->trans_lib->safe("seotitle");
		$array_sys["keywords"] = $this->trans_lib->safe("keywords");
		$array_sys["description"] = $this->trans_lib->safe("description");
		$array_sys["note"] = $this->trans_lib->html("note");		
		//锁定标识串
		if(!$id || ($id && !$m_rs["lock_identifier"]))
		{
			$array_sys["identifier"] = $this->trans_lib->safe("identifier");
		}
		$array_sys["tplfile"] = $this->trans_lib->safe("tplfile");//内容模板
		$array_sys["hits"] = $this->trans_lib->int("hits");
		$array_sys["good_hits"] = $this->trans_lib->int("good_hits");
		$array_sys["bad_hits"] = $this->trans_lib->int("bad_hits");
		$post_date = $this->trans_lib->safe("post_date");
		$array_sys["post_date"] = $post_date ? strtotime($post_date) : $this->system_time;
		if($id)
		{
			//最后更改时间
			$array_sys["modify_date"] = $this->system_time;
		}
		$array_sys["thumb_id"] = $this->trans_lib->int("thumb_id");
		$array_sys["istop"] = isset($_POST["istop"]) ? 1 : 0;
		$array_sys["isvouch"] = isset($_POST["isvouch"]) ? 1 : 0;
		$array_sys["isbest"] = isset($_POST["isbest"]) ? 1 : 0;
		$array_sys["points"] = $this->trans_lib->int("points");
		if(!$id)
		{
			$array_sys["langid"] = $this->langid;
		}
		$array_sys["taxis"] = $this->trans_lib->int("taxis");
		$array_sys["htmltype"] = $this->trans_lib->safe("htmltype");//静态页存储方式
		
			if($this->trans_lib->safe("hy")==false){
		$array_sys["subtitle"] = $this->trans_lib->safe("subtitle");//副主题
		
		}else{
			$STR_2 = explode(",", $this->trans_lib->safe("hy"));
			$array_sys["subtitle"] = $STR_2[1];
			$array_sys["s_ns"] = $STR_2[0];
			}
		
		
		//针对电子商务里的核心字段
		
		
		$array_sys["price"] = $this->trans_lib->float("price");//价格
		$array_sys["price_currency"] = $this->trans_lib->safe("price_currency");//货币符号
		$array_sys["weight"] = $this->trans_lib->float("weight");//重量，系统使用Kg来计
		$array_sys["qty"] = $this->trans_lib->int("qty");//产品数量
		$array_sys["is_qty"] = $this->trans_lib->checkbox("is_qty");//是否启用产品数量统计
		$array_sys["qty_unit"] = $this->trans_lib->safe("qty_unit");//数量单位

		$insert_id = $this->list_m->save_sys($array_sys,$id);//存储数据
		if(!$insert_id)
		{
			error("数据存储失败，请检查",site_url("list,set","id=".$id));
		}
		//存储扩展分类
		$cate_string = $this->trans_lib->safe("cate_string");
		$cate_string = $cate_string ? $cate_string.",".$cateid : $cateid;
		if($cate_string)
		{
			$ext_catelist = sys_id_list($cate_string,"intval");
			$this->list_m->save_catelist($insert_id,$ext_catelist);
		}
		unset($array_sys);//注销存储信息
		//[读取核心模块配置信息]
		load_plugin("module:".$m_rs["identifier"].":setok:prev",$id ? $id : $m_rs);//执行模块前的操作
		if($m_rs)
		{
			//判断是否
			$extlist = $this->_load_ext_fields($module_id);
			if(!$extlist) $extlist = array();
			foreach($extlist AS $key=>$value)
			{
				$array_ext = array();
				$array_ext["id"] = $insert_id;
				$array_ext["field"] = $value["identifier"];//扩展字段信息
				$format_type = $value["if_html"] ? "html" : "safe";
				if($value["if_js"] && $format_type == "html")
				{
					$this->trans_lib->setting(true,true,true);
				}
				$val = $this->trans_lib->$format_type($value["identifier"]);
				//如果插入的数据是时间表单
				if($value["input"] == "time" && $val)
				{
					$val = strtotime($val);
				}
				if($value["if_js"] && $format_type == "html")
				{
					$this->trans_lib->setting(false,false,false);
				}
				if(is_array($val))
				{
					$val = implode(",",$val);
				}
				$array_ext["val"] = $val;
				$this->list_m->save_ext($array_ext,$value["tbl"]);
			}
		}
		//提示添加成功，进入跳转
		$go_url = $this->url("list","module_id=".$module_id."&cate_id=".$cateid);
		load_plugin("module:".$m_rs["identifier"].":setok:next",$id ? $id : $insert_id);//执行模块前的操作
		load_plugin("list:setok:next",$id ? $id : $insert_id);//执行模块前的操作
		//判断是否接入地方
		$_hotid = $this->trans_lib->int("_hotid");
		if($_hotid)
		{
			$this->load_model("hotlink");
			$hot_rs = $this->hotlink_m->get_one($_hotid);
			$hot_rs["linkurl"] = str_replace("{admin}",HOME_PAGE,$hot_rs["linkurl"]);
			$hot_rs["linkurl"] = str_replace("{c}",$this->config->c,$hot_rs["linkurl"]);
			$hot_rs["linkurl"] = str_replace("{f}",$this->config->f,$hot_rs["linkurl"]);
			$hot_rs["linkurl"] = str_replace("{d}",$this->config->d,$hot_rs["linkurl"]);
			$go_url = $hot_rs["linkurl"];
			$go_url.= "&_hotid=".$_hotid."&";
		}
		error("数据存储成功，请稍候……",$this->url("list",$go_url));
	}

	function copy_list_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("没有指定要批量创建的主题！");
		}
		$total = $this->trans_lib->int("total");
		if(!$total) $total = 30;
		$rs = $this->list_m->get_one($id);
		if(!$rs)
		{
			exit("没有找到相关主题信息");
		}
		$array_sys = array();
		$array_sys["module_id"] = $rs["module_id"];
		$array_sys["cate_id"] = $rs["cate_id"];
		$array_sys["title"] = $rs["title"];
		$array_sys["style"] = $rs["style"];
		$array_sys["hidden"] = $rs["hidden"];
		$array_sys["link_url"] = $rs["link_url"];
		$array_sys["target"] = $rs["target"];
		$array_sys["author"] = $rs["author"];
		$array_sys["author_type"] = $rs["author_type"];
		$array_sys["ip"] = $rs["ip"];
		$array_sys["keywords"] = $rs["keywords"];
		$array_sys["description"] = $rs["description"];
		$array_sys["note"] = $rs["note"];
		$array_sys["tplfile"] = $rs["tplfile"];
		$array_sys["hits"] = $rs["hits"];
		$array_sys["good_hits"] = $rs["good_hits"];
		$array_sys["bad_hits"] = $rs["bad_hits"];
		$array_sys["post_date"] = $rs["post_date"];
		$array_sys["modify_date"] = $rs["modify_date"];
		$array_sys["thumb_id"] = $rs["thumb_id"];
		$array_sys["istop"] = $rs["istop"];
		$array_sys["isvouch"] = $rs["isvouch"];
		$array_sys["isbest"] = $rs["isbest"];
		$array_sys["points"] = $rs["points"];
		$array_sys["langid"] = $rs["langid"];
		$array_sys["taxis"] = $rs["taxis"];
		$array_sys["htmltype"] = $rs["htmltype"];
		if($rs[hy]==false){
		$array_sys["subtitle"] = $rs["subtitle"];}else{
			$STR_2 = explode(",", $rs["hy"]);
			$array_sys["subtitle"] = $STR_2[1];
			$array_sys["s_ns"] = $STR_2[0];
			}
		$array_sys["weight"] = $rs["weight"];
		$array_sys["ns"] = $rs["ns"];
		//$array_sys["s_ns"] = $rs["s_ns"];
		$array_sys["qx"] = $rs["qx"];
		$array_sys["s_qx"] = $rs["s_qx"];
		$array_sys["rg"] = $rs["rg"];
		$array_sys["s_rg"] = $rs["s_rg"];
		
		
		
		$array_sys["price"] = $rs["price"];
		$array_sys["price_currency"] = $rs["price_currency"];
		$array_sys["qty"] = $rs["qty"];
		$array_sys["is_qty"] = $rs["is_qty"];
		$array_sys["qty_unit"] = $rs["qty_unit"];
		$m_rs = $this->module_m->get_one($rs["module_id"]);
		if(!$m_rs)
		{
			exit("没有找到主题对应的模块，请检查！");
		}

		//取得扩展字段
		$extlist = $this->_load_ext_fields($rs["module_id"]);
		if(!$extlist) $extlist = array();

		//存储核心数据
		for($i=0;$i<$total;$i++)
		{
			$insert_id = $this->list_m->save_sys($array_sys);//存储数据
			if(!$insert_id)
			{
				continue;
			}
			//更新编码
			if($rs["identifier"])
			{
				$tmp_identifier = array();
				$tmp_identifier["identifier"] = $rs["identifier"].$insert_id;
				$this->list_m->save_sys($tmp_identifier,$insert_id);//存储数据
			}
			$this->list_m->set_taxis($insert_id,$insert_id);
			//存储扩展字段
			foreach($extlist AS $key=>$value)
			{
				$array_ext = array();
				$array_ext["id"] = $insert_id;
				$array_ext["field"] = $value["identifier"];//扩展字段信息
				$array_ext["val"] = $rs[$value["identifier"]];
				$this->list_m->save_ext($array_ext,$value["tbl"]);
			}
			//更新扩展分类
			$this->list_m->save_catelist($insert_id,array($rs["cate_id"]));
		}
		exit("ok");
	}

	function ajax_update_cate_f()
	{
		$cateid = $this->trans_lib->int("cateid");
		$id = $this->trans_lib->safe("id");
		if(!$cateid || !$id)
		{
			exit("error: 操作错误！");
		}
		//$this->list_m->set_cate($id,$cateid);
		$ext_catelist = sys_id_list($id,"intval");
		if(!$ext_catelist) $ext_catelist = array();
		foreach($ext_catelist AS $key=>$value)
		{
			//更新扩展分类
			$this->list_m->update_main_cate($value,$cateid);
		}
		exit("ok");
	}

	function ajax_pl_f()
	{
		$id = $this->trans_lib->safe("id");
		$field = $this->trans_lib->safe("field");
		$val = $this->trans_lib->int("val");
		if(!$id)
		{
			exit("error:没有指定ID");
		}
		$array = sys_id_list($id);
		if(!$array[0])
		{
			exit("error:错误");
		}
		$rs = $this->list_m->get_one($array[0]);
		sys_popedom($rs["module_id"].":check","ajax");
		$this->list_m->set_pl($id,$field,$val);
		exit("ok");
	}

	function taxis_pl_f()
	{
		$taxis = $this->trans_lib->safe("taxis");
		if(!$taxis || !is_array($taxis) || count($taxis)<1)
		{
			exit("error: 错误，没有取得有效信息");
		}
		foreach($taxis AS $key=>$value)
		{
			$key = intval($key);
			$value = intval($value);
			$this->list_m->set_taxis($key,$value);
		}
		exit("ok");
	}

	function ajax_status_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error:没有指定ID");
		}
		$rs = $this->list_m->get_one($id);
		sys_popedom($rs["module_id"].":check","ajax");
		$status = $rs["status"] ? 0 : 1;
		$this->list_m->set_pl($id,"status",$status);
		exit("ok");
	}

	function ajax_del_f()
	{
		$id = $this->trans_lib->safe("id");
		if(!$id)
		{
			exit("error:没有指定ID");
		}
		$array = sys_id_list($id);
		if(!$array[0])
		{
			exit("error:错误");
		}
		$rs = $this->list_m->get_one($array[0]);
		$module_id = $rs["module_id"];
		sys_popedom($module_id.":delete","ajax");
		$this->list_m->del($id);
		exit("ok");
	}

	//加载分类
	function _load_cate($module_id,$cate_id,$if_array=false,$if_ext_select=true)
	{
		$this->cate_m->langid($_SESSION["sys_lang_id"]);
		$this->cate_m->get_catelist($module_id);
		$ext_message = $if_ext_select ? $this->lang["select_cate"] : "";
		$cate_html = $this->cate_m->html_select("cate_id",$cate_id,$ext_message);
		$this->tpl->assign("cate_html",$cate_html);
		if($if_array)
		{
			$cate_list_array = $this->cate_m->html_select_array();
			$this->tpl->assign("cate_list_array",$cate_list_array);
		}
		return $cate_list_array ? $cate_list_array : $this->cate_m->catelist();
	}

	//加载模块
	function _load_module($module_id)
	{
		$m_rs = $this->module_m->get_one($module_id);
		$this->tpl->assign("m_rs",$m_rs);
		return $m_rs;
	}

	//加载扩展的字段
	function _load_ext_fields($module_id)
	{
		if(!$module_id)
		{
			return false;
		}
		//读取扩展的字段列表
		$ext_list = $this->module_m->fields_index($module_id,1);
		return $ext_list;
	}

	//虚弹分类信息
	function open_cate_f()
	{
		$cate_id = $this->trans_lib->int("cate_id");
		$cate_string = $this->trans_lib->safe("cate_string");
		$this->tpl->assign("cate_id",$cate_id);
		$this->tpl->assign("cate_string",$cate_string);
		$cate_list = $cate_string ? sys_id_list($cate_string,"intval") : array();
		$this->tpl->assign("cate_list",$cate_list);
		$mid = $this->trans_lib->int("mid");
		$this->tpl->assign("mid",$mid);
		if(!$mid)
		{
			error("未指定模块，请返回！");
		}
		$catelist = $this->_load_cate($mid,$cate_id,true,false);
		$this->tpl->assign("catelist",$catelist);
		$this->_load_module($mid);
		$this->tpl->display("list/cate.html");
	}
}
?>