<?php
/***********************************************************
	Filename: app/admin/models/subscribers.php
	Note	: 邮件订阅之订户管理
	Version : 3.0
	Author  : qinggan
	Update  : 2011-03-11
***********************************************************/
class subscribers_m extends Model
{
	var $psize = 20;
	function __construct()
	{
		parent::Model();
	}

	function subscribers_m()
	{
		$this->__construct();
	}

	function get_one($id)
	{
		if(!$id)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."subscribers WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	//读取会员列表数据
	function get_list($keywords="",$pageid=0,$psize=30)
	{
		$offset = $pageid>0 ? ($pageid-1)*$psize : 0;
		$sql = "SELECT * FROM ".$this->db->prefix."subscribers ";
		$sql.= "WHERE 1=1 ";
		if($keywords)
		{
			$sql.= " AND email LIKE '%".$keywords."%' ";
		}
		$sql.= " ORDER BY postdate DESC,id DESC LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
	}

	//取得总数量
	function get_count($keywords="")
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."subscribers ";
		$sql.= "WHERE 1=1 ";
		if($keywords)
		{
			$sql.= " AND email LIKE '%".$keywords."%' ";
		}
		return $this->db->count($sql);
	}

	function del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."subscribers WHERE id='".$id."'";
		return $this->db->query($sql);
	}
}
?>