<?php
/***********************************************************
	Filename: app/admin/models/menu.php
	Note	: 导航菜单
	Version : 3.0
	Author  : qinggan
	Update  : 2010-05-19
***********************************************************/
class menu_m extends Model
{
	var $langid = "zh";
	function __construct()
	{
		parent::Model();
	}

	function menu_m()
	{
		$this->__construct();
	}

	function langid($langid="zh")
	{
		$this->langid = $langid;
	}

	function get_all()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."menu WHERE langid='".$this->langid."' ORDER BY taxis ASC,id DESC";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist)
		{
			return false;
		}
		$plist = $slist = array();
		foreach($tmplist AS $key=>$value)
		{
			if($value["parentid"])
			{
				$slist[] = $value;
			}
			else
			{
				$plist[] = $value;
			}
		}
		$rslist = array();
		foreach($plist AS $key=>$value)
		{
			$rslist[] = $value;
			foreach($slist AS $k=>$v)
			{
				if($v["parentid"] == $value["id"])
				{
					$rslist[] = $v;
				}
			}
		}
		return $rslist;
	}

	function get_parent()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."menu WHERE parentid='0' AND langid='".$this->langid."' ORDER BY taxis ASC";
		return $this->db->get_all($sql);
	}

	//取得一条记录
	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."menu WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	//删除一条记录
	function del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."menu WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//判断是否有子类
	function ifson($id)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."menu WHERE parentid='".$id."'";
		return $this->db->get_one($sql);
	}

	function save($data,$id=0)
	{
		if($id)
		{
			$this->db->update_array($data,"menu",array("id"=>$id));
			return true;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"menu");
			return $insert_id;
		}
	}

	function set_status($id,$status=0)
	{
		if(!$id)
		{
			return false;
		}
		$sql = "UPDATE ".$this->db->prefix."menu SET status='".$status."' WHERE id='".$id."'";
		$this->db->query($sql);
	}

}
?>