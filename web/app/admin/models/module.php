<?php
/***********************************************************
	Filename: app/admin/models_mysql/module.php
	Note	: 后台登录后默认面板管理器
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-24
***********************************************************/
class module_m extends Model
{
	var $sql_ext;
	var $psize = 20;
	function __construct()
	{
		parent::Model();
		global $_SESSION;
		$this->psize = defined("SYS_PSIZE") ? SYS_PSIZE : 20;
	}

	function module_m()
	{
		$this->__construct();
	}

	function set_sql_ext()
	{
		$this->sql_ext = " FROM ".$this->db->prefix."module m LEFT JOIN ".$this->db->prefix."module_group mg ON(m.group_id=mg.id) WHERE 1=1 ";
	}

	//加载模块数
	//groupid：组ID
	//status：状态
	function top($groupid="",$status="0")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."module_group WHERE 1=1 ";
		if($status)
		{
			$sql .= " AND status='1' ";
		}
		if($groupid)
		{
			$sql .= " AND id='".$groupid."' ";
			$rs = $this->db->get_one($sql);
		}
		else
		{
			$sql .= " ORDER BY taxis ASC,id DESC ";
			$rs = $this->db->get_all($sql);
		}
		//读取指定的语言包，以实现后台多语言功能
		//$rs = $this->merge_lang($rs,"module_group");
		return $rs;
	}

	//获取默认的左侧模块信息
	function left($groupid=0,$status=0)
	{
		if(!$groupid)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."module WHERE (group_id='".$groupid."' OR group_id='0') ";
		//是否有状态限制
		if($status)
		{
			$sql .= " AND status='1' ";
		}
		$sql .= " AND if_hidden='0' ";
		$sql .= " ORDER BY taxis ASC,id DESC ";
		$rslist = $this->db->get_all($sql);
		if(!$rslist)
		{
			return false;
		}
		//$rslist = $this->sys_merge_lang($rslist,"module");
		return $rslist;
	}

	//取得所有的模块
	function all_module($status=0,$condition="")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."module WHERE 1=1 ";
		if($status)
		{
			$sql .= " AND status='1' ";
		}
		if($condition)
		{
			$sql .= " AND ".$condition;
		}
		$sql.= " ORDER BY taxis ASC,id DESC ";
		$rslist = $this->db->get_all($sql);
		return $rslist;
	}

	//模块组
	function all_module_group()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."module_group WHERE js_function='' ORDER BY taxis ASC,id DESC";
		$rslist = $this->db->get_all($sql);
		return $rslist;
	}

	//取得列表
	function get_list($pageid=0,$condition="")
	{
		$this->condition($condition);
		$sql = " SELECT m.*,mg.title g_title ";
		$sql.= $this->sql_ext;
		$sql.= " ORDER BY mg.taxis ASC,m.taxis ASC,m.id DESC ";
		if($pageid<1) $pageid = 1;
		$offset = ($pageid-1) * $this->psize;
		$sql.= " LIMIT ".$offset.",".$this->psize;
		$rslist = $this->db->get_all($sql);
		return $rslist;
	}

	//取得一个条件
	function condition($array)
	{
		$this->set_sql_ext();
		if(!$array || !is_array($array))
		{
			return false;
		}
		if($array["groupid"])
		{
			$this->sql_ext .= " AND m.group_id='".$array["groupid"]."' ";
		}
		foreach($array AS $key=>$value)
		{
			//如果是语言包，先跳过，等后续再来开发区分语言包的
			if($key == "langid")
			{
				continue;
			}
			if($key == "groupid")
			{
				$this->sql_ext .= " AND m.group_id='".$value."'";
				continue;
			}
			if(is_array($value))
			{
				$string_array = array();
				foreach($value AS $k=>$v)
				{
					$string_array[] = "m.".$k."='".$v."'";
				}
				$string = implode(" OR ",$string_array);
				$this->sql_ext .= " AND ( ".$string." )";
				continue;
			}
			$this->sql_ext .= " AND m.".$key."='".$value."'";
		}
		return true;
	}

	//取得总数
	function get_count()
	{
		$sql = "SELECT count(m.id) ".$this->sql_ext;
		$total = $this->db->count($sql);
		return $total;
	}

	//设置权限状态
	function status($id)
	{
		$sql = "SELECT status FROM ".$this->db->prefix."module WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		$status = $rs["status"] ? 0 : 1;
		$sql = "UPDATE ".$this->db->prefix."module SET status='".$status."' WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	//判断内容是否有内容，返回true表示有内容，false表示没有内容
	function if_list($id)
	{
		if(!$id)
		{
			return false;
		}
		$sql = "SELECT id FROM ".$this->db->prefix."list WHERE module_id='".$id."'";
		$rs = $this->db->get_one($sql);
		if($rs)
		{
			return true;
		}
		return false;
	}

	function if_system_module($id)
	{
		if(!$id)
		{
			return false;
		}
		$sql = "SELECT if_system FROM ".$this->db->prefix."module WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if($rs["if_system"])
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function module_id($name)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."module WHERE identifier='".$name."'";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		return $rs["id"];
	}

	//删除模块操作
	function del($id)
	{
		//删除模块
		$sql = "DELETE FROM ".$this->db->prefix."module WHERE id='".$id."'";
		$this->db->query($sql);
		//删除模块字段
		$sql = "DELETE FROM ".$this->db->prefix."module_fields WHERE module_id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	//取得一个模块的内容信息
	function get_one($id)
	{
		if(!$id)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."module WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		//分析模块中的权限
		$rs["popedom"] = $rs["popedom"] ? explode(",",$rs["popedom"]) : array();
		return $rs;
	}

	//判断标识符是否使用
	function chk_identifier($val)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."module WHERE identifier='".$val."'";
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

	//判断标识符是否使用
	function chk_identifier2($val,$module_id)
	{
		$forbidden = array("thumb","picture","ext","list","filename");
		//判断该字段是否和核心表中的字段重名，主表
		$sql = "SHOW FIELDS FROM ".$this->db->prefix."list";
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
		//自定义的禁用添加的字段
		if(in_array($val,$forbidden))
		{
			return true;
		}
		//判断模块中是否有相应的标识串，如有则禁止使用
		$sql = "SELECT id FROM ".$this->db->prefix."module_fields WHERE identifier='".$val."' AND module_id='".$module_id."'";
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
	function save($data,$id=0)
	{
		if($id)
		{
			$this->db->update_array($data,"module",array("id"=>$id));
			return true;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"module");
			return $insert_id;
		}
	}

	//读取字段列表，这里不使用分表
	function fields_index($module_id,$ifstatus=0,$condition="")
	{
		if(!$module_id)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."module_fields WHERE module_id='".$module_id."' ";
		if($ifstatus)
		{
			$sql .= " AND status='1' ";
		}
		if($condition)
		{
			$sql .= " AND ".$condition;
		}
		$sql.= " ORDER BY taxis ASC,id DESC ";
		return $this->db->get_all($sql);
	}

	function fields_index_identifier($mid)
	{
		$sql = "SELECT identifier,title FROM ".$this->db->prefix."module_fields WHERE module_id='".$mid."'";
		return $this->db->get_all($sql,"identifier");
	}

	//读取某个字段内容
	function fields_one($id)
	{
		if(!$id)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."module_fields WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		return $rs;
	}

	//存储信息
	function fields_save($data,$id=0)
	{
		if($id)
		{
			$this->db->update_array($data,"module_fields",array("id"=>$id));
			return true;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"module_fields");
			return $insert_id;
		}
	}

	//设置权限状态
	function fields_status($id)
	{
		$sql = "SELECT status FROM ".$this->db->prefix."module_fields WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		$status = $rs["status"] ? 0 : 1;
		$sql = "UPDATE ".$this->db->prefix."module_fields SET status='".$status."' WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	//删除模块操作
	function fields_del($id)
	{
		//删除模块字段
		$sql = "DELETE FROM ".$this->db->prefix."module_fields WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	//取得input类型
	function input_type($langid="zh",$ifuser=false)
	{
		$sql = "SELECT input,name FROM ".$this->db->prefix."input WHERE 1=1 ";
		if($ifuser)
		{
			$sql .= " AND ifuser='1' ";
		}
		$sql.= " ORDER BY taxis ASC";
		$rs = $this->db->get_all($sql);
		if(!$rs)
		{
			return false;
		}
		$rslist = array();
		foreach($rs AS $key=>$value)
		{
			$rslist[$value["input"]] = $value["name"];
		}
		return $rslist;
	}

	function min_max()
	{
		$sql = "SELECT min(id) min_id,max(id) max_id FROM ".$this->db->prefix."module WHERE ctrl_init='list' AND if_list='1'";
		return $this->db->get_one($sql);
	}

	function next_mid($mid)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."module WHERE ctrl_init='list' AND if_list='1' AND id>'".$mid."' ORDER BY id ASC LIMIT 1";
		$rs = $this->db->get_one($sql);
		if($rs)
		{
			return $rs["id"];
		}
		else
		{
			return false;
		}
	}

	//取得组信息
	function group_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."module_group WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function group_save($data,$id)
	{
		if($id)
		{
			$this->db->update_array($data,"module_group",array("id"=>$id));
			return true;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"module_group");
			return $insert_id;
		}
	}

	function group_del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."module_group WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//取得有效的内容模块列表
	function module_list($ifcate=1,$condition="")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."module WHERE ctrl_init='list' AND if_cate='".$ifcate."'";
		if($condition)
		{
			$sql .= " AND ".$condition;
		}
		$sql .= " ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql);
	}
}
?>