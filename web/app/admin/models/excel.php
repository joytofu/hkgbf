<?php
/***********************************************************
	Filename: excel.php
	Note	: Excel里用到的相用SQL语句
	Version : 4.0
	Author  : qinggan
	Update  : 2011-12-08 13:24
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class excel_m extends Model
{
	var $extlist;
	var $clist;
	function __construct()
	{
		parent::Model();
	}

	function excel_m()
	{
		$this->__construct();
	}

	function system_fields()
	{
		//判断该字段是否和核心表中的字段重名，主表
		$sql = "SHOW FIELDS FROM ".$this->db->prefix."list";
		$rslist = $this->db->get_all($sql);
		$idlist = array("thumb");
		foreach($rslist AS $key=>$value)
		{
			$idlist[] = $value["Field"];
		}
		return $idlist;
	}

	function ext_c_fields($mid)
	{
		$sql = "SELECT id,identifier,input FROM ".$this->db->prefix."module_fields WHERE module_id='".$mid."'";
		$rslist = $this->db->get_all($sql);
		$extlist = $clist = array();
		foreach(($rslist ? $rslist : array()) AS $key=>$value)
		{
			if($value["input"] != "edit")
			{
				$extlist[] = $value["identifier"];
			}
			else
			{
				$clist[] = $value["identifier"];
			}
		}
		$this->extlist = $extlist;
		$this->clist = $clist;
	}

	function extlist()
	{
		return $this->extlist;
	}

	function clist()
	{
		return $this->clist;
	}
	
	function get_allcate($module_id,$lang_id){
	  $sql = "SELECT * FROM ".$this->db->prefix."cate WHERE module_id = $module_id AND langid = '$lang_id'";
	  return  $this->db->get_all($sql);
	}

}
?>