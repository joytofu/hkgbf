<?php
/***********************************************************
	Filename: app/admin/models/reply.php
	Note	: 回复评论管理
	Version : 3.0
	Author  : qinggan
	Update  : 2010-05-16
***********************************************************/
class reply_m extends Model
{
	var $psize = 20;
	function __construct()
	{
		parent::Model();
		if(defined("SYS_PSIZE"))
		{
			$this->psize = SYS_PSIZE;
		}
	}

	function reply_m()
	{
		$this->__construct();
	}

	function get_list($offset=0,$condition="")
	{
		$sql = "SELECT r.*,l.title t_title FROM ".$this->db->prefix."reply r LEFT JOIN ".$this->db->prefix."list l ON(r.tid=l.id) ";
		if($condition)
		{
			$sql .= " WHERE ".$condition;
		}
		$sql .= " ORDER BY postdate DESC,id DESC LIMIT ".$offset.",".$this->psize;
		return $this->db->get_all($sql);
	}

	function get_one($id)
	{
		$sql = "SELECT r.*,l.title t_title FROM ".$this->db->prefix."reply r LEFT JOIN ".$this->db->prefix."list l ON (r.tid=l.id) WHERE r.id='".$id."'";
		return $this->db->get_one($sql);
	}

	function get_count($condition="")
	{
		$sql = "SELECT count(r.id) FROM ".$this->db->prefix."reply r ";
		if($condition)
		{
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	function save($data,$id)
	{
		if(!$id || !$data || !is_array($data))
		{
			return false;
		}
		$this->db->update_array($data,"reply",array("id"=>$id));
		return true;
	}

	//删除
	function del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."reply WHERE id IN(".$id.")";
		return $this->db->query($sql);
	}

	//审核
	function status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."reply SET status='".$status."' WHERE id IN(".$id.")";
		return $this->db->query($sql);
	}

	//更新星星点评数
	function update_star($id)
	{
		$sql = "SELECT count(id) mycount,sum(star) mystar FROM ".$this->db->prefix."reply WHERE tid='".$id."' AND status='1'";
		$rs = $this->db->get_one($sql);
		$star = $rs["mycount"]>0 ? round($rs["mystar"]/$rs["mycount"],2) : 0;
		$sql = "UPDATE ".$this->db->prefix."list SET star='".$star."' WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}
}
?>