<?php
/***********************************************************
	Filename: libs/models/format_model.php
	Note	: 格式化字段内容
	Version : 3.0
	Author  : qinggan
	Update  : 2010-05-15
***********************************************************/
class format_model extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function format_model()
	{
		$this->__construct();
	}

	function format($val,$input)
	{
		if($input == "opt")
		{
			return $this->format_opt($val);
		}
		elseif($input == "img")
		{
			return $this->format_img($val);
		}
		elseif($input == "video")
		{
			return $this->format_video_download($val);
		}
		elseif($input == "download")
		{
			return $this->format_video_download($val);
		}
		elseif($input == "module")
		{
			return $this->format_subject($val);
		}
		else
		{
			return $val;
		}
	}

	//取得内容
	function format_subject($val)
	{
		$val = sys_id_string($val,",","intval");
		if(!$val) return false;
		$app = sys_init();
		$sql = "SELECT l.*,c.cate_name,u.filename picture FROM ".$this->db->prefix."list l LEFT JOIN ".$this->db->prefix."cate c ON(l.cate_id = c.id) ";
		$sql.= " LEFT JOIN ".$this->db->prefix."upfiles u ON (l.thumb_id=u.id) ";
		$sql.= " WHERE l.id IN(".$val.")";
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."list_ext WHERE id IN(".$val.")";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist) $tmplist = array();
		foreach($tmplist AS $key=>$value)
		{
			$rslist[$value["id"]][$value["field"]] = $value["val"];
		}
		unset($tmplist);
		$sql = "SELECT * FROM ".$this->db->prefix."list_c WHERE id IN(".$val.")";
		$tmp_rs = $this->db->get_all($sql);
		if(!$tmp_rs) $tmp_rs = array();
		foreach($tmp_rs AS $key=>$value)
		{
			$rslist[$value["id"]][$value["field"]] = $value["val"];
		}
		unset($tmp_rs);
		$list = array();
		foreach($rslist AS $key=>$value)
		{
			$list[] = $value;
		}
		unset($rslist);
		return $list;
	}

	function format_video_download($val)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."upfiles WHERE id='".$val."'";
		return $this->db->get_one($sql);
	}

	function format_img($val)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."upfiles WHERE id IN(".$val.") ORDER BY substring_index('".$val."',id,1)";
		$rslist = $this->db->get_all($sql);
		if(!$rslist)
		{
			return false;
		}
		$array = array();
		foreach($rslist AS $key=>$value)
		{
			$array[$value["id"]]["files"] = $value;
		}
		unset($rslist);
		$sql = "SELECT * FROM ".$this->db->prefix."upfiles_gd WHERE pid IN(".$val.") ORDER BY substring_index('".$val."',pid,1)";
		$rslist = $this->db->get_all($sql);
		if($rslist)
		{
			foreach($rslist AS $key=>$value)
			{
				$array[$value["pid"]]["gd"][$value["gdtype"]] = $value["filename"];
			}
		}
		$tmp_array = array();
		foreach($array AS $key=>$value)
		{
			$tmp_array[] = $value;
		}
		unset($array);
		return $tmp_array;
	}

	function format_opt($val)
	{
		$sql = "SELECT pid,title FROM ".$this->db->prefix."select WHERE val='".$val."'";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		$array = array();
		$array["title"] = $rs["title"];
		if($rs["pid"])
		{
			$sql = "SELECT title FROM ".$this->db->prefix."select WHERE id='".$rs["pid"]."'";
			$tmp_rs = $this->db->get_one($sql);
			if($tmp_rs)
			{
				$array["parent"] = $tmp_rs["title"];
			}
		}
		return $array;
	}
}
?>