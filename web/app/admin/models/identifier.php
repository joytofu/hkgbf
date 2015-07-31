<?php
#=====================================================================
#	Filename: app/admin/models_mysql/identifier.php
#	Note	: 标识符管理工具
#	Version : 3.0
#	Author  : qinggan
#	Update  : 2009-11-4
#=====================================================================
class identifier_m extends Model
{
	var $langid = "zh";
	function __construct()
	{
		parent::Model();
	}

	function identifier_m()
	{
		$this->__construct();
	}

	function langid($langid="zh")
	{
		$this->langid = $langid;
	}

	//取得指定的组列表信息
	function get_sign($g_sign)
	{
		if(!$g_sign)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."identifier WHERE ";
		$sql .= " g_sign='".$g_sign."' ";
		$sql.= " ORDER BY taxis ASC,id DESC";
		$rslist = $this->db->get_all($sql);
		return $rslist;
	}

	//取得权限ID
	function popedom_id($name)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."identifier WHERE g_sign='popedom' AND sign='".$name."'";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		return $rs["id"];
	}

	//取得权限
	function popedom_list()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."identifier WHERE ";
		$sql .= " g_sign='popedom' ";
		$sql.= " ORDER BY taxis ASC,id DESC";
		$rslist = $this->db->get_all($sql);
		if(!$rslist)
		{
			return false;
		}
		$r = array();
		foreach($rslist AS $key=>$value)
		{
			$r[$value["id"]] = $value["title"];
		}
		return $r;
	}
}
?>