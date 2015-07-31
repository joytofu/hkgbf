<?php
/***********************************************************
	Filename: app/www/control/js.php
	Note	: 通过程序控制动态信息显示
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-16
***********************************************************/
class js_c extends Control
{
	function __construct()
	{
		parent::Control();
	}

	function js_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		$act = $this->trans_lib->safe("act");
		$act = str_replace(array(".","/"),"",$act);
		if(file_exists(ROOT_JS.$act.".php"))
		{
			include(ROOT_JS.$act.".php");
		}
		else
		{
			echo "ERROR";
		}
		exit();
	}
}
?>