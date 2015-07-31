<?php
/***********************************************************
	Filename: langconfig.php
	Note	: 重新读取语言模块
	Version : 3.0
	Author  : qinggan
	Update  : 2009-12-22
***********************************************************/
class langconfig_m extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function langconfig_m()
	{
		$this->__construct();
	}

	function get_one($langid="zh")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."lang WHERE status='1'";
		if($langid)
		{
			$sql.= " AND langid='".$langid."' ";
		}
		else
		{
			$sql.= " AND (ifdefault='1' OR ifsystem='1') ";
		}
		$sql .= " ORDER BY ifdefault DESC,ifsystem DESC,langid DESC";
		return $this->db->get_one($sql);
	}

	function get_all($format=true)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."lang WHERE status='1' ORDER BY ifdefault DESC,taxis ASC";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist)
		{
			return false;
		}
		if(!$format)
		{
			return $tmplist;
		}
		$rslist = array();
		foreach($tmplist AS $key=>$value)
		{
			$rslist[$value["langid"]] = $value["title"];
		}
		return $rslist;
	}
}
?>