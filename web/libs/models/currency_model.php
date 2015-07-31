<?php
/***********************************************************
	Filename: libs/models/currency_model.php
	Note	: 货币转换
	Version : 3.0
	Author  : qinggan
	Update  : 2011-07-16 06:41
***********************************************************/
class currency_model extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function currency_model()
	{
		$this->__construct();
	}

	//取得当前货币信息
	function get_one($currency="RMB")
	{
		$app = sys_init();
		if($app->currency->$currency)
		{
			return $app->currency->$currency;
		}
		else
		{
			$sql = "SELECT * FROM ".$app->db->prefix."currency WHERE code='".$currency."'";
			$rs = $this->db->get_one($sql);
			if(!$rs) return false;
			$app->currency($rs,$currency);
			return $rs;
		}
	}

	function get_default()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."currency WHERE ifdefault='1'";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			$rs = array();
			$rs["val"] = "1.0000";
			$rs["title"] = "人民币";
			$rs["code"] = "CNY";
			$rs["symbol_left"] = "RMB￥";
			$rs["symbol_right"] = "";
		}
		return $rs;
	}

	function get_default_currency()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."currency WHERE ifdefault='1'";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return "CNY";
		}
		else
		{
			return $rs["code"];
		}
	}
}
?>