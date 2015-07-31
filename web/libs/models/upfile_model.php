<?php
/***********************************************************
	Filename: libs/models/upfile_model.php
	Note	: 附件公共模块
	Version : 3.0
	Author  : qinggan
	Update  : 2011-03-14
***********************************************************/
class upfile_model extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function upfile_model()
	{
		$this->__construct();
	}

	function get_one($id)
	{
		$this->db->close_cache();
		$sql = "SELECT * FROM ".$this->db->prefix."upfiles WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		$this->db->open_cache();
		return $rs;
	}

	//取得附件列表
	function pic_gd_list($id)
	{
		$this->db->close_cache();
		$sql = "SELECT * FROM ".$this->db->prefix."upfiles_gd WHERE pid='".$id."'";
		$rslist = $this->db->get_all($sql);
		$this->db->open_cache();
		return $rslist;
	}

	function save_gd($data)
	{
		return $this->db->insert_array($data,"upfiles_gd","replace");
	}

}
?>