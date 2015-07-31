<?php
/***********************************************************
	Filename: js/php/usercp.php
	Note	: 会员登录界面操作
	Version : 3.0
	Author  : qinggan
	Update  : 2010-01-08
***********************************************************/
if($_SESSION["user_id"])
{
	//加载登录后的模板信息
	$this->load_model("user");
	//$module_list = $this->user_m->get_module_list();
	//$this->tpl->assign("module_list",$module_list);
	$msg = $this->tpl->fetch("js_tpl/usercp.".$this->tpl->ext);
}
else
{
	if(!$this->sys_config["login_status"])
	{
		sys_html2js("false");
	}
	$msg = $this->tpl->fetch("js_tpl/login.".$this->tpl->ext);
}
sys_html2js($msg);
?>