<?php
/***********************************************************
	Filename: app/admin/models_mysql/websitesystem.php
	Note	: 管理员模块加载
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-22
***********************************************************/
class user_m extends Model
{
	var $psize = 20;
	function __construct()
	{
		parent::Model();
		$this->psize = defined("SYS_PSIZE") ? SYS_PSIZE : 20;
	}

	function user_m()
	{
		$this->__construct();
	}

	function get_one($id)
	{
		if(!$id)
		{
			return false;
		}
		$sql = "SELECT u.*,f.thumb picture FROM ".$this->db->prefix."user u LEFT JOIN ".$this->db->prefix."upfiles f ON(u.thumb_id=f.id) WHERE u.id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		//取得扩展内容
		//取得扩展字段信息
		$sql = "SELECT field,val FROM ".$this->db->prefix."user_ext WHERE id='".$id."'";
		$tmp_rs = $this->db->get_all($sql);
		if($tmp_rs && is_array($tmp_rs) && count($tmp_rs)>0)
		{
			foreach($tmp_rs AS $key=>$value)
			{
				$rs[$value["field"]] = $value["val"];
			}
		}
		return $rs;
	}

	//读取会员列表数据
	function get_list($pageid=0,$keywords="")
	{
		$offset = $pageid>0 ? ($pageid-1)*$this->psize : 0;
		$sql = " SELECT * FROM ".$this->db->prefix."user WHERE 1=1 ";
		if($keywords)
		{
			$sql .= " AND (email LIKE '%".$keywords."%' OR name LIKE '%".$keywords."%') ";
		}
		$sql.= " ORDER BY id DESC LIMIT ".$offset.",".$this->psize;
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist) return false;
		$idstring = sys_id_string(array_keys($rslist));
		$sql = "SELECT * FROM ".$this->db->prefix."user_ext WHERE id IN(".$idstring.")";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist) $tmplist = array();
		foreach($tmplist AS $key=>$value)
		{
			$rslist[$value["id"]][$value["field"]] = $value["val"];
		}
		unset($tmplist);
		$tmplist = array();
		foreach($rslist AS $key=>$value)
		{
			$tmplist[] = $value;
		}
		return $tmplist;
	}

	//取得总数量
	function get_count($keywords="")
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."user WHERE 1=1 ";
		if($keywords)
		{
			$sql .= " AND (email LIKE '%".$keywords."%' OR name LIKE '%".$keywords."%') ";
		}
		return $this->db->count($sql);
	}

	//存储会员数据
	function save($data,$id=0)
	{
		if($id)
		{
			$this->db->update_array($data,"user",array("id"=>$id));
			return $id;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"user");
			return $insert_id;
		}
	}

	function set_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."user SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	function del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."user WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//检测账号是否冲突
	function chk_name($name,$id=0)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."user WHERE name='".$name."' ";
		if($id)
		{
			$sql.= " AND id!='".$id."' ";
		}
		return $this->db->get_one($sql);
	}

	//检测邮箱是否冲突
	function chk_email($email,$id=0)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."user WHERE email='".$email."' ";
		if($id)
		{
			$sql.= " AND id!='".$id."' ";
		}
		return $this->db->get_one($sql);
	}

}
?>