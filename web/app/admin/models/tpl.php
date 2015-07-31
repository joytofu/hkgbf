<?php
#=====================================================================
#	Filename: app/admin/models_mysql/tpl.php
#	Note	: 后台模板信息管理
#	Version : 3.0
#	Author  : qinggan
#	Update  : 2009-11-4
#=====================================================================
class tpl_m extends Model
{
	var $langid = "zh";
	function __construct()
	{
		parent::Model();
	}

	function tpl_m()
	{
		$this->__construct();
	}

	function set_langid($langid="zh")
	{
		$this->langid = $langid;
	}

	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."tpl WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	//取得模板列表
	function get_list($langid="zh",$status=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."tpl WHERE langid='".$langid."' ";
		if($status)
		{
			$sql.= " AND status='1' ";
		}
		$sql.= " ORDER BY taxis ASC,id DESC ";
		return $this->db->get_all($sql);
	}

	function save($data,$id=0)
	{
		if($id)
		{
			$this->db->update_array($data,"tpl",array("id"=>$id));
			return true;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"tpl");
			return $insert_id;
		}
	}

	function set_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."tpl SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	function del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."tpl WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	function set_default($id,$langid="zh")
	{
		$sql = "UPDATE ".$this->db->prefix."tpl SET ifdefault='0' WHERE ifdefault='1' AND langid='".$langid."'";
		$this->db->query($sql);
		$sql = "UPDATE ".$this->db->prefix."tpl SET ifdefault='1' WHERE id='".$id."' AND langid='".$langid."'";
		$this->db->query($sql);
		return true;
	}
}
?>