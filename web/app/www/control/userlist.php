<?php
/***********************************************************
	Filename: userlist.php
	Note	: 会员列表
	Version : 4.0
	Author  : qinggan
	Update  : 2011-11-09 15:23
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class userlist_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("user");
		$this->load_model("usergroup");
	}

	function userlist_c(){$this->__construct();}

	//会员列表
	function index_f()
	{
		$groupid = $this->trans_lib->int("groupid");
		if($groupid)
		{
			$group_rs = $this->usergroup_m->get_one($groupid);
			if(!$group_rs || !$group_rs["status"])
			{
				$group_rs = $this->usergroup_m->get_default();
				$groupid = $group_rs["id"];
			}
		}
		else
		{
			$group_rs = $this->usergroup_m->get_default();
			$groupid = $group_rs["id"];
		}
		$this->tpl->assign("groupid",$group_rs["id"]);
		$this->tpl->assign("group_rs",$group_rs);
		//取得会员组列表
		$grouplist = $this->usergroup_m->group_list();
		$this->tpl->assign("grouplist",$grouplist);
		//取得当前会员组的会员列表，每页只显示30;
		if($group_rs["ifshow"])
		{
			$psize = defined("SYS_PSIZE") ? SYS_PSIZE : 30;
			$pageid = $this->trans_lib->int(SYS_PAGEID);
			$offset = $pageid>0 ? ($pageid-1) * $psize : 0;
			$userlist = $this->user_m->user_list($groupid,$offset,$psize);
			$this->tpl->assign("userlist",$userlist);
			$total = $this->user_m->user_total($groupid);
			$this->tpl->assign("total",$total);
			$pageurl = site_url("userlist","groupid=".$groupid);
			$this->page_lib->set_psize($psize);
			$pagelist = $this->page_lib->page($pageurl,$total,true);//分页数组
			$this->tpl->assign("pagelist",$pagelist);
		}
		$this->tpl->p("userlist");
	}
}
?>