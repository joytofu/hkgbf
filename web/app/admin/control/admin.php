<?php
/***********************************************************
	Filename: app/admin/websitesystem.php
	Note	: 管理员
	Version : 3.0
	Author  : qinggan
	Update  : 2009-12-23
***********************************************************/
class admin_c extends Control
{
	var $module_sign = "admin";//权限标识
	function __construct()
	{
		parent::Control();
		$this->load_model("admin");
		$this->load_model("module");
	}

	//兼容PHP4的写法
	function admin_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		sys_popedom($this->module_sign.":list","tpl");
		$pageid = $this->trans_lib->int(SYS_PAGEID);
		$page_url = site_url("admin");
		$condition = "";
		$admin_rs = $this->admin_m->get_one($_SESSION["admin_id"]);
		if(!$admin_rs["if_system"])
		{
			$condition = " if_system='0' ";
		}
		$rslist = $this->admin_m->get_list($pageid,$condition);
		$this->tpl->assign("rslist",$rslist);
		$count = $this->admin_m->get_count($condition);
		$this->tpl->assign("total",$count);
		$this->tpl->display("admin/list.html");
	}

	function set_f()
	{
		$id = $this->trans_lib->int("id");
		if($id)
		{
			if($id == $_SESSION["admin_id"])
			{
				error("对不起，您不能修改自己的信息",site_url("admin"));
			}
			sys_popedom($this->module_sign.":modify","tpl");
			$rs = $this->admin_m->get_one($id);
			$popedom = $rs["popedom"] ? sys_id_list($rs["popedom"]) : array("0:0");
			$this->tpl->assign("rs",$rs);
			$lang_popedom = $rs["langid"] ? sys_id_list($rs["langid"]) : array();
			$this->tpl->assign("lang_popedom",$lang_popedom);
		}
		else
		{
			sys_popedom($this->module_sign.":add","tpl");
			$popedom = array("0:0");
			$lang_popedom = $rs["langid"] ? sys_id_list($rs["langid"]) : array();
			$this->tpl->assign("lang_popedom",$lang_popedom);
		}
		$this->tpl->assign("popedom",$popedom);
		//读取所有可以配置权限的模块
		$mlist = $this->module_m->all_module();
		$this->tpl->assign("mlist",$mlist);
		$this->load_model("identifier");
		$plist = $this->identifier_m->popedom_list();
		$this->tpl->assign("plist",$plist);
		//读取所有可以配置的语言
		$this->load_model("lang");
		$langlist = $this->lang_m->get_list();
		$this->tpl->assign("langlist",$langlist);
		$this->tpl->display("admin/set.html");
	}

	function chk_f()
	{
		$id = $this->trans_lib->int("id");
		$email = $this->trans_lib->safe("email");
		if(!$email)
		{
			exit("error: 管理员邮箱不允许为空");
		}
		$name = $this->trans_lib->safe("name");
		if(!$name)
		{
			exit("error: 管理员账号不允许为空");
		}
		$rs_name = $this->admin_m->chk_name($name,$id);
		if($rs_name)
		{
			exit("error:管理员账号已经存在");
		}
		$rs_email = $this->admin_m->chk_email($email,$id);
		if($rs_email)
		{
			exit("error:管理员邮箱已经存在");
		}
		exit("ok");
	}

	//存储信息
	function setok_f()
	{
		$id = $this->trans_lib->int("id");
		if($id)
		{
			sys_popedom($this->module_sign.":modify","tpl");
		}
		else
		{
			sys_popedom($this->module_sign.":add","tpl");
		}
		$array = array();
		$array["name"] = $this->trans_lib->safe("name");
		$pass = $this->trans_lib->safe("pass");
		if($pass)
		{
			$array["pass"] = sys_md5($pass);
		}
		else
		{
			if(!$id)
			{
				$array["pass"] = sys_md5("123456");
			}
		}
		$array["email"] = $this->trans_lib->safe("email");//模板目录
		$array["if_system"] = $this->trans_lib->int("if_system");
		if(!$array["if_system"])
		{
			$popedom = $this->trans_lib->safe("popedom");
			$array["popedom"] = $popedom ? implode(",",$popedom) : "";
		}
		else
		{
			$array["popedom"] = "";
		}
		$mylangid = $this->trans_lib->safe("mylangid");
		$array["langid"] = sys_id_string($mylangid);
		//存储分类信息
		$this->admin_m->save($array,$id);
		error("管理员信息添加/存储成功",site_url("admin"));
	}

	function ajax_status_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error:没有指定ID");
		}
		if($id == $_SESSION["admin_id"])
		{
			exit("对不起，你不能锁定自己！");
		}
		sys_popedom($this->module_sign.":check","ajax");
		$rs = $this->admin_m->get_one($id);
		$status = $rs["status"] ? 0 : 1;
		$this->admin_m->set_status($id,$status);
		exit("ok");
	}

	function ajax_del_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error:没有指定ID");
		}
		$module_id = $this->trans_lib->int("module_id");
		if(!$module_id)
		{
			exit("error: 无法读取模块ID");
		}
		if($id == $_SESSION["admin_id"])
		{
			exit("error: 管理员不能删除自己！");
		}
		sys_popedom($this->module_sign.":delete","ajax");
		$this->admin_m->del($id);
		exit("ok");
	}


}
?>