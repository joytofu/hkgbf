<?php
/***********************************************************
	Filename: libs/payment/paypal.php
	Note	: Paypal付款操作
	Version : 3.0
	Author  : qinggan
	Update  : 2010-03-11
***********************************************************/
class paypal_payment
{
	#[付款接收的账号]
	var $payid;
	#[自动返回的认证串，在启用paypal自动返回功能时使用]
	var $pdt;
	#[支持的货币类型]
	var $currency_string = "USD,AUD,CAD,EUR,GBP,CHF,CZK,DKK,HKD,HUF,JPY,NOK,NZD,PLN,SEK,SGD,THB";
	#[当前正在使用的货币类型，如果不符合系统限定，则使用默认USD]
	var $currency = "USD";
	#[付款按钮目标地址，调试和正式使用是不一样的]
	#[调试地址：https://www.sandbox.paypal.com/cgi-bin/webscr]
	#[正式地址：https://www.paypal.com/cgi-bin/webscr]
	var $action_url = "https://www.paypal.com/cgi-bin/webscr";
	#[附款成功后自动跳回页面]
	var $return_url;
	#[取消付款自动返回的网址]
	var $cancel_return;
	#[订单编号，在不使用产品购物车时用于显示相关名称]
	var $ordersn;
	#[付款金额，不允许为空和0]
	var $amount;

	#[指定按钮的唯一ID号]
	var $address_html;

	#[在操作成功后，执行的脚本]
	var $notify_url;
	var $logo = "";

	function __construct($payid,$pdt="")
	{
		$this->payid = $payid;
		$this->pdt = $pdt;
	}
	function paypal_payment($payid,$pdt="")
	{
		$this->__construct($payid,$pdt);
	}

	function set_paypal($payid,$pdt="")
	{
		$this->payid = $payid;
		$this->pdt = $pdt;
	}

	//设置货币
	function set_currency($value)
	{
		if(!in_array($value,explode(",",$this->currency_string)))
		{
			$this->currency = "USD";
		}
		else
		{
			$this->currency = $value;
		}
	}

	//设置其他变量
	function set_value($var,$value)
	{
		$this->$var = $value;
	}

	function create_button()
	{
		$html = "<form method='post' name='paypalform' id='paypalform' action='".$this->action_url."'>";
		$html .= "<input type='hidden' name='cmd' value='_ext-enter'>";
		$html .= "<input type='hidden' name='return' value='".$this->return_url."'>";
		$html .= "<input type='hidden' name='business' value='".$this->payid."'>";
		$html .= "<input type='hidden' name='amount' value='".$this->amount."'>";
		$html .= "<input type='hidden' name='redirect_cmd' value='_xclick'>";
		$html .= "<input type='hidden' name='undefined_quantity' value='0'>";
		$html .= "<input type='hidden' name='item_name' value='Order number: ".$this->ordersn."'>";
		$html .= "<input type='hidden' name='item_number' value='".$this->ordersn."'>";
		$html .= "<input type='hidden' name='invoice' value='".$this->ordersn."'>";
		$html .= "<input type='hidden' name='charset' value='utf-8'>";
		$html .= "<input type='hidden' name='cancel_return' value='".$this->cancel_return."'>";
		$html .= "<input type='hidden' name='cn' value='1'>";
		$html .= "<input type='hidden' name='notify_url' value='".$this->notify_url."'>";
		$html .= "<input type='hidden' name='rm' value='2'>";
		$html .= "<input type='hidden' name='currency_code' value='".$this->currency."'>";
		$html .= "<input type='hidden' name='no_shipping' value='1'>";
		$html .= "</form>";
		return $html;
	}

	function get_details($tx)
	{
		if(!$tx || !$this->pdt)
		{
			return false;
		}
		$req = "cmd=_notify-synch";
		$req.= "&tx=".$tx;
		$req.= "&at=".$this->pdt;
		$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header.= "Content-type: application/x-www-form-urlencoded\r\n";
		$header.= "Content-length: " . strlen($req) . "\r\n\r\n";
		$tmp = parse_url($this->action_url);
		$host = $tmp["host"] ? $tmp["host"] : "www.paypal.com";
		$handle= fsockopen($host,80,$errno,$errstr,30);
		if(!$handle)
		{
			return false;
		}
		fputs($handle,$header.$req);
		$res = '';
		$headerdone = false;
		while(!feof($handle))
		{
			$line = fgets($handle,1024);
			if(strcmp($line, "\r\n")== 0)
			{
				$headerdone = true;
			}
			elseif($headerdone)
			{
				$res .= $line;
			}
		}
		fclose($handle);
		$lines = explode("\n", $res);
		$keyarray = array();
		$count_lines = count($lines);
		if($lines[0] && trim($lines[0]) == "SUCCESS")
		{
			for($i=1; $i<$count_lines;$i++)
			{
				list($key,$val) = explode("=", $lines[$i]);
				$keyarray[urldecode($key)] = urldecode($val);
			}
			return $keyarray;
		}
		else
		{
			return false;
		}
	}
}
?>