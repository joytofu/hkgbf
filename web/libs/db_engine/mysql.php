<?php
#==================================================================================================
#	Filename: class/db/mysql.php
#	Note	: 连接数据库类
#	Version : 3.0
#	Author  : qinggan
#	Update  : 2008-05-27
#==================================================================================================

#[类库sql]
class db_mysql
{
	var $query_count = 0;
	var $host;
	var $user;
	var $pass;
	var $data;
	var $conn;
	var $result;
	var $prefix = "qinggan_";
	//返回结果集类型，默认是数字+字符
	var $rs_type = MYSQL_ASSOC;
	var $query_times = 0;#[查询时间]
	var $conn_times = 0;#[连接数据库时间]
	var $unbuffered = false;#[是否不使用结果缓存集查询功能，默认为不使用]
	var $dbcache = false;
	var $cache_type = "txt";//缓存类型，默认只支持 txt和mem两种方式
	var $cache_folder = "cache/sql_c/";
	var $cache_server = "localhost";
	var $cache_port = "11211";
	var $cache_time = 3600;
	var $cache_conn;//连接到缓存类中，主要是用于memcache里
	var $cache_status = false;

	#[构造函数]
	function __construct($config=array())
	{
		$this->host = $config['host'] ? $config['host'] : 'localhost';
		$this->port = $config['port'] ? $config['port'] : '3306';
		$this->user = $config['user'] ? $config['user'] : 'root';
		$this->pass = $config['pass'] ? $config['pass'] : '';
		$this->data = $config['data'] ? $config['data'] : '';
		$this->prefix = $config['prefix'] ? $config['prefix'] : 'qinggan_';
		if($this->data)
		{
			$ifconnect = $this->connect();
			if(!$ifconnect)
			{
				return false;
			}
		}
		$this->dbcache = (defined("DB_CACHE") && DB_CACHE == true && $config["cache_time"]>0) ? true : false;
		$this->cache_type = $config["cache_type"];
		$this->cache_folder = defined("ROOT") ? ROOT.$config["cache_folder"] : $config["cache_folder"];//缓存目录
		if(substr($this->cache_folder,-1) != "/")
		{
			$this->cache_folder .= "/";
		}
		$this->cache_server = $config["cache_server"];
		$this->cache_port = $config["cache_port"];
		$this->cache_time = $config["cache_time"];
	}

	#[兼容PHP4]
	function db_mysql($config=array())
	{
		$this->__construct($config);
	}

	#[连接数据库]
	function connect($database="")
	{
		$start_time = $this->time_used();
		$server = ($this->port && $this->port != "3306") ? $this->host.":".$this->port : $this->host;
		$this->conn = mysql_connect($server,$this->user,$this->pass);
		if(!$this->conn)
		{
			return false;
		}
		$mysql_version = $this->get_version();
		if($mysql_version>"4.1")
		{
			mysql_query("SET NAMES 'utf8'",$this->conn);
		}
		if($mysql_version>"5.0.1")
		{
			mysql_query("SET sql_mode=''",$this->conn);
		}
		$end_time = $this->time_used();
		$this->conn_times += round($end_time - $start_time,5);#[连接数据库的时间]
		$ifok = $this->select_db($database);
		return $ifok ? true : false;
	}

	//存储缓存
	function cache_write($key,$value)
	{
		if(!$this->dbcache || !$key)
		{
			return false;
		}
		$app = sys_init();
		return $app->cache_lib->cache_write($key,$value);
	}

	//读取缓存
	function cache_read($key)
	{
		if(!$this->dbcache || !$key)
		{
			return false;
		}
		$app = sys_init();
		return $app->cache_lib->cache_read($key);
	}

	function cache_clear()
	{
		$app = sys_init();
		return $app->cache_lib->cache_clear();
	}

	function select_db($data="")
	{
		$database = $data ? $data : $this->data;
		if(!$database)
		{
			return false;
		}
		$this->data = $database;
		$start_time = $this->time_used();
		$ifok = mysql_select_db($this->data,$this->conn);
		if(!$ifok)
		{
			return false;
		}
		$end_time = $this->time_used();
		$this->conn_times += round($end_time - $start_time,5);#[连接数据库的时间]
		return true;
	}

	#[关闭数据库连接，当您使用持续连接时该功能失效]
	function close()
	{
		return mysql_close($this->conn);
	}

	function __destruct()
	{
		@session_write_close();#[关闭session写入]
		return $this->close();
	}

	function set($name,$value)
	{
		if($name == "rs_type")
		{
			$value = strtolower($value) == "num" ? MYSQL_NUM : MYSQL_ASSOC;
		}
		$this->$name = $value;
	}

	function query($sql)
	{
		if(!mysql_ping($this->conn))
		{
			$this->close();
			$this->connect();
		}
		$start_time = $this->time_used();
		$func = $this->unbuffered && function_exists("mysql_multi_query") ? "mysql_multi_query" : "mysql_query";
		$this->result = $func($sql,$this->conn);
		$this->query_count++;
		$end_time = $this->time_used();
		$this->query_times += round($end_time - $start_time,5);#[查询时间]
		if(!$this->result)
		{
			return false;
		}
		return $this->result;
	}

	function get_all($sql="",$primary="")
	{
		$cache_key = "";
		if($sql && $this->dbcache)
		{
			$cache_key = md5($sql);
			$rs = $this->cache_read($cache_key);
			if($rs)
			{
				return $rs;
			}
		}
		$result = $sql ? $this->query($sql) : $this->result;
		if(!$result)
		{
			return false;
		}
		$start_time = $this->time_used();
		while($rows = mysql_fetch_array($result,$this->rs_type))
		{
			if($primary && $rows[$primary])
			{
				$rs[$rows[$primary]] = $rows;
			}
			else
			{
				$rs[] = $rows;
			}
		}
		$end_time = $this->time_used();
		$this->query_times += round($end_time - $start_time,5);#[查询时间]
		$this->cache_write($cache_key,$rs);
		return ($rs ? $rs : false);
	}

	function get_one($sql="")
	{
		$cache_key = "";
		if($sql && $this->dbcache)
		{
			$cache_key = md5($sql);
			$rs = $this->cache_read($cache_key);
			if($rs)
			{
				return $rs;
			}
		}
		$start_time = $this->time_used();
		$result = $sql ? $this->query($sql) : $this->result;
		if(!$result)
		{
			return false;
		}
		$rows = mysql_fetch_array($result,$this->rs_type);
		$end_time = $this->time_used();
		$this->query_times += round($end_time - $start_time,5);#[查询时间]
		$this->cache_write($cache_key,$rows);
		return $rows;
	}

	function insert_id($sql="")
	{
		if($sql)
		{
			$rs = $this->get_one($sql);
			return $rs;
		}
		else
		{
			return mysql_insert_id($this->conn);
		}
	}

	function insert($sql)
	{
		$this->result = $this->query($sql);
		$id = $this->insert_id();
		return $id;
	}

	function all_array($table,$condition="",$orderby="")
	{
		if(!$table)
		{
			return false;
		}
		$table = $this->prefix.$table;
		$sql = "SELECT * FROM ".$table;
		if($condition && is_array($condition) && count($condition)>0)
		{
			$sql_fields = array();
			foreach($condition AS $key=>$value)
			{
				$sql_fields[] = "`".$key."`='".$value."' ";
			}
			$sql .= " WHERE ".implode(" AND ",$sql_fields);
		}
		if($orderby)
		{
			$sql .= " ORDER BY ".$orderby;
		}
		$rslist = $this->get_all($sql);
		return $rslist;
	}

	function one_array($table,$condition="")
	{
		if(!$table)
		{
			return false;
		}
		$table = $this->prefix.$table;
		$sql = "SELECT * FROM ".$table;
		if($condition && is_array($condition) && count($condition)>0)
		{
			$sql_fields = array();
			foreach($condition AS $key=>$value)
			{
				$sql_fields[] = "`".$key."`='".$value."' ";
			}
			$sql .= " WHERE ".implode(" AND ",$sql_fields);
		}
		$rslist = $this->get_one($sql);
		return $rslist;
	}

	//将数组写入数据中
	function insert_array($data,$table,$insert_type="insert")
	{
		if(!$table || !is_array($data) || !$data)
		{
			return false;
		}
		$table = $this->prefix.$table;//自动增加表前缀
		if($insert_type == "insert")
		{
			$sql = "INSERT INTO ".$table;
		}
		else
		{
			$sql = "REPLACE INTO ".$table;
		}
		$sql_fields = array();
		$sql_val = array();
		foreach($data AS $key=>$value)
		{
			$sql_fields[] = "`".$key."`";
			$sql_val[] = "'".$value."'";
		}
		$sql.= "(".(implode(",",$sql_fields)).") VALUES(".(implode(",",$sql_val)).")";

		//file_put_contents("data/tmp/".md5($sql).".sql",$sql);
		return $this->insert($sql);
	}

	//更新数据
	function update_array($data,$table,$condition)
	{
		if(!$data || !$table || !$condition || !is_array($data) || !is_array($condition))
		{
			return false;
		}
		$table = $this->prefix.$table;//自动增加表前缀
		$sql = "UPDATE ".$table." SET ";
		$sql_fields = array();
		foreach($data AS $key=>$value)
		{
			$sql_fields[] = "`".$key."`='".$value."'";
		}
		$sql.= implode(",",$sql_fields);
		$sql_fields = array();
		foreach($condition AS $key=>$value)
		{
			$sql_fields[] = "`".$key."`='".$value."' ";
		}
		$sql .= " WHERE ".implode(" AND ",$sql_fields);
		//echo $sql;
		//exit;
		//file_put_contents("data/tmp/".md5($sql).".sql",$sql);
		return $this->query($sql);
	}

	function count($sql="")
	{
		if($sql)
		{
			$this->rs_type = MYSQL_NUM;
			$this->query($sql);
			$rs = $this->get_one();
			$this->rs_type = MYSQL_ASSOC;
			return $rs[0];
		}
		else
		{
			return mysql_num_rows($this->result);
		}
	}

	function num_fields($sql="")
	{
		if($sql)
		{
			$this->query($sql);
		}
		return mysql_num_fields($this->result);
	}

	function list_fields($table)
	{
		$rs = $this->get_all("SHOW COLUMNS FROM ".$table);
		if(!$rs)
		{
			return false;
		}
		foreach($rs AS $key=>$value)
		{
			$rslist[] = $value["Field"];
		}
		return $rslist;
	}

	#[显示表名]
	function list_tables()
	{
		$rs = $this->get_all("SHOW TABLES");
		return $rs;
	}

	function table_name($table_list,$i)
	{
		return $table_list[$i];
	}

	function escape_string($char)
	{
		if(!$char)
		{
			return false;
		}
		return mysql_escape_string($char);
	}

	function get_version()
	{
		return mysql_get_server_info($this->conn);
	}

	function time_used()
	{
		$time = explode(" ",microtime());
		$used_time = $time[0] + $time[1];
		return $used_time;
	}

	//Mysql的查询时间
	function conn_times()
	{
		return $this->conn_times + $this->query_times;
	}

	//MySQL查询资料
	function conn_count()
	{
		return $this->query_count;
	}

	function close_cache()
	{
		if($this->dbcache)
		{
			$this->dbcache = false;
		}
	}

	function open_cache()
	{
		if(defined("DB_CACHE") && DB_CACHE == true && $this->cache_time > 0)
		{
			$this->dbcache = true;
		}
	}

	function set_cache_time($time=0)
	{
		$this->cache_time = $time;
		if($time<1)
		{
			$this->dbcache = false;
		}
	}

}
?>