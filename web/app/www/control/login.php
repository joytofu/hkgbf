<?php
/***********************************************************
	Filename: app/www/control/login.php
	Note	: 会员登录页
	Version : 3.0
	Author  : qinggan
	Update  : 2010-01-08
***********************************************************/
class login_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("user");
	}

	function login_c()
	{
		$this->__construct();
	}

	//会员登录界面
	function index_f()
	{
		if(!$this->sys_config["login_status"])
		{
			$message = $this->sys_config["close_login"] ? $this->sys_config["close_login"] : "Not Login!";
			error($message,$this->url());
		}
		$b_url = $_SESSION["last_url"] ? $_SESSION["last_url"] : ($_SERVER["HTTP_REFERER"] ? $_SERVER["HTTP_REFERER"] : site_url("index"));
		if($_SESSION["user_id"] && $_SESSION["user_name"])
		{
			error($this->lang["login_exists"],$b_url);
		}
		//登录后的向导
		$leader[0] = array("url"=>site_url("login","",false),"title"=>$this->lang["login"]);
		$this->tpl->assign("leader",$leader);
		$this->tpl->display("login.".$this->tpl->ext);
	}

	function ok_f()
	{
		load_plugin("login:ok:prev");//在执行登录前运行相关插件
		if(!$this->sys_config["login_status"])
		{
			$message = $this->sys_config["close_login"] ? $this->sys_config["close_login"] : "Not Login!";
			error($message,$this->url());
		}
		$username = $this->trans_lib->safe("username");
		$password = $this->trans_lib->safe("password");
		$login_url = site_url("login","",false);
		//账号和密码为空时警告
		if(!$username || !$password)
		{
			error($this->lang["login_false_empty"],$login_url);
		}
		//检查会员不存在时的警告
		$rs = $this->user_m->user_from_name($username);
		if(!$rs)
		{
			error($this->lang["login_false_rs"],$login_url);
		}
		//密码检测
		$password = sys_md5($password);
		if($rs["pass"] != $password)
		{
			error($this->lang["login_false_password"],$login_url);
		}
		//检查会员状态的警告
		if(!$rs["status"])
		{
			error($this->lang["login_false_check"],$login_url);
		}
		//检查会员是否被锁定
		if($rs["status"] == 2)
		{
			error($this->lang["login_false_lock"],$login_url);
		}
		//将数据存到session中
		$_SESSION["user_id"] = $rs["id"];
		$_SESSION["user_name"] = $rs["name"];
		$_SESSION["group_id"] = $rs["groupid"];
		$_SESSION["user_rs"]= $rs;
		$_SESSION[SYS_CHECKED_SESSION_ID] = sys_md5($rs);
		//执行插件
		load_plugin("login:ok:next",$rs);
		//error($this->lang["login_usccess"].$ext,site_url("index"));
						echo "<script language=javascript>
window.alert('登录成功!');
window.location.href='cs-putong.html';
</script>";
	}

	//取得密码
	function getpass_f()
	{
		if($_SESSION["user_id"])
		{
			error($this->lang["login_exists"],$this->url());
		}
		$sitetitle = $this->lang["login_getpass"];
		$this->tpl->assign("sitetitle",$sitetitle);
		$array[0]["title"] = $this->lang["login_getpass"];
		$this->tpl->assign("leader",$array);
		$this->tpl->display("getpass.".$this->tpl->ext);
	}

	function getpasschk_f()
	{
		$username = $this->trans_lib->safe("username");
		$email = $this->trans_lib->safe("email");
		if(!$username || !$email)
		{
			error($this->lang["login_user_email_chk"],$this->url("login,getpass"));
		}
		$rs = $this->user_m->user_from_name($username);
		if(!$rs || $rs["email"] != $email)
		{
			error($this->lang["login_not_user_email"],$this->url("login,getpass"));
		}
		//
		$this->user_m->create_chkcode($rs["id"],$this->system_time);
		//发送电子邮件通知客户到邮箱取得认证
		$this->load_lib("email");
		$this->email_lib->getpass($rs["id"]);//通知客户修改邮箱
		error($this->lang["login_getpass_title"],$this->url());
	}

	function repass_f()
	{
		$username = $this->trans_lib->safe("username");
		$chkcode = $this->trans_lib->safe("chkcode");
		$this->tpl->assign("username",$username);
		$this->tpl->assign("chkcode",$chkcode);
		$sitetitle = $this->lang["login_reset_pass"];
		$this->tpl->assign("sitetitle",$sitetitle);
		$array[0]["title"] = $this->lang["login_reset_pass"];
		$this->tpl->assign("leader",$array);
		$this->tpl->display("repass.".$this->tpl->ext);
	}

	function update_pass_f()
	{
		$username = $this->trans_lib->safe("username");
		$chkcode = $this->trans_lib->safe("chkcode");
		if(!$username || !$chkcode)
		{
			error($this->lang["login_not_code_user"],$this->url("login,repass"));
		}
		$rs = $this->user_m->user_from_name($username);
		if(!$rs)
		{
			error($this->lang["login_not_user"],$this->url("login,repass"));
		}
		if($rs["chkcode"] != $chkcode)
		{
			error($this->lang["login_error_code"],$this->url("login,repass"));
		}
		if(($rs["chktime"] - 24*3600) > $this->system_time)
		{
			error($this->lang["login_code_exp"],$this->url("login,repass"));
		}

		$newpass = $this->trans_lib->safe("newpass");
		$chkpass = $this->trans_lib->safe("chkpass");
		if(!$newpass || !$chkpass)
		{
			error($this->lang["login_not_pass"],$this->url("login,repass"));
		}
		if($newpass != $chkpass)
		{
			error($this->lang["login_error_pass"],$this->url("login,repass"));
		}
		$pass = sys_md5($newpass);
		$this->user_m->update_pass($pass,$rs["id"]);
		//直接登录
		$_SESSION["user_id"] = $rs["id"];
		$_SESSION["user_name"] = $rs["name"];
		error($this->lang["login_update"],$this->url("usercp"));
	}

	function codes_f()
	{
		$x_size=76;
		$y_size=23;
		if(!defined("SYS_VCODE_VAR"))
		{
			define("SYS_VCODE_VAR","phpok_login_chk");
		}
		$aimg = imagecreate($x_size,$y_size);
		$back = imagecolorallocate($aimg, 255, 255, 255);
		$border = imagecolorallocate($aimg, 0, 0, 0);
		imagefilledrectangle($aimg, 0, 0, $x_size - 1, $y_size - 1, $back);
		$txt="0123456789";
		$txtlen=strlen($txt);

		$thetxt="";
		for($i=0;$i<4;$i++)
		{
			$randnum=mt_rand(0,$txtlen-1);
			$randang=mt_rand(-10,10);	//文字旋转角度
			$rndtxt=substr($txt,$randnum,1);
			$thetxt.=$rndtxt;
			$rndx=mt_rand(1,5);
			$rndy=mt_rand(1,4);
			$colornum1=($rndx*$rndx*$randnum)%255;
			$colornum2=($rndy*$rndy*$randnum)%255;
			$colornum3=($rndx*$rndy*$randnum)%255;
			$newcolor=imagecolorallocate($aimg, $colornum1, $colornum2, $colornum3);
			imageString($aimg,3,$rndx+$i*21,5+$rndy,$rndtxt,$newcolor);
		}
		unset($txt);
		$thetxt = strtolower($thetxt);
		$_SESSION[SYS_VCODE_VAR] = md5($thetxt);#[写入session中]
		@session_write_close();#[关闭session写入]
		imagerectangle($aimg, 0, 0, $x_size - 1, $y_size - 1, $border);
		$newcolor="";
		$newx="";
		$newy="";
		$pxsum=30;	//干扰像素个数
		for($i=0;$i<$pxsum;$i++)
		{
			$newcolor=imagecolorallocate($aimg, mt_rand(0,254), mt_rand(0,254), mt_rand(0,254));
			imagesetpixel($aimg,mt_rand(0,$x_size-1),mt_rand(0,$y_size-1),$newcolor);
		}
		header("Pragma:no-cache");
		header("Cache-control:no-cache");
		header("Content-type: image/png");
		imagepng($aimg);
		imagedestroy($aimg);
		exit;
	}

}
?>