<?php
#=====================================================================
#	Filename: app/admin/models_mysql/datalink.php
#	Note	: 标识符管理工具
#	Version : 3.0
#	Author  : qinggan
#	Update  : 2009-11-4
#=====================================================================
class datalink_m extends Model
{
	var $sql_ext = "WHERE 1=1 ";
	var $psize = 20;
	function __construct()
	{
		parent::Model();
		$this->psize = defined("SYS_PSIZE") ? SYS_PSIZE : 20;
	}

	function datalink_m()
	{
		$this->__construct();
	}

	//取得所有组
	function all_group($langid="zh")
	{
		$sql = "SELECT id,title FROM ".$this->db->prefix."select_group ORDER BY id ASC";
		return $this->db->get_all($sql);
	}

	//取得值信息
	function all_fields($pageid=0,$condition="")
	{
		$this->set_condition($condition);
		$offset = $pageid>0 ? ($pageid-1)*$this->psize : 0;
		$sql = "SELECT s.*,g.title g_title,p.title p_title FROM ".$this->db->prefix."select s LEFT JOIN ".$this->db->prefix."select p ON(s.pid=p.id) LEFT JOIN ".$this->db->prefix."select_group g ON(s.gid=g.id) ".$this->sql_ext." ORDER BY s.gid ASC,s.taxis ASC";
		$tmplist = $this->db->get_all($sql);//取得全部结果
		//排列结果集
		if(!$tmplist)
		{
			return false;
		}
		$plist = $slist = array();
		foreach($tmplist AS $key=>$value)
		{
			if(!$value["pid"])
			{
				$plist[] = $value;
			}
			else
			{
				$slist[$value["pid"]][] = $value;
			}
		}
		$rslist = array();
		foreach($plist AS $key=>$value)
		{
			$rslist[] = $value;
			if($slist[$value["id"]])
			{
				foreach($slist[$value["id"]] AS $k=>$v)
				{
					$rslist[] = $v;
				}
			}
		}
		$array = array_chunk($rslist,$this->psize);
		if($pageid>0)
		{
			$pageid = $pageid - 1;
		}
		return $array[$pageid] ? $array[$pageid] : $array[0];
	}

	function get_count()
	{
		$sql = "SELECT count(s.id) total FROM ".$this->db->prefix."select s ".$this->sql_ext;
		return $this->db->count($sql);
	}

	//取得其中一条信息
	function get_one($id)
	{
		if(!$id)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."select WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function val_one($val,$gid)
	{
		if(!$val || !$gid)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."select WHERE val='".$val."' AND gid='".$gid."'";
		return $this->db->get_one($sql);
	}

	function get_parent($gid)
	{
		if(!$gid)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."select WHERE (pid='0' OR pid is NULL) AND gid='".$gid."' ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql);
	}

	//取得子类
	function get_son($pid)
	{
		if(!$pid)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."select WHERE pid='".$pid."' ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql);
	}


	function set_condition($condition="")
	{
		if(!$condition || !is_array($condition) || count($condition)<1)
		{
			return true;
		}
		if($condition["groupid"])
		{
			$this->sql_ext .= " AND s.gid='".$condition["groupid"]."' ";
		}
		return true;
	}

	//存储信息
	function save_group($data,$id=0)
	{
		if($id)
		{
			$this->db->update_array($data,"select_group",array("id"=>$id));
			return true;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"select_group");
			return $insert_id;
		}
	}

	function save($data,$id=0)
	{
		if($id)
		{
			$this->db->update_array($data,"select",array("id"=>$id));
			return true;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"select");
			return $insert_id;
		}
	}

	function del_field($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."select WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROm ".$this->db->prefix."select WHERE pid='".$id."'";
		$this->db->query($sql);
		return true;
	}

	function del_group($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."select_groupid WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."select WHERE gid='".$id."'";
		$this->db->query($sql);
		return true;
	}
}
?>