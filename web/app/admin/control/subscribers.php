<?php
/***********************************************************
	Filename: app/admin/subscribers.php
	Note	: 邮件订阅-会员管理
	Version : 3.0
	Author  : qinggan
	Update  : 2011-03-11
***********************************************************/
class subscribers_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("subscribers");
		$this->load_model("subscribers_model",true);
	}

	//兼容PHP4的写法
	function subscribers_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		sys_popedom("subscribers:list","tpl");
		$keywords = $this->trans_lib->safe("keywords");
		$psize = SYS_PSIZE;
		$pageid = $this->trans_lib->int(SYS_PAGEID);
		$rslist = $this->subscribers_m->get_list($keywords,$pageid,$psize);
		$this->tpl->assign("rslist",$rslist);
		//取得总数量
		$total_count = $this->subscribers_m->get_count($keywords);
		$this->page_lib->set_psize($psize);
		$page_url = $this->url("subscribers");
		$pagelist = $this->page_lib->page($page_url,$total_count);
		$this->tpl->assign("pagelist",$pagelist);
		//判断是否有编辑权限
		$ifmodify = sys_popedom("subscribers:modify");
		$ifadd = sys_popedom("subscribers:add");
		$ifdel = sys_popedom("subscribers:delete");
		$this->tpl->assign("ifmodify",$ifmodify);
		$this->tpl->assign("ifadd",$ifadd);
		$this->tpl->assign("ifdel",$ifdel);
		$this->tpl->display("user/subscribers.html");//邮件订阅管理
	}

	function setok_f()
	{
		$id = $this->trans_lib->int("id");
		//判断是否有添加或编辑的权限
		$id ? sys_popedom("subscribers:modify","ajax") : sys_popedom("subscribers:add","ajax");
		$email = $this->trans_lib->safe("email");
		$status = $this->trans_lib->int("status");
		$rs = $this->subscribers_model->chk_email($email,$id);
		if($rs)
		{
			exit("对不起，邮件已经存在！");
		}
		//更新邮件
		$array = array();
		$array["email"] = $email;
		$array["status"] = $status;
		if(!$id)
		{
			$array["postdate"] = $this->system_time;
			$array["md5pass"] = md5($email."_".$this->system_time);
		}
		$this->subscribers_model->save($array,$id);
		exit("ok");
	}

	function del_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("对不起，你的操作有错误！");
		}
		sys_popedom("subscribers:delete","ajax");
		$this->subscribers_m->del($id);
		exit("ok");
	}
}
?>