<?php
/***********************************************************
	Filename: app/www/control/plugin.php
	Note	: 插件前台
	Version : 3.0
	Author  : qinggan
	Update  : 2011-04-23
***********************************************************/
class plugin_c extends Control
{
	function __construct()
	{
		parent::Control();
	}

	function plugin_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		//插件标识
		$plugin = $this->trans_lib->safe("plugin");
		if(!$plugin)
		{
			return false;
		}
		$pt = $this->plugin($plugin);
		$pt->index();
	}
}