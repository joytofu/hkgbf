<?php
/***********************************************************
	Filename: app/admin/models/usergroup.php
	Note	: 会员组模块
	Version : 3.0
	Author  : qinggan
	Update  : 2011-03-14
***********************************************************/
class usergroup_m extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function usergroup_m()
	{
		$this->__construct();
	}

	function get_default()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user_group WHERE ifdefault='1' AND group_type='user'";
		return $this->db->get_one($sql);
	}

	function get_one($id)
	{
		if(!$id)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."user_group WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function get_all()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user_group ORDER BY id DESC";
		return $this->db->get_all($sql);
	}


	//存储会员数据
	function save($data,$id=0)
	{
		if($id)
		{
			$this->db->update_array($data,"user_group",array("id"=>$id));
			return true;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"user_group");
			return $insert_id;
		}
	}

	function set_default($id)
	{
		$sql = "UPDATE ".$this->db->prefix."user_group SET ifdefault='0' WHERE group_type='user' AND ifdefault='1'";
		$this->db->query($sql);
		$sql = "UPDATE ".$this->db->prefix."user_group SET ifdefault='1' WHERE group_type='user' AND id='".$id."'";
		return $this->db->query($sql);
	}

	//删除操作
	function del($id)
	{
		//取得默认数
		$rs = $this->get_default();
		if(!$rs) return false;
		$new_id = $rs["id"];
		$sql = "UPDATE ".$this->db->prefix."user SET groupid='".$new_id."' WHERE groupid='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."user_group WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//扩展表字段
	function fields_index($groupid,$ifstatus=0)
	{
		if(!$groupid)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."user_fields WHERE group_id='".$groupid."' ";
		if($ifstatus)
		{
			$sql .= " AND status='1' ";
		}
		$sql.= " ORDER BY taxis ASC,id DESC ";
		return $this->db->get_all($sql);
	}

	//读取某个字段内容
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

	//判断标识符是否使用
	function chk_identifier2($val,$group_id)
	{
		$forbidden = array("photo","thumb","picture","ext","user","filename","list");
		//自定义的禁用添加的字段
		if(in_array($val,$forbidden))
		{
			return true;
		}
		//判断该字段是否和核心表中的字段重名，主表
		$sql = "SHOW FIELDS FROM ".$this->db->prefix."user";
		$rslist = $this->db->get_all($sql);
		$idlist = array();
		foreach($rslist AS $key=>$value)
		{
			$idlist[] = $value["Field"];
		}
		if(in_array($val,$idlist))
		{
			return true;
		}
		unset($rslist,$idlist);
		//判断模块中是否有相应的标识串，如有则禁止使用
		$sql = "SELECT id FROM ".$this->db->prefix."user_fields WHERE identifier='".$val."' AND group_id='".$group_id."'";
		$rs = $this->db->get_one($sql);
		if($rs)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	//存储信息
	function fields_save($data,$id=0)
	{
		if($id)
		{
			$this->db->update_array($data,"user_fields",array("id"=>$id));
			return true;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"user_fields");
			return $insert_id;
		}
	}

	//设置权限状态
	function fields_status($id)
	{
		$sql = "SELECT status FROM ".$this->db->prefix."user_fields WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		$status = $rs["status"] ? 0 : 1;
		$sql = "UPDATE ".$this->db->prefix."user_fields SET status='".$status."' WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	//删除模块操作
	function fields_del($id)
	{
		//删除模块字段
		$sql = "DELETE FROM ".$this->db->prefix."user_fields WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

}
?>