<?php
/***********************************************************
	Filename: libs/models/plugin_model.php
	Note	: 插件信息读取
	Version : 3.0
	Author  : qinggan
	Update  : 2011-05-16
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class plugin_model extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function plugin_model()
	{
		$this->__construct();
	}

	//取得符合条件的插件信息
	function get_all($string="")
	{
		if(!$string)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."plugins WHERE status='1' AND hooks LIKE '%".$string."%' ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql);
	}
}
