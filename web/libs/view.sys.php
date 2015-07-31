<?php
/***********************************************************
	Filename: view.sys.php
	Note	: V层引挈管理器
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-16
***********************************************************/
class View
{
	var $engine = 'et';
	var $configdata = '';
	var $tpl_engine = 'et.tpl.php';
	var $tpl;
	var $db;
	function __construct()
	{
		//如果有针对某应用层配置模板引挈，则加载这个应用，返之加载公共应用
		$view_config = file_exists(APP.'view.config.php') ? APP.'view.config.php' : APP_ROOT.'view.config.php';
		if(!file_exists($view_config))
		{
			exit('error: unable to load the file: '.basename($view_config));
		}
		include_once($view_config);
		if(!$_view_config || !is_array($_view_config))
		{
			exit('error: not setting template config');
		}
		$this->configdata = $_view_config['config'];
		//参数
		$this->tpl_engine = LIBS."tpl_engine/".$_view_config['engine'].'.tpl.php';
		if(!file_exists($this->tpl_engine))
		{
			exit('error: unable to load the template controller: '.strtolower($_view_config['engine']));
		}
		$this->engine = strtolower($_view_config['engine'])."_tpl";
		unset($_view_config,$view_config);
	}

	//兼容PHP4操作
	function View()
	{
		$this->__construct();
	}

	//运行引挈
	function run()
	{
		include_once($this->tpl_engine);
		//初始化引挈
		$this->tpl = new $this->engine($this->configdata);
		return $this->tpl;
	}
}
?>