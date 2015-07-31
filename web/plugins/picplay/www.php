<?php
/***********************************************************
	Filename: plugins/picplay/www.php
	Note	: 调用xml
	Version : 3.0
	Author  : qinggan
	Update  : 2011-05-08
***********************************************************/
if(!defined('PHPOK_SET')) exit('Access Denied.');
class plugin_picplay extends Plugin
{
	function __construct()
	{
		parent::Plugin();
	}

	function plugin_picplay()
	{
		$this->__construct();
	}

	function index()
	{
		$app = sys_init();
		$phpok = $app->trans_lib->safe("phpok");
		if(!$phpok)
		{
			return false;
		}
		$rslist = phpok($phpok);
		if(!$rslist["rslist"] || !is_array($rslist["rslist"]) || count($rslist["rslist"])<1)
		{
			return false;
		}
		$app->tpl->assign("rslist",$rslist["rslist"]);
		$rs = $this->config["ext"] ? $this->config["ext"] : array();
		$showtitle = $rs["showtitle"] ? "true" : "false";
		$showbtn = $rs["showbtn"] ? "true" : "false";
		$app->tpl->assign("showtitle",$showtitle);
		$app->tpl->assign("showbtn",$showbtn);
		header("Content-type: application/xml");
		$app->tpl->plugin($this->config["identifier"],"templates/xml.html");
	}

	function phpok()
	{
		$rs = $this->config["ext"] ? $this->config["ext"] : array();
		$width = $rs["width"] ? $rs["width"] : 330;
		$height = $rs["height"] ? $rs["height"] : 190;
		$phpok = $rs["phpok"] ? $rs["phpok"] : "";
		//取得插件扩展配置
		$app = sys_init();
		$swfurl = "plugins/".$this->config["identifier"]."/templates/player.swf";
		$xmlfile = rawurlencode($app->url("plugin","plugin=picplay&phpok=".$phpok,"&"));
		$app->tpl->assign("swfurl",$swfurl);
		$app->tpl->assign("xmlfile",$xmlfile);
		$app->tpl->assign("width",$width);
		$app->tpl->assign("height",$height);
		$app->tpl->plugin($this->config["identifier"],"templates/play.html");
	}
}
?>