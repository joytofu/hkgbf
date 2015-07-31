<?php
/***********************************************************
	Filename: libs/autoload/cache.php
	Note	: 缓存类
	Version : 3.0
	Author  : qinggan
	Update  : 2010-05-26
***********************************************************/
class cache_lib
{
	var $iscache = false;
	var $cache_type = "txt";
	var $langid = "zh";
	var $cache_server = "localhost";
	var $cache_port = "11211";
	var $app;
	var $cache_status = false;
	var $txt_line = "[§[№[※[◆]※]№]§]";
	var $txt_split = "[◆[№[-[※]-]№]◆]";
	var $cache_time = 3600;
	var $cache_rs;

	function __construct()
	{
	}

	function cache_lib()
	{
		$this->__construct();
	}

	function load_setting()
	{
		$this->app = sys_init();
		if($this->app->db->dbcache)
		{
			$this->iscache = true;
		}
		$this->cache_type = $this->app->db->cache_type;
		$this->cache_server = $this->app->db->cache_server;
		$this->cache_port = $this->app->db->cache_port;
		$this->cache_time = $this->app->db->cache_time;
		if($this->cache_type == "sql")
		{
			$this->app->load_model("cache_model",true);//加载SQL里的缓存
		}
	}

	function cache_status($ifopen=false)
	{
		$this->iscache = $ifopen;
	}

	//读取缓存信息
	function cache_type($cache_type="txt")
	{
		$this->cache_type = $cache_type;
		if($cache_type == "sql")
		{
			$this->app->load_model("cache_model",true);//加载SQL里的缓存
		}
	}

	function langid($langid="zh")
	{
		$this->langid = $langid;
	}

	//针对内存缓存的操作
	function mem_server($server="localhost",$port="11211")
	{
		$this->cache_server = $server;
		$this->cache_port = $port;
	}

	function cache_connect_server()
	{
		if(!$this->iscache)
		{
			return false;
		}
		//如果服务器使用memcache缓存，则
		if($this->cache_type == "mem")
		{
			$this->cache_conn = new Memcache;
			$conn = $this->cache_conn->connect($this->cache_server,$this->cache_port) OR false;
			if(!$conn)
			{
				return false;
			}
			$this->cache_status = true;//通知系统，缓存已经在运行
		}
		elseif($this->cache_type == "sql")
		{
			$this->app->cache_model->langid($this->langid);//设置语言
			$this->cache_rs = $this->app->cache_model->get_all();//取得全部模块
		}
		elseif($this->cache_type == "txt")
		{
			if(!file_exists(ROOT_DATA."cache_".$this->langid.".php"))
			{
				return false;
			}
			$content = file_get_contents(ROOT_DATA."cache_".$this->langid.".php");
			if(!$content)
			{
				return false;
			}
			$content = str_replace('<?php exit;?>','',$content);
			$rs = explode($this->txt_line,$content);
			if(!$rs || !is_array($rs))
			{
				return false;
			}
			foreach($rs AS $key=>$value)
			{
				$tmp = explode($this->txt_split,$value);
				if($tmp[1] > (time()-$this->cache_time))
				{
					$this->cache_rs[$tmp[0]]["content"] = $tmp[2];
					$this->cache_rs[$tmp[0]]["date"] = $tmp[1];
				}
			}
			unset($rs,$content);
		}
	}

	//存储缓存
	function cache_write($key,$value)
	{
		//echo $key."----".$value;
		if(!$this->iscache || !$key || !$value)
		{
			return false;
		}
		$value = serialize($value);
		if($this->cache_type == "mem" && $this->cache_status)
		{
			$this->cache_conn->set($key,$value,0,$this->cache_time);
		}
		elseif($this->cache_type == "sql")
		{
			$this->app->cache_model->langid($this->langid);
			$this->app->cache_model->update($key,$value);
		}
		else
		{
			$this->cache_rs[$key]["content"] = $value;
			$this->cache_rs[$key]["date"] = time();
		}
		return true;
	}

	function cache_write_txt()
	{
		if(!$this->iscache || $this->cache_type != "txt")
		{
			return false;
		}
		$rslist = $this->cache_rs;
		if(!$rslist || !is_array($rslist) || count($rslist)<1)
		{
			return false;
		}
		$tmparray = array();
		foreach($rslist AS $key=>$value)
		{
			$tmp = array();
			$tmp[0] = $key;
			$tmp[1] = $value["date"];
			$content = $value["content"];
			$content = str_replace($this->txt_split,"",$content);
			$content = str_replace($this->txt_line,"",$content);
			$tmp[2] = $content;
			$tmparray[] = implode($this->txt_split,$tmp);
		}
		$this->file_put_msg(ROOT_DATA."cache_".$this->langid.".php",'<?php exit;?>'.implode($this->txt_line,$tmparray));
		return true;
	}

	function file_put_msg($file="",$content="")
	{
		if(!$file || !$content)
		{
			return false;
		}
		if(function_exists("file_put_contents"))
		{
			file_put_contents($file,$content);
		}
		else
		{
			$handle = fopen($file,"wb");
			fwrite($handle,$content);
			fclose($handle);
		}
		return true;
	}

	//读取缓存
	function cache_read($key)
	{
		if(!$this->iscache || !$key)
		{
			return false;
		}
		$time = time() - $this->cache_time;
		if($this->cache_type == "mem" && $this->cache_status)
		{
			$content = $this->cache_conn->get($key);
			if(!$content)
			{
				return false;
			}
			return unserialize($content);
		}
		else
		{
			return $this->cache_rs[$key]["content"] ? unserialize($this->cache_rs[$key]["content"]) : false;
		}
	}

	function cache_clear()
	{
		if(!$this->iscache)
		{
			return true;
		}
		if($this->cache_type == "mem")
		{
			if(!$this->cache_status)
			{
				$this->cache_connect_server();
			}
			$this->cache_conn->flush();
		}
		elseif($this->cache_type == "sql")
		{
			$this->app->cache_model->clear();
		}
		else
		{

			$handle = opendir(ROOT_DATA);
			$array = array();
			while(false !== ($myfile = readdir($handle)))
			{
				if($myfile != "." && $myfile != ".." && $myfile !=".svn") $array[] = ROOT_DATA.$myfile;
			}
			closedir($handle);
			foreach($array AS $key=>$value)
			{
				if(file_exists($value) && is_file($value))
				{
					if(substr(basename($value),0,5) == "cache")
					{
						@unlink($value);
					}
				}
			}
		}
		return true;
	}

	function cache_cart()
	{
		$this->app->load_model("cache_model",true);//加载SQL里的缓存
		return $this->app->cache_model->clear_cart();
	}

}
?>