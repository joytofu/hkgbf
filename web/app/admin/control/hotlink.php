<?php
/***********************************************************
	Filename: app/admin/control/hotlink.php
	Note	: 快捷键管理
	Version : 3.0
	Author  : qinggan
	Update  : 2011-07-19 14:35
***********************************************************/
class hotlink_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("hotlink");
	}

	function hotlink_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		sys_popedom("hotlink:list","tpl");
		$rslist = $this->hotlink_m->get_list($_SESSION["sys_lang_id"]);
		$this->tpl->assign("rslist",$rslist);
		$this->tpl->display("hotlink/list.html");
	}

	function set_f()
	{
		$id = $this->trans_lib->safe("id");
		if($id)
		{
			sys_popedom("hotlink:modify","tpl");
		}
		else
		{
			sys_popedom("hotlink:add","tpl");
		}
		$this->tpl->assign("id",$id);
		if($id)
		{
			$rs = $this->hotlink_m->get_one($id);
			$this->tpl->assign("rs",$rs);
		}
		//取得图标信息
		$icolist = $this->file_lib->ls($this->tpl->tpldir."/images/ico/");
		$this->tpl->assign("icolist",$icolist);
		$this->tpl->display("hotlink/set.html");
	}

	function setok_f()
	{
		$id = $this->trans_lib->int("id");
		if($id)
		{
			sys_popedom("hotlink:modify","tpl");
		}
		else
		{
			sys_popedom("hotlink:add","tpl");
		}
		$array = array();
		if(!$id)
		{
			$array["langid"] = $_SESSION["sys_lang_id"];
		}
		$array["linkurl"] = $this->trans_lib->safe("linkurl");
		$array["title"] = $this->trans_lib->safe("title");
		$array["ico"] = $this->trans_lib->safe("ico");
		$array["taxis"] = $this->trans_lib->int("taxis");
		$array["status"] = $this->trans_lib->int("status");
		#[检测相关信息]
		if(!$array["title"])
		{
			error("名称不允许为空",site_url("hotlink,set","id=".$id));
		}
		if(!$array["linkurl"])
		{
			error("链接不允许为空！",site_url("hotlink,set","id=".$id));
		}
		$this->hotlink_m->save($array,$id);
		error("设置操作成功",site_url("hotlink"));
	}

	//通过Ajax创建热键
	function ajax_f()
	{
		$array = array();
		$array["langid"] = $_SESSION["sys_lang_id"];
		$array["linkurl"] = $this->trans_lib->safe("linkurl");
		$array["title"] = $this->trans_lib->safe("title");
		$array["ico"] = "";
		$array["taxis"] = 0;
		$array["status"] = "1";
		if(!$array["linkurl"] || !$array["title"])
		{
			exit("error:创建快捷键的条件不完整");
		}
		$rs = $this->hotlink_m->get_one_url($array["linkurl"],$_SESSION["sys_lang_id"]);
		if($rs)
		{
			exit("error:快捷键已经存在！");
		}
		$this->hotlink_m->save($array);
		exit("ok");
	}

	//删除快捷键
	function del_f()
	{
		$id = $this->trans_lib->safe("id");
		if(!$id)
		{
			exit("Error:操作非法，没有指定ID");
		}
		sys_popedom("hotlink:delete","ajax");
		$this->hotlink_m->del($id);
		exit("ok");
	}
}
?>
