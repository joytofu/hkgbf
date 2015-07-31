<?php
/***********************************************************
	Filename: tplconfig.php
	Note	: 重新读取模板配置
	Version : 3.0
	Author  : qinggan
	Update  : 2009-12-22
***********************************************************/
class tplconfig_m extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function tplconfig_m()
	{
		$this->__construct();
	}

	function get_one($id=0,$langid="zh")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."tpl WHERE status='1'";
		if($id)
		{
			$sql.= " AND id='".$id."' ";
		}
		else
		{
			$sql.= " AND (ifdefault='1' OR ifsystem='1') ";
		}
		$sql .= " AND langid='".$langid."' ";
		$sql .= " ORDER BY ifdefault DESC,ifsystem DESC,id DESC";
		return $this->db->get_one($sql);
	}
}
?>