<?php
/***********************************************************
	Filename: app/admin/models_mysql/upfile.php
	Note	: 附件管理模块
	Version : 3.0
	Author  : qinggan
	Update  : 2009-11-24
***********************************************************/
class gdtype_m extends Model
{
	var $condition = " WHERE 1=1 ";
	var $psize = 20;
	function __construct()
	{
		parent::Model();
		$this->psize = defined("SYS_PSIZE") ? SYS_PSIZE : 100;
	}

	function gdtype_m()
	{
		$this->__construct();
	}

	function get_all($status=0,$edit=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."gd WHERE 1=1 ";
		if($status == 1)
		{
			$sql .= " AND status='1' ";
		}
		if($edit)
		{
			$sql .= " AND edit_default='1' ";
		}
		$sql.= " ORDER BY taxis ASC,id DESC";
		$rslist = $this->db->get_all($sql);
		return $rslist;
	}

}
?>