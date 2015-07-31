<?php
/***********************************************************
	Filename: format.php
	Note	: 格式化字段内容
	Version : 3.0
	Author  : qinggan
	Update  : 2010-05-15
***********************************************************/
class format_m extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function format_m()
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
		else
		{
			return $val;
		}
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