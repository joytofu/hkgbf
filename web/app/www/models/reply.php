<?php
#=====================================================================
#	Filename: app/www/models/reply.php
#	Note	: 回复操作
#	Version : 3.0
#	Author  : qinggan
#	Update  : 2010-2-11
#=====================================================================
class reply_m extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function reply_m()
	{
		$this->__construct();
	}

	function save($data,$id=0)
	{
		if($id)
		{
			$this->db->update_array($data,"reply",array("id"=>$id));
			return true;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"reply");
			return $insert_id;
		}
	}

	function get_list($tid,$offset=0,$psize=30)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."reply WHERE tid='".$tid."' AND status='1' ORDER BY postdate DESC,id DESC LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
	}

	function get_best_list($tid)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."reply WHERE tid='".$tid."' AND status='1' AND ifbest='1' ORDER BY postdate DESC,id DESC LIMIT 100";
		return $this->db->get_all($sql);
	}

	function get_count($tid)
	{
		$sql = "SELECT count(id) total FROM ".$this->db->prefix."reply WHERE tid='".$tid."' AND status='1'";
		return $this->db->count($sql);
	}

	//更新星星点评数
	function update_star($id)
	{
		$sql = "SELECT count(id) mycount,sum(star) mystar FROM ".$this->db->prefix."reply WHERE tid='".$id."' AND status='1'";
		$this->db->close_cache();
		$rs = $this->db->get_one($sql);
		$this->db->open_cache();
		$star = $rs["mycount"]>0 ? round($rs["mystar"]/$rs["mycount"],2) : 0;
		$sql = "UPDATE ".$this->db->prefix."list SET star='".$star."' WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	//获取星星评论各个等级，通过Group By来实现
	function getlist_star($id)
	{
		$sql = "SELECT count(id) mycount,star FROM ".$this->db->prefix."reply WHERE tid='".$id."' AND status='1' GROUP BY star ORDER BY star ASC";
		return $this->db->get_all($sql,"star");
	}

}
?>