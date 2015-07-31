<?php
/***********************************************************
	Filename: app/www/control/reply.php
	Note	: 评论信息
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-16
***********************************************************/
class reply_c extends Control
{
	var $subject;
	function __construct()
	{
		parent::Control();
		$this->load_model("msg");
		$this->load_model("reply");
		$this->load_model("cate");
	}

	function reply_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			error($this->lang["error_not_id"],site_url());
		}
		$rs = $this->msg_m->get_one($id);
		if(!$rs)
		{
			error($this->lang["error_not_rs"],site_url());
		}
		$this->subject = $rs["title"];
		$this->tpl->assign("id",$id);
		$this->tpl->assign("rs",$rs);
		//读取评论信息
		$pageid = $this->trans_lib->int(SYS_PAGEID);
		$offset = $pageid>0 ? ($pageid-1)*SYS_PSIZE : 0;
		$rslist = $this->reply_m->get_list($id,$offset,SYS_PSIZE);
		$this->tpl->assign("rslist",$rslist);
		$total = $this->reply_m->get_count($id);
		$this->tpl->assign("total",$total);
		$pageurl = site_url("reply","id=".$id);
		$this->page_lib->set_psize(SYS_PSIZE);
		$pagelist = $this->page_lib->page($pageurl,$total,true);//分页数组
		$this->tpl->assign("pagelist",$pagelist);
		$rs["cate_id"] ? $this->load_cate_msg($rs["cate_id"],$id) : $this->load_module_msg($rs["module_id"],$id);
		$this->tpl->display("reply.".$this->tpl->ext);
	}

	function load_module_msg($mid,$id=0)
	{
		$rs = $this->module_m->get_one($mid);
		if(!$rs)
		{
			return false;
		}
		$array = array();
		$array[0]["title"] = $rs["title"];
		$array[1]["title"] = $this->subject;
		$array[1]["url"] = site_url("msg","id=".$id);
		$array[2]["title"] = $this->lang["reply"];
		$this->tpl->assign("leader",$array);
		//设置头部信息
		$sitetitle = $this->lang["reply"]." ".$this->subject." - ".$rs["title"];
		$this->tpl->assign("sitetitle",$sitetitle);
		return true;
	}

	function load_cate_msg($cateid,$id=0)
	{
		$rs = $this->cate_m->get_one($cateid);
		if(!$rs)
		{
			return false;
		}
		$this->tpl->assign("cate_rs",$rs);
		$array = array();
		$array[0] = $rs;
		if($rs["parentid"])
		{
			$this->cate_m->get_parent_array($array,$rs["parentid"]);
		}
		$rslist = array();
		$site_title_array = array();
		foreach($array AS $key=>$value)
		{
			$tmp = array();
			$tmp["title"] = $value["cate_name"];
			$ext = $value["identifier"] ? "cs=".$value["identifier"] : "cid=".$value["id"];
			$tmp["url"] = site_url("list",$ext);
			$rslist[$key] = $tmp;
			$site_title_array[] = $value["cate_name"];
		}
		$sitetitle = $this->lang["reply"]." ".$this->subject." - ".implode(" - ",$site_title_array);
		$this->tpl->assign("sitetitle",$sitetitle);
		//倒序数组
		krsort($rslist);
		$count = count($rslist);
		$rslist[$count]["title"] = $this->subject;
		$rslist[$count]["url"] = site_url("msg","id=".$id);
		$rslist[$count+1]["title"] = $this->lang["reply"];
		unset($array);
		$this->tpl->assign("leader",$rslist);
	}

	function save_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			error($this->lang["error_not_id"],site_url());
		}
		$rs = $this->msg_m->get_one($id);
		if(!$rs || !$rs["status"])
		{
			error($this->lang["error_not_rs"],site_url());
		}
		$username = $this->trans_lib->safe("username");
		if(!$username)
		{
			$username = $this->lang["guest"];
		}
		$pass = $this->trans_lib->safe("password");
		//如果非会员，尝试登录
		$array = array();
		$array["tid"] = $id;
		$array["userid"] = 0;
		if(!$_SESSION["user_id"] && $pass)
		{
			//判断并模拟登录
			$this->load_model("user");
			$u_rs = $this->user_m->user_from_name($username);
			if($u_rs && $u_rs["pass"] == sys_md5($pass))
			{
				//执行会员登录
				$_SESSION["user_id"] = $u_rs["id"];
				$array["userid"] = $u_rs["id"];
				$_SESSION["user_name"] = $u_rs["name"];
				$username = $u_rs["name"];
			}
			unset($u_rs);
		}
		elseif($_SESSION["user_id"])
		{
			$array["userid"] = $_SESSION["user_id"];
			$username = $_SESSION["user_name"];
		}
		$array["username"] = $username;
		$array["ip"] = sys_ip();
		$title = $this->trans_lib->safe("reply_subject");
		if(!$title)
		{
			$title = "Re:".$rs["subject"];
		}
		$array["title"] = $title;
		$array["postdate"] = $this->system_time;
		$array["status"] = 0;
		//判断回复是否需要审核
		if($rs["cate_id"])
		{
			//[读取分类配置信息]
			$this->load_model("cate");
			$cate_rs = $this->cate_m->get_one($rs["cate_id"]);
			if($cate_rs)
			{
				if(!$cate_rs["status"])
				{
					error($this->lang["reply_lock"],site_url("msg","id=".$id));
				}
				if(!$cate_rs["ifreply"])
				{
					error($this->lang["reply_not"],site_url("msg","id=".$id));
				}
				$array["status"] = $cate_rs["chk_reply"];
			}
		}
		else
		{
			$this->load_model("module");
			$module_rs = $this->module_m->get_one($rs["module_id"]);
			if($module_rs)
			{
				if(!$module_rs["status"])
				{
					error($this->lang["reply_lock"],site_url("msg","id=".$id));
				}
				if(!$module_rs["ifreply"])
				{
					error($this->lang["reply_not"],site_url("msg","id=".$id));
				}
				$array["status"] = $module_rs["r_free_check"];
			}
		}
		//判断是否
		$array["content"] = $this->trans_lib->safe("reply_content");
		$array["admreply"] = "";//管理员回复，默认为空
		$array["star"] = $this->trans_lib->int("star");
		$this->reply_m->save($array);//存储评论信息
		//更新主题的最后回复时间
		$this->msg_m->update_replay_date($id,$this->system_time);
		//更新主题的星星点评
		$this->reply_m->update_star($id);
		error($this->lang["save_success"],site_url("msg","id=".$id));
	}
}
?>