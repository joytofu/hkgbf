<?php
/***********************************************************
	Filename: app/admin/control/index.php
	Note	: 首页
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-16
***********************************************************/
class index_c extends Control
{
	function __construct()
	{
		parent::Control();
	}

	function index_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		$this->tpl->display("index.".$this->tpl->ext);
	}

	//网站关闭说明
	function close_f()
	{
		$this->tpl->display("close.".$this->tpl->ext);
	}
}
?>