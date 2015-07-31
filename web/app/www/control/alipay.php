<?php
/***********************************************************
	Filename: app/www/control/paypal.php
	Note	: 使用Paypal付款
	Version : 3.0
	Author  : qinggan
	Update  : 2010-05-10
***********************************************************/
class alipay_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("checkout");
		$this->load_model("payment");
	}

	function alipay_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		$sn = $this->trans_lib->safe("sn");
		$paycode = "alipay";
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
		//加载alipay付款类
		$return_url = $this->sys_config["siteurl"].site_url("alipay,return","sn=".$sn."&pass=".$order_rs["pass"]);
		$show_url = $this->sys_config["siteurl"].site_url("checkout,info","sn=".$sn."&pass=".$order_rs["pass"]);
		$notify_url = $this->sys_config["siteurl"].site_url("alipay,notify","sn=".$sn."&pass=".$order_rs["pass"]);
		$order_rs["pdate"] = date("Y-m-d H:i:s",$order_rs["postdate"]);
		$parameter = array(
			"service"         => "create_direct_pay_by_user",  //交易类型
			"partner"         => $f_rs["partner"],          //合作商户号
			"return_url"      => $return_url,       //同步返回
			"notify_url"      => $notify_url,       //异步返回
			"_input_charset"  => $f_rs["charset"],   //字符集，默认为GBK
			"subject"         => sys_eval($this->lang["alipay_order_title"],$order_rs),        //商品名称，必填
			"body"            => sys_eval($this->lang["alipay_order_body"],$order_rs),        //商品描述，必填
			"out_trade_no"    => $order_rs["sn"],      //商品外部交易号，必填（保证唯一性）
			"total_fee"       => floatval($order_rs["price"]),            //商品单价，必填（价格不能为0）
			"payment_type"    => "1",               //默认为1,不需要修改
			"seller_email"    => $f_rs["seller_email"],
			"show_url"        => $show_url);
		include_once(LIBS."payment/alipay_service.php");//加载alipay_server操作类
		$alipay = new alipay_service($parameter,$f_rs["code"],"MD5");
		$link=$alipay->create_url();
		sys_header($link);
	}

	function return_f()
	{
		$sn = $this->trans_lib->safe("sn");
		$pass = $this->trans_lib->safe("pass");
		$paycode = "alipay";
		$rs = $this->payment_m->get_one_code($paycode);
		$order_rs = $this->checkout_m->get_one_sn($sn);
		$data = array();
		$id = $order_rs["id"];
		$f_rs = $this->payment_m->get_fields($rs["id"]);
		include_once(LIBS."payment/alipay_notify.php");//加载alipay_notify操作类
		$alipay = new alipay_notify($f_rs["partner"],$f_rs["code"],$f_rs["sign_type"],$f_rs["charset"],$f_rs["transport"]);
		$tmp_array = array();
		$tmp_array[] = $this->config->c;
		$tmp_array[] = $this->config->f;
		$tmp_array[] = "sn";
		$tmp_array[] = "pass";
		$verify_result = $alipay->return_verify($tmp_array);
		if(!$verify_result)
		{
			error($this->lang["alipay_payment_false"],$this->url("checkout,info","sn=".$sn."&pass=".$pass));
		}
		else
		{
			$f_rs = $this->payment_m->get_fields($rs["id"]);
			$data["pay_status"] = 1;
			$data["pay_price"] = $this->trans_lib->safe("total_fee");
			$data["pay_currency"] = $f_rs["currency"];
			$data["pay_type"] = $rs["id"];
			$data["pay_date"] = $this->system_time;
			$data["pay_code"] = $this->trans_lib->safe("trade_no");
			$this->payment_m->update_order($id,$data);
			error($this->lang["alipay_payment_success"],$this->url("checkout,info","sn=".$sn."&pass=".$pass));
		}
	}

	function notify_f()
	{
		$sn = $this->trans_lib->safe("sn");
		$pass = $this->trans_lib->safe("pass");
		$paycode = "alipay";
		$rs = $this->payment_m->get_one_code($paycode);
		$order_rs = $this->checkout_m->get_one_sn($sn);
		$f_rs = $this->payment_m->get_fields($rs["id"]);
		$data = array();
		$id = $order_rs["id"];
		include_once(LIBS."payment/alipay_notify.php");//加载alipay_notify操作类
		$alipay = new alipay_notify($f_rs["partner"],$f_rs["code"],"MD5",$f_rs["charset"],$f_rs["transport"]);
		$tmp_array = array();
		$tmp_array[] = $this->config->c;
		$tmp_array[] = $this->config->f;
		$tmp_array[] = "sn";
		$tmp_array[] = "pass";
		$verify_result = $alipay->notify_verify($tmp_array);
		if(!$verify_result)
		{
			exit("fail");
		}
		$payment_status = $this->trans_lib->safe("trade_status");
		if($payment_status == "WAIT_BUYER_PAY")
		{
			exit("success");
		}
		if($payment_status == "TRADE_FINISHED" || $payment_status == "TRADE_SUCCESS")
		{
			$data["pay_status"] = 1;
			$data["pay_price"] = $this->trans_lib->safe("total_fee");
			$data["pay_currency"] = $f_rs["currency"];
			$data["pay_type"] = $rs["id"];
			$data["pay_date"] = $this->system_time;
			$data["pay_code"] = $this->trans_lib->safe("trade_no");
			$this->payment_m->update_order($id,$data);
			$this->load_lib("email");
			$this->email_lib->order_update($id);//通知客户订单信息
			$this->email_lib->order_update_admin($id);//通知管理员有订单信息
		}
		exit("success");
	}
}
?>