<?php
/***********************************************************
	Filename: libs/payment/tenpay.php
	Note	: 财付通支付接口
	Version : 3.0
	Author  : qinggan
	Update  : 2011-02-11
***********************************************************/
class tenpay_payment
{
	#[附款成功后自动跳回页面]
	var $return_url;
	#[支付页面]
	var $payurl = "http://service.tenpay.com/cgi-bin/v3.0/payservice.cgi";
	#[订单信息，数组]
	var $order_rs;
	var $order_id;
	var $order_time;
	var $order_sn;
	var $order_price;
	var $order_email;
	var $tenpay_id;//腾讯订单ID
	var $attach = "";//自定义参数，原样返回
	var $spbill_create_ip;
	var $sign_array; //要签名的数组
	var $get_sign_array;//取回值的签名数据
	var $get_array;//返回值数组;

	function __construct($payment_rs,$return_url)
	{
		foreach($payment_rs As $key=>$value)
		{
			$this->$key = $value;
		}
		//$this->return_url = "http://www.phpok.com";
		$this->return_url = $return_url;
		//更新要签名的数组
		$this->sign_array["bargainor_id"] = $this->bargainor_id;
		$this->sign_array["return_url"] = $this->return_url;
	}

	function tenpay_payment($payment_rs,$return_url)
	{
		$this->__construct($payment_rs,$return_url);
	}

	//生成ORDER信息
	function order_rs($rs,$attach="")
	{
		$this->order_rs = $rs;
		$this->order_id = $rs["id"];
		$this->order_time = $rs["postdate"];
		$this->order_sn = $rs["sn"];
		$this->order_price = intval($rs["price"] * 100);
		$this->order_email = $rs["email"];
		$this->order_desc = "Order Number: ".$rs["sn"];
		$order_id = str_pad($rs["id"], 10, 0, STR_PAD_LEFT);
		$this->tenpay_id = $this->bargainor_id.date("Ymd",$rs["postdate"]).$order_id;
		$this->attach = $attach;
		//签名数组
		$this->sign_array["cmdno"] = 1;
		$this->sign_array["date"] = date("Ymd",$this->order_time);
		$this->sign_array["transaction_id"] = $this->tenpay_id;
		$this->sign_array["sp_billno"] = $this->order_id;
		$this->sign_array["total_fee"] = $this->order_price;
		$this->sign_array["fee_type"] = 1;
		$this->sign_array["attach"] = $this->attach;
	}

	function ip($ip="")
	{
		$this->spbill_create_ip = $ip;
		$this->sign_array["spbill_create_ip"] = $this->spbill_create_ip;
	}

	//创建按钮HTML代码
	function create_button()
	{
		if(!$this->bargainor_id) return false;
		$html = "<form method='post' name='payform' id='payform' action='".$this->payurl."'>";
		$html .= "<input type='hidden' name='cmdno' value='1'>";
		$html .= "<input type='hidden' name='cs' value='".$this->cs."'>";
		$html .= "<input type='hidden' name='date' value='".date("Ymd",$this->order_time)."'>";
		$html .= "<input type='hidden' name='bank_type' value='0'>";
		$html .= "<input type='hidden' name='transaction_id' value='".$this->tenpay_id."'>";
		$html .= "<input type='hidden' name='sp_billno' value='".$this->order_id."'>";
		$html .= "<input type='hidden' name='purchaser_id' value='".$this->order_email."'>";
		$html .= "<input type='hidden' name='desc' value='".$this->order_desc."'>";
		$html .= "<input type='hidden' name='total_fee' value='".$this->order_price."'>";
		$html .= "<input type='hidden' name='fee_type' value='1'>";
		$html .= "<input type='hidden' name='return_url' value='".$this->return_url."'>";
		$html .= "<input type='hidden' name='bargainor_id' value='".$this->bargainor_id."'>";
		$html .= "<input type='hidden' name='attach' value='".$this->attach."'>";
		$html .= "<input type='hidden' name='spbill_create_ip' value='".$this->spbill_create_ip."'>";
		$html .= "<input type='hidden' name='sign' value='".$this->sign($this->sign_array)."'>";
		$html .= "</form>";
		//echo $html;
		//exit;
		return $html;
	}

	function sign($rslist)
	{
		$string = "cmdno=".$rslist["cmdno"]."&";
		$string.= "date=".$rslist["date"]."&";
		$string.= "bargainor_id=".$rslist["bargainor_id"]."&";
		$string.= "transaction_id=".$rslist["transaction_id"]."&";
		$string.= "sp_billno=".$rslist["sp_billno"]."&";
		$string.= "total_fee=".$rslist["total_fee"]."&";
		$string.= "fee_type=".$rslist["fee_type"]."&";
		$string.= "return_url=".$rslist["return_url"]."&";
		$string.= "attach=".$rslist["attach"]."&";
		$string.= "spbill_create_ip=".$rslist["spbill_create_ip"]."&";
		$string.= "key=".$this->key;
		$sign = strtolower(md5($string));
		return $sign;
	}


	function get_sign($rslist)
	{
		$string = "cmdno=".$rslist["cmdno"]."&";
		$string.= "pay_result=".$rslist["pay_result"]."&";
		$string.= "date=".$rslist["date"]."&";
		$string.= "transaction_id=".$rslist["transaction_id"]."&";
		$string.= "sp_billno=".$rslist["sp_billno"]."&";
		$string.= "total_fee=".$rslist["total_fee"]."&";
		$string.= "fee_type=".$rslist["fee_type"]."&";
		$string.= "attach=".$rslist["attach"]."&";
		$string.= "key=".$this->key;
		$sign = strtolower(md5($string));
		return $sign;
	}


	//取得返回信息
	function response_array($rslist)
	{
		if(!$rslist || !is_array($rslist)) return false;
		$get_sign_key = array("cmdno","pay_result","date","transaction_id","sp_billno","total_fee","fee_type","attach");
		foreach($rslist As $key=>$value)
		{
			if(in_array($key,$get_sign_key))
			{
				$this->get_sign_array[$key] = $value;
			}
			if($key == "key")
			{
				$this->key = $value;
			}
		}
		$this->get_array = $rslist;
	}

	//检测返回值是否正常
	function check_sign()
	{
		$sign = $this->get_sign($this->get_sign_array);
		if($sign == strtolower($this->get_array["sign"]))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	//财付通系统判断是否成功返回支付
	function success($url)
	{
		$html = "<html><head>\r\n";
		$html.= "<meta name=\"TENCENT_ONLINE_PAYMENT\" content=\"China TENCENT\">";
		$html.= "<script language=\"javascript\">\r\n";
		$html.= "window.location.href='" . $url . "';\r\n";
		$html.= "</script>\r\n";
		$html.= "</head><body></body></html>";
		echo $html;
		exit;
	}
}
?>