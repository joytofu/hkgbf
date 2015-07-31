<?php
/***********************************************************
	Filename: app/admin/control/phpok.php
	Note	: 数据调用配置页，支持自定义代码
	Version : 3.0
	Author  : qinggan
	Update  : 2009-12-30
***********************************************************/
class phpok_c extends Control
{
	var $module_sign = "phpok";
	function __construct()
	{
		parent::Control();
		$this->load_model("module");//读取模块列表
		$this->load_model("phpok");
		//加载核心参数
		include_once(LIBS."phpok.sys.php");
	}

	function phpok_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		sys_popedom($this->module_sign.":list","tpl");
		$this->phpok_m->langid($_SESSION["sys_lang_id"]);
		$pageid = $this->trans_lib->int(SYS_PAGEID);
		$keywords = $this->trans_lib->safe("keywords");
		$page_url = site_url("phpok");
		$condition = "";
		if($keywords)
		{
			$this->tpl->assign("keywords",$keywords);
			$page_url.="keywords=".rawurlencode($keywords)."&";
			$condition = " title LIKE '%".$keywords."%' OR note LIKE '%".$keywords."%' ";
		}
		$rslist = $this->phpok_m->get_list($pageid,$condition);
		$this->tpl->assign("rslist",$rslist);
		$count = $this->phpok_m->get_count($condition);
		$this->tpl->assign("total",$count);
		$pagelist = $this->page_lib->page($page_url,$count);
		$this->tpl->assign("pagelist",$pagelist);
		$this->tpl->display("phpok/list.html");
	}

	function chk_f()
	{
		$sign = $this->trans_lib->safe("sign");
		if(!$sign)
		{
			exit("error: 标识串为空");
		}
		//检测标识串是否符合要求
		if(!ereg("[a-z][a-z0-9\_]+",$sign))
		{
			exit("error: 标识串仅限小写英文字母，数字及下划线，且第一位必须是字母");
		}
		//检测唯一性
		$rs = $this->phpok_m->chksign($sign);
		if($rs)
		{
			exit("error: 标识串已被使用，请返回修改");
		}
		else
		{
			exit("ok");
		}
	}

	function set_f()
	{
		$this->load_model("cate");//读取分类列表
		$id = $this->trans_lib->int("id");
		if($id)
		{
			sys_popedom($this->module_sign.":modify","tpl");
			$rs = $this->phpok_m->get_one($id);
			$typeid = $rs["mid"].":".$rs["cid"];//合并模块ID和分类ID
			$rs["typetext"] = $this->trans_lib->html_fck($rs["typetext"]);
			$this->tpl->assign("rs",$rs);
		}
		else
		{
			sys_popedom($this->module_sign.":add","tpl");
			$typeid = "0:0";
		}
		$this->cate_m->langid($_SESSION["sys_lang_id"]);
		$this->cate_m->get_all();
		$this->cate_m->format_list(0,0);
		$catelist = $this->cate_m->flist();
		if(!$catelist) $catelist = array();
		foreach($catelist AS $key=>$value)
		{
			$value["space"] = "";
			for($i=0;$i<$value["level"];$i++)
			{
				$value["space"] .= "　　";
			}
			$catelist[$key] = $value;
		}
		$module_list = $this->module_m->all_module();//取得模块列表
		$cate_html = "<select name='typeid' id='typeid' onchange='to_fields(this.value)'>";
		$cate_html.= "<option value='0:0'>不限制，根据参数调用变量获取</option>";
		$cate_html.= "<optgroup label='选择模块'>";
		$new_mlist = array();
		foreach($module_list AS $key=>$value)
		{
			if($value["ctrl_init"] == "list")
			{
				$new_mlist[] = $value;
				$cate_html.= "<option value='".$value["id"].":0'";
				if($typeid == $value["id"].":0")
				{
					$cate_html.= " selected";
				}
				$cate_html.= ">".$value["title"];
				if(!$value["status"])
				{
					$cate_html .= "（已停用）";
				}
				$cate_html .= "</option>";
			}
		}
		$cate_html.= "</optgroup>";
		$cate_html.= "<optgroup label='选择分类'>";
		$this->tpl->assign("mlist",$new_mlist);
		foreach($catelist AS $key=>$value)
		{
			$cate_html.= "<option value='".$value["module_id"].":".$value["id"]."'";
			if($typeid == $value["module_id"].":".$value["id"])
			{
				$cate_html.= " selected";
			}
			$cate_html.= ">【".$value["title"]."】".$value["space"].$value["cate_name"];
			if(!$value["status"])
			{
				$cate_html .= "（已停用）";
			}
			$cate_html.= "</option>";
		}
		$cate_html.= "</optgroup>";
		$cate_html.= "</select>";
		$this->tpl->assign("cate_html",$cate_html);
		//关联图片类型
		$this->load_model("gdtype");
		$gdlist = $this->gdtype_m->get_all();
		$this->tpl->assign("gdlist",$gdlist);
		//排序类型
		$orderlist["post_desc"] = "刚刚发布的排前面";
		$orderlist["post_asc"] = "以前发布的排前面";
		$orderlist["modify_desc"] = "最后修改的排前面";
		$orderlist["modify_asc"] = "较早修改的排前面";
		$orderlist["reply_desc"] = "最新回复的排前面";
		$orderlist["reply_asc"] = "以前回复的排前面";
		$orderlist["hits_desc"] = "访问量高的排前面";
		$orderlist["hits_asc"] = "访问量低的排前面";
		$orderlist["rand"] = "随机排序（慎用）";
		$this->tpl->assign("orderlist",$orderlist);
		$_hotid = $this->trans_lib->int("_hotid");
		if($_hotid)
		{
			$this->tpl->assign("_hotid",$_hotid);
		}
		$this->tpl->display("phpok/set.html");
	}

	function chk_identifier_f()
	{
		$val = $this->trans_lib->safe("identifier");
		if(!$val)
		{
			exit("error: 标识串为空");
		}
		//判断这个标识符是否有被使用
		$this->phpok_m->langid($_SESSION["sys_lang_id"]);
		$if_used = $this->phpok_m->chk_identifier($val);
		if($if_used)
		{
			exit("error: 标识串已经被使用");
		}
		//判断标识串是否符合要求
		if(!ereg("[a-z][a-z0-9\_]+",$val))
		{
			exit("error: 标识串仅限小写英文字母，数字及下划线，且第一位必须是字母");
		}
		exit("ok");
	}

	//存储分类信息
	function setok_f()
	{
		$id = $this->trans_lib->int("id");
		$intype = $this->trans_lib->safe("intype");
		$array = array();
		$array["title"] = $this->trans_lib->safe("title");
		if($id)
		{
			sys_popedom($this->module_sign.":modify","tpl");
		}
		else
		{
			sys_popedom($this->module_sign.":add","tpl");
			$array["identifier"] = $this->trans_lib->safe("identifier");
			$array["langid"] = $_SESSION["sys_lang_id"];
		}
		$array["note"] = $this->trans_lib->safe("note");//备注
		$array["vartext"] = $this->trans_lib->safe("vartext");//传递的参数
		//调用数据
		$maxcount = 1;
		$typetext = "";
		if($intype == "sql")
		{
			$maxcount = $this->trans_lib->int("sql_maxcount");
			$typetext = $this->trans_lib->html("sql_typetext");
		}
		elseif($intype == "sign")
		{
			$maxcount = $this->trans_lib->int("usr_maxcount");
		}
		elseif($intype == "html")
		{
			$maxcount = 1;
			$this->trans_lib->setting(true,true,true);
			$typetext = $this->trans_lib->html("typetext");
			$this->trans_lib->setting(false,false,false);
		}
		$typeid = $this->trans_lib->safe("typeid");
		if($typeid)
		{
			$type_array = explode(":",$typeid);
			$array["mid"] = intval($type_array[0]);
			$array["cid"] = intval($type_array[1]);
		}
		$array["intype"] = $intype;//调用类型
		$array["typetext"] = $typetext;//调用的内容
		$array["maxcount"] = $maxcount;
		$array["orderby"] = $this->trans_lib->safe("orderby");
		$array["inpic"] = $this->trans_lib->safe("inpic");
		$array["pic_required"] = $this->trans_lib->checkbox("pic_required");
		$array["attr"] = $this->trans_lib->safe("attr");
		$array["datatype"] = $this->trans_lib->safe("datatype");
		$array["extsign"] = $this->trans_lib->safe("extsign");
		//存储分类信息
		$this->phpok_m->save($array,$id);
		//判断是否接入地方
		$go_url = $this->url("phpok");
		$_hotid = $this->trans_lib->int("_hotid");
		if($_hotid)
		{
			$this->load_model("hotlink");
			$hot_rs = $this->hotlink_m->get_one($_hotid);
			$hot_rs["linkurl"] = str_replace("{admin}",HOME_PAGE,$hot_rs["linkurl"]);
			$hot_rs["linkurl"] = str_replace("{c}",$this->config->c,$hot_rs["linkurl"]);
			$hot_rs["linkurl"] = str_replace("{f}",$this->config->f,$hot_rs["linkurl"]);
			$hot_rs["linkurl"] = str_replace("{d}",$this->config->d,$hot_rs["linkurl"]);
			$go_url = $hot_rs["linkurl"];
			$go_url.= "&_hotid=".$_hotid."&";
		}
		error("信息添加/存储成功",$go_url);
	}

	function ajax_status_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error:没有指定ID");
		}
		sys_popedom($this->module_sign.":check","ajax");
		$rs = $this->phpok_m->get_one($id);
		$status = $rs["status"] ? 0 : 1;
		$this->phpok_m->set_status($id,$status);
		exit("ok");
	}

	function ajax_del_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error:没有指定ID");
		}
		sys_popedom($this->module_sign.":delete","ajax");
		$this->phpok_m->del($id);
		exit("ok");
	}
}
?>