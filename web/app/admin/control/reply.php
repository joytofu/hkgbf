<?php
/***********************************************************
	Filename: app/admin/control/reply.php
	Note	: 回复及评论管理
	Version : 3.0
	Author  : qinggan
	Update  : 2010-05-16
***********************************************************/
class reply_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("reply");
	}

	function reply_c()
	{
		$this->__construct();
	}

	//回复列表
	function index_f()
	{
		sys_popedom("reply:list","tpl");
		$pageid = $this->trans_lib->int(SYS_PAGEID);
		$offset = $pageid>0 ? ($pageid-1)*SYS_PSIZE : 0;
		$condition = " 1=1 ";
		$startdate = $this->trans_lib->safe("startdate");
		$page_url = $this->url("reply");
		if($startdate)
		{
			$this->tpl->assign("startdate",$startdate);
			$condition .= " AND r.postdate>='".strtotime($startdate)."'";
			$page_url .= "startdate=".rawurlencode($startdate)."&";
		}
		$enddate = $this->trans_lib->safe("enddate");
		if($enddate)
		{
			$this->tpl->assign("enddate",$enddate);
			$condition .= " AND r.postdate<='".strtotime($enddate)."'";
			$page_url .= "enddate=".rawurlencode($enddate)."&";
		}
		$status = $this->trans_lib->int("status");
		if($status)
		{
			$this->tpl->assign("status",$status);
			$condition .= " AND r.status='".($status == 1 ? 1 : 0)."'";
			$page_url .= "status=".$status."&";
		}
		$tid = $this->trans_lib->int("tid");
		if($tid)
		{
			$this->tpl->assign("tid",$tid);
			$condition .= " AND r.tid='".$tid."'";
			$page_url .= "tid=".$tid."&";
		}
		$keytype = $this->trans_lib->safe("keytype");
		$keywords = $this->trans_lib->safe("keywords");
		if($keytype && $keywords)
		{
			$this->tpl->assign("keytype",$keytype);
			$this->tpl->assign("keywords",$keywords);
			$condition .= " AND r.".$keytype." LIKE '%".$keywords."%' ";
			$page_url .= "keytype=".rawurlencode($keytype)."&keywords=".rawurlencode($keywords)."&";
		}
		$total = $this->reply_m->get_count($condition);
		$rslist = $this->reply_m->get_list($offset,$condition);
		$this->tpl->assign("total",$total);
		$this->tpl->assign("rslist",$rslist);
		$pagelist = $this->page_lib->page($page_url,$total);
		$this->tpl->assign("pagelist",$pagelist);
		$this->tpl->display("reply/list.html");
	}

	function set_f()
	{
		sys_popedom("reply:modify","tpl");
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			error("操作错误，没有指定ID！",$this->url("reply"));
		}
		$rs = $this->reply_m->get_one($id);
		$this->tpl->assign("rs",$rs);
		$this->tpl->assign("id",$id);
		$this->tpl->display("reply/set.html");
	}

	function setok_f()
	{
		sys_popedom("reply:modify","tpl");
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			error("操作错误，没有指定ID！",$this->url("reply"));
		}
		$rs = $this->reply_m->get_one($id);
		$array["title"] = $this->trans_lib->safe("subject");
		$array["modifydate"] = $this->system_time;
		$array["ifbest"] = $this->trans_lib->int("ifbest");
		$array["star"] = $this->trans_lib->int("star");
		$array["content"] = $this->trans_lib->safe("content");
		$array["admreply"] = $this->trans_lib->html("admreply");
		if($array["admreply"])
		{
			$array["replydate"] = $this->system_time;
		}
		$this->reply_m->save($array,$id);
		$this->reply_m->update_star($rs["tid"]);
		error("回复信息更新成功，请稍候！",$this->url("reply","pid=".$rs["pid"]));
	}

	//删除回复
	function del_f()
	{
		sys_popedom("reply:delete","ajax");
		$id = $this->trans_lib->safe("id");
		if(!$id)
		{
			exit("操作错误，没有指定ID！");
		}
		$this->reply_m->del($id);
		exit("ok");
	}

	function pl_status_f()
	{
		sys_popedom("reply:check","ajax");
		$id = $this->trans_lib->safe("id");
		$status = $this->trans_lib->int("status");
		$this->reply_m->status($id,$status);
		//批量更新星星数
		$idlist = sys_id_list($id,"intval");
		if($idlist)
		{
			foreach($idlist AS $key=>$value)
			{
				$rs = $this->reply_m->get_one($value);
				if($rs)
				{
					$this->reply_m->update_star($rs["tid"]);
				}
			}
		}
		exit("ok");
	}

	//审核回复
	function status_f()
	{
		sys_popedom("reply:check","ajax");
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("操作错误，没有指定ID！");
		}
		$rs = $this->reply_m->get_one($id);
		$status = $rs["status"] ? 0 : 1;
		$this->reply_m->status($id,$status);
		$this->reply_m->update_star($rs["tid"]);
		exit("ok");
	}
}
?>