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

	function get_one($langid)
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

	//读取语言包变量信息
	function get_list($langid)
	{
		if(!$langid)
		{
			return false;
		}
		$sql = "SELECT var,val FROM ".$this->db->prefix."lang_msg WHERE langid='".$langid."' AND (ltype='admin' OR ltype='all') ORDER BY var ASC";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist)
		{
			return false;
		}
		$rslist = array();
		foreach($tmplist AS $key=>$value)
		{
			$keys = strtolower($value["var"]);
			$rslist[$keys] = $value["val"];
		}
		unset($tmplist);
		return $rslist;
	}
}
?>