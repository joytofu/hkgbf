<?php
/***********************************************************
	Filename: control.sys.php
	Note	: 控制层核心文件
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-16
***********************************************************/
//如果没有指定libs层，则禁止访问
if(!defined('LIBS'))
{
	exit('error: Not define libs');
}

class Plugin
{
	var $db;
	var $config;
	//var $app;
	function Plugin()
	{
		//$app = sys_init();
		//$this->app = $app;
	}

	//加载数据库运行
	function load_db($db)
	{
		$this->db = $db;
	}

	function load_plugin($plugin_name)
	{
		if(is_array($plugin_name))
		{
			foreach($plugin_name AS $key=>$value)
			{
				$this->load_plugin($value);
			}
		}
		else
		{
			$this->_load_plugin($plugin_name);
		}
		return true;
	}

	function _load_plugin($plugin_name)
	{
		if(!$plugin_name)
		{
			return false;
		}
		if(!$plugin_name || !defined("APP_NAME"))
		{
			return false;
		}
		if(!defined("PLUGIN_NAME"))
		{
			define("PLUGIN_NAME",APP_NAME);
		}
		//判断插件是否已安装
		$config_file = ROOT_PLUGIN.$plugin_name."/config.php";
		if(!file_exists($config_file))
		{
			return false;
		}
		$config = array();
		include($config_file);
		$plugin_file = ROOT_PLUGIN.$plugin_name."/".PLUGIN_NAME.".php";
		if(!file_exists($plugin_file))
		{
			return false;
		}
		include_once($plugin_file);//执行函数
		$set_name = "plugin_".$plugin_name;
		$this->$set_name = new $set_name();
		$this->$set_name->db = $this->db;
		$this->config = $config;
		$this->$set_name->config = $config;
		unset($set_name,$plugin_file,$plugin_name);
		return true;
	}

	//插件编辑常涉及到的函数
	function index()
	{
		return false;
	}

	//设置
	function set($rs)
	{
		return false;
	}

	//存储扩展
	function setok($id)
	{
		return false;
	}

	//配置文件操作
	function config($rs)
	{
		return false;
	}

	//返回内容信息
	function content($rs)
	{
		return false;
	}
}
?>