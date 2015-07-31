<?php
/***********************************************************
	Filename: app/www/control/ajax.php
	Note	: 常用Ajax模块，直接调用Ajax目录下的信息
	Version : 3.0
	Author  : qinggan
	Update  : 2011-10-17 09:49
***********************************************************/
class ajax_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("cate");
		$this->load_model("list");
		$this->load_model("msg");
		$this->load_model("module");
	}

	function ajax_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		//要调用的Ajax文件名，不含.php文件，仅限ajax目录下
		$filename = $this->trans_lib->safe("filename");
		if($filename && file_exists(ROOT."ajax/".$filename.".php"))
		{
			include_once(ROOT."ajax/".$filename.".php");
		}
		else
		{
			if(!$filename)
			{
				exit("error:filename is empty!");
			}
			else
			{
				exit("error:not filename[".ROOT."ajax/".$filename.".php]");
			}
		}
	}

}