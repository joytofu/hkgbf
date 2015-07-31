<?php
/***********************************************************
	Filename: app/www/models/module.php
	Note	: 模块中心
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-24
***********************************************************/
class module_m extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function module_m()
	{
		$this->__construct();
	}

	//取得一个模块的内容信息
	function get_one($id)
	{
		if(!$id)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."module WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function get_one_from_code($code)
	{
		if(!$code)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."module WHERE identifier='".$code."'";
		return $this->db->get_one($sql);
	}

	function get_mid_from_code($code)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."module WHERE identifier='".$code."'";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		return $rs["id"];
	}

	//取得所有模块中的标识串及ID，并配上对应关系
	function get_id_code_list()
	{
		$sql = "SELECT id,identifier FROM ".$this->db->prefix."module";
		$rslist = $this->db->get_all($sql);
		if(!$rslist)
		{
			return false;
		}
		$idlist = $codelist = array();
		foreach($rslist AS $key=>$value)
		{
			$idlist[$value["id"]] = $value["identifier"];
			$codelist[$value["identifier"]] = $value["id"];
		}
		return array("code"=>$codelist,"id"=>$idlist);
	}

	function fields_one($id)
	{
		if(!$id)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."module_fields WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		return $rs;
	}

	//取得模块的第一个
	function get_module_sub_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."list WHERE module_id='".$id."' ORDER BY taxis DESC,post_date DESC,id DESC LIMIT 1";
		return $this->db->get_one($sql);
	}

	function get_module_cateid($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."cate WHERE module_id='".$id."' AND status='1' AND parentid='0' ORDER BY taxis ASC,id DESC LIMIT 1";
		return $this->db->get_one($sql);
	}

	//取得所有支持搜索的模块
	function get_all_module()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."module WHERE ctrl_init='list' AND insearch='1' AND status='1' ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql);
	}

	//读取字段列表，这里不使用分表
	function fields_index($module_id,$ifstatus=0)
	{
		if(!$module_id)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."module_fields WHERE module_id='".$module_id."' ";
		if($ifstatus)
		{
			$sql .= " AND status='1' ";
		}
		$sql.= " ORDER BY taxis ASC,id DESC ";
		return $this->db->get_all($sql);
	}


}
?>