<?php
/***********************************************************
	Filename: libs/models/user_model.php
	Note	: 会员公共模块，可在前后台共同使用
	Version : 3.0
	Author  : qinggan
	Update  : 2011-03-14
***********************************************************/
class user_model extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function user_model()
	{
		$this->__construct();
	}

	//存储扩展信息
	function save_ext($array)
	{
		return $this->db->insert_array($array,"user_ext","replace");
	}
}
?>