<?php
#=====================================================================
#	Filename: app/www/models/cate.php
#	Note	: 分类管理
#	Version : 3.0
#	Author  : qinggan
#	Update  : 2009-11-4
#=====================================================================
class cate_m extends Model
{
	var $langid = "zh";
	function __construct()
	{
		parent::Model();
	}

	function cate_m()
	{
		$this->__construct();
	}

	function set_langid($langid="zh")
	{
		$this->langid = $langid;
	}

	//取得第一个分类信息
	function get_one($id,$condition="")
	{
		$sql = "SELECT c.*,m.title,m.if_cate,m.if_list,m.if_msg FROM ".$this->db->prefix."cate c JOIN ".$this->db->prefix."module m ON(c.module_id=m.id) WHERE c.id='".$id."'";
		if($condition)
		{
			$sql.= " AND ".$condition;
		}
		return $this->db->get_one($sql);
	}

	function get_cid_from_code($code)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."cate WHERE identifier='".$code."' AND langid='".$this->langid."'";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		return $rs["id"];
	}

	//取得父级分类
	function get_parent_array(&$array,$id=0)
	{
		if(!$id)
		{
			return $array;
		}
		else
		{
			$rs = $this->get_one($id);
			if(!$rs)
			{
				return $array;
			}
			$array[] = $rs;
			if($rs["parentid"])
			{
				$this->get_parent_array($array,$rs["parentid"]);
			}
			else
			{
				return $array;
			}
		}
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

	//取得第一个主题
	function get_cate2sub($idstring,$ordertype="")
	{
		$sql = "SELECT id,identifier FROM ".$this->db->prefix."list WHERE status='1' AND cate_id IN(".$idstring.") ORDER BY istop DESC,taxis DESC ";
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

	//取得所有模块中的标识串及ID，并配上对应关系
	function get_id_code_list()
	{
		$sql = "SELECT id,identifier FROM ".$this->db->prefix."cate WHERE langid='".$this->langid."'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist)
		{
			return false;
		}
		$idlist = $codelist = array();
		foreach($rslist AS $key=>$value)
		{
			$idlist[$value["id"]] = $value["identifier"];
			$codelist[$value["identifier"]] = $value["id"];
		}
		return array("code"=>$codelist,"id"=>$idlist);
	}

	function get_catelist($module_id=0,$condition="")
	{
		$sql = "SELECT c.* FROM ".$this->db->prefix."cate c JOIN ".$this->db->prefix."module m ON(c.module_id=m.id) WHERE c.status='1' ";
		$sql.= " AND c.langid='".$this->langid."' ";
		if($module_id)
		{
			$sql .= " AND c.module_id='".$module_id."' ";
		}
		if($condition)
		{
			$sql.= " AND ".$condition;
		}
		$sql .= " ORDER BY c.taxis ASC,c.module_id DESC,c.parentid ASC,c.id DESC ";
		$rslist = $this->db->get_all($sql);
		if(!$rslist)
		{
			return false;
		}
		foreach($rslist AS $key=>$value)
		{
			if(!$value["parentid"])
			{
				$this->rootlist[] = $value;
			}
			else
			{
				$this->sublist[] = $value;
			}
		}
		$this->catelist = $rslist;
		return true;
	}

	//放在List操作中应用到的html表单
	function html_select($select_id="cateid",$selected=0,$lang="",$ext="")
	{
		$select = "<select name='".$select_id."' id='".$ext.$select_id."'>";
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
				$select .= "</option>";
				$this->_html_select($value["id"],$selected,1);
				$select.= $this->html_ext;
			}
		}
		$select .= "</select>";
		return $select;
	}

	//根据当前分类取得子类HTML
	function _html_select($parentid=0,$selected=0,$space_id=1)
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
				$this->html_ext .= "<option value='".$value["id"]."'";
				if($value["id"] == $selected)
				{
					$this->html_ext .= " selected";
				}
				$this->html_ext .= ">".$space.$value["cate_name"];
				$this->html_ext .= "</option>";
				$this->_html_select($value["id"],$selected,($space_id+1));
			}
		}
		return true;
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
				$rslist[] = $value;
				$this->_html_select_array($rslist,$value["id"],($space_id+1));
			}
		}
		return true;
	}

}
?>