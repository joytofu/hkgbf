<?php
/***********************************************************
	Filename: libs/system/email.php
	Note	: 发送邮件类
	Version : 3.0
	Author  : qinggan
	Update  : 2010-05-10
***********************************************************/
//引入phpmail控件发送邮件
require_once(LIBS."phpmailer/class.phpmailer.php");
class email_lib
{
	var $app;
	var $timeout = 15;
	var $smtp_server = "";
	var $smtp_port = 25;
	var $smtp_ssl = 0;
	var $smtp_user = "";
	var $smtp_pass = "";
	var $smtp_reply = "";
	var $smtp_admin = "";
	var $smtp_fromname = "Webmaster";
	var $smtp;

	function __construct()
	{
		$this->app = sys_init();
	}

	function email_lib()
	{
		$this->__construct();
	}

	//连接到email环境中
	function send_mail($sendto,$subject,$content,$user_name="")
	{
		if(!$sendto || !$subject || !$content)
		{
			return false;
		}
		//如果没有指定邮箱服务器
		if(!$this->app->sys_config["smtp_server"])
		{
			return false;
		}
		$this->smtp_server = $this->app->sys_config["smtp_server"];
		//设置邮件服务器端口
		if($this->app->sys_config["smtp_port"] && $this->app->sys_config["smtp_port"] != "25")
		{
			$this->smtp_port = $this->app->sys_config["smtp_port"];
		}
		//判断是否有启用SSL
		$this->smtp_ssl = $this->app->sys_config["smtp_ssl"];
		//判断是否有设置用户名
		if(!$this->app->sys_config["smtp_user"])
		{
			return false;
		}
		$this->smtp_user = $this->app->sys_config["smtp_user"];
		//判断是否有启用密码
		if(!$this->app->sys_config["smtp_pass"])
		{
			return false;
		}
		$this->smtp_pass = $this->app->sys_config["smtp_pass"];
		$this->smtp_reply = $this->app->sys_config["smtp_reply"];
		$this->smtp_admin = $this->app->sys_config["smtp_admin"];
		if(!$this->smtp_reply && !$this->smtp_admin)
		{
			return false;
		}
		$mail = new PHPMailer();
		$mail->CharSet =  ($this->app->sys_config["smtp_charset"] == "gbk" && function_exists("iconv")) ? "gbk" : "utf8";
		$mail->IsSMTP();
		$mail->SMTPAuth = true;
		$mail->SMTPDebug = false;//是否启用调试
		$mail->IsHTML(true);
		$mail->Username = trim($this->smtp_user);
		$mail->Password = trim($this->smtp_pass);
		$mail->Host = trim($this->smtp_server);
		$mail->Port = $this->smtp_port;
		if($this->smtp_ssl)
		{
			$mail->SMTPSecure = 'ssl';
		}
		$mail->LE = "\r\n";
		$mail->Timeout = 15;
		//发件人
		$mail->From = $this->smtp_admin;
		$mail->FromName = $this->app->sys_config["smtp_fromname"] ? $this->app->sys_config["smtp_fromname"] : $this->smtp_fromname;
		if($mail->CharSet != "utf8")
		{
			$subject = $this->app->trans_lib->charset($subject,"UTF-8","GBK");
			$content = $this->app->trans_lib->charset($content,"UTF-8","GBK");
			$mail->FromName = $this->app->trans_lib->charset($mail->FromName,"UTF-8","GBK");
		}
		$mail->Subject = $subject;
		$mail->MsgHTML($content);
		$sendto_array = explode(";",$sendto);
		if(count($sendto_array)<2)
		{
			if(!$user_name)
			{
				$user_name = str_replace(strstr($sendto,"@"),"",$sendto);
			}
			$mail->AddAddress($sendto,$user_name);
		}
		else
		{
			foreach($sendto_array AS $key=>$value)
			{
				$v_name = str_replace(strstr($value,"@"),"",$value);
				$mail->AddAddress($value,$v_name);
			}
		}
		if($mail->Send())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	//订单，通知给客户
	function order($id)
	{
		if(!$this->app->sys_config["smtp_order"])
		{
			return true;
		}
		$this->app->load_model("checkout");
		$rs = $this->app->checkout_m->get_one($id);
		$this->app->tpl->assign("rs",$rs);
		$content = $this->app->tpl->fetch("email/order_content.".$this->app->tpl->ext);//邮件内容
		$subject = $this->app->tpl->fetch("email/order_title.".$this->app->tpl->ext);//邮件标题
		$this->send_mail($rs["email"],$subject,$content,$rs["fullname"]);
		return true;
	}

	//订单，通知给管理员
	function order_admin($id)
	{
		if(!$this->app->sys_config["smtp_order"])
		{
			return true;
		}
		$this->app->load_model("checkout");
		$rs = $this->app->checkout_m->get_one($id);
		$this->app->tpl->assign("rs",$rs);
		$content = $this->app->tpl->fetch("email/order_admin_content.".$this->app->tpl->ext);//邮件内容
		$subject = $this->app->tpl->fetch("email/order_admin_title.".$this->app->tpl->ext);//邮件标题
		$this->send_mail($this->app->sys_config["smtp_admin"],$subject,$content,$rs["fullname"]);
		return true;
	}

	//订单更新状态通知客户
	function order_update($id)
	{
		if(!$this->app->sys_config["smtp_order"])
		{
			return true;
		}
		$this->app->load_model("checkout");
		$rs = $this->app->checkout_m->get_one($id);
		$this->app->tpl->assign("rs",$rs);
		$content = $this->app->tpl->fetch("email/order_update_content.".$this->app->tpl->ext);//邮件内容
		$subject = $this->app->tpl->fetch("email/order_update_title.".$this->app->tpl->ext);//邮件标题
		$this->send_mail($rs["email"],$subject,$content,$rs["fullname"]);
		return true;
	}

	//订单更新状态通知管理员
	function order_update_admin($id)
	{
		if(!$this->app->sys_config["smtp_order"])
		{
			return true;
		}
		$this->app->load_model("checkout");
		$rs = $this->app->checkout_m->get_one($id);
		$this->app->tpl->assign("rs",$rs);
		$content = $this->app->tpl->fetch("email/order_update_admin_content.".$this->app->tpl->ext);//邮件内容
		$subject = $this->app->tpl->fetch("email/order_update_admin_title.".$this->app->tpl->ext);//邮件标题
		$this->send_mail($this->app->sys_config["smtp_admin"],$subject,$content,$rs["fullname"]);
		return true;
	}

	//注册，通知给客户，请参考getpass进行编写，注
	function reg($uid)
	{
		if(!$this->app->sys_config["smtp_reg"])
		{
			return true;
		}
		//注册通知
		$this->app->load_model("user");
		$rs = $this->app->user_m->user_from_id($uid);
		$this->app->tpl->assign("rs",$rs);
		$content = $this->app->tpl->fetch("email/reg_content.".$this->app->tpl->ext);//邮件内容
		$subject = $this->app->tpl->fetch("email/reg_title.".$this->app->tpl->ext);//邮件标题
		$this->send_mail($rs["email"],$subject,$content,$rs["name"]);
		return true;
	}

	//取回密码时通知客户
	function getpass($uid)
	{
		$this->app->load_model("user");
		$rs = $this->app->user_m->user_from_id($uid);
		$this->app->tpl->assign("rs",$rs);
		$content = $this->app->tpl->fetch("email/getpass_content.".$this->app->tpl->ext);//邮件内容
		$subject = $this->app->tpl->fetch("email/getpass_title.".$this->app->tpl->ext);//邮件标题
		$this->send_mail($rs["email"],$subject,$content,$rs["name"]);
		return true;
	}

	//前台客户基于此模块发布的信息，进行通知
	//ID为主题ID
	function module_mail($id)
	{
		$this->app->load_model("msg");
		$this->app->load_model("module");
		$rs = $this->app->msg_m->get_one($id,false);
		if(!$rs)
		{
			return false;
		}
		$this->app->tpl->assign("rs",$rs);
		$m_rs = $this->app->module_m->get_one($rs["module_id"]);
		$this->app->tpl->assign("m_rs",$m_rs);
		//检测是否自定义模板
		$chk_tplfile = ROOT.$this->app->tpl->tpldir."/email/module_".$m_rs["identifier"]."_content.".$this->tpl->ext;
		if(file_exists($chk_tplfile))
		{
			$content = $this->app->tpl->fetch("email/module_".$m_rs["identifier"]."_content.".$this->tpl->ext);
		}
		else
		{
			$content = $this->app->tpl->fetch("email/module_content.".$this->app->tpl->ext);//邮件内容
		}
		$chk_tplfile = ROOT.$this->app->tpl->tpldir."/email/module_".$m_rs["identifier"]."_title.".$this->tpl->ext;
		if(file_exists($chk_tplfile))
		{
			$subject = $this->app->tpl->fetch("email/module_".$m_rs["identifier"]."_title.".$this->tpl->ext);
		}
		else
		{
			$subject = $this->app->tpl->fetch("email/module_title.".$this->app->tpl->ext);//邮件标题
		}
		$email_to = $this->app->sys_config["smtp_to"] ? $this->app->sys_config["smtp_to"] : $this->app->sys_config["smtp_admin"];
		$this->send_mail($email_to,$subject,$content,"Webmaster");
		return true;
	}
}
?>