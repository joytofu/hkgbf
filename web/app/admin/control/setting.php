<?php
/***********************************************************
	Filename: app/admin/control/setting.php
	Note	: 网站信息设置
	Version : 3.0
	Author  : qinggan
	Update  : 2010-06-22
***********************************************************/
class setting_c extends Control
{
	var $module_sign = "setting";
	function __construct()
	{
		parent::Control();
	}

	function setting_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		sys_popedom($this->module_sign.":setting","tpl");
		$file = ROOT_DATA."system_".$_SESSION["sys_lang_id"].".php";
		$_sys = array();
		if(file_exists($file))
		{
			include($file);
		}
		//改写SMTP密码格式
		if($_sys["smtp_pass"])
		{
			$smtp_pass = substr($_sys["smtp_pass"],0,1);
			//将中间的值长度都替换为0
			$smtp_tmp_length = strlen($_sys["smtp_pass"]) - 2;
			for($i=0;$i<$smtp_tmp_length;$i++)
			{
				$smtp_pass .= "*";
			}
			$smtp_pass.= substr($_sys["smtp_pass"],-1);
			$_sys["smtp_pass"] = $smtp_pass;
		}
		//改写FTP密码格式
		if($_sys["ftp_pass"])
		{
			$smtp_pass = substr($_sys["ftp_pass"],0,1);
			//将中间的值长度都替换为0
			$smtp_tmp_length = strlen($_sys["ftp_pass"]) - 2;
			for($i=0;$i<$smtp_tmp_length;$i++)
			{
				$smtp_pass .= "*";
			}
			$smtp_pass.= substr($_sys["ftp_pass"],-1);
			$_sys["ftp_pass"] = $smtp_pass;
		}
		$this->tpl->assign("rs",$_sys);
		$if_modify = sys_popedom($this->module_sign.":modify");
		$this->tpl->assign("if_modify",$if_modify);
		$this->load_model("gdtype");
		$gdlist = $this->gdtype_m->get_all();
		$this->tpl->assign("gdlist",$gdlist);
		$this->tpl->display("setting.html");
	}

	function setok_f()
	{
		sys_popedom($this->module_sign.":setting","tpl");
		$rs = array();
		if($_POST && is_array($_POST) && count($_POST)>0)
		{
			foreach($_POST AS $key=>$value)
			{
				$rs[$key] = $this->trans_lib->safe($key);
			}
		}
		$file = ROOT_DATA."system_".$_SESSION["sys_lang_id"].".php";
		$_sys = array();
		if(file_exists($file))
		{
			include($file);
		}
		//判断邮件密码及FTP密码
		if($rs["smtp_pass"] && strlen($rs["smtp_pass"]) == strlen($_sys["smtp_pass"]) && substr($rs["smtp_pass"],0,1) == substr($_sys["smtp_pass"],0,1) && substr($rs["smtp_pass"],-1) == substr($_sys["smtp_pass"],-1))
		{
			$rs["smtp_pass"] = $_sys["smtp_pass"];
		}
		if($rs["ftp_pass"] && strlen($rs["ftp_pass"]) == strlen($_sys["ftp_pass"]) && substr($rs["ftp_pass"],0,1) == substr($_sys["ftp_pass"],0,1) && substr($rs["ftp_pass"],-1) == substr($_sys["ftp_pass"],-1))
		{
			$rs["ftp_pass"] = $_sys["ftp_pass"];
		}
		//判断FTP复选框
		$rs["ftp_pasv"] = $this->trans_lib->checkbox("ftp_pasv");
		$this->file_lib->vi($rs,ROOT_DATA."system_".$_SESSION["sys_lang_id"].".php","_sys");
		error("数据更新成功！",site_url("setting"));
	}
}
?>