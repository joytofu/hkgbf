<?php
/***********************************************************
	Filename: app/view.config.php
	Note	: 加载View层配置文件
			  如在子项目中已定义，则不加载此文件
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-16
***********************************************************/
//et: Ease template 模板引挈
$_view_config["engine"] = "et";

//配置模板引挈参数
$_view_config["config"]["tplid"] = 1;
$_view_config["config"]["tpldir"] = APP."view";
$_view_config["config"]["cache"] = ROOT."data/admin_tplc";
$_view_config["config"]["phpdir"] = "";
$_view_config["config"]["ext"] = "html";
$_view_config["config"]["autorefresh"] = true;
$_view_config["config"]["autoimg"] = true;
?>