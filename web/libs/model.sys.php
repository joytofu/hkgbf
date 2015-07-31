<?php
/***********************************************************
	Filename: libs/model.sys.php
	Note	: Model层管理器
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-16
***********************************************************/
class Model
{
	var $db_engine = "db";
	var $db_type = "mysql";
	var $langid = "zh";//指定语言包
	function Model($db=false)
	{
		$db ? $this->run_db("database","db") : $this->load_db();
		//指定langid
		if($_SESSION["sys_lang_id"])
		{
			$this->langid = $_SESSION["sys_lang_id"];
		}
	}

	function load_db()
	{
		$database_config_file = APP_ROOT.'database.config.php';
		$_db_type["prefix"] = "qinggan_";
		if(file_exists($database_config_file))
		{
			include($database_config_file);
		}
		$this->db->prefix = $_db_type["prefix"];
		return true;
	}

	//加载model层
	function load_model($model_name,$db)
	{
		if(is_array($model_name))
		{
			foreach($model_name AS $key=>$value)
			{
				$this->load_model($value,$db);
			}
		}
		else
		{
			$this->_load_model($model_name,$db);
		}
		return true;
	}

	//用于内部使用的加载模块
	function _load_model($model_name,$db)
	{
		if(!$model_name)
		{
			return false;
		}
		$model_name = strtolower($model_name);
		if(file_exists(LIBS."models/".$model_name.".php") || file_exists(LIBS."models/".$this->db_type."/".$model_name.".php"))
		{
			$model_file = file_exists(LIBS."models/".$this->db_type."/".$model_name.".php") ? LIBS."models/".$this->db_type."/".$model_name.".php" : LIBS."models/".$model_name.".php";
			$set_name = $model_name;
		}
		else
		{
			$model_file = file_exists(APP.'models_'.$this->db_type.'/'.$model_name.'.php') ? APP.'models_'.$this->db_type.'/'.$model_name.'.php' : APP.'models/'.$model_name.'.php';
			if(!file_exists($model_file))
			{
				exit('error: unable to load the model: '.basename($model_file));
			}
			$set_name = $model_name.'_m';
		}
		//加载模块文件
		include_once($model_file);
		$this->$set_name = new $set_name();
		$this->$set_name->db = $db;
		unset($set_name,$model_file,$model_name);
		return true;
	}

	//运行连接数据库引挈
	function run_db($data_file='database',$engine='db')
	{
		if(!$engine) $engine = 'db';
		$this->db_engine = $engine;
		if($this->$engine && is_object($this->$engine))
		{
			return true;
		}
		if(!file_exists(APP_ROOT.$data_file.'.config.php'))
		{
			exit('error: unable to load the database: '.$data_file.'.config.php');
		}
		//加载数据库配置文件
		include(APP_ROOT.$data_file.'.config.php');
		$this->db_type = $_db_config['type'];//计算要加载的Model层对应的数据库类型
		//加载数据库引挈
		$db_file = LIBS.'db_engine/'.($_db_config['engine'] ? $_db_config['engine'] : $_db_config['type']).'.php';
		if(!file_exists($db_file))
		{
			exit("error: unable to load the database engine file");
		}
		include_once($db_file);
		$class_name = 'db_'.($_db_config['engine'] ? $_db_config['engine'] : $_db_config['type']);
		$this->$engine = new $class_name($_db_config);
		return true;
	}


	//加载数据库里的语言包
	//rs：初始数组，且要求初始数据中必须包含langid和id两个值
	//tbl：指定是属于哪个表的
	function sys_merge_lang($rs,$tbl="")
	{
		//如果没有传值过来，返回false
		if(!$rs)
		{
			return false;
		}
		//如果传过来的值不是数组，则返回原值
		if(!is_array($rs) || count($rs)<1)
		{
			return $rs;
		}
		//如果没有指定表名，则返回原值
		if(!$tbl)
		{
			return $rs;
		}
		$engine = $this->db_engine;
		$this->db_sql = $this->$engine;
		if(!$rs["langid"] && !$rs["id"])
		{
			$t_rs = array();
			foreach($rs AS $key=>$value)
			{
				if(!$value["langid"] || !$value["id"])
				{
					return $rs;
				}
				if($value["langid"] == $this->langid)
				{
					return $rs;
				}
				$t_rs[$value["id"]] = $value;
			}
			unset($rs);
			$rs = $t_rs;
			ksort($rs);
			//读取数据
			$sql = "SELECT tbl_id,keys,val FROM ".$this->db_sql->prefix."langs WHERE langid='".$this->langid."' AND tbl='".$tbl."' ORDER BY tbl_id ASC";
			$rslist = $this->db_sql->get_all($sql);
			if(!$rslist)
			{
				return $rs;
			}
			$tmp_rslist = array();
			foreach($rslist AS $key=>$value)
			{
				$tmp_rslist[$value["tbl_id"]][$value["keys"]] = $value["val"];
			}
			unset($rslist);
			$rslist = $tmp_rslist;
			if(!$rslist || count($rslist)<1)
			{
				return $rs;
			}
			foreach($rslist AS $key=>$value)
			{
				$rs[$key] = array_merge($rs[$key],$value);
			}
			return $rs;
		}
		else
		{
			if(!$rs["id"] || !$rs["langid"])
			{
				return $rs;
			}
			$sql = "SELECT keys,val FROM ".$this->db_sql->prefix."langs WHERE langid='".$this->langid."' AND tbl='".$tbl."' AND tpl_id='".$rs["id"]."'";
			$rslist = $this->db_sql->get_all($sql);
			if(!$rslist)
			{
				return $rs;
			}
			foreach($rslist AS $key=>$value)
			{
				$rs[$value["keys"]] = $value["val"];
			}
			return $rs;
		}
	}
}
?>