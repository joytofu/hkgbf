<?php
/***********************************************************
	Filename: np_model.php
	Note	: 上下主题模块
	Version : 3.0
	Author  : qinggan
	Update  : 2010-05-15
***********************************************************/
class np_model extends Model
{
	var $langid = "zh";
	function __construct()
	{
		parent::Model();
	}

	function np_model()
	{
		$this->__construct();
	}

	function langid($langid="zh")
	{
		$this->langid = $langid;
	}

	function get_mid($id)
	{
		$sql = "SELECT module_id FROM ".$this->db->prefix."list WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		return $rs["module_id"];
	}

	//取得下一主题
	function get_next($id,$cateid=0,$pictype="",$num=1)
	{
		if(!$cateid)
		{
			$mid = $this->get_mid($id);
		}
		if(!$cateid && !$mid)
		{
			return false;
		}
		if($pictype)
		{
			$sql = "SELECT l.*,u.filename picture FROM ";
		}
		else
		{
			$sql = "SELECT l.* FROM ";
			$pictype = "thumb";
		}
		$sql.= " ".$this->db->prefix."list l LEFT JOIN ".$this->db->prefix."upfiles_gd u ON (l.thumb_id=u.pid AND u.gdtype='".$pictype."') ";
		$sql.= " WHERE l.id>'".$id."' AND l.langid='".$this->langid."' AND l.status='1' AND l.hidden='0' ";
		if($cateid)
		{
			$sql.= " AND l.cate_id='".$cateid."'";
		}
		else
		{
			$sql.= " AND l.module_id='".$mid."'";
		}
		$sql.= " ORDER BY l.post_date DESC,l.id DESC LIMIT ".$num;
		return $this->db->get_all($sql);
	}

	//取得上一主题
	function get_prev($id,$cateid=0,$pictype="",$num=1)
	{
		if(!$cateid)
		{
			$mid = $this->get_mid($id);
		}
		if(!$cateid && !$mid)
		{
			return false;
		}
		if($pictype)
		{
			$sql = "SELECT l.*,u.filename picture FROM ";
		}
		else
		{
			$sql = "SELECT l.* FROM ";
			$pictype = "thumb";
		}
		$sql.= " ".$this->db->prefix."list l LEFT JOIN ".$this->db->prefix."upfiles_gd u ON (l.thumb_id=u.pid AND u.gdtype='".$pictype."') ";
		$sql.= " WHERE l.id<'".$id."' AND l.langid='".$this->langid."' AND l.status='1' AND l.hidden='0' ";
		if($cateid)
		{
			$sql.= " AND l.cate_id='".$cateid."'";
		}
		else
		{
			$sql.= " AND l.module_id='".$mid."'";
		}
		$sql.= " ORDER BY l.post_date DESC,l.id DESC LIMIT ".$num;
		return $this->db->get_all($sql);
	}
}
?>