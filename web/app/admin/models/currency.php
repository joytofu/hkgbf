<?php
/***********************************************************
	Filename: app/admin/models/currency.php
	Note	: GD相关数据库操作类
	Version : 3.0
	Author  : qinggan
	Update  : 2010-05-07
***********************************************************/
class currency_m extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function currency_m()
	{
		$this->__construct();
	}

	function get_list()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."currency ORDER BY taxis ASC";
		return $this->db->get_all($sql);
	}

	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."currency WHERE code='".$id."'";
		return $this->db->get_one($sql);
	}

	//存储信息
	function save($data)
	{
		return $this->db->insert_array($data,"currency","replace");
	}

	//删除操作
	function del($id)
	{
		if(!$id)
		{
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."currency WHERE code='".$id."'";
		return $this->db->query($sql);
	}

	function set_default($code="RMB")
	{
		$rs = $this->get_one($code);
		if(!$rs)
		{
			return false;
		}
		$sql = "UPDATE ".$this->db->prefix."currency SET ifdefault='0'";
		$this->db->query($sql);
		$sql = "UPDATE ".$this->db->prefix."currency SET ifdefault='1' WHERE code='".$code."'";
		$this->db->query($sql);
		return true;
	}
}
?>
