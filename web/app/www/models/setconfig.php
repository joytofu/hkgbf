<?php
/***********************************************************
	Filename: tplconfig.php
	Note	: 重新读取模板配置
	Version : 3.0
	Author  : qinggan
	Update  : 2009-12-22
***********************************************************/
class setconfig_m extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function setconfig_m()
	{
		$this->__construct();
	}

	function get_list($langid="zh")
	{
		$file = ROOT_DATA."system_".$langid.".php";
		$_sys = array();
		if(file_exists($file))
		{
			include($file);
		}
		return $_sys;
	}
}
?>