<?php
/***********************************************************
	Filename: app/admin/control/currency.php
	Note	: 货币管理
	Version : 3.0
	Author  : qinggan
	Update  : 2011-07-16 07:15
***********************************************************/
class currency_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("currency");
	}

	function currency_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		sys_popedom("currency:list","tpl");
		$rslist = $this->currency_m->get_list();
		$this->tpl->assign("rslist",$rslist);
		$this->tpl->display("currency/list.html");
	}

	function set_f()
	{
		$id = $this->trans_lib->safe("id");
		if($id)
		{
			sys_popedom("currency:modify","tpl");
		}
		else
		{
			sys_popedom("currency:add","tpl");
		}
		$this->tpl->assign("id",$id);
		if($id)
		{
			$rs = $this->currency_m->get_one($id);
			$this->tpl->assign("rs",$rs);
		}
		$this->tpl->display("currency/set.html");
	}

	function setok_f()
	{
		$id = $this->trans_lib->safe("id");
		if($id)
		{
			sys_popedom("currency:modify","tpl");
		}
		else
		{
			sys_popedom("currency:add","tpl");
		}
		$array = array();
		$array["code"] = $id ? $id : $this->trans_lib->safe("code");
		$array["val"] = $this->trans_lib->float("val");
		$array["title"] = $this->trans_lib->safe("title");
		$array["symbol_left"] = $this->trans_lib->safe("symbol_left");
		$array["symbol_right"] = $this->trans_lib->safe("symbol_right");
		$array["taxis"] = $this->trans_lib->int("taxis");
		$array["status"] = $this->trans_lib->int("status");
		#[检测相关信息]
		if(!$array["title"])
		{
			error("名称不允许为空",site_url("currency,set","id=".$id));
		}
		if(!$array["code"])
		{
			error("编码不允许为空！",site_url("currency,set","id=".$id));
		}
		$this->currency_m->save($array);
		//设置为默认
		$ifdefault = $this->trans_lib->int("ifdefault");
		if($ifdefault)
		{
			$this->currency_m->set_default($array["code"]);
		}
		error("货币设置操作成功",site_url("currency"));
	}

	//删除图片库方案
	function del_f()
	{
		$id = $this->trans_lib->safe("id");
		if(!$id)
		{
			exit("Error:操作非法，没有指定ID");
		}
		sys_popedom("currency:delete","ajax");
		$this->currency_m->del($id);
		exit("ok");
	}
}
?>
