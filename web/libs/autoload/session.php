<?php
/***********************************************************
	Filename: session.php
	Note	: SESSION管理器
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-19
***********************************************************/
if(!defined("PHPOK_SET"))
{
	exit("Access Denied");
}

CLASS session_lib
{
	var $db;
	var $table;
	var $sessid;
	var $sys_time;

	Function __construct()
	{
		$this->sys_time = time();
	}

	#[兼容PHP4]
	Function session_lib()
	{
		$this->__construct();
	}

	function start($db,$prefix)
	{
		$this->db = $db;
		$this->prefix = $prefix;
		//定义SESSIONID
		if(!defined("SYS_SESSION_ID"))
		{
			define("SYS_SESSION_ID","PHPSESSID");
		}
		$session_id = isset($_POST[SYS_SESSION_ID]) ? $_POST[SYS_SESSION_ID] : (isset($_GET[SYS_SESSION_ID]) ? $_GET[SYS_SESSION_ID] : "");
		if($session_id)
		{
			session_id($session_id);
			$this->sessid = $session_id;
		}
		session_set_save_handler
		(
			array($this,"open"),
			array($this,"close"),
			array($this,"read"),
			array($this,"write"),
			array($this,"destory"),
			array($this,"gc")
		);
		session_start();
	}

	Function open($save_path,$session_name)
	{
		return true;
	}

	Function close()
	{
		return true;
	}

	Function read($sid="")
	{
		$this->sessid = $sid;
		$this->db->close_cache();//关闭缓存
		$rs = $this->db->get_one("SELECT * FROM ".$this->prefix."session WHERE id='".$sid."'");
		$this->db->open_cache();//开启缓存
		if(!$rs)
		{
			$sql = "INSERT INTO ".$this->prefix."session(id,data,lasttime) VALUES('".$sid."','','".$this->sys_time."')";
			$this->db->query($sql);
			return false;
		}
		else
		{
			if(!$rs["data"])
			{
				return false;
			}
			return $rs["data"];
		}
	}

	Function write($sid,$data)
	{
		$this->db->query_count++;
		$this->db->query("UPDATE ".$this->prefix."session SET data='".$data."',lasttime='".$this->sys_time."' WHERE id='".$sid."'");
		return true;
	}

	function destory($sid)
	{
		$this->db->query("DELETE FROM ".$this->prefix."session WHERE id='".$sid."'");
		return true;
	}

	function gc()
	{
		$this->db->query("DELETE FROM ".$this->prefix."session WHERE lasttime+1800<'".$this->sys_time."'");
		return true;
	}

	function sessid()
	{
		return $this->sessid;
	}

	function __destruct()
	{
		return true;
	}
}
?>