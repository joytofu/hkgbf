<?php
/***********************************************************
	Filename: app/admin/payment.php
	Note	: 付款方案管理
	Version : 3.0
	Author  : qinggan
	Update  : 2009-12-23
***********************************************************/
class payment_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("payment");
	}

	//兼容PHP4的写法
	function payment_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		sys_popedom("payment:list","tpl");
		$this->payment_m->langid($_SESSION["sys_lang_id"]);
		$rslist = $this->payment_m->get_list();
		$this->tpl->assign("rslist",$rslist);
		$this->tpl->display("payment/list.html");
	}

	function del_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error:没有指定模块！");
		}
		sys_popedom("payment:delete","ajax");
		$this->payment_m->del($id);
		exit("ok");
	}

	function set_f()
	{
		$id = $this->trans_lib->int("id");
		if($id)
		{
			sys_popedom("payment:modify","tpl");
		}
		else
		{
			sys_popedom("payment:add","tpl");
		}
		$this->tpl->assign("id",$id);
		if($id)
		{
			$rs = $this->payment_m->get_one($id);
			$this->tpl->assign("rs",$rs);
		}
		$this->tpl->display("payment/set.html");
	}

	function setok_f()
	{
		$id = $this->trans_lib->int("id");
		if($id)
		{
			sys_popedom("payment:modify","tpl");
		}
		else
		{
			sys_popedom("payment:add","tpl");
		}
		$array = array();
		if(!$id)
		{
			$array["code"] = $this->trans_lib->safe("code");
			$chk = $this->code_chk($array["code"]);//检测标识串是否被使用过
			if($chk != "ok")
			{
				error("付款标识符已被使用！",$this->url("payment,set"));
			}
		}
		$array["title"] = $this->trans_lib->safe("title");
		$array["taxis"] = $this->trans_lib->int("taxis");
		$array["next_act"] = $this->trans_lib->safe("next_act");//下一步执行动作
		$array["note"] = $this->trans_lib->html("note");
		if(!$id)
		{
			$array["langid"] = $_SESSION["sys_lang_id"];
		}
		$id = $this->payment_m->save($array,$id);
		//更新
		if(!$id)
		{
			error("操作失败，请检查",$this->url("payment"));
		}
		error("付款方案管理操作成功",$this->url("payment"));
	}

	//设置参数信息
	function fields_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			error("操作有错误，没有指定ID",$this->url("payment"));
		}
		sys_popedom("payment:setting","tpl");
		$this->tpl->assign("id",$id);
		$rs = $this->payment_m->get_one($id);
		$this->tpl->assign("rs",$rs);
		//判断已经填写的字段信息
		$rslist = $this->payment_m->fields($id);
		if($rslist)
		{
			$this->tpl->assign("rslist",$rslist);
		}
		$this->tpl->display("payment/fields.html");
	}

	function fields_set_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			error("操作有错误，没有指定ID",$this->url("payment"));
		}
		$this->tpl->assign("id",$id);
		$this->tpl->assign("tid",$tid);
		$rs = $this->payment_m->get_one($id);
		$this->tpl->assign("rs",$rs);
		$tid = $this->trans_lib->int("tid");
		if($tid)
		{
			sys_popedom("payment:modify","tpl");
			$trs = $this->payment_m->fields_one($tid);
			$this->tpl->assign("trs",$trs);
		}
		else
		{
			sys_popedom("payment:add","tpl");
		}
		$this->tpl->display("payment/fields_set.html");
	}

	function fields_ok_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			error("操作有错误，没有指定ID",$this->url("payment"));
		}
		$rs = $this->payment_m->get_one($id);
		$array = array();
		$array["payid"] = $id;
		$array["title"] = $this->trans_lib->safe("title");
		$tid = $this->trans_lib->int("tid");
		if(!$tid)
		{
			sys_popedom("payment:add","tpl");
			$array["code"] = $this->trans_lib->safe("code");
			$chk = $this->fields_chk($id,$array["code"]);//检测标识串是否被使用过
			if($chk != "ok")
			{
				error("标识符已被使用！",$this->url("payment,fields","id=".$id));
			}
		}
		else
		{
			sys_popedom("payment:modify","tpl");
		}
		$array["val"] = $this->trans_lib->safe("val");
		$tmp_id = $this->payment_m->save_fields($array,$tid);
		//更新
		if(!$tmp_id)
		{
			error("操作失败，请检查",$this->url("payment,fields_set","id=".$id."&tid=".$tid));
		}
		error("付款方案 ".$rs["title"]."参数设置操作成功",$this->url("payment,fields","id=".$id));
	}

	function fields_chk_f()
	{
		$id = $this->trans_lib->int("id");
		$code = $this->trans_lib->safe("code");
		exit($this->fields_chk($id,$code));
	}

	function fields_chk($id,$code)
	{
		if(!$code)
		{
			return "Error:编号不允许为空！";
		}
		if(!$id)
		{
			return "Error:操作有错误没有指定ID";
		}
		//检测是否已经被使用了
		$rs = $this->payment_m->chksign($code,$id);
		if($rs)
		{
			return "Error:编号已经被使用，请选择其他";
		}
		else
		{
			return "ok";
		}
	}

	function code_chk_f()
	{
		$code = $this->trans_lib->safe("code");
		exit($this->code_chk($code));
	}

	//检测编号是否唯一
	function code_chk($code="")
	{
		if(!$code)
		{
			return "Error:编号不允许为空！";
		}
		$this->payment_m->langid($_SESSION["sys_lang_id"]);
		$rs = $this->payment_m->chk_identifier($code);
		if($rs)
		{
			return "Error:编号已经被使用，请选择其他";
		}
		else
		{
			return "ok";
		}
	}

	function ajax_status_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("Error:操作非法，没有指定ID");
		}
		sys_popedom("payment:check","ajax");
		$rs = $this->payment_m->get_one($id);
		$status = $rs["status"] ? 0 : 1;
		$this->payment_m->set_status($id,$status);
		exit("ok");
	}

	function fields_del_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("Error:操作非法，没有指定ID");
		}
		sys_popedom("payment:delete","ajax");
		$this->payment_m->fields_del($id);
		exit("ok");
	}
}
?>