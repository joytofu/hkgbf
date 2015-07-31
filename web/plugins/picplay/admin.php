<?php
/***********************************************************
	Filename: plugins/picplay/websitesystem.php
	Note	: 管理图片播放器
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

	//管理员配置
	function index()
	{
		//
	}

	function set($rs)
	{
		$app = sys_init();
		if($rs["ext"])
		{
			$myext = unserialize($rs["ext"]);
			$app->tpl->assign("myext",$myext);
		}
		//加载模块配置
		$app->load_model("phpok");
		$condition = " intype='sign' AND pic_required='1'";
		$app->phpok_m->psize(100);
		$rslist = $app->phpok_m->get_list(0,$condition);
		$app->tpl->assign("phpoklist",$rslist);
		return $app->tpl->plugin($rs["identifier"],"templates/admin.html",true);
	}

	function setok($id)
	{
		$app = sys_init();
		$tmp = array();
		$tmp["phpok"] = $app->trans_lib->safe("plugin_phpok");
		$tmp["width"] = $app->trans_lib->safe("plugin_width");
		$tmp["height"] = $app->trans_lib->safe("plugin_height");
		$tmp["showtitle"] = $app->trans_lib->int("plugin_showtitle");
		$tmp["showbtn"] = $app->trans_lib->int("plugin_showbtn");
		return serialize($tmp);
	}

	function config($rs)
	{
		$app = sys_init();
		if($rs["ext"])
		{
			$rs["ext"] = unserialize($rs["ext"]);
		}
		$app->file_lib->vi($rs,ROOT_PLUGIN.$rs["identifier"]."/config.php","config");
	}
}
?>