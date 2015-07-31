<?php
/***********************************************************
	Filename: app/www/models/cart.php
	Note	: 购物车表中涉及到的操作
	Version : 3.0
	Author  : qinggan
	Update  : 2010-05-07
***********************************************************/
class cart_m extends Model
{
	var $sessid;
	function __construct()
	{
		parent::Model();
	}

	function cart_m()
	{
		$this->__construct();
	}

	function sessid($sessid)
	{
		$this->sessid = $sessid;
	}

	function get_all($thumb_type="")
	{
		$this->db->close_cache();
		if($thumb_type)
		{
			$sql = "SELECT c.*,u.filename picture FROM ".$this->db->prefix."cart c ";
			$sql.= "LEFT JOIN ".$this->db->prefix."upfiles_gd u ON(c.thumb_id=u.pid AND u.gdtype='".$thumb_type."') ";
			$sql.= " WHERE c.sessid='".$this->sessid."'";
			$sql.= " ORDER BY c.id DESC LIMIT 100";
		}
		else
		{
			$sql = "SELECT * FROM ".$this->db->prefix."cart WHERE sessid='".$this->sessid."'";
		}
		return $this->db->get_all($sql);
	}

	//添加有记录的产品
	function add_array($rs,$amount=1,$id=0)
	{
		$this->db->close_cache();
		if(!$rs || !is_array($rs) || !$id)
		{
			return false;
		}
		if(intval($amount)<1)
		{
			$amount = 1;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."cart WHERE sessid='".$this->sessid."' AND proid='".$id."'";
		$old_rs = $this->db->get_one($sql);
		if($old_rs)
		{
			$amount += $old_rs["amount"];
			$sql = "UPDATE ".$this->db->prefix."cart SET title='".$rs["title"]."',amount='".$amount."',";
			$sql.= "price='".$rs["price"]."',price_currency='".$rs["price_currency"]."',";
			$sql.= "postdate='".time()."',thumb_id='".$rs["thumb_id"]."' WHERE id='".$id."'";
		}
		else
		{
			$sql = "INSERT INTO ".$this->db->prefix."cart(sessid,proid,title,amount,price,price_currency,postdate,thumb_id) ";
			$sql.= " VALUES('".$this->sessid."','".$id."','".$rs["title"]."','".$amount."','".$rs["price"]."',";
			$sql.= "'".$rs["price_currency"]."','".time()."','".$rs["thumb_id"]."')";
		}
		return $this->db->query($sql);
	}

	//添加自定义的产品
	function add($title="",$price=0,$price_currency="CNY",$amount=1)
	{
		$this->db->close_cache();
		$sql = "INSERT INTO ".$this->db->prefix."cart(sessid,proid,title,amount,price,postdate,thumb_id) VALUES('".$this->sessid."','".$id."','".$title."','".$amount."','".$price."','".$price_currency."','".time()."','0')";
		return $this->db->query($sql);
	}

	function update($id,$amount=0)
	{
		$this->db->close_cache();
		$sql = "UPDATE ".$this->db->prefix."cart SET amount='".$amount."',postdate='".time()."' WHERE id='".$id."' AND sessid='".$this->sessid."'";
		return $this->db->query($sql);
	}

	//删除
	function del($id=0)
	{
		$this->db->close_cache();
		$sql = "DELETE FROM ".$this->db->prefix."cart WHERE sessid='".$this->sessid."'";
		if($id)
		{
			$sql .= " AND id='".$id."' ";
		}
		return $this->db->query($sql);
	}
}
?>