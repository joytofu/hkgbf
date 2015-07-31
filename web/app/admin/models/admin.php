<?php
/***********************************************************
	Filename: app/admin/models_mysql/websitesystem.php
	Note	: 管理员模块加载
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-22
***********************************************************/
class admin_m extends Model
{
	var $psize = 20;
	function __construct()
	{
		parent::Model();
		$this->psize = defined("SYS_PSIZE") ? SYS_PSIZE : 20;
	}

	function admin_m()
	{
		$this->__construct();
	}

	function check_login($user,$pass)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."admin ";
		$sql.= " WHERE name='".$user."' AND pass='".sys_md5($pass)."' AND status='1'";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		return $rs;
	}

	function get_module_id($adminid)
	{
		$sql = " SELECT * FROM ".$this->db->prefix."admin WHERE id='".$adminid."' AND status='1' ";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		if($rs["if_system"])
		{
			return "all";
		}
		if(!$rs["popedom"])
		{
			return false;
		}
		$popedom = explode(",",$rs["popedom"]);
		$idlist = array();
		foreach($popedom AS $key=>$value)
		{
			$tmp = explode(":",$value);
			if($tmp[0])
			{
				$idlist[] = $tmp[0];
			}
		}
		if(count($idlist)>0)
		{
			return array_unique($idlist);
		}
		else
		{
			return false;
		}
	}

	function get_one($id)
	{
		if(!$id)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."admin WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		return $rs;
	}

	function get_list($pageid=0,$condition="")
	{
		$offset = $pageid>0 ? ($pageid-1)*$this->psize : 0;
		$sql = " SELECT * FROM ".$this->db->prefix."admin WHERE 1=1 ";
		if($condition)
		{
			$sql .= " AND ".$condition;
		}
		$sql.= " ORDER BY id DESC LIMIT ".$offset.",".$this->psize;
		return $this->db->get_all($sql);
	}

	//取得总数量
	function get_count($condition="")
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."admin WHERE 1=1 ";
		if($condition)
		{
			$sql .= " AND ".$condition;
		}
		return $this->db->count($sql);
	}

	//存储会员数据
	function save($data,$id=0)
	{
		if($id)
		{
			$this->db->update_array($data,"admin",array("id"=>$id));
			return true;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"admin");
			return $insert_id;
		}
	}

	function set_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."admin SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	function del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."admin WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//检测账号是否冲突
	function chk_name($name,$id=0)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."admin WHERE name='".$name."' ";
		if($id)
		{
			$sql.= " AND id!='".$id."' ";
		}
		return $this->db->get_one($sql);
	}

	//检测邮箱是否冲突
	function chk_email($email,$id=0)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."admin WHERE email='".$email."' ";
		if($id)
		{
			$sql.= " AND id!='".$id."' ";
		}
		return $this->db->get_one($sql);
	}

	//更新个人密码
	function update_pass($pass,$id)
	{
		$sql = "UPDATE ".$this->db->prefix."admin SET pass='".sys_md5($pass)."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}
}
?>