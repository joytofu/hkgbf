<?php
/***********************************************************
	Filename: app/admin/control/menu.php
	Note	: 导航菜单管理
	Version : 3.0
	Author  : qinggan
	Update  : 2010-05-19
***********************************************************/
class menu_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("menu");
	}

	function menu_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		sys_popedom("menu:list","tpl");
		$this->menu_m->langid($_SESSION["sys_lang_id"]);
		$rslist = $this->menu_m->get_all();
		$this->tpl->assign("rslist",$rslist);
		$this->tpl->display("menu/list.html");
	}

	function set_f()
	{
		$this->menu_m->langid($_SESSION["sys_lang_id"]);
		$id = $this->trans_lib->int("id");
		if($id)
		{
			sys_popedom("menu:modify","tpl");
			$rs = $this->menu_m->get_one($id);
			$this->tpl->assign("rs",$rs);
			$this->tpl->assign("id",$id);
		}
		else
		{
			sys_popedom("menu:add","tpl");
		}
		//读取父级菜单
		$parentlist = $this->menu_m->get_parent();
		$this->tpl->assign("parentlist",$parentlist);
		//读取模块及
		$this->tpl->display("menu/set.html");
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
		$array["highlight"] = $this->trans_lib->safe("highlight");
		$array["highlight_id"] = $this->trans_lib->safe("highlight_id");
		$array["picurl"] = $this->trans_lib->safe("picurl");
		$array["picover"] = $this->trans_lib->safe("picover");
		$array["status"] = $this->trans_lib->int("status");
		if(!$id)
		{
			$array["langid"] = $_SESSION["sys_lang_id"];
		}
		$this->menu_m->save($array,$id);
		error("菜单信息添加/编辑成功！",$this->url("menu"));
	}

	function ajax_del_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error:没有指定ID");
		}
		sys_popedom("menu:delete","ajax");
		$rs = $this->menu_m->ifson($id);
		if($rs)
		{
			exit("请先删除子分类！");
		}
		else
		{
			$this->menu_m->del($id);
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
		sys_popedom("menu:check","ajax");
		$rs = $this->menu_m->get_one($id);
		$status = $rs["status"] ? 0 : 1;
		$this->menu_m->set_status($id,$status);
		exit("ok");
	}

	function highlight_f()
	{
		$htype = $this->trans_lib->safe("htype");
		$hid = $this->trans_lib->safe("hid");
		if($htype != "module" && $htype != "cate" && $htype != "subject")
		{
			exit("hidden");
		}
		if(!$hid)
		{
			exit("error");
		}
		if($htype == "module")
		{
			$this->load_model("module");
			$rs = $this->module_m->get_one($hid);
			if(!$rs)
			{
				exit("empty");
			}
			exit("模块：".$rs["title"]);
		}
		elseif($htype == "cate")
		{
			$this->load_model("cate");
			$hid = sys_id_string($hid);
			$rslist = $this->cate_m->get_list_idstring($hid);
			if(!$rslist)
			{
				exit("empty");
			}
			$msg = "分类：";
			foreach($rslist AS $key=>$value)
			{
				$msg.= " ".$value["cate_name"];
			}
			exit($msg);
		}
		elseif($htype == "subject")
		{
			$this->load_model("list");
			$rs = $this->list_m->get_one($hid);
			if(!$rs)
			{
				exit("empty");
			}
			exit("主题：".$rs["title"]);
		}
		exit("hidden");
	}
}
?>