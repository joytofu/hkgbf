<?php
/***********************************************************
	Filename: libs/models/subscribers_model.php
	Note	: 订阅用户前后台通用模块
	Version : 3.0
	Author  : qinggan
	Update  : 2011-03-12
***********************************************************/
class subscribers_model extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function subscribers_model()
	{
		$this->__construct();
	}

	//检测邮箱是否冲突
	function chk_email($email,$id=0)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."subscribers WHERE email='".$email."' ";
		if($id)
		{
			$sql.= " AND id!='".$id."' ";
		}
		return $this->db->get_one($sql);
	}

	//删除邮件注册
	function del_email($email)
	{
		$sql = "DELETE FROM ".$this->db->prefix."subscribers WHERE email='".$email."'";
		return $this->db->query($sql);
	}

	//存储会员数据
	function save($data,$id=0)
	{
		if($id)
		{
			$this->db->update_array($data,"subscribers",array("id"=>$id));
			return true;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"subscribers");
			return $insert_id;
		}
	}

	function get_one_email($email)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."subscribers WHERE email='".$email."'";
		return $this->db->get_one($sql);
	}
}
?>