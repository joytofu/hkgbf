<?php
#=====================================================================
#	Filename: app/www/models/list.php
#	Note	: 获取内容数据
#	Version : 3.0
#	Author  : qinggan
#	Update  : 2009-12-30
#=====================================================================
class list_m extends Model
{
	var $cate_rs;
	var $module_rs;
	var $idstring;
	var $langid = "zh";
	function __construct()
	{
		parent::Model();
	}

	function list_m()
	{
		$this->__construct();
	}

	function set_cate($rs)
	{
		$this->cate_rs = $rs;
	}

	function langid($langid="zh")
	{
		$this->langid = $langid;
	}

	function set_module($rs)
	{
		$this->module_rs = $rs;
	}

	function set_idstring($idstring)
	{
		$this->idstring = $idstring;
	}

	//读取数量
	function get_count_from_cate()
	{
		if($this->idstring)
		{
			$sql = "SELECT count(DISTINCT lc.id) FROM ".$this->db->prefix."list_cate lc JOIN ".$this->db->prefix."list l ON(lc.id=l.id) WHERE l.status='1' AND l.hidden='0' ";
			$sql.= " AND lc.cateid IN(".$this->idstring.") ";
		}
		else
		{
			$sql = "SELECT count(l.id) FROM ".$this->db->prefix."list l WHERE l.status='1' AND l.hidden='0' ";
			$sql.= " AND l.module_id='".$this->module_rs["id"]."' AND l.langid='".$this->langid."' ";
		}
		return $this->db->count($sql);
	}

	function get_list_from_cate($offset=0,$psize=30)
	{
		$fields = "l.*,c.cate_name";
		//$join = " JOIN ".$this->db->prefix."list_cate lc ON(lc.id=l.id) ";
		//$join .= " LEFT JOIN ".$this->db->prefix."cate c ON(l.cate_id=c.id) ";
		$condition = " WHERE l.status='1' AND l.hidden='0' ";
		if($this->idstring)
		{
			$join = " JOIN ".$this->db->prefix."list_cate lc ON(l.id=lc.id AND lc.cateid IN(".$this->idstring.")) ";
			$join.= " JOIN ".$this->db->prefix."cate c ON(lc.cateid=c.id) ";
			//$condition .= " AND lc.cateid IN(".$this->idstring.") ";
		}
		else
		{
			$join = " LEFT JOIN ".$this->db->prefix."cate c ON(l.cate_id=c.id) ";
			$condition.= " AND l.module_id='".$this->module_rs["id"]."' AND l.langid='".$this->langid."' ";
		}
		//判断是否关联图片
		if($this->module_rs["if_thumb"])
		{
			if($this->cate_rs["inpic"])
			{
				$fields .= ",u.filename picture";
				$join .= " LEFT JOIN ".$this->db->prefix."upfiles_gd u ON(l.thumb_id=u.pid AND u.gdtype='".$this->cate_rs["inpic"]."') ";
			}
			else
			{
				if($this->module_rs["inpic"])
				{
					$fields.= ",u.filename picture";
					$join .= " LEFT JOIN ".$this->db->prefix."upfiles_gd u ON(l.thumb_id=u.pid AND u.gdtype='".$this->module_rs["inpic"]."') ";
				}
			}
		}
		$sql = "SELECT ".$fields." FROM ".$this->db->prefix."list l ".$join." ".$condition;
		//$sql = "SELECT ".$fields." FROM ".$this->db->prefix."list l LEFT JOIN ".$this->db->prefix."list_cate lc ON(lc.id=l.id) ".$join." ".$condition;
		$sql.= " ORDER BY ";
		if($this->module_rs["if_propety"])
		{
			$sql.= " l.istop DESC,";
		}
		$sql.= " l.taxis DESC ";
		//echo "<pre>".print_r($this->cate_rs,true)."</pre>";
		if($this->cate_rs["ordertype"])
		{
			$sql.= ",l.";
			$sql.= str_replace(":"," ",$this->cate_rs["ordertype"]);
		}
		$sql.= ",l.id DESC LIMIT ".$offset.",".$psize;
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist)
		{
			return false;
		}
		$idlist = implode(",",array_keys($rslist));
		$sql = "SELECT * FROM ".$this->db->prefix."list_ext WHERE id IN(".$idlist.")";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist) $tmplist = array();
		foreach($tmplist AS $key=>$value)
		{
			$rslist[$value["id"]][$value["field"]] = $value["val"];
		}
		if($this->module_rs["if_content"])
		{
			$sql = "SELECT * FROM ".$this->db->prefix."list_c WHERE id IN(".$idlist.")";
			$tmp_rs = $this->db->get_all($sql);
			if($tmp_rs && is_array($tmp_rs) && count($tmp_rs)>0)
			{
				foreach($tmp_rs AS $key=>$value)
				{
					$rslist[$value["id"]][$value["field"]] = $value["val"];
				}
			}
		}
		return $rslist;
	}

	//取得主题列表，以供sitemap使用
	function getlist_for_sitemap($max="49999")
	{
		$sql = "SELECT l.* FROM ".$this->db->prefix."list l JOIN ".$this->db->prefix."module m ON(l.module_id=m.id) WHERE l.status='1' AND l.hidden='0' AND l.langid='".$this->langid."' AND m.if_msg='1' LIMIT ".$max;
		return $this->db->get_all($sql);
	}

	//取得指定模块下的主题和ID，仅供扩展字段使用
	function getlist_for_input($mid,$max=100)
	{
		$sql = "SELECT id,title FROM ".$this->db->prefix."list WHERE module_id='".$mid."' AND langid='".$this->langid."'";
		$sql.= " ORDER BY taxis DESC,post_date DESC,id DESC LIMIT ".$max;
		return $this->db->get_all($sql);
	}
}
?>