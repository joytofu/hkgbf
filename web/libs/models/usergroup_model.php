<?php
/***********************************************************
	Filename: libs/models/usergroup_model.php
	Note	: 会员组公共模块，可在前后台共同使用
	Version : 3.0
	Author  : qinggan
	Update  : 2011-03-14
***********************************************************/
class usergroup_model extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function usergroup_model()
	{
		$this->__construct();
	}

	function fields_one($id)
	{
		if(!$id)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."user_fields WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		return $rs;
	}

}
?>