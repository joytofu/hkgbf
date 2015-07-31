<?php
/***********************************************************
	Filename: app/admin/user.php
	Note	: 会员中心
	Version : 3.0
	Author  : qinggan
	Update  : 2009-12-23
***********************************************************/
class user_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("user");
		$this->load_model("usergroup");
		$this->load_model("module");
		$this->load_model("user_model",true);
	}

	//兼容PHP4的写法
	function user_c()
	{
		$this->__construct();
	}

	//会员列表
	function index_f()
	{
		sys_popedom("user:list","tpl");
		load_plugin("user:index:prev");
		$this->tpl->assign("m_rs",$rs);
		$pageid = $this->trans_lib->int(SYS_PAGEID);
		$keywords = $this->trans_lib->safe("keywords");
		$page_url = site_url("user");
		if($keywords)
		{
			$this->tpl->assign("keywords",$keywords);
			$page_url.="keywords=".rawurlencode($keywords)."&";
		}
		$rslist = $this->user_m->get_list($pageid,$keywords);
		$this->tpl->assign("rslist",$rslist);
		$count = $this->user_m->get_count($keywords);
		$this->tpl->assign("total",$count);
		$pagelist = $this->page_lib->page($page_url,$count);
		$this->tpl->assign("pagelist",$pagelist);
		load_plugin("user:index:next");
		$this->tpl->display("user/list.html");
	}

	function set_f()
	{
		load_plugin("user:set:prev");
		$id = $this->trans_lib->int("id");
		$groupid = $this->trans_lib->int("groupid");
		if($id)
		{
			sys_popedom("user:modify","tpl");
			$rs = $this->user_m->get_one($id);
			$this->tpl->assign("rs",$rs);
			if(!$groupid) $groupid = $rs["groupid"];
		}
		else
		{
			sys_popedom("user:add","tpl");
		}
		if(!$groupid)
		{
			$group_rs = $this->usergroup_m->get_default();
			$groupid = $group_rs["id"];
		}
		if(!$groupid)
		{
			error("没有获取到用户组！");
		}
		$this->tpl->assign("groupid",$groupid);
		$grouplist = $this->usergroup_m->get_all();
		$this->tpl->assign("grouplist",$grouplist);
		//加载扩展字段
		$ext_list = $this->usergroup_m->fields_index($groupid,1);
		//echo "<pre>".print_r($ext_list,true)."</pre>";
		if($ext_list && is_array($ext_list) && count($ext_list)>0)
		{
			$optlist = array();
			$this->load_lib("phpok_input");
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
			//echo "<pre>".print_r($extlist);
		}
		load_plugin("user:set:next");
		$this->tpl->display("user/set.html");
	}

	function view_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			error("error: 操作错误");
		}
		load_plugin("user:view:prev");
		$rs = $this->user_m->get_one($id);
		$this->tpl->assign("rs",$rs);
		$ext_list = $this->usergroup_m->fields_index($rs["groupid"],1);
		if($ext_list && is_array($ext_list) && count($ext_list)>0)
		{
			$extlist = array();
			foreach($ext_list AS $key=>$value)
			{
				$_field_name = $value["identifier"];
				$tmp = array();
				$tmp["title"] = $value["title"];
				$tmp["input"] = $value["input"];
				$tmp["val"] = $rs[$_field_name] ? $rs[$_field_name] : $value["default_val"];
				if($value["input"] == "time" && $tmp["val"])
				{
					$tmp["val"] = date("Y-m-d H:i",$tmp["val"]);
				}
				$extlist[] = $tmp;
				unset($tmp);
			}
			$this->tpl->assign("extlist",$extlist);
		}
		load_plugin("user:view:next");
		$this->tpl->display("user/view.html");
	}

	function chk_f()
	{
		load_plugin("user:chk:prev");
		$id = $this->trans_lib->int("id");
		$email = $this->trans_lib->safe("email");
		if(!$email)
		{
			exit("error: 会员邮箱不允许为空");
		}
		$name = $this->trans_lib->safe("name");
		if(!$name)
		{
			exit("error: 会员账号不允许为空");
		}
		$rs_name = $this->user_m->chk_name($name,$id);
		if($rs_name)
		{
			exit("error:会员账号已经存在");
		}
		$rs_email = $this->user_m->chk_email($email,$id);
		if($rs_email)
		{
			exit("error:会员邮箱已经存在");
		}
		load_plugin("user:chk:next");
		exit("ok");
	}

	//存储信息
	function setok_f()
	{
		$id = $this->trans_lib->int("id");
		if($id)
		{
			sys_popedom("user:modify","tpl");
		}
		else
		{
			sys_popedom("user:add","tpl");
		}
		load_plugin("user:setok:prev");
		$array = array();
		$array["name"] = $this->trans_lib->safe("name");
		$pass = $this->trans_lib->safe("pass");
		if($pass)
		{
			$array["pass"] = sys_md5($pass);
		}
		else
		{
			if(!$id)
			{
				$array["pass"] = sys_md5("123456");
			}
		}
		$array["ifshow"] = $this->trans_lib->int("ifshow");//是否在前台显示
		$array["email"] = $this->trans_lib->safe("email");//模板目录
		$regdate = $this->trans_lib->safe("regdate");
		$array["regdate"] = $regdate ? strtotime($regdate) : $this->system_time;
		$array["thumb_id"] = $this->trans_lib->int("thumb_id");//存储图像
		$array["groupid"] = $this->trans_lib->int("groupid");//存储会员组
		//存储扩展表信息
		$insert_id = $this->user_m->save($array,$id);
		$extlist = $this->usergroup_m->fields_index($array["groupid"],1);
		if(!$extlist) $extlist = array();
		$ext_array = array();
		foreach($extlist AS $key=>$value)
		{
			$array_ext = array();
			$array_ext["id"] = $insert_id;
			$array_ext["field"] = $value["identifier"];//扩展字段信息
			$val = $this->trans_lib->safe($value["identifier"]);
			if($value["input"] == "time" && $val)
			{
				$val = strtotime($val);
			}
			if(is_array($val))
			{
				$val = sys_id_string(",",$val);
			}
			$array_ext["val"] = $val;
			$this->user_model->save_ext($array_ext);
		}
		//存储分类信息
		load_plugin("user:setok:next");
		error("会员信息添加/存储成功",site_url("user"));
	}

	function ajax_status_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error:没有指定ID");
		}
		sys_popedom("user:check","ajax");
		$rs = $this->user_m->get_one($id);
		$status = $rs["status"] ? 0 : 1;
		$this->user_m->set_status($id,$status);
		exit("ok");
	}

	function ajax_del_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error:没有指定ID");
		}
		sys_popedom("user:delete","ajax");
		load_plugin("user:del:prev");
		$this->user_m->del($id);
		load_plugin("user:del:next");
		exit("ok");
	}

}
?>