<?php
/***********************************************************
	Filename: app/www/control/usercp.php
	Note	: 用户控制面板
	Version : 3.0
	Author  : qinggan
	Update  : 2011-10-14 15:22
***********************************************************/
class usercp_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("user");
		$this->load_model("usergroup");
		$this->load_model("user_model",true);
	}

	function usercp_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		if(!$_SESSION["user_id"])
		{
			error($this->lang["user_not_login"],site_url("login"));
		}
		load_plugin("usercp:index:prev");
		//判断是否存在模板文件
		if(file_exists($this->tpl->tpldir."/usercp.".$this->tpl->ext))
		{
			$this->tpl->display("usercp.".$this->tpl->ext);
		}
		else
		{
			$this->my_f();
		}
	}


	function my_f()
	{
		if(!$_SESSION["user_id"])
		{
			error($this->lang["user_not_login"],site_url("login"));
		}
		load_plugin("usercp:index:prev");
		$rs = $this->user_m->user_from_id($_SESSION["user_id"]);
		$this->tpl->assign("rs",$rs);
		$sitetitle = $this->lang["usercp_info"]." - ".$this->lang["usercp"];
		$this->tpl->assign("sitetitle",$sitetitle);
		$array[0]["title"] = $this->lang["usercp"];
		$array[0]["url"] = site_url("usercp");
		$array[1]["title"] = $this->lang["usercp_info"];
		$this->tpl->assign("leader",$array);
		//生成扩展文本
		$ext_list = $this->usergroup_m->fields_index($rs["groupid"],1);
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
		}
		load_plugin("usercp:index:next");
		$this->tpl->display("usercp_info.".$this->tpl->ext);
	}

	//存储个人信息
	function info_f()
	{
		if(!$_SESSION["user_id"])
		{
			error($this->lang["user_not_login"],site_url("login"));
		}
		load_plugin("usercp:info:prev");
		$array = array();
		$array["email"] = $this->trans_lib->safe("email");
		$array["rname"] = $this->trans_lib->safe("rname");
		if(!$array["email"])
		{
			error($this->lang["empty_email"],site_url("usercp,my"));
		}
		$array["thumb_id"] = $this->trans_lib->int("thumb_id");
		$this->user_m->update_info($array,$_SESSION["user_id"]);
		//更新扩展信息
		$extlist = $this->usergroup_m->fields_index($_SESSION["group_id"],1);
		if(!$extlist) $extlist = array();
		$ext_array = array();
		foreach($extlist AS $key=>$value)
		{
			$array_ext = array();
			$array_ext["id"] = $_SESSION["user_id"];
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
		$rs = $this->user_m->user_from_id($_SESSION["user_id"]);
		$_SESSION["user_rs"]= $rs;
		load_plugin("usercp:info:next");
		error($this->lang["usercp_save_success"],site_url("usercp,my"));
	}

	function pass_f()
	{
		if(!$_SESSION["user_id"])
		{
			error($this->lang["user_not_login"],site_url("login"));
		}
		load_plugin("usercp:pass:prev");
		$sitetitle = $this->lang["usercp_changepass"]." - ".$this->lang["usercp"];
		$this->tpl->assign("sitetitle",$sitetitle);
		$array[0]["title"] = $this->lang["usercp"];
		$array[0]["url"] = site_url("usercp");
		$array[1]["title"] = $this->lang["usercp_changepass"];
		$this->tpl->assign("leader",$array);
		load_plugin("usercp:pass:next");
		$this->tpl->display("usercp_pass.".$this->tpl->ext);
	}

	function passok_f()
	{
		if(!$_SESSION["user_id"])
		{
			error($this->lang["user_not_login"],site_url("login"));
		}
		load_plugin("usercp:passok:prev");
		$rs = $this->user_m->user_from_id($_SESSION["user_id"]);
		$old = $this->trans_lib->safe("oldpass");
		$new = $this->trans_lib->safe("newpass");
		$chk = $this->trans_lib->safe("chkpass");
		$mima=$this->trans_lib->safe("newpass");
		if(!$old || sys_md5($old) != $rs["pass"])
		{
			error($this->lang["usercp_not_oldpass"],site_url("usercp,pass"));
		}
		if(!$new || $new != $chk)
		{
			error($this->lang["usercp_not_newpass"],site_url("usercp,pass"));
		}
		if($new == $old)
		{
			error($this->lang["usercp_old_new"],site_url("usercp,pass"));
		}
		//更新密码
	
		$this->user_m->update_pass(sys_md5($new),$_SESSION["user_id"],$mima);
		load_plugin("usercp:passok:next");
		error($this->lang["pass_save_success"],site_url("usercp,pass"));
	}
}
?>