<?php
/***********************************************************
	Filename: app/www/control/msg.php
	Note	: 内容详细页
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-16
***********************************************************/
class msg_c extends Control
{
	var $subject;
	var $tplfile = "msg";
	function __construct()
	{
		parent::Control();
		$this->load_model("cate");
		$this->load_model("msg");
		$this->load_model("module");
	}

	function msg_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		$id = $this->trans_lib->int("id");
		$ts = $this->trans_lib->safe("ts");
		//增加分页
		$pageid = $this->trans_lib->int(SYS_PAGEID);
		if($pageid<1) $pageid=1;
		$this->tpl->assign("pageid",$pageid);
		if(!$id && !$ts)
		{
			error($this->lang["msg_not_id"],site_url("index"));
		}
		if($id)
		{
			$rs = $this->msg_m->get_one($id,true,$pageid);
		}
		else
		{
			$rs = $this->msg_m->get_one_fromtype($ts,$_SESSION["sys_lang_id"],$pageid);
		}
		if(!$rs)
		{
			error($this->lang["msg_not_rs"],site_url("index"));
		}
		//判断阅读权限
		$popedom = sys_user_popedom("read");//获取阅读权限
		if($rs["cate_id"])
		{
			if(!$popedom || !$popedom["category"] || ($popedom != "all" && !in_array($rs["cate_id"],$popedom["category"])))
			{
				error($this->lang["not_popedom"],site_url("usercp"));
			}
		}
		else
		{
			if(!$popedom || !$popedom["module"] || ($popedom != "all" && !in_array($rs["module_id"],$popedom["module"])))
			{
				error($this->lang["not_popedom"],site_url("usercp"));
			}
		}

		//判断如果语言包不一样，自动刷新一遍
		if($rs["langid"] != $_SESSION["sys_lang_id"])
		{
			$_SESSION["sys_lang_id"] = $rs["langid"];
			sys_header(site_url("msg","id=".$rs["id"]));
		}
		//如果存在分类
		$this->subject = $rs["title"];
		$this->load_module_msg($rs["module_id"]);
		if($rs["cate_id"])
		{
			$this->load_cate_msg($rs["cate_id"]);
		}
		$this->phpok_seo($rs);
		$id = $rs["id"];
		$this->tpl->assign("id",$id);
		$this->tpl->assign("rs",$rs);
		$this->tpl->assign("cateid",$rs["cate_id"]);
		//读取自定义配置字段的数据信息
		if($rs["tplfile"])
		{
			$this->tplfile = $rs["tplfile"];
		}
		//更新点击率
		$this->msg_m->update_hits($rs["id"]);
		$this->tpl->display($this->tplfile.".".$this->tpl->ext);
	}

	function load_module_msg($mid)
	{
		$rs = $this->module_m->get_one($mid);
		if(!$rs)
		{
			return false;
		}
		$this->tpl->assign("mid",$mid);
		$this->tpl->assign("m_rs",$rs);
		//设置模块涉及到的文件
		$this->tplfile = "msg_".$rs["identifier"];//内容模块
		$array = array();
		$array[0]["title"] = $rs["title"];
		$array[1]["title"] = $this->subject;
		$this->tpl->assign("leader",$array);
		//设置头部信息
		$sitetitle = $this->subject;
		$this->tpl->assign("sitetitle",$sitetitle);
		return true;
	}

	function load_cate_msg($cateid)
	{
		$this->tpl->assign("cid",$cateid);
		$rs = $this->cate_m->get_one($cateid);
		if(!$rs)
		{
			return false;
		}
		if($rs["tpl_file"])
		{
			$this->tplfile = $rs["tpl_file"];
		}
		$this->phpok_seo($rs);
		$this->tpl->assign("cate_rs",$rs);
		$array = array();
		$array[0] = $rs;
		if($rs["parentid"])
		{
			$this->cate_m->get_parent_array($array,$rs["parentid"]);
		}
		$rslist = array();
		$site_title_array = array();
		foreach($array AS $key=>$value)
		{
			$tmp = array();
			$tmp["title"] = $value["cate_name"];
			$ext = $value["identifier"] ? "cs=".$value["identifier"] : "cid=".$value["id"];
			$tmp["url"] = site_url("list",$ext);
			$rslist[$key] = $tmp;
			$site_title_array[] = $value["cate_name"];
		}
		$sitetitle = $this->subject." - ".implode(" - ",$site_title_array);
		$this->tpl->assign("sitetitle",$sitetitle);
		//倒序数组
		krsort($rslist);
		$count = count($rslist);
		$rslist[$count]["title"] = $this->subject;
		unset($array);
		$this->tpl->assign("leader",$rslist);
	}

	function content_f()
	{
		$id = $this->trans_lib->int("id");
		$field = $this->trans_lib->safe("field");
		$pageid = $this->trans_lib->int("pageid");
		$msg = phpok_c($id,$field,$pageid,false);
		exit($msg);
	}

	//SEO优化
	function phpok_seo($rs)
	{
		$_sys = $this->sys_config;
		if($rs["keywords"])
		{
			$_sys["keywords"] = $rs["keywords"];
		}
		if($rs["description"])
		{
			$_sys["description"] = $rs["description"];
		}
		if($rs["seotitle"])
		{
			$_sys["seotitle"] = $rs["seotitle"];
		}
		$this->sys_config($_sys);
		$this->tpl->assign("_sys",$_sys);
	}
}
?>