<?php
/***********************************************************
	Filename: home.php
	Note	: 后台首页
	Version : 3.0
	Author  : qinggan
	Update  : 2010-05-27
***********************************************************/
class home_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("home");
		$this->load_model("order");
		$this->load_model("reply");
	}

	function home_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		$this->home_m->langid($_SESSION["sys_lang_id"]);
		//检测未审核订单
		$order_count = $this->order_m->get_count("o.status='0'");
		$tlist = $this->home_m->get_list_count();
		if(!$tlist) $tlist = array();
		if($order_count>0)
		{
			$tlist[] = array("title"=>"订单中心","total"=>$order_count);
		}
		$reply = $this->reply_m->get_count("r.status='0'");
		if($reply>0)
		{
			$tlist[] = array("title"=>"主题回复","total"=>$reply);
		}
		$this->tpl->assign("tlist",$tlist);
		if(function_exists("gd_info"))
		{
			$gd = gd_info();
			$gdinfo = $gd["GD Version"];
		}
		else
		{
			$gdinfo = "不支持";
		}
		$this->tpl->assign("gdinfo",$gdinfo);
		//取得订单总数
		$order_total = $this->order_m->get_count();
		$this->tpl->assign("order_total",$order_total);
		//取得回复总数
		$reply_total = $this->reply_m->get_count();
		$this->tpl->assign("reply_total",$reply_total);
		//取得主题总数
		$title_total = $this->home_m->get_total();
		$this->tpl->assign("title_total",$title_total);
		//取得附件总数
		$files_total = $this->home_m->get_file();
		$this->tpl->assign("files_total",$files_total);
		//取得授权信息
		$license = "PHPOK3FULL";
		if(file_exists(ROOT."license.php"))
		{
			include_once(ROOT."license.php");
			if(defined("LICENSE"))
			{
				$license = LICENSE;
			}
		}
		$this->tpl->assign("license",$license);
		$this->tpl->display("home.html");
	}

	function info_f()
	{
		phpinfo();
		exit;
	}
}
?>