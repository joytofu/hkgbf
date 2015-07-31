<?php
/***********************************************************
	Filename: app/admin/models/login.php
	Note	: 登录模块加载
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-22
***********************************************************/
class login_m extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function login_m()
	{
		$this->__construct();
	}

	function check_login($user,$pass)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."admin ";
		$sql.= " WHERE name='".$user."' AND pass='".md5($pass)."' AND status='1'";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		return $rs;
	}
}
?>