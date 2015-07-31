<?php
/***********************************************************
	Filename: app/www/control/order.php
	Note	: 订单列表
	Version : 3.0
	Author  : qinggan
	Update  : 2010-05-10
***********************************************************/
class order_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("checkout");
		$this->load_model("payment");
	}

	function order_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		exit("ok");
	}

	function list_f()
	{
		if(!$_SESSION["user_id"])
		{
			error($this->lang["please_login"],$this->url("login"));
		}
		$page_url = $this->url("order,list");
		$offset = $this->trans_lib->int(SYS_PAGEID);
		$psize = defined("SYS_PSIZE") ? SYS_PSIZE : 20;
		$keywords = $this->trans_lib->safe("keywords");
		$rslist = $this->checkout_m->get_list($_SESSION["user_id"],$keywords,$offset,$psize);
		$this->tpl->assign("rslist",$rslist);
		$total = $this->checkout_m->get_count($_SESSION["user_id"],$keywords);
		$this->tpl->assign("total",$total);
		$this->tpl->assign("sitetitle",$this->lang["order"]." - ".$this->lang["usercp"]);
		$leader[0] = array("title"=>$this->lang["usercp"],"url"=>$this->url("usercp"));
		$leader[1] = array("title"=>$this->lang["order"],"url"=>$this->url("order,list"));
		if($keywords)
		{
			$leader[2] = array("title"=>sys_eval($this->lang["keywords"],array("kw"=>$keywords)));
			$page_url.= "keywords=".rawurlencode($keywords);
		}
		$this->tpl->assign("leader",$leader);
		$pagelist = $this->page_lib->page($page_url,$total,false,false);
		$this->tpl->assign("pagelist",$pagelist);
		$this->tpl->display("order_list.".$this->tpl->ext);
	}

	//付款
	function pay_f()
	{
		$sn = $this->trans_lib->safe("sn");
		$paycode = $this->trans_lib->safe("paycode");
		$pass = $this->trans_lib->safe("pass");
		if(!$paycode || !$sn)
		{
			error($this->lang["error"],$this->url("index"));
		}
		$rs = $this->payment_m->get_one_code($paycode);
		if(!$rs)
		{
			error($this->lang["order_not_payrs"],$this->url("index"));
		}
		$order_rs = $this->checkout_m->get_one_sn($sn);
		if(!$_SESSION["user_id"])
		{
			if(!$pass || $pass != $order_rs["pass"])
			{
				error($this->lang["order_not_popedom"],$this->url("index"));
			}
		}
		else
		{
			if($_SESSION["user_id"] != $order_rs["uid"])
			{
				error($this->lang["order_not_popedom"],$this->url("index"));
			}
		}
		$this->tpl->assign("order_rs",$order_rs);
		if($order_rs["pay_status"])
		{
			error($this->lang["order_status_pay_ok"],$this->url("index"));
		}
		$this->tpl->assign("rs",$rs);
		if($rs["next_act"] && file_exists(APP."control/".$rs["next_act"].".php"))
		{
			sys_header($this->url($rs["next_act"],"sn=".$sn."&paycode=".$paycode."&pass=".$pass));
		}
		else
		{
			$this->tpl->display("payment.".$this->tpl->ext);
		}
	}
}
?>