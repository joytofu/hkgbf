<?php
/***********************************************************
	Filename: app/www/control/post.php
	Note	: 修正信息发布，如果有游客输入密码再发布的记录
	Version : 3.0
	Author  : qinggan
	Update  : 2011-10-17 10:17
***********************************************************/
class post_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("post");
		$this->load_model("module");
		$this->load_model("cate");
	}

	function index_c()
	{
		$this->__construct();
	}

	function _load_moduel($module_id)
	{
		$m_rs = $this->module_m->get_one($module_id);
		if(!$m_rs || !$m_rs["status"] || $m_rs["ctrl_init"] != "list")
		{
			error($this->lang["post_not_module"],$this->url());
		}
		$this->tpl->assign("m_rs",$m_rs);
		return $m_rs;
	}

	function list_f()
	{
		//这一模块仅限会员查看
		if(!$_SESSION["user_id"])
		{
			error("",$this->url());
		}
		$backurl = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : $this->url("usercp");
		$page_url = $this->url("post,list");
		$module_id = $this->trans_lib->int("module_id");
		if(!$module_id)
		{
			$ms = $this->trans_lib->safe("ms");
			if($ms)
			{
				$module_id = $this->module_m->get_mid_from_code($ms);
			}
			if(!$module_id)
			{
				error($this->lang["post_not_mid"],$backurl);
			}
		}
		$this->tpl->assign("module_id",$module_id);
		$page_url .= "module_id=".$module_id."&";
		$m_rs = $this->_load_moduel($module_id);
		//取得模块列表
		$ifcate = $m_rs["if_cate"] ? true : false;
		$ifbiz = $m_rs["if_biz"] ? true : false;
		$ifthumb = $m_rs["if_thumb"] ? true : false;
		$this->tpl->assign("ifcate",$ifcate);
		$pageid = $this->trans_lib->int(SYS_PAGEID);
		$this->post_m->set_condition("m.author='".$_SESSION["user_name"]."'");
		$this->post_m->set_condition("m.author_type='user'");
		$this->post_m->set_condition("m.langid='".$_SESSION["sys_lang_id"]."'");//区分语言
		$this->post_m->set_condition("m.module_id='".$module_id."'");
		if($ifcate)
		{
			$cate_id = $this->trans_lib->int("cate_id");
			if($cate_id>0)
			{
				$page_url .= "cate_id=".$cate_id."&";
				$this->post_m->set_condition("m.cate_id='".$cate_id."'");
			}
		}
		$keywords = $this->trans_lib->safe("keywords");
		if($keywords)
		{
			$this->post_m->set_condition("(m.title LIKE '%".$keywords."%' OR m.keywords LIKE '%".$keywords."%' OR m.description LIKE '%".$keywords."%' OR m.note LIKE '%".$keywords."%')");
			$page_url .= "keywords=".rawurlencode($keywords)."&";
		}
		$rslist = $this->post_m->get_list($pageid,$ifcate,$ifbiz,$ifthumb);
		$this->tpl->assign("rslist",$rslist);
		$total_count = $this->post_m->get_count();//取得总数
		$pagelist = $this->page_lib->page($page_url,$total_count,false,false);
		$this->tpl->assign("del_refresh_url",$page_url.SYS_PAGEID."=".$pageid);
		$this->tpl->assign("pagelist",$pagelist);
		//如果有分类
		if($ifcate)
		{
			//$condition = $_SESSION["user_id"] ? "c.ifuser='1'" : "c.ifguest='1'";
			$this->cate_m->get_catelist($module_id,$condition);
			$cate_html = $this->cate_m->html_select("cate_id",$cate_id,$this->lang["all_category"]);
			$this->tpl->assign("cate_html",$cate_html);
		}
		$tplfile = "post_list_".$m_rs["identifier"];
		$chk_tplfile = ROOT.$this->tpl->tpldir."/".$tplfile.".".$this->tpl->ext;
		if(file_exists($chk_tplfile))
		{
			$this->tpl->display($tplfile.".".$this->tpl->ext);
		}
		else
		{
			$this->tpl->display("post_list.".$this->tpl->ext);
		}
	}

	function set_f()
	{
		//返回上一级网址
		$referurl = $this->trans_lib->safe("referurl");
		$referurl = $referurl ? $referurl : $_SERVER["HTTP_REFERER"];
		if(!$referurl) $referurl = $this->url();
		$iframe = $this->trans_lib->safe("iframe");
		if($iframe == "true")
		{
			$referurl = "";
		}
		$this->tpl->assign("referurl",$referurl);
		//现在判断是否有权限
		$no_popedom = $this->lang["post_not_popedom"];//没有权限的提示语
		$popedom = sys_user_popedom("post");//获取发布权限
		if(!$popedom) error($no_popedom,$referurl);
		//判断是否有主题ID
		$id = $this->trans_lib->int("id");
		if($id)
		{
			//锁定主题，限制非会员不允许编辑主题
			if(!$_SESSION["user_id"]) error($no_popedom,$referurl);
			$rs = $this->post_m->get_one($id);
			//获取主题为空时提示错误！
			if(!$rs) error($this->lang["msg_not_rs"],$referurl);
			//判断会员和发布人是否一致，不一致禁止执行操作
			if($rs["author"] != $_SESSION["user_name"] || $rs["author_type"] != "user")
			{
				error($no_popedom,$referurl);
			}
			$this->tpl->assign("rs",$rs);
			$this->tpl->assign("id",$id);
			$module_id = $rs["module_id"];
			$cate_id = $rs["cate_id"];
		}
		else
		{
			$module_id = $this->trans_lib->int("module_id");
			if(!$module_id)
			{
				$ms = $this->trans_lib->safe("ms");
				$module_id = $this->module_m->get_mid_from_code($ms);
				if(!$module_id) error($this->lang["post_not_mid"],$referurl);
			}
			$cate_id = $this->trans_lib->int("cate_id");
		}
		$this->tpl->assign("module_id",$module_id);
		$m_rs = $this->_load_moduel($module_id);
		//读取内容
		$ifcate = $m_rs["if_cate"] ? true : false;
		$this->tpl->assign("ifcate",$ifcate);
		if($ifcate)
		{
			//判断无分类权限时，禁止执行操作
			if(!$popedom["category"] && !$id) error($no_popedom,$referurl);
			$condition = "c.id IN(".sys_id_string($popedom["category"],",","intval").") ";
			$chk_return = $this->cate_m->get_catelist($module_id,$condition);
			if(!$chk_return)
			{
				//如果同时没有指定ID，那么返回无权限
				if(!$id) error($no_popedom,$referurl);
				$cate_rs = $this->cate_m->get_one($cate_id);
				$cate_html = "<select name='cate_id' id='cate_id'><option value='".$cate_id."'>".$cate_rs["cate_name"]."</option></select>";
			}
			else
			{
				$cate_html = $this->cate_m->html_select("cate_id",$cate_id,$this->lang["category_select"]);
			}
			$this->tpl->assign("cate_html",$cate_html);
		}
		else
		{
			//无主题时，如果也没有相匹配的模块ID，则提示无权限操作！
			if(!$popedom["module"] && !$id) error($no_popedom,$referurl);
			if(!in_array($module_id,$popedom["module"]) && !$id) error($no_popedom,$referurl);
		}
		$ext_list = $this->module_m->fields_index($module_id);
		if($ext_list && is_array($ext_list) && count($ext_list)>0)
		{
			$optlist = array();
			$this->load_lib("phpok_input");
			$extlist_must = $extlist_need = array();
			foreach($ext_list AS $key=>$value)
			{
				if(!$value["if_post"] && !$value["if_guest"])
				{
					continue;
				}
				$_field_name = $value["identifier"];
				$value["default_val"] = $rs[$_field_name] ? $rs[$_field_name] : $value["default_val"];
				$extlist = $this->phpok_input_lib->get_html($value);
				$extlist_must[] = $extlist;
				if($value["input"] == "opt")
				{
					$optlist[] = $value;
				}
				$extlist_need[] = $value;
			}
			$this->tpl->assign("extlist_must",$extlist_must);
			$this->tpl->assign("optlist",$optlist);
			$this->tpl->assign("extlist",$extlist_need);
		}
		$tplfile = "post_".$m_rs["identifier"];
		if($iframe == "true")
		{
			$tplfile = "iframe_post";
			$chk_tplfile = ROOT.$this->tpl->tpldir."/iframe_post.".$this->tpl->ext;
			$bgcolor = $this->trans_lib->safe("bgcolor");
			if(!$bgcolor) $bgcolor = "#FFF";
			$this->tpl->assign("bgcolor",$bgcolor);
			//返回地址
			$goback = $this->trans_lib->safe("_goback");
			if(!$goback)
			{
				$goback = $this->url("list","ms=".$m_rs["identifier"]);
			}
			$this->tpl->assign("_goback",$goback);
		}
		else
		{
			$chk_tplfile = ROOT.$this->tpl->tpldir."/".$tplfile.".".$this->tpl->ext;
		}
		//取得参数外的其他扩展数据
		$_ext = array($this->config->c,$this->config->f,$this->config->d,"ms","module_id","cate_id","id");
		foreach($_GET AS $key=>$value)
		{
			if(!in_array($key,$_ext) && $value)
			{
				$value = $this->trans_lib->safe($key);
				$this->tpl->assign($key,$value);
			}
		}
		if(file_exists($chk_tplfile))
		{
			$this->tpl->display($tplfile.".".$this->tpl->ext);
		}
		else
		{
			$this->tpl->display("post_set.".$this->tpl->ext);
		}
	}

	function setok_f()
	{
		//判断是否有使用验证码
		if(function_exists("imagecreate") && defined("SYS_VCODE_USE") && SYS_VCODE_USE == true)
		{
			$chk = $this->trans_lib->safe("sys_check");
			if(!$chk)
			{
				error($this->lang["login_vcode_empty"],$_SERVER["HTTP_REFERER"]);
			}
			$chk = md5($chk);
			if($chk != $_SESSION[SYS_VCODE_VAR])
			{
				error($this->lang["login_vcode_false"],$_SERVER["HTTP_REFERER"]);
			}
			unset($_SESSION[SYS_VCODE_VAR]);
		}
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			$module_id = $this->trans_lib->int("module_id");
			if(!$module_id)
			{
				error($this->lang["post_not_mid"],$this->url());
			}
		}
		else
		{
			$rs = $this->post_m->get_one($id);
			$module_id = $rs["module_id"];
		}
		$m_rs = $this->_load_moduel($module_id);
		//获取核心数据
		$array_sys = array();
		if(!$id)
		{
			$array_sys["module_id"] = $module_id;
		}
		$array_sys["cate_id"] = $this->trans_lib->int("cate_id");
		$cateid = $array_sys["cate_id"];
		$array_sys["title"] = $this->trans_lib->safe("subject");
		if($_SESSION["user_id"])
		{
			$array_sys["author"] = $_SESSION["user_name"];
			$array_sys["author_type"] = "user";
		}
		else
		{
			$username = $this->trans_lib->safe("username");
			$password = $this->trans_lib->safe("password");
			$_is_user = false;
			if($password && $username)
			{
				//检查会员不存在时的警告
				$rs = $this->user_m->user_from_name($username);
				if($rs && $rs["pass"] == sys_md5($password))
				{
					$array_sys["author"] = $username;
					$array_sys["author_type"] = "user";
					//尝试登录，下次发表留言时不用输入密码
					if($rs["status"] && $rs["status"] != "2")
					{
						$_SESSION["user_id"] = $rs["id"];
						$_SESSION["user_name"] = $rs["name"];
						$_SESSION["group_id"] = $rs["groupid"];
						$_SESSION["user_rs"]= $rs;
						$_SESSION[SYS_CHECKED_SESSION_ID] = sys_md5($rs);
					}
				}
				else
				{
					$array_sys["author"] = $username;
					$array_sys["author_type"] = "guest";
				}
			}
			else
			{
				$array_sys["author"] = $username ? $username : $this->lang["guest"];
				$array_sys["author_type"] = "guest";
			}
		}
		$array_sys["ip"] = sys_ip();//发布人IP
		$array_sys["post_date"] = $this->system_time;
		$array_sys["link_url"] = $this->trans_lib->safe("link_url");
		if($id)
		{
			$array_sys["post_date"] = $rs["post_date"];
			$array_sys["modify_date"] = $this->system_time;
		}
		$array_sys["thumb_id"] = $this->trans_lib->int("thumb_id");
		if(!$id)
		{
			$array_sys["langid"] = $_SESSION["sys_lang_id"];
		}
		$array_sys["htmltype"] = "cateid";
		$array_sys["status"] = 0;
		//如果有分类
		if($cateid)
		{
			//$condition = $_SESSION["user_id"] ? "c.ifuser='1'" : "c.ifguest='1'";
			$cate_rs = $this->cate_m->get_one($cateid,$condition);
			if(!$cate_rs)
			{
				error($this->lang["post_cate_error"],$this->url("post,set","id=".$id."&module_id=".$module_id));
			}
			$tmp_check_status = $_SESSION["user_id"] ? "chk_user" : "chk_guest";
			$array_sys["status"] = $cate_rs[$tmp_check_status];
		}
		else
		{
			$tmp_check_status = $_SESSION["user_id"] ? "u_free_check" : "g_free_check";
			$array_sys["status"] = $m_rs[$tmp_check_status];
		}
		$array_sys["qx"] = $this->trans_lib->float("qx");//
		$array_sys["price"] = $this->trans_lib->float("price");//价格
		$array_sys["price_currency"] = $this->trans_lib->safe("price_currency");//货币符号
		$array_sys["weight"] = $this->trans_lib->float("weight");//重量，系统使用Kg来计
		$array_sys["qty"] = $this->trans_lib->int("qty");//产品数量
		$array_sys["is_qty"] = $this->trans_lib->int("is_qty");//是否启用产品数量统计
		$array_sys["qty_unit"] = $this->trans_lib->safe("qty_unit");//产品数量

		$insert_id = $this->post_m->save_sys($array_sys,$id);//存储数据
		if(!$insert_id)
		{
			error($this->lang["error_save"],site_url("post,set","id=".$id."&module_id=".$module_id));
		}
		unset($array_sys);//注销存储信息
		//判断是否
		$extlist = $this->module_m->fields_index($module_id);
		foreach(($extlist ? $extlist : array()) AS $key=>$value)
		{
			$array_ext = array();
			$array_ext["id"] = $insert_id;
			$array_ext["field"] = $value["identifier"];//扩展字段信息
			$format_type = $value["if_html"] ? "html" : "safe";
			$val = $this->trans_lib->$format_type($value["identifier"]);
			//如果插入的数据是时间表单
			if($value["input"] == "time" && $val)
			{
				$val = strtotime($val);
			}
			if(is_array($val))
			{
				$val = implode(",",$val);
			}
			$array_ext["val"] = $val;
			$this->post_m->save_ext($array_ext,$value["tbl"]);
		}
		$goback = $this->trans_lib->safe("_to_url");
		if(!$goback)
		{
			if($_SESSION["user_id"])
			{
				$goback = site_url("post,list","module_id=".$module_id."&cate_id=".$cateid);
			}
			else
			{
				$goback = $_SERVER["HTTP_REFERER"] ? $_SERVER["HTTP_REFERER"] : $this->url();
			}
		}
		//判断是否有邮件通知管理员
		if($m_rs["if_email"])
		{
			$this->load_lib("email");
			$this->email_lib->module_mail($insert_id);//通知客户订单信息
		}
		error($this->lang["save_success"],$goback);
	}

	function del_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit($this->lang["del_not_id"]);
		}
		$rs = $this->post_m->get_one($id);
		if($rs["author"] != $_SESSION["user_name"] || $rs["author_type"] != "user")
		{
			exit($this->lang["post_del_not_me"]);
		}
		$this->post_m->del($id);
		exit("ok");
	}
}
?>