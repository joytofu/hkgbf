<?php
/***********************************************************
	Filename: app/www/models/usergroup.php
	Note	: 前台会员组权限获取
	Version : 3.0
	Author  : qinggan
	Update  : 2011-03-14
***********************************************************/
class usergroup_m extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function usergroup_m()
	{
		$this->__construct();
	}

	function get_default()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user_group WHERE ifdefault='1' AND group_type='user'";
		return $this->db->get_one($sql);
	}

	function get_one($id)
	{
		if(!$id)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."user_group WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function get_guest()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user_group WHERE group_type='guest'";
		return $this->db->get_one($sql);
	}

	//扩展表字段
	function fields_index($groupid,$ifstatus=0)
	{
		if(!$groupid)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."user_fields WHERE group_id='".$groupid."' ";
		if($ifstatus)
		{
			$sql .= " AND status='1' ";
		}
		$sql.= " ORDER BY taxis ASC,id DESC ";
		return $this->db->get_all($sql);
	}

	//会员组，仅显示前100个会员组
	function group_list()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user_group WHERE ifshow='1' ORDER BY id DESC LIMIT 100";
		return $this->db->get_all($sql);
	}
}
?>