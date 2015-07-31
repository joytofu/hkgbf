<?php
/***********************************************************
	Filename: cache_model.php
	Note	: 缓存模块中涉及到数据库的操作
	Version : 3.0
	Author  : qinggan
	Update  : 2010-05-15
***********************************************************/
class cache_model extends Model
{
	var $langid = "zh";
	var $cache_time = 3600;
	function __construct()
	{
		parent::Model();
	}

	function cache_model()
	{
		$this->__construct();
	}

	function langid($langid="zh")
	{
		$this->langid = $langid;
	}

	function get_all()
	{
		$sql = "DELETE FROM ".$this->db->prefix."cache WHERE postdate<'".(time()-$this->cache_time)."' ";
		$this->db->query($sql);
		$this->db->close_cache();
		$sql = "SELECT * FROM ".$this->db->prefix."cache WHERE langid='".$this->langid."'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist)
		{
			return false;
		}
		$rs = array();
		foreach($rslist AS $key=>$value)
		{
			$rs[$value["id"]]["content"] = $value["content"];
			$rs[$value["id"]]["date"] = $value["postdate"];
		}
		unset($rslist);
		return $rs;
	}

	function update($key,$value)
	{
		$sql = "REPLACE INTO ".$this->db->prefix."cache(id,langid,content,postdate) VALUES('".$key."','".$this->langid."','".$value."','".time()."')";
		return $this->db->query($sql);
	}

	//清空
	function clear()
	{
		$sql = "TRUNCATE TABLE ".$this->db->prefix."cache ";
		return $this->db->query($sql);
	}

	//清除超过当前时间1天的购物车信息
	function clear_cart()
	{
		$time = time() - 3600*24;
		$sql = "DELETE FROM ".$this->db->prefix."cart WHERE postdate<'".$time."'";
		return $this->db->query($sql);
	}
}
?>