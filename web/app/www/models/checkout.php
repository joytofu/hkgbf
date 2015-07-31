<?php
/***********************************************************
	Filename: app/www/models/checkout.php
	Note	: 购物车表中涉及到的操作
	Version : 3.0
	Author  : qinggan
	Update  : 2010-05-07
***********************************************************/
class checkout_m extends Model
{
	var $sessid;
	function __construct()
	{
		parent::Model();
	}

	function checkout_m()
	{
		$this->__construct();
	}

	//存储订单，返回订单ID
	function save_order($data)
	{
		if(!$data || !is_array($data))
		{
			return false;
		}
		$id = $this->db->insert_array($data,"order");
		if(!$id)
		{
			return false;
		}
		//生成订单编号
		$this->_order_sn($id);
		return $id;
	}

	function save_address($data)
	{
		if(!$data || !is_array($data))
		{
			return false;
		}
		$this->db->insert_array($data,"order_address","replace");
	}

	function save_products($id,$rslist)
	{
		$this->db->close_cache();
		if(!$id || !$rslist)
		{
			return false;
		}
		foreach($rslist AS $key=>$value)
		{
			$array = array();
			$array["orderid"] = $id;
			$array["proid"] = $value["proid"];
			$array["title"] = $value["title"];
			$array["amount"] = $value["amount"];
			$array["price"] = $value["price"];
			$array["price_currency"] = $value["price_currency"];
			$array["thumb_id"] = $value["thumb_id"];
			$this->db->insert_array($array,"order_products","replace");
		}
		return true;
	}

	//初始化订单编号
	function _order_sn($id)
	{
		$this->db->close_cache();
		if(!$id)
		{
			return false;
		}
		$rs = $this->get_one($id);
		if(!$rs)
		{
			return false;
		}
		$ordersn = defined("ORDER_SN") ? ORDER_SN : "year-month-date-id";
		$array = explode("-",$ordersn);
		$sn = "";
		foreach($array AS $key=>$value)
		{
			$sn .= $value == "date" ? date("d",$rs["postdate"]) : "";
			$sn .= $value == "month" ? date("m",$rs["postdate"]) : "";
			$sn .= $value == "year" ? date("Y",$rs["postdate"]) : "";
			$sn .= $value == "id" ? str_pad($id,5,"0",STR_PAD_LEFT) : "";
			$sn .= $value == "rand" ? rand(10,99) : "";
			$sn .= $value == "usertype" ? ($rs["uid"] ? "C" : "G") : "";
			$sn .= $value == "otype" ? ($rs["otype"] == "cart" ? "C" : "U") : "";
			$sn .= $value == "count" ? $this->format_count($rs["postdate"]) : "";
		}
		$sql = "UPDATE ".$this->db->prefix."order SET sn='".$sn."' WHERE id='".$id."'";
		$this->db->query($sql);
		return $sn;
	}

	function format_count($postdate="")
	{
		$this->db->close_cache();
		$start_date = strtotime(date("Y-m-d",$postdate));//今日订单
		$end_date = $start_date + 24*60*60;
		$sql = "SELECT count(id) FROM ".$this->db->prefix."order WHERE postdate>='".$start_date."' AND postdate<'".$end_date."'";
		$count = $this->db->count($sql);
		if(!$count)
		{
			return "001";
		}
		else
		{
			return str_pad($count+1,3,"0",STR_PAD_LEFT);
		}
	}

	function get_one($id)
	{
		$this->db->close_cache();
		$sql = "SELECT * FROM ".$this->db->prefix."order WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function get_one_sn($sn,$pass="")
	{
		$rs = array();
		$this->db->close_cache();
		$sql = "SELECT * FROM ".$this->db->prefix."order WHERE sn='".$sn."'";
		if($pass)
		{
			$sql .= " AND pass='".$pass."'";
		}
		return $this->db->get_one($sql);
	}

	function get_products($id,$thumb_type="")
	{
		$this->db->close_cache();
		if($thumb_type)
		{
			$sql = "SELECT c.*,u.filename picture FROM ".$this->db->prefix."order_products c ";
			$sql.= "LEFT JOIN ".$this->db->prefix."upfiles_gd u ON(c.thumb_id=u.pid AND u.gdtype='".$thumb_type."') ";
			$sql.= " WHERE c.orderid='".$id."'";
			$sql.= " LIMIT 100";
		}
		else
		{
			$sql = "SELECT * FROM ".$this->db->prefix."order_products WHERE orderid='".$id."'";
		}
		return $this->db->get_all($sql);
	}

	function get_address($id)
	{
		if(!$id)
		{
			return false;
		}
		$this->db->close_cache();
		$sql = "SELECT * FROM ".$this->db->prefix."order_address WHERE order_id='".$id."' ORDER BY address_type ASC";
		$rslist = $this->db->get_all($sql,"address_type");
		return $rslist;
	}

	function get_list($uid,$keywords="",$offset=0,$psize=30)
	{
		$this->db->close_cache();
		$sql = "SELECT * FROM ".$this->db->prefix."order WHERE uid='".$uid."' ";
		if($keywords)
		{
			$sql .= " AND sn LIKE '%".$keywords."%' ";
		}
		$sql.= " ORDER BY postdate DESC,id DESC LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
	}

	function get_count($uid,$keywords="")
	{
		$this->db->close_cache();
		$sql = "SELECT count(id) FROM ".$this->db->prefix."order WHERE uid='".$uid."'";
		if($keywords)
		{
			$sql .= " AND sn LIKE '%".$keywords."%' ";
		}
		return $this->db->count($sql);
	}

	//付款信息
	function pay_one($id)
	{
		$this->db->close_cache();
		$sql = "SELECT * FROM ".$this->db->prefix."payment WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	//付款列表
	function pay_list()
	{
		$this->db->close_cache();
		$sql = "SELECT * FROM ".$this->db->prefix."payment WHERE status='1' ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql);
	}
}
?>