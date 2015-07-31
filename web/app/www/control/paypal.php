<?php
/***********************************************************
	Filename: app/www/control/paypal.php
	Note	: 使用Paypal付款
	Version : 3.0
	Author  : qinggan
	Update  : 2010-05-10
***********************************************************/
include_once(LIBS."payment/paypal.php");//加载paypal操作类
class paypal_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("checkout");
		$this->load_model("payment");
	}

	function paypal_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		$sn = $this->trans_lib->safe("sn");
		$paycode = "paypal";
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
		//加载paypal付款类
		$paypal = new paypal_payment($f_rs["business"],$f_rs["at"]);
		$price = $f_rs["price_format"] ? sys_format_price($f_rs["price_format"] * $order_rs["price"]) : sys_format_price($order_rs["price"]);
		$paypal->set_value("amount",$price);
		$currency = $f_rs["currency_code"] ? $f_rs["currency_code"] : "USD";
		$paypal->set_value("currency",$currency);
		$paypal->set_value("ordersn",$order_rs["sn"]);
		$paypal->set_value("action_url",$f_rs["action_url"]);
		$return_url = $this->sys_config["siteurl"].site_url("checkout,info","sn=".$sn."&pass=".$order_rs["pass"]);
		$paypal->set_value("return_url",$return_url);//成功返回
		$paypal->set_value("cancel_return",$return_url);//取消退出
		$notify_url = $this->sys_config["siteurl"].site_url("paypal,notify","sn=".$sn."&pass=".$order_rs["pass"]);
		$paypal->set_value("notify_url",$notify_url);//订单成功后发送给网站的信息
		$htmlbutton = $paypal->create_button();
		$this->tpl->assign("htmlbutton",$htmlbutton);
		$this->tpl->display("payment/paypal.".$this->tpl->ext);
	}

	function notify_f()
	{
		$sn = $this->trans_lib->safe("sn");
		$pass = $this->trans_lib->safe("pass");
		$paycode = "paypal";
		$rs = $this->payment_m->get_one_code($paycode);
		$order_rs = $this->checkout_m->get_one_sn($sn);
		$data = array();
		$id = $order_rs["id"];
		$payment_status = $this->trans_lib->safe("payment_status");
		$data["pay_status"] = $payment_status == "Completed" ? 1 : 0;
		$data["pay_price"] = $this->trans_lib->safe("mc_gross");
		$data["pay_currency"] = $this->trans_lib->safe("mc_currency");
		$data["pay_type"] = $rs["id"];
		$data["pay_date"] = $this->system_time;
		$data["pay_code"] = $this->trans_lib->safe("txn_id");
		$this->payment_m->update_order($id,$data);
		$this->load_lib("email");
		$this->email_lib->order_update($id);//通知客户订单信息
		$this->email_lib->order_update_admin($id);//通知管理员有订单信息
		exit("ok");
	}
}
?>