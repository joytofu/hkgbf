<?php
/***********************************************************
	Filename: app/www/control/tenpay.php
	Note	: 使用财付通付款
	Version : 3.0
	Author  : qinggan
	Update  : 2010-05-10
***********************************************************/
include_once(LIBS."payment/tenpay.php");//加载tenpay操作类
class tenpay_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("checkout");
		$this->load_model("payment");
	}

	function tenpay_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		$attach = $this->trans_lib->safe("attach");
		$sp_billno = $this->trans_lib->int("sp_billno");
		$pay_info = $this->trans_lib->safe("pay_info");
		if($attach && $sp_billno && $pay_info == "OK")
		{
			$this->notify($sp_billno,$attach,"0");
		}
		else
		{
			$this->load_tenpay();
		}
	}

	function load_tenpay()
	{
		$sn = $this->trans_lib->safe("sn");
		$paycode = "tenpay";
		$pass = $this->trans_lib->safe("pass");
		$rs = $this->payment_m->get_one_code($paycode);
		if(!$rs)
		{
			error($this->lang["alipay_not_rs"],$this->url("checkout,info","sn=".$sn."&pass=".$pass));
		}
		$order_rs = $this->checkout_m->get_one_sn($sn);
		if(!$_SESSION["user_id"])
		{
			if(!$pass || $pass != $order_rs["pass"])
			{
				error($this->lang["alipay_not_popedom"],$this->url("index"));
			}
		}
		else
		{
			if($_SESSION["user_id"] != $order_rs["uid"])
			{
				error($this->lang["alipay_not_popedom"],$this->url("index"));
			}
		}
		$this->tpl->assign("order_rs",$order_rs);
		if($order_rs["pay_status"])
		{
			error($this->lang["alipay_paystatus_ok"],$this->url("checkout,info","sn=".$sn."&pass=".$pass));
		}
		if($order_rs["price"]<0.001)
		{
			error($this->lang["alipay_free"],$this->url("checkout,info","sn=".$sn."&pass=".$pass));
		}
		$f_rs = $this->payment_m->get_fields($rs["id"]);
		//判断是否是客
		$tenpay_url = substr($this->url("tenpay","","&"),0,-1);
		$return_url = $this->sys_config["siteurl"].$tenpay_url;
		$tenpay = new tenpay_payment($f_rs,$return_url);
		$tenpay->order_rs($order_rs,$pass);//定单信息
		$tenpay->ip(sys_ip());
		$htmlbutton = $tenpay->create_button();//创建按钮
		$this->tpl->assign("htmlbutton",$htmlbutton);
		$this->tpl->display("payment/tenpay.".$this->tpl->ext);
	}

	function notify($id,$pass,$pay_result="0")
	{
		$paycode = "tenpay";
		$rs = $this->payment_m->get_one_code($paycode);
		$order_rs = $this->checkout_m->get_one($id);
		if($order_rs["pass"] != $pass)
		{
			error($this->lang["alipay_not_popedom"]."-----123456----".$pass,$this->url("index"));
		}
		$data = array();
		$f_rs = $this->payment_m->get_fields($rs["id"]);
		$tenpay_url = substr($this->url("tenpay","","&"),0,-1);
		$return_url = $this->sys_config["siteurl"].$tenpay_url;
		$tenpay = new tenpay_payment($f_rs,$return_url);
		$tenpay->response_array($_GET);
		if($pay_result == "0" && $tenpay->check_sign())
		{
			$total_fee = $this->trans_lib->int("total_fee");
			$price  = $total_fee>0 ? round($total_fee/100,2) : 0;
			$data["pay_status"] = $pay_result == "0" ? 1 : 0;
			$data["pay_price"] = $price;
			$data["pay_currency"] = "RMB";
			$data["pay_type"] = $rs["id"];
			$data["pay_date"] = $this->system_time;
			$data["pay_code"] = $this->trans_lib->safe("transaction_id");
			$this->payment_m->update_order($id,$data);
			$this->load_lib("email");
			$this->email_lib->order_update($id);//通知客户订单信息
			$this->email_lib->order_update_admin($id);//通知管理员有订单信息
		}
		else
		{
			error($this->lang["alipay_payment_false"],$this->url("checkout,info","sn=".$order_rs["sn"]."&pass=".$pass));
		}
		$show_url = $this->url("checkout,info","sn=".$order_rs["sn"]."&pass=".$pass,"&");
		$tenpay->success($show_url);
	}
}
?>