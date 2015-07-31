<?php
/***********************************************************
	Filename: app/admin/login.php
	Note	: 超级管理员登录页
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-20
***********************************************************/
class login_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("admin");
	}

	//兼容PHP4的写法
	function login_c()
	{
		$this->__construct();
	}

	//登录页面板
	function index_f()
	{
		//读取语言列表
		$this->load_model("lang");
		$langlist = $this->lang_m->get_list();
		$this->tpl->assign("langlist",$langlist);
		$this->tpl->p('login');
	}

	//登录操作
	function login_ok_f()
	{
		$login_name = $this->trans_lib->safe("username");
		$login_pass = $this->trans_lib->safe("password");
		if(!$login_name || !$login_pass)
		{
			error($this->lang["login_not_user_pass"],$this->url("login"));
		}
		//判断是否需要用验证码
		if(function_exists("imagecreate") && defined("SYS_VCODE_USE") && SYS_VCODE_USE == true)
		{
			$chk = $this->trans_lib->safe("chk");
			if(!$chk)
			{
				error($this->lang["login_vcode_empty"],$this->url("login"));
			}
			$chk = md5($chk);
			if($chk != $_SESSION[SYS_VCODE_VAR])
			{
				error($this->lang["login_vcode_false"],$this->url("login"));
			}
			unset($_SESSION[SYS_VCODE_VAR]);
		}
		//判断账号或密码是否正确
		$rs = $this->admin_m->check_login($login_name,$login_pass);
		if(!$rs)
		{
			error($this->lang["login_false"],$this->url("login"));
		}
		else
		{
			//非系统管理员，登录时同时判断语言包权限
			if(!$rs["if_system"] && !$rs["langid"])
			{
				error("当前账号尚未配置相应的语言权限",$this->url("login"));
			}
			//加载语言包
			$this->load_model("lang");
			$rs_langid = $rs["if_system"] ? "" : $rs["langid"];
			$chk_admin = $this->lang_m->get_list_chk($rs_langid);
			if(!$chk_admin)
			{
				error("当前账号没有找到符合要求的内容管理权限",$this->url("login"));
			}
			//取得第一个语言ID做为默认语言
			$_SESSION["sys_lang_id"] = $chk_admin[0]["langid"];
			$_SESSION["admin_id"] = $rs["id"];
			$_SESSION["admin_name"] = $rs["name"];
			$_SESSION["admin_realname"] = $rs["realname"] ? $rs["realname"] : $rs["name"];
			$_SESSION[SYS_CHECKED_SESSION_ID] = sys_md5($rs);
			$login_success = sys_eval($this->lang["login_success"],$rs["name"]);//格式化模板标签中的变量
			//登记langid
			$langid = $this->trans_lib->safe("langid");
			if($langid)
			{
				$this->load_model("lang");
				$lang_rs = $this->lang_m->get_one($langid);
				if($lang_rs)
				{
					$_SESSION["sys_lang_id"] = $langid;
				}
			}
			//判断是否是IE6
			$is_ie6 = false;
			$ie6 = $this->trans_lib->safe("ie6");
			if($ie6)
			{
				$is_ie6 = true;
			}
			if($is_ie6)
			{
				error($login_success,$this->url("index","ie6=true"));
			}
			else
			{
				error($login_success,$this->url("index"));
			}
		}
	}

	function ajax_logout_f()
	{
		session_destroy();
		exit("ok");
	}

	//退出操作
	function logout_f()
	{
		$my_realname = $_SESSION["admin_realname"];
		unset($_SESSION["admin_id"],$_SESSION[SYS_CHECKED_SESSION_ID],$_SESSION["admin_realname"],$_SESSION["admin_name"]);
		$logout_success = sys_eval($this->lang["logout_success"],$my_realname);
		error($logout_success,$this->url("login"));
	}


	//创建验证码
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