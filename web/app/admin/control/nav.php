<?php
/***********************************************************
	Filename: app/admin/control/menu.php
	Note	: 底部导航菜单管理
	Version : 3.0
	Author  : qinggan
	Update  : 2010-05-19
***********************************************************/
class nav_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("nav");
	}

	function nav_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		sys_popedom("nav:list","tpl");
		$this->nav_m->langid($_SESSION["sys_lang_id"]);
		$rslist = $this->nav_m->get_all();
		$this->tpl->assign("rslist",$rslist);
		$this->tpl->display("nav/list.html");
	}

	function set_f()
	{
		$this->nav_m->langid($_SESSION["sys_lang_id"]);
		$id = $this->trans_lib->int("id");
		if($id)
		{
			sys_popedom("nav:modify","tpl");
			$rs = $this->nav_m->get_one($id);
			$this->tpl->assign("rs",$rs);
			$this->tpl->assign("id",$id);
		}
		else
		{
			sys_popedom("nav:add","tpl");
		}
		//读取父级菜单
		$parentlist = $this->nav_m->get_parent();
		$this->tpl->assign("parentlist",$parentlist);
		//读取模块及
		$this->tpl->display("nav/set.html");
	}

	function setok_f()
	{
		$id = $this->trans_lib->int("id");
		$array = array();
		$array["title"] = $this->trans_lib->safe("title");
		$array["link"] = $this->trans_lib->safe("link");
		$array["link_html"] = $this->trans_lib->safe("link_html");
		$array["link_rewrite"] = $this->trans_lib->safe("link_rewrite");
		$array["target"] = $this->trans_lib->int("target");
		$array["note"] = $this->trans_lib->safe("note");
		$array["parentid"] = $this->trans_lib->int("parentid");
		$array["taxis"] = $this->trans_lib->int("taxis");
		$array["status"] = $this->trans_lib->int("status");
		if(!$id)
		{
			$array["langid"] = $_SESSION["sys_lang_id"];
		}
		$this->nav_m->save($array,$id);
		error("菜单信息添加/编辑成功！",$this->url("nav"));
	}

	function ajax_del_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error:没有指定ID");
		}
		sys_popedom("nav:delete","ajax");
		$rs = $this->nav_m->ifson($id);
		if($rs)
		{
			exit("请先删除子分类！");
		}
		else
		{
			$this->nav_m->del($id);
			exit("ok");
		}
	}

	function ajax_status_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error:没有指定ID");
		}
		sys_popedom("nav:check","ajax");
		$rs = $this->nav_m->get_one($id);
		$status = $rs["status"] ? 0 : 1;
		$this->nav_m->set_status($id,$status);
		exit("ok");
	}

}
?>