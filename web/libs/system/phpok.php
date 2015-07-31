<?php
/***********************************************************
	Filename: libs/system/phpok.php
	Note	: 数据调用（供前台调用及后台参数帮助解说）
	Version : 3.0
	Author  : qinggan
	Update  : 2010-01-01
***********************************************************/
class phpok_lib
{
	var $db;
	var $langid = "zh";
	var $app;
	function __construct()
	{
		$this->app = sys_init();
		//执行读取list类
		$this->app->load_model("list_model",true);
	}

	function phpok_lib()
	{
		$this->__construct();
	}

	function langid($langid="zh")
	{
		$this->langid = $langid;
		$this->app->list_model->langid($langid);
	}

	function thumbtype($type="")
	{
		$this->app->list_model->thumbtype($type);
	}

	function set_rs($rs)
	{
		$this->app->list_model->set_rs($rs);
	}

	function exec_sql($sql,$type="get_one")
	{
		return $this->app->list_model->exec_sql($sql,$type);
	}

	//执行根据参数配置的信息
	function list_sql($var,$count=1,$orderby="post_desc")
	{
		//如果存在主题ID，则系统调用详细信息
		if($var["id"])
		{
			return $this->app->list_model->get_one($var["id"],$var);
		}
		//读取内容标识
		if($var["ts"])
		{
			return $this->app->list_model->get_one_ts($var["ts"],$var);
		}

		//如果只读一条信息，则排在最前面的那一条为符合条件
		if($count == 1)
		{
			if($var["cid"])
			{
				return $this->app->list_model->get_one_cid($var["cid"],$orderby);
			}
			if($var["cs"])
			{
				return $this->app->list_model->get_one_cs($var["cs"],$orderby);
			}
			if($var["mid"])
			{
				return $this->app->list_model->get_one_mid($var["mid"],$orderby);
			}
			if($var["ms"])
			{
				return $this->app->list_model->get_one_ms($var["ms"],$orderby);
			}
		}

		//如果数量不是为一的话，多值混合

		//接下来读取的是多条信息
		if($var["cid"])
		{
			return $this->app->list_model->get_list_cid($var["cid"],$count,$orderby);
		}

		//如果有参数cs
		if($var["cs"])
		{
			return $this->app->list_model->get_list_cs($var["cs"],$count,$orderby);
		}

		//如查是
		if($var["mid"])
		{
			return $this->app->list_model->get_list_mid($var["mid"],$count,$orderby);
		}

		if($var["ms"])
		{
			return $this->app->list_model->get_list_ms($var["ms"],$count,$orderby);
		}

		//如果都不符合，返回为否
		return false;
	}


	//读取分类或是子类信息
	function cate_sql($var)
	{
		if($var["id"])
		{
			$cateid = $this->app->list_model->get_cateid_from_id($var["id"]);
			return $this->app->list_model->get_catelist($cateid,"cate");
		}

		if($var["ts"])
		{
			$cateid = $this->app->list_model->get_cateid_from_ts($var["ts"]);
			return $this->app->list_model->get_catelist($cateid,"cate");
		}

		if($var["cid"])
		{
			return $this->app->list_model->get_catelist($var["cid"],"cate");
		}

		if($var["cs"])
		{
			$cateid = $this->app->list_model->get_cateid_from_cs($var["cs"]);
			return $this->app->list_model->get_catelist($cateid,"cate");
		}

		if($var["mid"])
		{
			return $this->app->list_model->get_catelist($var["mid"],"module");
		}

		if($var["ms"])
		{
			$mid = $this->app->list_model->get_mid_from_ms($var["ms"]);
			return $this->app->list_model->get_catelist($mid,"module");
		}

		return false;
	}
}
?>