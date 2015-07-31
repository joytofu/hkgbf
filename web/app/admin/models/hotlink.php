<?php
/***********************************************************
	Filename: app/admin/models/hotlink.php
	Note	: GD相关数据库操作类
	Version : 3.0
	Author  : qinggan
	Update  : 2010-05-07
***********************************************************/
class hotlink_m extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function hotlink_m()
	{
		$this->__construct();
	}

	function get_list($langid="zh",$status=false)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."module_link WHERE langid='".$langid."' ";
		if($status)
		{
			$sql .= " AND status='1'";
		}
		$sql.= " ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql);
	}

	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."module_link WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function get_one_url($url,$langid="zh")
	{
		if(!$url) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."module_link WHERE langid='".$langid."' AND linkurl='".$url."'";
		return $this->db->get_one($sql);
	}

	//存储信息
	function save($data,$id=0)
	{
		if($id)
		{
			$this->db->update_array($data,"module_link",array("id"=>$id));
			return $id;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"module_link");
			return $insert_id;
		}
	}

	//删除操作
	function del($id)
	{
		if(!$id)
		{
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."module_link WHERE id='".$id."'";
		return $this->db->query($sql);
	}
}
?>
