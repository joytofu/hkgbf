<?php
/***********************************************************
	Filename: app/admin/models/order.php
	Note	: 订单模块
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-22
***********************************************************/
class order_m extends Model
{
	var $psize = 20;
	function __construct()
	{
		parent::Model();
		$this->psize = defined("SYS_PSIZE") ? SYS_PSIZE : 20;
	}

	function order_m()
	{
		$this->__construct();
	}

	function get_list($offset=0,$condition="")
	{
		$sql = "SELECT o.*,p.title payment_title FROM ".$this->db->prefix."order o LEFT JOIN ".$this->db->prefix."payment p ON(o.pay_type=p.id) ";
		if($condition)
		{
			$sql.= " WHERE ".$condition;
		}
		$sql.= " ORDER BY o.postdate DESC,o.id DESC LIMIT ".$offset.",".$this->psize;
		return $this->db->get_all($sql);
	}

	function get_count($condition="")
	{
		$sql = "SELECT count(o.id) FROM ".$this->db->prefix."order o";
		if($condition)
		{
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function get_products($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order_products WHERE orderid='".$id."'";
		return $this->db->get_all($sql);
	}

	function get_address($id)
	{
		if(!$id)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."order_address WHERE order_id='".$id."' ORDER BY address_type ASC";
		return $this->db->get_all($sql,"address_type");
	}

	function get_one_products($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."order_products WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."order SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	function del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."order WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."order_products WHERE orderid='".$id."'";
		$this->db->query($sql);
		return true;
	}

	function pro_del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."order_products WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	function update($data,$id=0)
	{
		if(!$id)
		{
			return false;
		}
		$this->db->update_array($data,"order",array("id"=>$id));
		return true;
	}


	//更新订单总价
	function update_totoal_price($id)
	{
		$sql = "SELECT SUM(amount * price) total FROM ".$this->db->prefix."order_products WHERE orderid='".$id."'";
		$rs = $this->db->get_one($sql);
		$sql = "UPDATE ".$this->db->prefix."order SET price='".$rs["total"]."' WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	function pro_save($data,$id=0)
	{
		if($id)
		{
			$this->db->update_array($data,"order_products",array("id"=>$id));
			return $id;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"order_products");
			return $insert_id;
		}
	}

	function save_address($data)
	{
		if(!$data || !is_array($data))
		{
			return false;
		}
		$this->db->insert_array($data,"order_address","replace");
	}

}
?>