<?php
/***********************************************************
	Filename: langconfig.php
	Note	: 重新读取语言模块
	Version : 3.0
	Author  : qinggan
	Update  : 2009-12-22
***********************************************************/
class lang_m extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function lang_m()
	{
		$this->__construct();
	}

	function get_one($id)
	{
		if(!$id)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."lang WHERE langid='".$id."'";
		return $this->db->get_one($sql);
	}

	//读取语言包信息
	function get_list()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."lang ORDER BY taxis ASC,langid ASC";
		return $this->db->get_all($sql);
	}

	function get_list_chk($string="")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."lang WHERE status='1' ";
		if($string)
		{
			$c = implode("','",explode(",",$string));
			$sql.= " AND langid IN('".$c."')";
		}
		$sql .= " ORDER BY ifdefault DESC,ifsystem DESC,taxis ASC,langid ASC";
		return $this->db->get_all($sql);
	}

	//存储数据
	function save($data,$id=0)
	{
		if($id)
		{
			$this->db->update_array($data,"lang",array("langid"=>$id));
			return true;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"lang");
			return $insert_id;
		}
	}

	//存储变量和值
	function lang_list($langid="",$ifsystem=0)
	{
		if(!$langid && !$ifsystem)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."lang_msg WHERE ";
		if($langid)
		{
			$sql.= " langid='".$langid."' ";
		}
		else
		{
			$tmpsql = "SELECT langid FROM ".$this->db->prefix."lang WHERE ifsystem='1' ";
			$tmp_rs = $this->db->get_one($tmpsql);
			if(!$tmp_rs)
			{
				return false;
			}
			$sql.= " langid='".$tmp_rs["langid"]."' ";
			unset($tmp_rs);
		}
		$sql .= " ORDER BY var ASC";
		//echo $sql;
		return $this->db->get_all($sql);
	}

	//读取前台语言包信息
	function lang_list_www($langid)
	{
		if(!$langid)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."lang_msg WHERE langid='".$langid."' ";
		$sql.= " AND (ltype='www' OR ltype='all') ORDER BY var ASC ";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist)
		{
			return false;
		}
		$rslist = array();
		foreach($tmplist AS $key=>$value)
		{
			$keys = strtolower($value["var"]);
			$rslist[$keys] = $value["val"];
		}
		return $rslist;
	}

	//存储数据
	function save_m($data,$id=0)
	{
		if($id)
		{
			$this->db->update_array($data,"lang_msg",array("id"=>$id));
			return true;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"lang_msg");
			return $insert_id;
		}
	}

	//删除值数据
	function del_m($var)
	{
		if(!$var)
		{
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."lang_msg WHERE var='".$var."'";
		return $this->db->query($sql);
	}

	//删除语言包
	function del($id)
	{
		if(!$id)
		{
			return false;
		}
		//更新管理员的语言包权限
		$sql = "SELECT FROM ".$this->db->prefix."admin WHERE langid LIKE '%".$id."%'";
		$rslist = $this->db->get_all($sql);
		if($rslist)
		{
			foreach($rslist AS $key=>$value)
			{
				$lang_array = explode(",",$value["langid"]);
				$n_array = array();
				foreach($lang_array AS $k=>$v)
				{
					if($v != $id) $n_array[] = $v;
				}
				$sql = "UPDATE ".$this->db->prefix."admin SET langid='".implode(",",$n_array)."' WHERE id='".$value["id"]."'";
				$this->db->query($sql);
			}
			unset($rslist);
		}
		//删除该语言包的分类信息
		$sql = "DELETE FROM ".$this->db->prefix."cate WHERE langid='".$id."'";
		$this->db->query($sql);
		//删除语言包信息
		$sql = "DELETE FROM ".$this->db->prefix."lang WHERE langid='".$id."'";
		$this->db->query($sql);
		//删除核心包语言信息
		$sql = "DELETE FROM ".$this->db->prefix."langs WHERE langid='".$id."'";
		$this->db->query($sql);
		//删除语言包信息
		$sql = "DELETE FROM ".$this->db->prefix."lang_msg WHERE langid='".$id."'";
		$this->db->query($sql);
		//删除内容数据
		$sql = "SELECT id FROM ".$this->db->prefix."list WHERE langid='".$id."'";
		$rslist = $this->db->get_all($sql);
		if($rslist)
		{
			foreach($rslist AS $key=>$value)
			{
				//删除内容扩展字段
				$sql = "DELETE FROM ".$this->db->prefix."list_c WHERE id='".$value["id"]."'";
				$this->db->query($sql);
				//删除扩展字段，短内容
				$sql = "DELETE FROM ".$this->db->prefix."list_ext WHERE id='".$value["id"]."'";
				$this->db->query($sql);
				//删除回复
				$sql = "DELETE FROM ".$this->db->prefix."reply WHERE tid='".$value["id"]."'";
				$this->db->query($sql);
			}
			unset($rslist);
		}
		$sql = "DELETE FROM ".$this->db->prefix."list WHERE langid='".$id."'";
		$this->db->query($sql);
		//删除导航菜单
		$sql = "DELETE FROM ".$this->db->prefix."menu WHERE langid='".$id."'";
		$this->db->query($sql);
		//删除页脚导航
		$sql = "DELETE FROM ".$this->db->prefix."nav WHERE langid='".$id."'";
		$this->db->query($sql);
		//删除付款方案
		$sql = "SELECT id FROM ".$this->db->prefix."payment WHERE langid='".$id."'";
		$rslist = $this->db->get_all($sql);
		if($rslist)
		{
			foreach($rslist AS $key=>$value)
			{
				$sql = "DELETE FROM ".$this->db->prefix."payment_val WHERE payid='".$value["id"]."'";
				$this->db->query($sql);
			}
			unset($rslist);
		}
		$sql = "DELETE FROM ".$this->db->prefix."payment WHERE langid='".$id."'";
		$this->db->query($sql);
		//删除数据调用中心信息
		$sql = "DELETE FROM ".$this->db->prefix."phpok WHERE langid='".$id."'";
		$this->db->query($sql);
		//删除数据联动
		$sql = "DELETE FROM ".$this->db->prefix."select WHERE langid='".$id."'";
		$this->db->query($sql);
		//删除数据联动组
		$sql = "DELETE FROM ".$this->db->prefix."select_group WHERE langid='".$id."'";
		$this->db->query($sql);
		//删除模板
		$sql = "DELETE FROM ".$this->db->prefix."tpl WHERE langid='".$id."'";
		$this->db->query($sql);
		return true;
	}

	function set_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."lang SET status='".$status."' WHERE langid='".$id."'";
		return $this->db->query($sql);
	}

	function set_default($id)
	{
		$sql = "UPDATE ".$this->db->prefix."lang SET ifdefault='0' WHERE ifdefault='1'";
		$this->db->query($sql);
		$sql = "UPDATE ".$this->db->prefix."lang SET ifdefault='1' WHERE langid='".$id."'";
		$this->db->query($sql);
		return true;
	}

	//检测变量名是否重复
	function chk_msg($var,$langid="zh",$ltype="all",$id=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."lang_msg WHERE var='".$var."' ";
		$sql.= " AND langid='".$langid."' ";
		if($id)
		{
			$sql.= " AND id!='".$id."' ";
		}
		if($ltype == "www")
		{
			$sql.= " AND (ltype='www' OR ltype='all') ";
		}
		elseif($ltype == "admin")
		{
			$sql.= " AND (ltype='admin' OR ltype='all') ";
		}
		$rs = $this->db->get_one($sql);
		return $rs;
	}

	function lang_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."lang_msg WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function lang_one_var($var,$langid="zh")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."lang_msg WHERE var='".$var."' AND langid='".$langid."'";
		return $this->db->get_one($sql);
	}

}
?>