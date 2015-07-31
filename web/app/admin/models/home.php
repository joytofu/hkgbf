<?php
/***********************************************************
	Filename: app/admin/models/home.php
	Note	: 首页的一些统计
	Version : 3.0
	Author  : qinggan
	Update  : 2010-05-27
***********************************************************/
class home_m extends Model
{
	var $langid = "zh";

	function __construct()
	{
		parent::Model();
	}

	function home_m()
	{
		$this->__construct();
	}

	function langid($langid="zh")
	{
		$this->langid = $langid;
	}

	function get_list_count()
	{
		$sql = "SELECT count(l.id) total,m.title,m.id mid FROM ".$this->db->prefix."list l JOIN ".$this->db->prefix."module m ON(l.module_id=m.id) ";
		$sql.= " WHERE l.status='0' AND l.langid='".$this->langid."' GROUP BY l.module_id ORDER BY m.taxis ASC";
		return $this->db->get_all($sql);
	}

	function get_total()
	{
		$sql = "SELECT count(id) total FROM ".$this->db->prefix."list";
		return $this->db->count($sql);
	}

	function get_file()
	{
		$sql = "SELECT count(id) total FROM ".$this->db->prefix."upfiles";
		return $this->db->count($sql);
	}
}
?>