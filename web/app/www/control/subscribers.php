<?php
/***********************************************************
	Filename: app/admin/subscribers.php
	Note	: 邮件订阅-会员管理
	Version : 3.0
	Author  : qinggan
	Update  : 2011-03-11
***********************************************************/
class subscribers_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("subscribers_model",true);
	}

	//兼容PHP4的写法
	function subscribers_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		$this->tpl->display("subscribers.".$this->tpl->ext);//邮件订阅管理
	}

	function setok_f()
	{
		$email = $this->trans_lib->safe("email");
		$goback = $this->trans_lib->safe("goback");
		if(!$goback)
		{
			$goback = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : $this->url("index");
		}
		$status = 0;
		$rs = $this->subscribers_model->chk_email($email);
		if($rs)
		{
			error("对不起，邮件已经存在！",$goback);
		}
		//更新邮件
		$array = array();
		$array["email"] = $email;
		$array["status"] = $status;
		$array["postdate"] = $this->system_time;
		$array["md5pass"] = md5($email."_".$this->system_time);
		$this->subscribers_model->save($array);
		error("邮件订阅创建成功！",$goback);
	}
}
?>