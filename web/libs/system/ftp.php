<?php
/***********************************************************
	Filename: ftp.php
	Note	: FTP操作类，通过FTP实现多个网站的推送
	Version : 3.x
	Author  : qinggan
	Update  : 2011-09-22 11:26
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class ftp_lib
{
	var $is_ftp = false;
	var $ftp_server = "127.0.0.1";
	var $ftp_user;
	var $ftp_pass;
	var $ftp_is_pasv = false;
	var $ftp_port = "21";
	var $ftp_folder = "/";
	var $conn;
	var $is_cover = true;//是否覆盖上传，默认是
	function __construct()
	{
		//
	}

	function ftp_lib()
	{
		$this->__construct();
	}

	function config($config)
	{
		if(!$config || !is_array($config))
		{
			return false;
		}
		$this->is_ftp = $config["is_ftp"];
		$this->ftp_server = $config["ftp_server"];
		$this->ftp_port = ($config["ftp_port"] && $config["ftp_port"] != "21") ? $config["ftp_port"] : 21;
		$this->ftp_user = $config["ftp_account"];
		$this->ftp_pass = $config["ftp_pass"];
		$this->ftp_is_pasv = $config["ftp_pasv"] ? true : false;
		$this->ftp_folder = $config["ftp_folder"];
	}

	function is_ftp()
	{
		return $this->is_ftp;
	}

	function error($note)
	{
		$r = array();
		$r["status"] = "error";
		$r["content"] = $note;
		return $r;
	}

	//初始化conn
	function conn($conn)
	{
		$this->conn = $conn;
		return true;
	}

	function connect()
	{
		if(!$this->is_ftp) return false;
		//$this->conn = ftp_connect($this->ftp_server,$this->ftp_port) or die("Couldn't connect to ".$this->ftp_server);
		if(!$this->conn)
		{
			$this->conn = ftp_connect($this->ftp_server,$this->ftp_port);
			if(!$this->conn)
			{
				return $this->error("Couldn't connect to ".$this->ftp_server);
			}
			//登录FTP
			$t = ftp_login($this->conn,$this->ftp_user,$this->ftp_pass);
			if(!$t)
			{
				return $this->error("Couldn't connect as ".$this->ftp_user);
			}
		}
		if($this->ftp_folder && $this->ftp_folder != "/")
		{
			$this->cd($dir);
		}
		return $this->conn;
	}

	function cd($dir="/")
	{
		if($dir != $this->ftp_dir) $dir = $this->ftp_dir.$dir."/";
		return ftp_chdir($this->conn,$dir);
	}

	function get_dir()
	{
		if($this->conn)
		{
			return ftp_pwd($this->conn);
		}
		else
		{
			return false;
		}
	}

	function __destruct()
	{
		if($this->conn)
		{
			ftp_close($this->conn);
		}
		return true;
	}

	function size($file)
	{
		return ftp_size($this->conn, $file);
	}

	function make($name="")
	{
		if(!$name) return false;
		$array = explode("/",$name);
		$count = count($array);
		$tmp_dir = "";
		for($i=0;$i<$count;$i++)
		{
			$tmp_val = trim($array[$i]);
			if($tmp_val)
			{
				$tmplist = ftp_nlist($this->conn,$tmp_dir);
				if(!$tmplist) $tmplist = array();
				if(!in_array($tmp_val,$tmplist))
				{
					$this->cd($tmp_dir);
					ftp_mkdir($this->conn,$tmp_val);
					$this->cd($this->ftp_dir);
				}
				ftp_chmod($this->conn,0755,$this->ftp_dir.$tmp_dir.$tmp_val);
				$tmp_dir .= $tmp_val."/";
			}
		}
	}

	function is_exist($file)
	{
		if(filemtime($file) <= ftp_mdtm($this->conn,$file) && filesize($file) == ftp_size($this->conn,$file))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function mv($file,$newfile)
	{
		if(!$file || !$newfile) return false;
		if(!file_exists($file)) return false;
		//检测新文件名
		$dir_string = substr($newfile,0,-(strlen(basename($newfile))));
		if($dir_string) $this->make($dir_string);
		return ftp_rename($this->conn,$file,$newfile);
	}

	function upload($file,$name="")
	{
		if(!$file) return false;
		if(!$name) $name = basename($file);
		$this->cd($this->ftp_dir);
		if($name != $file)
		{
			$dir_string = substr($file,0,-(strlen($name)));
			$this->make($dir_string);
		}
		//如果文件存在，且大小及修改时间一样时，则跳过上传
		if($this->is_exist($file))
		{
			return true;
		}
		if(ftp_size($this->conn,$file)>0)
		{
			ftp_delete($this->conn,$file);
		}
		//判断文件是否存在
		//尝试异步上传
		$ret = ftp_nb_put($this->conn,$file,$file,FTP_BINARY);
		while($ret == FTP_MOREDATA)
		{
			$ret = ftp_nb_continue ($this->conn);
		}
		if ($ret != FTP_FINISHED)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	function down($file,$name="")
	{
		if(!$file) return false;
		if(!$name) $name = basename($file);
		$rs = ftp_nb_get($this->conn, $file, $file, FTP_BINARY);
		while ($res == FTP_MOREDATA)
		{
			$res = ftp_nb_continue($this->conn);
		}
		if ($res == FTP_FINISHED)
		{
			return true;
		}
		elseif ($res == FTP_FAILED)
		{
			return false;
		}
	}
}
?>