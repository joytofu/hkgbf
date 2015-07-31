<?php
/***********************************************************
	Filename: libs/models/datalink_model.php
	Note	: 全局调用数据联动中心
	Version : 3.0
	Author  : qinggan
	Update  : 2010-01-04
***********************************************************/
class datalink_model extends Model
{
	var $langid = "zh";
	function __construct()
	{
		parent::Model();
	}

	function datalink_model()
	{
		$this->__construct();
	}

	function langid($langid="zh")
	{
		$this->langid = $langid;
	}

	function get_list($groupname="")
	{
		if(!$groupname)
		{
			return false;
		}
		$sql = "SELECT s.* FROM ".$this->db->prefix."select s JOIN ".$this->db->prefix."select_group g ON(s.gid=g.id) WHERE g.title='".$groupname."'";
		$sql.= " ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql);
	}

}
?>