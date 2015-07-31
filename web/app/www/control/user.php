<?php
/***********************************************************
	Filename: app/www/control/user.php
	Note	: 用户控制面板
	Version : 3.0
	Author  : qinggan
	Update  : 2011-10-14 15:22
***********************************************************/
class user_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("user");
		$this->load_model("usergroup");
	}

	function user_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			error("没有指定会员ID",site_url("userlist"));
		}
		$rs = $this->user_m->user_from_id($id);
		if($rs["ifshow"] == 2)
		{
			error("你没有查看该会员的权限！",site_url("userlist"));
		}
		$groupid = $rs["groupid"];
		$group_rs = $this->usergroup_m->get_one($groupid);
		if($group_rs && !$group_rs["ifshow"] && !$rs["ifshow"])
		{
			error("你没有查看该会员的权限！",site_url("userlist"));
		}
		$this->tpl->assign("rs",$rs);
		$this->tpl->assign("group_rs",$group_rs);
		$this->tpl->p("user");
	}
}
?>