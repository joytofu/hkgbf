<?php
/***********************************************************
	Filename: app/admin/control/mypass.php
	Note	: 个人密码管理
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-16
***********************************************************/
class mypass_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("admin");
	}

	function mypass_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		$this->tpl->display("mypass.html");
	}

	function setok_f()
	{
		$oldpass = $this->trans_lib->safe("oldpass");
		$newpass = $this->trans_lib->safe("newpass");
		$chkpass = $this->trans_lib->safe("chkpass");
		if(!$oldpass || !$newpass || !$chkpass)
		{
			error("所有加星号的文本框均必须填写！",site_url("mypass"));
		}
		if($newpass != $chkpass)
		{
			error("两次输入的新密码不一致！",site_url("mypass"));
		}
		$rs = $this->admin_m->get_one($_SESSION["admin_id"]);
		if($rs["pass"] != sys_md5($oldpass))
		{
			error("旧密码输入不正确！",site_url("mypass"));
		}
		$this->admin_m->update_pass($newpass,$_SESSION["admin_id"]);
		$rs["pass"] = sys_md5($newpass);
		$_SESSION[SYS_CHECKED_SESSION_ID] = sys_md5($rs);
		error("密码修改成功，请下次登录后使用新密码登录！",site_url("mypass"));
	}
}
?>