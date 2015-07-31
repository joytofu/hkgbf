<?php
/***********************************************************
	Filename: app/admin/control/email.php
	Note	: 邮件发送操作
	Version : 3.0
	Author  : qinggan
	Update  : 2011-03-12
***********************************************************/
class email_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("cate");
		$this->load_model("module");
		$this->load_lib("html");//加载生成静态页信息（通过HTML获取）
	}

	//兼容PHP4的写法
	function email_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		$this->tpl->display("email/list.html");
	}

	function one_f()
	{
		$this->tpl->display("email/email.html");
	}

	function postok_f()
	{
		$title_id = $this->trans_lib->int("title_id");
		if(!$title_id)
		{
			error("请选择要发送的主题！",$this->url("email"));
		}
		$content = $this->trans_lib->html("content",false);
		//创建邮件内容
		$file = ROOT_DATA."system_".$_SESSION["sys_lang_id"].".php";
		$_sys = array();
		if(file_exists($file))
		{
			include($file);
		}
		if(!$_sys["siteurl"] || !$_sys["indexphp"])
		{
			error("请先设置<span class='red'>网站信息里的网址及默认首页地址</span>",$this->url("email"));
		}
		$htmlurl = $_sys["siteurl"].$_sys["indexphp"]."?".$this->config->c."=msg&id=".$title_id;
		$index_content = $this->html_lib->get_content($htmlurl);
		//生成HTML文件
		$html_post_content = "<div>".$content."</div><div>".$index_content."</div>";
		$this->file_lib->vi($html_post_content,ROOT_DATA."tmp/email_".$_SESSION["admin_id"].".php");
		$email_count = $this->trans_lib->int("email_count");
		if(!$email_count) $email_count = 30;
		error("群发内容整理完毕，即将开始群发",$this->url("email,send","id=".$title_id."&psize=".$email_count."&pageid=1"));
	}

	function send_f()
	{
		$file = ROOT_DATA."system_".$_SESSION["sys_lang_id"].".php";
		$_sys = array();
		if(file_exists($file))
		{
			include($file);
		}
		$this->sys_config($_sys);
		$this->load_lib("email");//加载生成静态页信息（通过HTML获取）
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			$title = $this->trans_lib->safe("title");
			$content = $this->trans_lib->html("content",false);
			$email = $this->trans_lib->safe("email");
			if(!$title || !$content || !$email)
			{
				error("要发送的邮件信息不完整！",$this->url("email,one"));
			}
			$this->email_lib->send_mail($email,$title,$content);
			error("邮件发送成功！",$this->url("email,one"));
		}
		else
		{
			//获取内容信息
			$pageid = $this->trans_lib->int("pageid");
			if(!$pageid) $pageid = 1;
			$psize = 1;
			//获取邮件
			$this->load_model("subscribers");
			$rslist = $this->subscribers_m->get_list("",$pageid,$psize);
			if(!$rslist)
			{
				error("群发邮件结束！",$this->url("email"));
			}
			$email_array = array();
			foreach($rslist AS $key=>$value)
			{
				$email_array[] = $value["email"];
			}
			$email = implode(";",$email_array);
			//取得邮件标题
			$this->load_model("list");
			$rs = $this->list_m->get_one($id);
			if(!$rs)
			{
				error("没有相关主题！",$this->url("email"));
			}
			$title = $rs["title"];
			$content = $this->file_lib->cat(ROOT_DATA."tmp/email_".$_SESSION["admin_id"].".php");
			//echo $email."<br />";
			//echo $title."<br />";
			//echo $content."<br />";
			//exit;
			$this->email_lib->send_mail($email,$title,$content);
			error("正在将邮件发送给：".$email."，请不要关掉浏览器……！",$this->url("email,send","id=".$id."&pageid=".($pageid+1)));
		}
	}
}
?>