<?php
#=====================================================================
#	Filename: app/www/models/search.php
#	Note	: 获取内容数据
#	Version : 3.0
#	Author  : qinggan
#	Update  : 2009-12-30
#=====================================================================
class search_m extends Model
{
	var $langid = "zh";
	var $psize = 30;
	var $pageid = 1;
	var $thumb = false;
	var $max_count = 30000;
	var $idlist = "";
	var $total = 0;
	function __construct()
	{
		parent::Model();
	}

	function search_m()
	{
		$this->__construct();
	}

	function langid($langid="zh")
	{
		$this->langid = $langid;
	}

	//设置每页数量
	function set_psize($psize=30)
	{
		$this->psize = $psize;
	}

	//设置当前页码
	function set_pageid($pageid=1)
	{
		$this->pageid = $pageid;
	}

	//设置关联缩略图
	function set_thumb($thumb=false)
	{
		$this->thumb = $thumb;
	}

	//最多搜索结果数量
	function set_max($max_count=30000)
	{
		$this->max_count = $max_count;
	}

	function get_all_id($condition="",$ext_condition="")
	{
		$sql = "SELECT id FROM ".$this->db->prefix."list ";
		if($condition)
		{
			$sql.= "WHERE ".$condition;
		}
		$sql .= " ORDER BY taxis DESC,post_date DESC,id DESC ";
		$sql .= " LIMIT ".$this->max_count;
		$rslist = $this->db->get_all($sql);
		if(!$rslist)
		{
			return false;
		}
		$rs = array();
		foreach($rslist AS $key=>$value)
		{
			$rs[] = $value["id"];
		}
		//合并ID
		$myid = implode(",",$rs);
		if($ext_condition && is_array($ext_condition))
		{
			$myid = $this->sc_id_list($myid,$ext_condition);
		}
		if(!$myid)
		{
			return false;
		}
		$this->idlist = $myid;
		//判断总数
		$idlist = explode(",",$myid);
		$this->total = count($idlist);
	}

	//筛选ID信息
	function sc_id_list($myid,$ext_condition)
	{
		if(!$myid || !$ext_condition) return false;
		foreach($ext_condition AS $key=>$value)
		{
			if($key && $value)
			{
				$this->_id_list($myid,$key,$value);
			}
		}
		return $myid;
	}

	function _id_list(&$myid,$field,$val)
	{
		if(!$myid || !$field && !$val)
		{
			return false;
		}
		$sql = "SELECT id FROM ".$this->db->prefix."list_ext WHERE id IN(".$myid.") AND `field`='".$field."' AND `val` LIKE '%".$val."%'";
		$tmplist = $this->db->get_all($sql);
		//如果有一次结果集为空，则返空结集
		if(!$tmplist)
		{
			$myid = "";
			return false;
		}
		$tmp = array();
		foreach($tmplist AS $k=>$v)
		{
			$tmp[] = $v["id"];
		}
		$myid_list = explode(",",$myid);
		$tmp = array_unique($tmp);
		$myid_list = array_intersect($myid_list,$tmp);
		if($myid_list)
		{
			$myid = implode(",",$myid_list);
		}
		else
		{
			$myid = "";
		}
	}



	//搜索结果，最多不超过
	function get_list()
	{
		if(!$this->idlist)
		{
			return false;
		}
		//执行分页信息
		$myid = explode(",",$this->idlist);
		$myrs = array_chunk($myid,$this->psize);
		$pageid = $this->pageid;
		if($pageid>0)
		{
			$pageid = $pageid - 1;
		}
		$idrs = $myrs[$pageid] ? $myrs[$pageid] : $myrs[0];
		$idstring = implode(",",$idrs);
		if($this->thumb)
		{
			$sql = "SELECT l.*,u.filename picture,c.cate_name,m.title module_title FROM ".$this->db->prefix."list l ";
			$sql.= " LEFT JOIN ".$this->db->prefix."upfiles_gd u ON(l.thumb_id=u.pid AND u.gdtype='".$this->thumb."') ";
		}
		else
		{
			$sql = "SELECT l.*,c.cate_name,m.title module_title FROM ".$this->db->prefix."list l ";
		}
		$sql.= " LEFT JOIN ".$this->db->prefix."cate c ON(l.cate_id=c.id) ";
		$sql.= " LEFT JOIN ".$this->db->prefix."module m ON(l.module_id=m.id) ";
		$sql.= " WHERE l.id IN(".$idstring.") ORDER BY l.taxis DESC,l.post_date DESC,l.id DESC";
		return $this->db->get_all($sql);
	}

	function get_count()
	{
		if(!$this->total)
		{
			return false;
		}
		else
		{
			return $this->total;
		}
	}
}
?>