<?php
/***********************************************************
	Filename: app/admin/usergroup.php
	Note	: 会员组管理
	Version : 3.1
	Author  : qinggan
	Update  : 2011-03-13
***********************************************************/
class usergroup_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("usergroup");
	}

	//兼容PHP4的写法
	function user_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		sys_popedom("usergroup:list","tpl");//查看权限
		$rslist = $this->usergroup_m->get_all();
		$this->tpl->assign("rslist",$rslist);
		$this->tpl->display("user/group_list.html");
	}

	function set_f()
	{
		$id = $this->trans_lib->int("id");
		$id ? sys_popedom("usergroup:modify","tpl") : sys_popedom("usergroup:add","tpl");
		if($id)
		{
			$rs = $this->usergroup_m->get_one($id);
			$p_post = $rs["popedom_post"] ? sys_id_list($rs["popedom_post"]) : array();
			$p_reply = $rs["popedom_reply"] ? sys_id_list($rs["popedom_reply"]) : array();
			$p_read = $rs["popedom_read"] ? sys_id_list($rs["popedom_read"]) : array();
			$this->tpl->assign("rs",$rs);
			$this->tpl->assign("id",$id);
		}
		else
		{
			$p_post = $p_reply = $p_read = array();
		}
		$this->tpl->assign("p_post",$p_post);
		$this->tpl->assign("p_reply",$p_reply);
		$this->tpl->assign("p_read",$p_read);
		//读取模块组
		$this->load_model("module");
		$modulelist = $this->module_m->module_list(0);
		$this->tpl->assign("modulelist",$modulelist);
		//读取分类
		$this->load_model("cate");
		$this->cate_m->langid($_SESSION["sys_lang_id"]);
		$this->cate_m->get_catelist();
		$catelist = $this->cate_m->html_select_array();
		$this->tpl->assign("catelist",$catelist);

		$this->tpl->display("user/group_set.html");
	}

	//存储信息
	function setok_f()
	{
		$id = $this->trans_lib->int("id");
		$id ? sys_popedom("usergroup:modify","tpl") : sys_popedom("usergroup:add","tpl");
		$title = $this->trans_lib->safe("title");
		if(!$title)
		{
			error("组名称不允许为空！",$this->url("usergroup,set","id=".$id));
		}
		$popedom_post = $this->trans_lib->safe("popedom_post");
		$popedom_reply = $this->trans_lib->safe("popedom_reply");
		$popedom_read = $this->trans_lib->safe("popedom_read");
		if(!$popedom_post) $popedom_post = array();
		if(!$popedom_reply) $popedom_reply = array();
		if(!$popedom_read) $popedom_read = array();
		if(in_array("all",$popedom_read))
		{
			$p_read = "all";
		}
		else
		{
			$p_read = sys_id_string($popedom_read);
		}
		$p_post = sys_id_string($popedom_post);
		$p_reply = sys_id_string($popedom_reply);
		$post_cert = $this->trans_lib->checkbox("post_cert");
		$reply_cert = $this->trans_lib->checkbox("reply_cert");
		$array = array();
		$array["title"] = $title;
		$array["popedom_post"] = $p_post;
		$array["popedom_reply"] = $p_reply;
		$array["popedom_read"] = $p_read;
		$array["post_cert"] = $post_cert;
		$array["reply_cert"] = $reply_cert;
		$array["ifshow"] = $this->trans_lib->checkbox("ifshow");//是否在前台显示
		if(!$id)
		{
			$array["group_type"] = "user";
			$array["ifsystem"] = 0;
			$array["ifdefault"] = 0;
		}
		$this->usergroup_m->save($array,$id);
		error("会员组信息添加/存储成功",$this->url("usergroup"));
	}

	//设置默认
	function ajax_default_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error:没有指定ID");
		}
		$rs = $this->usergroup_m->set_default($id);
		exit("ok");
	}

	function ajax_del_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error:没有指定ID");
		}
		sys_popedom("usergroup:delete","ajax");
		$rs = $this->usergroup_m->get_one($id);
		if($rs["ifdefault"])
		{
			exit("默认组不允许删除！");
		}
		if($rs["ifsystem"])
		{
			exit("系统组不允许删除！");
		}
		$this->usergroup_m->del($id);
		exit("ok");
	}


	//字段管理列表
	function fields_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			error("操作非法，没有指定ID",$this->url("usergroup"));
		}
		sys_popedom("usergroup:list","tpl");
		$this->tpl->assign("id",$id);
		//读取字段管理
		$rslist = $this->usergroup_m->fields_index($id);
		$this->tpl->assign("rslist",$rslist);
		//读取当前模块的配置信息
		$rs = $this->usergroup_m->get_one($id);
		$this->tpl->assign("rs",$rs);
		$this->tpl->display("user/fields.html");
	}

	function fields_set_f()
	{
		$id = $this->trans_lib->int("id");
		if($id)
		{
			sys_popedom("usergroup:modify","tpl");
			$rs = $this->usergroup_m->fields_one($id);
			$this->tpl->assign("rs",$rs);
			$this->tpl->assign("groupid",$rs["group_id"]);
		}
		else
		{
			sys_popedom("usergroup:add","tpl");
			$groupid = $this->trans_lib->int("groupid");
			$this->tpl->assign("groupid",$groupid);
		}
		//读取会员组信息
		$m_rs = $this->usergroup_m->get_one($id);
		$this->tpl->assign("m_rs",$m_rs);
		//
		$this->load_model("module");
		$input_list = $this->module_m->input_type($_SESSION["sys_lang_id"],true);
		$this->tpl->assign("input_list",$input_list);
		//获取联动组
		$this->load_model("datalink");
		$datalink = $this->datalink_m->all_group($_SESSION["sys_lang_id"]);
		$this->tpl->assign("datalink",$datalink);
		$this->tpl->display("user/fields_set.html");
	}

	function chk_identifier2_f($val="",$groupid=0)
	{
		$exit = $val ? false : true;
		if(!$val)
		{
			$val = $this->trans_lib->safe("identifier");
		}
		//取得模块ID
		if(!$groupid)
		{
			$groupid = $this->trans_lib->int("groupid");
		}
		if(!$val)
		{
			if($exit)
			{
				exit("error: 标识串为空");
			}
			else
			{
				return false;
			}
		}
		if(!$groupid)
		{
			if($exit)
			{
				exit("error: 没有指定模块ID");
			}
			else
			{
				return false;
			}
		}
		//判断这个标识符是否有被使用
		$if_used = $this->usergroup_m->chk_identifier2($val,$groupid);
		if($if_used)
		{
			if($exit)
			{
				exit("error: 标识串已经被使用");
			}
			else
			{
				return false;
			}
		}
		//判断标识串是否符合要求
		if(!ereg("[a-z\_0-9]+",$val))
		{
			if($exit)
			{
				exit("error: 标识串仅限小写英文字母，数字及下划线，且第一位必须是字母");
			}
			else
			{
				return false;
			}
		}
		if($exit)
		{
			exit("ok");
		}
		else
		{
			return true;
		}
	}


	//存储添加/编辑后的字段信息
	function fields_setok_f()
	{
		$id = $this->trans_lib->int("id");
		if($id)
		{
			sys_popedom("usergroup:modify","tpl");
			$rs = $this->usergroup_m->fields_one($id);
			$groupid = $rs["group_id"];
		}
		else
		{
			sys_popedom("usergroup:add","tpl");
			$groupid = $this->trans_lib->int("groupid");
		}
		if(!$groupid)
		{
			error("操作非法",site_url("usergroup"));
		}
		if(!$id)
		{
			$array["group_id"] = $groupid;
			$array["identifier"] = $this->trans_lib->safe("identifier");
			if(!$array["identifier"])
			{
				error("标识串不允许为空",site_url("usergroup,fields_set","groupid=".$groupid));
			}
			//判断标识符是否使用了
			$chk_msg = $this->chk_identifier2_f($array["identifier"],$groupid);
			if(!$chk_msg)
			{
				error("标识符不符合系统要求",site_url("usergroup,fields_set","groupid=".$groupid));
			}
			$array["input"] = $this->trans_lib->safe("input");
		}
		$array["title"] = $this->trans_lib->safe("title");
		$array["sub_left"] = $this->trans_lib->safe("sub_left");
		$array["sub_note"] = $this->trans_lib->safe("sub_note");
		$array["width"] = $this->trans_lib->safe("width");
		$array["height"] = $this->trans_lib->safe("height");
		$array["default_val"] = $this->trans_lib->safe("default_val");
		$array["list_val"] = $this->trans_lib->safe("list_val");
		$array["link_id"] = $this->trans_lib->int("link_id");
		$array["taxis"] = $this->trans_lib->int("taxis");
		$array["if_must"] = $this->trans_lib->int("if_must");
		$array["error_note"] = $this->trans_lib->safe("error_note");
		$this->usergroup_m->fields_save($array,$id);
		error("字段数据配置成功",site_url("usergroup,fields","id=".$groupid));
	}


	//更改状态
	function fields_status_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error: 操作非法，没有指定ID");
		}
		sys_popedom("usergroup:check","ajax");
		$this->usergroup_m->fields_status($id);
		exit("ok");
	}

	function fields_del_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error: 操作非法，没有指定ID");
		}
		sys_popedom("usergroup:delete","tpl");
		//删除模块操作
		$this->usergroup_m->fields_del($id);
		exit("ok");
	}

}
?>