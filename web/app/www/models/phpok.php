<?php
#=====================================================================
#	Filename: app/admin/models/phpok.php
#	Note	: 数据调用模块层
#	Version : 3.0
#	Author  : qinggan
#	Update  : 2009-12-30
#=====================================================================
class phpok_m extends Model
{
	var $langid = "zh";
	function __construct()
	{
		parent::Model();
	}

	function langid($langid="zh")
	{
		$this->langid = $langid;
	}

	function phpok_m()
	{
		$this->__construct();
	}
	//通过ID取得数据（此操作用于后台）
	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."phpok WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	//通过标识串取得调用的配置数据
	function get_one_sign($val)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."phpok WHERE identifier='".$val."' AND langid='".$this->langid."' AND status='1'";
		return $this->db->get_one($sql);
	}
}
?>