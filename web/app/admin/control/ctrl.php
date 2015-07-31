<?php
#=====================================================================
#	Filename: app/admin/control/ctrl.php
#	Note	: 模块控制中心
#	Version : 3.0
#	Author  : qinggan
#	Update  : 2009-11-4
#=====================================================================
class ctrl_c extends Control
{
	var $module_sign = "module";
	function __construct()
	{
		parent::Control();
		$this->load_model("module");//读取模块列表
	}

	function ctrl_c()
	{
		$this->__construct();
	}

	function gset_f()
	{
		sys_popedom($this->module_sign.":group","tpl");
		$id = $this->trans_lib->int("id");
		if($id)
		{
			$this->tpl->assign("id",$id);
			$rs = $this->module_m->group_one($id);
			$this->tpl->assign("rs",$rs);
		}
		$this->tpl->display("ctrl/gset.html");
	}

	function gsetok_f()
	{
		sys_popedom($this->module_sign.":group","tpl");
		$id = $this->trans_lib->int("id");
		$array["title"] = $this->trans_lib->safe("title");
		$array["status"] = $this->trans_lib->int("status");
		$array["taxis"] = $this->trans_lib->int("taxis");
		$array["js_function"] = $this->trans_lib->safe("js_function");
		if(!$id)
		{
			$array["langid"] = $_SESSION["sys_lang_id"];
		}
		$this->module_m->group_save($array,$id);
		error("模块组编辑/添加成功！",$this->url("ctrl"));
	}

	function gdel_f()
	{
		sys_popedom($this->module_sign.":group","ajax");
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error:没有指定组ID");
		}
		$condition = array();
		$condition["groupid"] = $id;
		$condition["langid"] = $_SESSION["sys_lang_id"];
		$rslist = $this->module_m->get_list(0,$condition);
		if($rslist)
		{
			exit("error:模块组中有相应的模块信息，请先转移或删除！");
		}
		$this->module_m->group_del($id);
		exit("ok");
	}

	function index_f()
	{
		sys_popedom($this->module_sign.":list","tpl");
		$groupid = $this->trans_lib->int("groupid");
		$condition = array();
		if($groupid)
		{
			$condition["groupid"] = $groupid;
			$this->tpl->assign("groupid",$groupid);
		}
		$condition["langid"] = $_SESSION["sys_lang_id"];
		$pageid = $this->trans_lib->int(SYS_PAGEID);
		$rslist = $this->module_m->get_list($pageid,$condition);
		$this->tpl->assign("rslist",$rslist);
		$total = $this->module_m->get_count();//读取模块总数
		$page_url = $this->url("ctrl,index","groupid=".$groupid);
		$pagelist = $this->page_lib->page($page_url,$total);
		if($pagelist)
		{
			$this->tpl->assign("pagelist",$pagelist);
		}
		//获取所有模块
		$grouplist = $this->module_m->all_module_group();
		$this->tpl->assign("grouplist",$grouplist);
		$this->tpl->display("ctrl/list.html");
	}

	//更改状态
	function status_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error: 操作非法，没有指定ID");
		}
		sys_popedom($this->module_sign.":check","ajax");
		$rs = $this->module_m->get_one($id);
		if($rs["if_system"])
		{
			exit("error: 核心模块不允许此操作");
		}
		$this->module_m->status($id);
		exit("ok");
	}

	//删除模块
	function del_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error: 操作非法，没有指定ID");
		}
		sys_popedom($this->module_sign.":delete","ajax");
		//判断是否有内容
		$rs = $this->module_m->if_list($id);
		if($rs)
		{
			exit("error: 该模块已经有内容了，不允许删除");
		}
		//判断是否是核心模块，核心模块不允许删除
		$if_system = $this->module_m->if_system_module($id);
		if($if_system)
		{
			exit("error: 系统模块不允许删除");
		}
		//删除模块操作
		$this->module_m->del($id);
		exit("ok");
	}

	//编辑或添加模块
	function set_f()
	{
		$id = $this->trans_lib->int("id");
		$inc_module = array();
		$layout = array();
		if($id)
		{
			sys_popedom($this->module_sign.":modify","tpl");
			//[读取数据]
			$rs = $this->module_m->get_one($id);
			if($rs["inc_module"])
			{
				$inc_module = explode(",",$rs["inc_module"]);
			}
			//读取字段列表
			$tmp_list = $this->module_m->fields_index($id);
			if($tmp_list)
			{
				$mlist = array();
				foreach($tmp_list AS $key=>$value)
				{
					if($value["input"] != "edit") $mlist[] = $value;
				}
				unset($tmp_list);
				$this->tpl->assign("keylist",$mlist);
			}
			if($rs["layout"]) $layout = sys_id_list($rs["layout"]);
		}
		else
		{
			sys_popedom($this->module_sign.":add","tpl");
			$rs["popedom"] = array();
		}
		$this->tpl->assign("layout",$layout);
		$this->tpl->assign("inc_module",$inc_module);
		$this->tpl->assign("rs",$rs);
		//读取使用权限的字段
		$this->load_model("identifier");
		$popedom_list = $this->identifier_m->get_sign("popedom");
		$this->tpl->assign("popedom_list",$popedom_list);
		//取得模块组
		$group_list = $this->module_m->top();
		$this->tpl->assign("group_list",$group_list);
		//关联图片类型
		$this->load_model("gdtype");
		$gdlist = $this->gdtype_m->get_all();
		$this->tpl->assign("gdlist",$gdlist);
		//定制联动搜索
		$this->load_model("datalink");
		$datalink = $this->datalink_m->all_group($_SESSION["sys_lang_id"]);
		$this->tpl->assign("datalink",$datalink);

		$this->tpl->display("ctrl/set.html");
	}

	//存储信息
	function setok_f()
	{
		$id = $this->trans_lib->int("id");
		$array = array();
		$array["title"] = $this->trans_lib->safe("title");//模块名
		if(!$array["title"])
		{
			error("标题不允许为空",site_url("ctrl,set","id=".$id));
		}
		$array["group_id"] = $this->trans_lib->int("group_id");//组ID
		$array["note"] = $this->trans_lib->safe("note");//备注
		if(!$id)
		{
			sys_popedom($this->module_sign.":add","tpl");
			//在添加操作时核心参数的配置
			//判断标识符是否使用了
			$array["langid"] = $_SESSION["sys_lang_id"];//读取ID
			$array["identifier"] = $this->trans_lib->safe("identifier");//标识签，必须是唯一的
			if(!$array["identifier"])
			{
				error("标识串不允许为空",site_url("ctrl,set","id=".$id));
			}
			$chk_msg = $this->chk_identifier_f($array["identifier"]);
			if(!$chk_msg)
			{
				error("标识符不符合系统要求",site_url("ctrl,set","id=".$id));
			}
			//分析控制层是否符合系统要求
			$array["ctrl_init"] = $this->trans_lib->safe("ctrl_init");
			if(!$array["ctrl_init"])
			{
				$array["ctrl_init"] = $this->trans_lib->safe("ctrl_init_val");
			}
			if(!$array["ctrl_init"])
			{
				error("控制层配置为空，请检查",site_url("ctrl,set"));
			}
			if(!ereg("[a-z][a-z0-9\_]+",$array["ctrl_init"]))
			{
				error("控制层仅限字母，数字及下划线且要求必须是字母开头",site_url("ctrl,set"));
			}
			if(!file_exists(APP."control/".$array["ctrl_init"].".php"))
			{
				error("没有找到控制层文件:(",site_url("ctrl,set"));
			}
		}
		else
		{
			sys_popedom($this->module_sign.":modify","tpl");
			$rs = $this->module_m->get_one($id);
			$array["ctrl_init"] = $rs["ctrl_init"];
		}
		$array["taxis"] = $this->trans_lib->int("taxis");//排序
		if($array["ctrl_init"] == "list")
		{
			$array["if_cate"] = $this->trans_lib->int("if_cate");//是否分类支持
			$array["if_point"] = $this->trans_lib->int("if_point");//点数
			$array["if_thumb"] = $array["if_thumb_m"] = 0;
			$if_thumb = $this->trans_lib->int("if_thumb");
			if($if_thumb)
			{
				$array["if_thumb"] = 1;
				$array["if_thumb_m"] = $if_thumb == 2 ? 1 : 0;
			}
			$array["inpic"] = $this->trans_lib->safe("inpic");//读取默认图片
			$array["if_propety"] = $this->trans_lib->int("if_propety");
			$array["if_biz"] = $this->trans_lib->int("if_biz");//是否支持电子商务
			$array["if_url_m"] = $this->trans_lib->int("if_url_m");//第三方链接地址是否必填
			$array["insearch"] = $this->trans_lib->int("insearch");
			$array["if_content"] = $this->trans_lib->checkbox("if_content");//列表读内容
			$array["if_email"] = $this->trans_lib->int("if_email");//邮件通知
			$array["psize"] = $this->trans_lib->int("psize");//默认分页数量
			$array["if_subtitle"] = $this->trans_lib->int("if_subtitle");//是否启用副标题功能
			$array["tplset"] = $this->trans_lib->safe("tplset");//设定展示格式
			$array["title_nickname"] = $this->trans_lib->safe("title_nickname");
			$array["subtitle_nickname"] = $this->trans_lib->safe("subtitle_nickname");
			$array["sign_nickname"] = $this->trans_lib->safe("sign_nickname");
			$array["if_sign_m"] = $this->trans_lib->int("if_sign_m");//标识串是否必填
			$array["if_ext"] = $this->trans_lib->int("if_ext");
			$array["if_des"] = $this->trans_lib->int("if_des");
			$array["if_list"] = $this->trans_lib->int("if_list");
			$array["if_msg"] = $this->trans_lib->int("if_msg");
			$layout = $this->trans_lib->safe("layout");
			$array["layout"] = sys_id_string($layout);
		}
		else
		{
			$array["layout"] = "";
		}
		$array["ico"] = $this->trans_lib->safe("ico");//图标
		$array["small_pic"] = $this->trans_lib->safe("small_pic");//小图
		$array["medium_pic"] = $this->trans_lib->safe("medium_pic");//中图
		$array["big_pic"] = $this->trans_lib->safe("big_pic");//大图
		//更新时间：2011-07-20 20:36
		$array["if_hidden"] = $this->trans_lib->checkbox("if_hidden");//隐藏不在导航中体现
		$array["lock_identifier"] = $this->trans_lib->checkbox("lock_identifier");//隐藏不在导航中体现
		//读取权限配置信息
		$popedom = $this->trans_lib->safe("popedom");
		$array["popedom"] = $popedom ? implode(",",$popedom) : "";//收集权限
		$this->module_m->save($array,$id);
		Error("模块信息配置成功",site_url("ctrl"));
	}

	function chk_identifier_f($val="")
	{
		$exit = $val ? false : true;
		if(!$val)
		{
			$val = $this->trans_lib->safe("identifier");
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
		//判断这个标识符是否有被使用
		$if_used = $this->module_m->chk_identifier($val);
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
		if(!ereg("[a-z][a-z0-9\_]+",$val))
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

	//字段管理列表
	function fields_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			error("操作非法，没有指定ID",site_url("ctrl"));
		}
		sys_popedom($this->module_sign.":list","tpl");
		$this->tpl->assign("id",$id);
		//读取字段管理
		$rslist = $this->module_m->fields_index($id);
		$this->tpl->assign("rslist",$rslist);
		//读取当前模块的配置信息
		$rs = $this->module_m->get_one($id);
		$this->tpl->assign("rs",$rs);
		$this->tpl->display("ctrl/fields.html");
	}

	//更改状态
	function fields_status_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error: 操作非法，没有指定ID");
		}
		sys_popedom($this->module_sign.":check","ajax");
		$this->module_m->fields_status($id);
		exit("ok");
	}

	//添加或删除一个字段
	function fields_set_f()
	{
		$id = $this->trans_lib->int("id");
		if($id)
		{
			sys_popedom($this->module_sign.":modify","tpl");
			$rs = $this->module_m->fields_one($id);
			$this->tpl->assign("rs",$rs);
			$this->tpl->assign("module_id",$rs["module_id"]);
			$module_id = $rs["module_id"];
		}
		else
		{
			sys_popedom($this->module_sign.":add","tpl");
			$module_id = $this->trans_lib->int("module_id");
			$this->tpl->assign("module_id",$module_id);
		}
		//读取模块信息
		$m_rs = $this->module_m->get_one($id);
		$this->tpl->assign("m_rs",$m_rs);
		$input_list = $this->module_m->input_type($_SESSION["sys_lang_id"]);
		$this->tpl->assign("input_list",$input_list);
		//获取联动组
		$this->load_model("datalink");
		$datalink = $this->datalink_m->all_group($_SESSION["sys_lang_id"]);
		$this->tpl->assign("datalink",$datalink);
		//关联模块
		$m_list = $this->module_m->all_module(1,"ctrl_init='list'");
		$this->tpl->assign("m_list",$m_list);
		//关联图片类型
		$this->load_model("gdtype");
		$gdlist = $this->gdtype_m->get_all();
		$this->tpl->assign("gdlist",$gdlist);
		$this->tpl->display("ctrl/fields_set.html");
	}

	//存储添加/编辑后的字段信息
	function fields_setok_f()
	{
		$id = $this->trans_lib->int("id");
		if($id)
		{
			sys_popedom($this->module_sign.":modify","tpl");
			$rs = $this->module_m->fields_one($id);
			$module_id = $rs["module_id"];
		}
		else
		{
			sys_popedom($this->module_sign.":add","tpl");
			$module_id = $this->trans_lib->int("module_id");
		}
		if(!$module_id)
		{
			error("操作非法",site_url("ctrl"));
		}
		if(!$id)
		{
			$array["module_id"] = $module_id;
			$array["identifier"] = $this->trans_lib->safe("identifier");
			if(!$array["identifier"])
			{
				error("标识串不允许为空",site_url("ctrl,fields_set","module_id=".$module_id));
			}
			//判断标识符是否使用了
			$chk_msg = $this->chk_identifier2_f($array["identifier"],$module_id);
			if(!$chk_msg)
			{
				error("标识符不符合系统要求",site_url("ctrl,fields_set","module_id=".$module_id));
			}
			$array["input"] = $this->trans_lib->safe("input");
			if($array["input"] == "edit")
			{
				$array["tbl"] = "c";
			}
			else
			{
				$array["tbl"] = "ext";
			}
		}
		$input_type = $id ? $rs["input"] : $array["input"];
		//
		$array["title"] = $this->trans_lib->safe("title");
		$array["if_post"] = $this->trans_lib->int("if_post");
		$array["if_guest"] = $this->trans_lib->int("if_guest");
		$array["sub_left"] = $this->trans_lib->safe("sub_left");
		$array["sub_note"] = $this->trans_lib->safe("sub_note");
		$array["width"] = $this->trans_lib->safe("width");
		$array["height"] = $this->trans_lib->safe("height");
		$array["default_val"] = $this->trans_lib->safe("default_val");
		if($input_type == "module")
		{
			$array["link_id"] = $this->trans_lib->safe("in_module_id");
		}
		else
		{
			$array["link_id"] = $this->trans_lib->int("link_id");
		}
		$array["list_val"] = $this->trans_lib->safe("list_val");
		$array["taxis"] = $this->trans_lib->int("taxis");
		$array["if_must"] = $this->trans_lib->int("if_must");
		$array["if_html"] = $this->trans_lib->int("if_html");
		$array["error_note"] = $this->trans_lib->safe("error_note");
		$array["show_html"] = $this->trans_lib->checkbox("show_html");
		$array["if_js"] = $this->trans_lib->checkbox("if_js");
		$array["if_search"] = $this->trans_lib->int("if_search");
		$this->module_m->fields_save($array,$id);
		error("字段数据配置成功",site_url("ctrl,fields","id=".$module_id));
	}

	function chk_identifier2_f($val="",$module_id=0)
	{
		$exit = $val ? false : true;
		if(!$val)
		{
			$val = $this->trans_lib->safe("identifier");
		}
		//取得模块ID
		if(!$module_id)
		{
			$module_id = $this->trans_lib->int("module_id");
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
		if(!$module_id)
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
		$if_used = $this->module_m->chk_identifier2($val,$module_id);
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
		if(!ereg("[a-z]+",$val))
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

	function fields_del_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error: 操作非法，没有指定ID");
		}
		sys_popedom($this->module_sign.":delete","tpl");
		//删除模块操作
		$this->module_m->fields_del($id);
		exit("ok");
	}

	//加入侧边栏
	function pladd_leftpanel_f()
	{
		$this->load_model("hotlink");
		$id = $this->trans_lib->safe("id");
		if(!$id)
		{
			exit("没有指定要加入的ID");
		}
		$id_list = sys_id_list($id,"intval");
		if(!$id_list || !is_array($id_list))
		{
			exit("没有指定要加入的ID");
		}
		foreach($id_list AS $key=>$value)
		{
			$value = trim($value);
			if(!$value)
			{
				continue;
			}
			$rs = $this->module_m->get_one($value);
			if(!$rs)
			{
				continue;
			}
			$linkurl = "{admin}?{c}=".$rs["ctrl_init"]."&module_id=".$value;
			$tmp_rs = $this->hotlink_m->get_one_url($linkurl,$_SESSION["sys_lang_id"]);
			if($tmp_rs)
			{
				continue;
			}
			$array = array();
			$array["langid"] = $_SESSION["sys_lang_id"];
			$array["title"] = $rs["title"];
			$array["ico"] = "list.gif";
			$array["linkurl"] = $linkurl;
			$array["status"] = 1;
			$array["taxis"] = $rs["taxis"];
			$this->hotlink_m->save($array);
		}
		exit("ok");
	}

	//取消侧边栏信息
	function pldel_leftpanel_f()
	{
		$this->load_model("hotlink");
		$id = $this->trans_lib->safe("id");
		if(!$id)
		{
			exit("没有指定要删除的热链");
		}
		$id_list = sys_id_list($id,"intval");
		if(!$id_list || !is_array($id_list))
		{
			exit("没有指定要删除的热链");
		}
		foreach($id_list AS $key=>$value)
		{
			$value = trim($value);
			if(!$value)
			{
				continue;
			}
			$rs = $this->module_m->get_one($value);
			if(!$rs)
			{
				continue;
			}
			$linkurl = "{admin}?{c}=".$rs["ctrl_init"]."&module_id=".$value;
			$tmp_rs = $this->hotlink_m->get_one_url($linkurl,$_SESSION["sys_lang_id"]);
			if(!$tmp_rs)
			{
				continue;
			}
			$this->hotlink_m->del($tmp_rs["id"]);
		}
		exit("ok");
	}
}
?>