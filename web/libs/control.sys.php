<?php
/***********************************************************
	Filename: control.sys.php
	Note	: 控制层核心文件
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-16
***********************************************************/
//如果没有指定libs层，则禁止访问
if(!defined('LIBS'))
{
	exit('error: Not define libs');
}

class Control
{
	//初始化
	var $tpl;
	var $lib_folder = "system";
	var $url;
	var $lang;//指定语言包数据
	var $lang_id;//语言包ID号
	var $inc_array;
	var $system_time = 0;
	var $sys_config;
	var $db;
	var $plugin;

	function Control()
	{
		$model = $this->r_model();
		$this->model = $model;
		foreach(array_keys(get_object_vars($model)) AS $key)
		{
			$this->$key = $model->$key;
		}
		//echo "<pre>".print_r($model,true)."</pre>";
		//unset($model);
		$this->autoload();//自动加载类
		$this->tpl = $this->view();
		//运行插件
		$this->plugin = $this->r_plugin();
		$this->plugin->load_db($this->model->db);
	}

	//加载model层对数据进行管理
	function r_model()
	{
		require_once(LIBS.'model.sys.php');
		return new Model(true);
	}

	function r_plugin()
	{
		require_once(LIBS.'plugin.sys.php');
		return new Plugin();
	}

	function plugin($plugin_name)
	{
		if(!$plugin_name)
		{
			return false;
		}
		$plugin_name = strtolower($plugin_name);
		$set_name = "plugin_".$plugin_name;
		if($this->inc_array && is_array($this->inc_array) && count($this->inc_array)>0)
		{
			if(in_array($set_name,$this->inc_array))
			{
				return $this->plugin->$set_name;
			}
		}
		$this->plugin->load_plugin($plugin_name);
		$this->inc_array[] = $set_name;
		return $this->plugin->$set_name;
	}

	function model($model_name,$ifglobal=false)
	{
		return $this->load_model($model_name,$ifglobal);
	}

	function load_model($model_name,$ifglobal=false)
	{
		if(!$model_name)
		{
			return false;
		}
		$model_name = strtolower($model_name);
		$set_name = $ifglobal ? $model_name : $model_name."_m";
		if($this->inc_array && is_array($this->inc_array) && count($this->inc_array)>0)
		{
			if(in_array($set_name,$this->inc_array))
			{
				return $this->$set_name;
			}
		}
		$this->model->load_model($model_name,$this->model->db);
		$this->$set_name = $this->model->$set_name;
		$this->inc_array[] = $set_name;
		return $this->$set_name;
	}

	//加载View层管理
	function view()
	{
		//模板引挈参数
		//加载View层涉及到的模板引挈
		require_once(LIBS.'view.sys.php');
		$V = new View();
		return $V->run();
	}

	function libs($lib)
	{
		//判断用户目录下是否有libs
		$lib_file = file_exists(APP."libs/".$lib.".php") ? APP."libs/".$lib.".php" : LIBS.$this->lib_folder."/".$lib.".php";
		if(!file_exists($lib_file))
		{
			exit("error: unable to load the library: ".$lib.".php");
		}
		include_once($lib_file);
		$lib_name = strtolower($lib)."_lib";
		$this->$lib_name = new $lib_name;
		$this->inc_array[] = $lib_name;
		return true;
	}

	//加载模块
	function load_lib($lib)
	{
		$this->libs($lib);
		return true;
	}

	//自动加载系统模块
	function autoload()
	{
		$handle = opendir(LIBS."autoload");
		$array = array();
		while(false !== ($file = readdir($handle)))
		{
			if($file != "." && $file != ".." && $file != ".svn") $array[] = LIBS."autoload/".basename($file);
		}
		closedir($handle);
		if(count($array)<1)
		{
			return false;
		}
		foreach($array AS $key=>$value)
		{
			$file_name = strtolower(basename($value));
			$c_name = str_replace(".php","",$file_name);
			$lib_name = $c_name."_lib";
			include_once($value);
			$this->$lib_name = new $lib_name;
			$this->inc_array[] = $lib_name;
		}
		//加载用户自定义的类
		return true;
	}

	//配置参数
	function set_config($config)
	{
		$this->config->c = $config['control_trigger'];
		$this->config->f = $config['function_trigger'];
		$this->config->d = $config['dir_trigger'];
		return true;
	}

	//格式化URL
	function url($value="",$extend="",$format_type="&amp;")
	{
		$url = defined("HOME_PAGE") ? HOME_PAGE : "index.php";
		$url.= "?";
		//判断是否是value;
		if(is_string($value) && $value)
		{
			$val_array = explode(",",$value);
			foreach($val_array AS $k=>$v)
			{
				$m = explode(":",$v);
				if($m[0] && $m[1])
				{
					$val[$m[0]] = $m[1];
				}
				else
				{
					if($k == 0)
					{
						$val["c"] = $v;
					}
					elseif($k == 1)
					{
						$val["f"] = $v;
					}
					elseif($k == 2)
					{
						$val["d"] = $v;
					}
				}
			}
		}
		else
		{
			if($value)
			{
				$val = $value;
			}
		}
		unset($value);
		//控制层
		if($val['c'])
		{
			$url.= $this->config->c."=".rawurlencode($val["c"]).$format_type;
		}
		//控制函数
		if($val["f"] && $val["f"] != "index")
		{
			$url.= $this->config->f."=".rawurlencode($val["f"]).$format_type;
		}
		if($val["d"])
		{
			$url.= $this->config->d."=".rawurlencode($val["d"]).$format_type;
		}
		//$this->url = $url;
		if($extend)
		{
			$url .= $extend.$format_type;
		}
		return $url;
	}

	//加载语言包
	function lang($var="zh",$msg="")
	{
		$this->lang = array();
		if($msg && is_array($msg) && count($msg)>0)
		{
			$this->lang = $msg;
		}
		$this->langid = $var;
		$this->lang_id = $var;
		return $msg;
	}

	function currency($rs,$currency="RMB")
	{
		if(!$rs) return false;
		$this->currency->$currency = $rs;
	}

	//加载配置信息
	function sys_config($array)
	{
		$this->sys_config = $array;
	}

	//读取时间及次数
	function db_count()
	{
		$db = $this->db_engine;
		$db_count = $this->$db->query_count;
		return $db_count;
	}

	function db_times()
	{
		$db = $this->db_engine;
		$db_times = ($this->$db->query_times + $this->$db->conn_times);
		return $db_times;
	}
}
?>