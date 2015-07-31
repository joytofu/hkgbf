<?php
#=====================================================================
#	Filename: app/admin/models_mysql/cate.php
#	Note	: 标识符管理工具
#	Version : 3.0
#	Author  : qinggan
#	Update  : 2009-11-4
#=====================================================================
class cate_m extends Model
{
	var $sql_ext = " WHERE 1=1 ";
	var $catelist;
	var $rootlist;
	var $indexlist;
	var $sublist;
	var $html_ext="";
	var $langid = "zh";
	var $all_list;
	var $flist;//格式化后的数组

	function __construct()
	{
		parent::Model();
	}

	function langid($langid="zh")
	{
		$this->langid = $langid;
	}

	function cate_m()
	{
		$this->__construct();
	}

	function if_status($status=0)
	{
		$this->sql_ext .= " AND status='".$status."' ";
	}

	//取得分类
	function get_catelist($module_id=0,$condition="")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."cate ".$this->sql_ext;
		$sql.= " AND langid='".$this->langid."' ";
		if($module_id)
		{
			$sql .= " AND module_id='".$module_id."' ";
		}
		if($condition)
		{
			$sql.= " AND ".$condition;
		}
		$sql .= " ORDER BY taxis ASC,module_id DESC,parentid ASC,id DESC ";
		$rslist = $this->db->get_all($sql);
		if(!$rslist)
		{
			return false;
		}
		$parentlist = array();
		foreach($rslist AS $key=>$value)
		{
			if(!$value["parentid"])
			{
				$this->rootlist[] = $value;
			}
			else
			{
				$this->sublist[] = $value;
				$parentlist[] = $value["parentid"];
			}
		}
		$parentlist = array_unique($parentlist);
		$this->parentlist = $parentlist;
		$this->catelist = $rslist;
		return true;
	}

	function catelist()
	{
		if(!$this->catelist) return false;
		return $this->catelist;
	}

	//取得所有分类
	function get_all()
	{
		$sql = "SELECT c.*,m.title,m.identifier m_sign FROM ".$this->db->prefix."cate c JOIN ".$this->db->prefix."module m ON(c.module_id=m.id) ";
		//$sql.= " WHERE c.langid='".$this->langid."' AND m.langid='".$this->langid."' ORDER BY m.taxis ASC,m.id DESC,c.taxis ASC,c.id DESC";
		$sql.= " WHERE c.langid='".$this->langid."' ORDER BY m.taxis ASC,m.id DESC,c.taxis ASC,c.id DESC";
		$rslist = $this->db->get_all($sql);
		$this->all_list = $rslist;
		return $rslist;
	}

	function get_list_idstring($cate_string)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."cate WHERE id IN(".$cate_string.")";
		return $this->db->get_all($sql);
	}

	function format_list($parentid=0,$level=0)
	{
		if(!$this->all_list)
		{
			return false;
		}
		foreach($this->all_list AS $key=>$value)
		{
			if($value["parentid"] == $parentid)
			{
				$value["level"] = $level;
				$this->flist[] = $value;
				$this->format_list($value["id"],($level+1));
			}
		}
		return true;
	}

	function flist()
	{
		return $this->flist;
	}

	function html_select_array()
	{
		$rslist = array();
		if(!$this->rootlist || !is_array($this->rootlist) || count($this->rootlist)<1)
		{
			return false;
		}
		foreach($this->rootlist AS $key=>$value)
		{
			$value["space"] = $space ? $space : "";
			$value["isend"] = true;
			if($this->parentlist && in_array($value["id"],$this->parentlist))
			{
				$value["isend"] = false;
			}
			$rslist[] = $value;
			$this->_html_select_array($rslist,$value["id"],1);
		}
		return $rslist;
	}

	function _html_select_array(&$rslist,$parentid=0,$space_id=1)
	{
		if(!$this->catelist)
		{
			return false;
		}
		$space = "";
		for($i=0;$i<$space_id;$i++)
		{
			$space .= "　　";
		}
		foreach($this->catelist AS $key=>$value)
		{
			if($value["parentid"] == $parentid)
			{
				$value["space"] = $space;
				$value["isend"] = true;
				if($this->parentlist && in_array($value["id"],$this->parentlist))
				{
					$value["isend"] = false;
				}
				$rslist[] = $value;
				$this->_html_select_array($rslist,$value["id"],($space_id+1));
			}
		}
		return true;
	}


	//放在List操作中应用到的html表单
	function html_select($select_id="cateid",$selected=0,$lang="",$stop_pid=0)
	{
		$select = "<select name='".$select_id."' id='".$select_id."'>";
		if($lang)
		{
			$select.= "<option value='0'>".$lang."</option>";
		}
		if($this->rootlist && is_array($this->rootlist) && count($this->rootlist)>0)
		{
			foreach($this->rootlist AS $key=>$value)
			{
				$select .= "<option value='".$value["id"]."'";
				if($selected == $value["id"])
				{
					$select.= " selected";
				}
				$select .= ">".$space.$value["cate_name"];
				if(!$value["status"])
				{
					$select .= "【已停用】";
				}
				if($value["linkurl"])
				{
					$select .= "【已使用外链】";
				}
				$select .= "</option>";
				$this->html_ext = "";
				$this->_html_select($value["id"],$selected,1,$stop_pid);
				$select.= $this->html_ext;
			}
		}
		$select .= "</select>";
		return $select;
	}

	//根据当前分类取得子类HTML
	function _html_select($parentid=0,$selected=0,$space_id=1,$stop_pid=0)
	{
		if(!$this->catelist)
		{
			return false;
		}
		$space = "";
		for($i=0;$i<$space_id;$i++)
		{
			$space .= "&nbsp; &nbsp; ";
		}
		foreach($this->catelist AS $key=>$value)
		{
			if($value["parentid"] == $parentid)
			{
				$this->html_ext .= "<option value='".$value["id"]."'";
				if($value["id"] == $selected)
				{
					$this->html_ext .= " selected";
				}
				$this->html_ext .= ">".$space.$value["cate_name"];
				if(!$value["status"])
				{
					$this->html_ext .= "【已停用】";
				}
				if($value["linkurl"])
				{
					$this->html_ext .= "【已使用外链】";
				}
				$this->html_ext .= "</option>";
				if($parentid != $stop_pid || !$stop_pid)
				{
					$this->_html_select($value["id"],$selected,($space_id+1),$stop_pid);
				}
			}
		}
		return true;
	}

	//取得第一个分类信息
	function get_one($id)
	{
		$sql = "SELECT c.*,m.title,m.if_cate,m.if_list,m.if_msg FROM ".$this->db->prefix."cate c JOIN ".$this->db->prefix."module m ON(c.module_id=m.id) WHERE c.id='".$id."'";
		return $this->db->get_one($sql);
	}

	//检测标识串是否存在
	function chksign($val,$id=0)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."cate WHERE identifier='".$val."' ";
		if($id)
		{
			$sql .= " AND id !='".$id."' ";
		}
		$sql.= "AND langid='".$this->langid."'";
		//$sql = "SELECT id FROM ".$this->db->prefix."cate WHERE identifier='".$val."'";
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

	function save($data,$id=0)
	{
		if($id)
		{
			$this->db->update_array($data,"cate",array("id"=>$id));
			return true;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"cate");
			return $insert_id;
		}
	}

	function update_son_fields($fields,$id=0)
	{
		if(!$id) return false;
		$array = array();
		$array["fields"] = $fields;
		$this->db->update_array($array,"cate",array("parentid"=>$id));
		return true;
	}


	function set_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."cate SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//检测是否有子分类
	function chk_son($id)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."cate WHERE parentid='".$id."'";
		return $this->db->get_one($sql);
	}

	//检测是否有内容
	function chk_msg($id)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."list WHERE cate_id='".$id."'";
		return $this->db->get_one($sql);
	}

	function del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."cate WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//取得第一个主题
	function get_cate2sub($idstring,$ordertype="")
	{
		$sql = "SELECT id,identifier,cate_id FROM ".$this->db->prefix."list WHERE status='1' AND cate_id IN(".$idstring.") ORDER BY istop DESC,taxis DESC ";
		if($ordertype)
		{
			$sql .= ", ";
			$sql .= str_replace(":"," ",$ordertype);
		}
		$sql.= ",id DESC LIMIT 1";
		return $this->db->get_one($sql);
	}

	function get_id_from_module($mid)
	{
		$sql = "SELECT id,identifier FROM ".$this->db->prefix."list WHERE status='1' AND module_id='".$mid."' ORDER BY istop DESC,taxis DESC ";
		$sql.= "post_date DESC,id DESC LIMIT 1";
		return $this->db->get_one($sql);
	}

	function min_max($mid,$langid="zh")
	{
		$sql = "SELECT min(c.id) min_id,max(c.id) max_id FROM ".$this->db->prefix."cate c ";
		$sql.= " JOIN ".$this->db->prefix."module m ON(c.module_id=m.id) ";
		$sql.= " WHERE c.status='1' AND c.module_id='".$mid."' AND c.langid='".$langid."' ";
		return $this->db->get_one($sql);
	}

	//取得子分类ID号
	function get_sonid_array(&$array,$id=0)
	{
		if(!$id)
		{
			return $array;
		}
		$sql = "SELECT id FROM ".$this->db->prefix."cate WHERE parentid='".$id."' AND status='1'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist)
		{
			return $array;
		}
		foreach($rslist AS $key=>$value)
		{
			$array[] = $value["id"];
			$this->get_sonid_array($array,$value["id"]);
		}
		return $array;
	}

	function next_cid($cid,$mid=0,$langid="zh")
	{
		$sql = "SELECT id FROM ".$this->db->prefix."cate WHERE status='1' AND id>'".$cid."'";
		if($mid)
		{
			$sql.= " AND module_id='".$mid."' ";
		}
		$sql.= " AND langid='".$langid."' ";
		$sql.= " ORDER BY id ASC LIMIT 1";
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
}
?>