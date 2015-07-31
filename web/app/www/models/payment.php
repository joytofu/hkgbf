<?php
/***********************************************************
	Filename: app/www/models/payment.php
	Note	: 购物车表中涉及到的操作
	Version : 3.0
	Author  : qinggan
	Update  : 2010-05-07
***********************************************************/
class payment_m extends Model
{
	var $sessid;
	function __construct()
	{
		parent::Model();
	}

	function payment_m()
	{
		$this->__construct();
	}

	//付款信息
	function get_one($id)
	{
		$this->db->close_cache();
		$sql = "SELECT * FROM ".$this->db->prefix."payment WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function get_one_code($code)
	{
		$this->db->close_cache();
		$sql = "SELECT * FROM ".$this->db->prefix."payment WHERE code='".$code."'";
		return $this->db->get_one($sql);
	}

	//付款列表
	function get_list()
	{
		$this->db->close_cache();
		$sql = "SELECT * FROM ".$this->db->prefix."payment WHERE status='1' ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql);
	}

	function get_fields($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."payment_val WHERE payid='".$id."'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist)
		{
			return false;
		}
		$rs = array();
		foreach($rslist AS $key=>$value)
		{
			$rs[$value["code"]] = $value["val"];
		}
		return $rs;
	}

	//更新订单状态
	function update_order($id,$data)
	{
		if(!$id || !$data || !is_array($data))
		{
			return false;
		}
		foreach($data AS $key=>$value)
		{
			$$key = $value;
		}
		if(!$pay_date)
		{
			$pay_date = time();
		}
		$sql = "UPDATE ".$this->db->prefix."order SET pay_status='".$pay_status."',pay_type='".$pay_type."',pay_code='".$pay_code."',pay_price='".$pay_price."',pay_currency='".$pay_currency."',pay_date='".$pay_date."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}
}
?>