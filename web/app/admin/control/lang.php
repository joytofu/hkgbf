<?php
/***********************************************************
	Filename: app/admin/control/lang.php
	Note	: 语言包管理
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-16
***********************************************************/
class lang_c extends Control
{
	var $module_sign = "lang";
	function __construct()
	{
		parent::Control();
		$this->load_model("lang");
	}

	function lang_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		sys_popedom($this->module_sign.":list","tpl");
		$rslist = $this->lang_m->get_list();
		$this->tpl->assign("rslist",$rslist);
		$this->tpl->display("lang/list.html");
	}

	function list_f()
	{
		$id = $this->trans_lib->safe("id");
		if(!$id)
		{
			error("操作非法，没有指定ID",site_url('lang'));
		}
		sys_popedom($this->module_sign.":list","tpl");
		$rs = $this->lang_m->get_one($id);
		$this->tpl->assign("rs",$rs);
		$tmplist = $this->lang_m->lang_list($id);
		if(!$tmplist) $tmplist = array();
		$rslist = !$rs["ifsystem"] ? $this->lang_m->lang_list("",1) : $tmplist;
		$tlist = array();
		foreach($tmplist AS $key=>$value)
		{
			$tlist[$value["var"]] = $value["val"];
		}
		if(!$rslist) $rslist = array();
		foreach($rslist AS $key=>$value)
		{
			if($tlist[$value["var"]])
			{
				$value["val"] = $tlist[$value["var"]]; //替换新版
			}
			if($value["val"])
			{
				$value["val"] = str_replace("&","&amp;",$value["val"]);
				$value["val"] = str_replace("'","&#39;",$value["val"]);
				$value["val"] = str_replace('"',"&quot;",$value["val"]);
				$value["val"] = str_replace("<","&lt;",$value["val"]);
				$value["val"] = str_replace(">","&gt;",$value["val"]);
			}
			$rslist[$key] = $value;
		}
		$this->tpl->assign("rslist",$rslist);
		$ltype_array = array("all"=>"全局","www"=>"前台","admin"=>"后台");
		$this->tpl->assign("ltype",$ltype_array);
		$this->tpl->display("lang/lang.html");
	}

	function ajax_setok_f()
	{
		$id = $this->trans_lib->int("id");
		$array = array();
		$array["val"] = $this->trans_lib->safe("val");
		if(!$array["val"])
		{
			exit("error: 值不允许为空");
		}
		$array["val"] = str_replace("&gt;",">",$array["val"]);
		$array["val"] = str_replace("&lt;","<",$array["val"]);
		$array["val"] = str_replace("&quot;",'"',$array["val"]);
		$array["val"] = str_replace("&#39;","'",$array["val"]);
		$array["val"] = str_replace("&amp;","&",$array["val"]);
		if($id)
		{
			$langid = $this->trans_lib->safe("langid");
			sys_popedom($this->module_sign.":modify","ajax");
			//读取ID信息
			$rs = $this->lang_m->lang_one($id);
			if(!$rs)
			{
				exit("error: 找不到相关变量信息！");
			}
			if($rs["langid"] != $langid)
			{
				$tmp_rs = $this->lang_m->lang_one_var($rs["var"],$langid);
				if($tmp_rs)
				{
					$this->lang_m->save_m($array,$tmp_rs["id"]);
				}
				else
				{
					$array["langid"] = $langid;
					$array["ltype"] = $rs["ltype"];
					$array["var"] = $rs["var"];
					$this->lang_m->save_m($array);
				}
			}
			else
			{
				$this->lang_m->save_m($array,$id);
			}
			exit("ok");
		}
		else
		{
			sys_popedom($this->module_sign.":add","ajax");
			$array["langid"] = $this->trans_lib->safe("langid");
			$array["ltype"] = $this->trans_lib->safe("ltype");
			$array["var"] = $this->trans_lib->safe("var");
			if(!$array["langid"] || !$array["ltype"] || !$array["var"])
			{
				exit("error: 操作非法，数据不完整");
			}
			$chk = $this->lang_m->chk_msg($array["var"],$array["langid"],$array["ltype"]);
			if($chk)
			{
				exit("error: 变量名已经存在");
			}
			$this->lang_m->save_m($array);
			exit("ok");
		}
	}

	function set_f()
	{
		$id = $this->trans_lib->safe("id");
		if($id)
		{
			sys_popedom($this->module_sign.":modify","tpl");
			$rs = $this->lang_m->get_one($id);
			$this->tpl->assign("rs",$rs);
		}
		else
		{
			sys_popedom($this->module_sign.":add","tpl");
		}
		$this->tpl->display("lang/set.html");
	}

	//信息存储
	function setok_f()
	{
		$id = $this->trans_lib->safe("id");
		$array = array();
		$array["title"] = $this->trans_lib->safe("title");
		if($id)
		{
			sys_popedom($this->module_sign.":modify","tpl");
		}
		else
		{
			sys_popedom($this->module_sign.":add","tpl");
			$array["langid"] = $this->trans_lib->safe("langid");
			if(!$array["langid"])
			{
				error("操作非法，没有指定语言ID",site_url("lang"));
			}
		}
		$array["note"] = $this->trans_lib->safe("note");
		$array["taxis"] = $this->trans_lib->int("taxis");
		$array["ico"] = $this->trans_lib->safe("ico");//图标
		$array["small_pic"] = $this->trans_lib->safe("small_pic");//小图
		$array["medium_pic"] = $this->trans_lib->safe("medium_pic");//中图
		$array["big_pic"] = $this->trans_lib->safe("big_pic");//大图
		//存储分类信息
		$this->lang_m->save($array,$id);
		error("语言包信息配置成功",site_url("lang"));
	}

	function ajax_status_f()
	{
		$id = $this->trans_lib->safe("id");
		if(!$id)
		{
			exit("error:没有指定ID");
		}
		sys_popedom($this->module_sign.":check","ajax");
		$rs = $this->lang_m->get_one($id);
		$status = $rs["status"] ? 0 : 1;
		$this->lang_m->set_status($id,$status);
		exit("ok");
	}

	function ajax_default_f()
	{
		$id = $this->trans_lib->safe("id");
		if(!$id)
		{
			exit("error:没有指定ID");
		}
		sys_popedom($this->module_sign.":check","ajax");
		$this->lang_m->set_default($id);
		exit("ok");
	}

	function ajax_del_f()
	{
		$id = $this->trans_lib->safe("id");
		if(!$id)
		{
			exit("error:没有指定ID");
		}
		sys_popedom($this->module_sign.":delete","ajax");
		$rs = $this->lang_m->get_one($id);
		if($rs["ifsystem"])
		{
			exit("error: 对不起，系统语言包不允许删除");
		}
		if($rs["ifdefault"])
		{
			exit("error: 对不起，默认语言包不允许删除");
		}
		$this->lang_m->del($id);
		exit("ok");
	}

	function ajax_lang_del_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error:没有指定ID");
		}
		sys_popedom($this->module_sign.":delete","ajax");
		$rs = $this->lang_m->lang_one($id);
		if(!$rs)
		{
			exit("error：没有找到相关数据！");
		}
		$this->lang_m->del_m($rs["var"]);
		exit("ok");
	}

	function ajax_chk_f()
	{
		$id = $this->trans_lib->safe("id");
		if(!$id)
		{
			exit("error: 检测失败，没有指定ID");
		}
		if(!ereg("[a-z]+",$id))
		{
			exit("error: 语言ID只能用字母表示");
		}
		$rs = $this->lang_m->get_one($id);
		if($rs)
		{
			exit("error: 对不起，当前语言包变量已经被使用！");
		}
		exit("ok");
	}
}
?>