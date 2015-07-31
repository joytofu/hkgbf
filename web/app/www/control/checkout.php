<?php
/***********************************************************
	Filename: app/www/control/checkout.php
	Note	: 存储订单信息，同时展示订单的一些其他信息
	Version : 3.0
	Author  : qinggan
	Update  : 2010-05-07
***********************************************************/
class checkout_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("cart");
		$this->load_model("checkout");
	}

	function checkout_c()
	{
		$this->__construct();
	}

	//存储订单信息
	function index_f()
	{
		//检测验证码是否为空
		if(function_exists("imagecreate") && defined("SYS_VCODE_USE") && SYS_VCODE_USE == true)
		{
			$chk = $this->trans_lib->safe("sys_check");
			if(!$chk)
			{
				error($this->lang["login_vcode_empty"],$this->url("cart"));
			}
			$chk = md5($chk);
			if($chk != $_SESSION[SYS_VCODE_VAR])
			{
				error($this->lang["login_vcode_false"],$this->url("cart"));
			}
			unset($_SESSION[SYS_VCODE_VAR]);
		}
		$this->db->close_cache();//禁止缓存
		$this->cart_m->sessid($this->session_lib->sessid);
		$rslist = $this->cart_m->get_all();
		$otype = "cart";
		if(!$rslist)
		{
			$otype = "user";
			$array = array();
			$array["proid"] = "0";
			$array["title"] = $this->trans_lib->safe("product_name");
			$array["amount"] = $this->trans_lib->int("product_amount");
			$array["price"] = $this->trans_lib->float("product_price");
			$array["price_currency"] = $_SESSION["currency_default"]["code"];
			$array["thumb_id"] = 0;
			if(!$array["title"])
			{
				error($this->lang["checkout_not_empty"],$this->url("cart"));
			}
			if($array["amount"]<1)
			{
				error($this->lang["checkout_limit_1"],$this->url("cart"));
			}
			$rslist[0] = $array;
		}
		if(!$rslist)
		{
			error($this->lang["checkout_not_rslist"],$this->url("cart"));
		}
		//计算总价
		$total_price = 0;
		foreach($rslist AS $key=>$value)
		{
			$total_price += sys_format_price($value["price"],$value["price_currency"],true) * $value["amount"];
			$n = $value;
			$n["price"] = sys_format_price($value["price"],$value["price_currency"],true);
			$n["price_currency"] = $_SESSION["currency_default"]["code"];
			$rslist[$key] = $n;
		}
		$array_sys = array();
		$array_sys["otype"] = $otype;
		$array_sys["pass"] = md5(rand(5,99).time().date("Ymd"));//32位密码串
		$array_sys["price"] = $total_price;
		$array_sys["price_currency"] = $_SESSION["currency_default"]["code"];
		$array_sys["uid"] = $_SESSION["user_id"] ? $_SESSION["user_id"] : "0";
		$array_sys["note"] = $this->trans_lib->safe("note");
		$array_sys["postdate"] = $this->system_time;
		$array_sys["email"] = $this->trans_lib->safe("email");
		//存储订单信息
		$order_id = $this->checkout_m->save_order($array_sys);
		if(!$order_id)
		{
			error($this->lang["checkout_save_error"],site_url("cart"));
		}
		//存储产品信息
		$this->checkout_m->save_products($order_id,$rslist);
		//存储收货人信息
		$address = array();
		$address["order_id"] = $order_id;
		$address["address_type"] = "shipping";
		$address["fullname"] = $this->trans_lib->safe("s_fullname");
		$address["tel"] = $this->trans_lib->safe("s_tel");
		$address["email"] = $this->trans_lib->safe("s_email");
		$address["country"] = $this->trans_lib->safe("s_country");
		$address["address"] = $this->trans_lib->safe("s_address");
		$address["zipcode"] = $this->trans_lib->safe("s_zipcode");
		$address["note"] = $this->trans_lib->safe("s_note");
		$this->checkout_m->save_address($address);
		//存储账单接收者地址信息
		if($this->sys_config["cart_address"])
		{
			$address = array();
			$address["order_id"] = $order_id;
			$address["address_type"] = "billing";
			$address["fullname"] = $this->trans_lib->safe("b_fullname");
			$address["tel"] = $this->trans_lib->safe("b_tel");
			$address["email"] = $this->trans_lib->safe("b_email");
			$address["country"] = $this->trans_lib->safe("b_country");
			$address["address"] = $this->trans_lib->safe("b_address");
			$address["zipcode"] = $this->trans_lib->safe("b_zipcode");
			$address["note"] = $this->trans_lib->safe("b_note");
			$this->checkout_m->save_address($address);
		}
		//删除购物车内容
		$this->cart_m->del();
		//取得订单信息
		$rs = $this->checkout_m->get_one($order_id);
		//判断是否发送订单通知
		if($_sys["smtp_order"])
		{
			$this->load_lib("email");
			$this->email_lib->order($order_id);//通知客户订单信息
			$this->email_lib->order_admin($order_id);//通知管理员有订单信息
		}
		$error_title = sys_eval($this->lang["checkout_save_success"],array("sn"=>$rs["sn"]));
		$error_url = site_url("checkout,info","sn=".$rs["sn"]."&pass=".$rs["pass"]);
		error($error_title,$error_url);
	}

	function info_f()
	{
		$sn = $this->trans_lib->safe("sn");
		$pass = $this->trans_lib->safe("pass");
		$rs = $this->checkout_m->get_one_sn($sn,$pass);
		if(!$rs)
		{
			error($this->lang["checkout_not_rs"],$this->url("index"));
		}
		if(!$_SESSION["user_id"])
		{
			if(!$pass || $pass != $rs["pass"])
			{
				error($this->lang["checkout_not_popedom"],$this->url("index"));
			}
		}
		else
		{
			if($_SESSION["user_id"] != $rs["uid"])
			{
				error($this->lang["checkout_not_popedom"],$this->url("index"));
			}
		}
		$this->tpl->assign("rs",$rs);
		//产品信息
		$rslist = $this->checkout_m->get_products($rs["id"],$this->sys_config["cart_thumb"]);
		$this->tpl->assign("rslist",$rslist);
		//地址信息
		$address = $this->checkout_m->get_address($rs["id"]);
		$this->tpl->assign("address",$address);
		//导航及头部菜单
		$this->tpl->assign("sitetitle",sys_eval($ths->lang["checkout_info_title"],$rs));
		$leader[0] = array("title"=>$this->lang["checkout"],"url"=>$this->url("order,list"));
		$leader[1] = array("title"=>sys_eval($this->lang["checkout_title_2"],$rs));
		$this->tpl->assign("leader",$leader);
		$print = $this->trans_lib->int("print");
		if($rs["pay_status"] && $rs["pay_type"])
		{
			$pay_rs = $this->checkout_m->pay_one($rs["pay_type"]);
			if($pay_rs)
			{
				$this->tpl->assign("pay_rs",$pay_rs);
			}
		}
		else
		{
			$pay_rslist = $this->checkout_m->pay_list();
			$this->tpl->assign("pay_rslist",$pay_rslist);
		}
		if($price)
		{
			$this->tpl->display("order_print.".$this->tpl->ext);
		}
		else
		{
			$this->tpl->display("order_info.".$this->tpl->ext);
		}
	}
}
?>