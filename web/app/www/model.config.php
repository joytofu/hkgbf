<?php
/***********************************************************
	Filename: app/www/model.config.php
	Note	: 加载Model层配置文件
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-16
***********************************************************/
//使用教程 autoload：加载自动导入的类文件
//autoload_function：自动运行函数
$_model_config["autoload"] = array("langconfig","tplconfig","setconfig","module","cate");
$_model_config["autoload_function"][] = "sys_format_module_id_code";
$_model_config["autoload_function"][] = "sys_format_cate_id_code";
?>