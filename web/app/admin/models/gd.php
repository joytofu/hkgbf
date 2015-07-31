<?php
/***********************************************************
	Filename: app/admin/models/gd.php
	Note	: GD相关数据库操作类
	Version : 3.0
	Author  : qinggan
	Update  : 2010-05-07
***********************************************************/
class gd_m extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function gd_m()
	{
		$this->__construct();
	}

	function get_list()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."gd ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql);
	}

	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."gd WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	//存储信息
	function save($data,$id=0)
	{
		if($id)
		{
			$this->db->update_array($data,"gd",array("id"=>$id));
			return $id;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"gd");
			return $insert_id;
		}
	}

	//
	function chk_sign($sign="")
	{
		if(!$sign)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."gd WHERE pictype='".$sign."'";
		if($this->db->get_one($sql))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	//删除操作
	function del($id)
	{
		if(!$id)
		{
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."gd WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	function gd_list($code="")
	{
		if(!$code)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."upfiles_gd WHERE gdtype='".$code."'";
		return $this->db->get_all($sql);
	}

	function gd_del($code="")
	{
		if(!$code)
		{
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."upfiles_gd WHERE gdtype='".$code."'";
		return $this->db->query($sql);
	}

}
?>
