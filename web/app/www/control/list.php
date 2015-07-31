<?php
/***********************************************************
	Filename: app/www/control/list.php
	Note	: 内容列表页
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-16
***********************************************************/
class list_c extends Control
{
	var $subject;
	var $tplfile = "list";
	var $pageurl = "";
	var $idstring = "";
	var $pageid = 0;//当前分页ID号
	function __construct()
	{
		parent::Control();
		$this->load_model("cate");
		$this->load_model("list");
		$this->load_model("module");
	}

	function list_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		$cid = $this->trans_lib->int("cid");
		$cs = $this->trans_lib->safe("cs");
		$mid = $this->trans_lib->int("mid");
		$ms = $this->trans_lib->safe("ms");
		if(!$cid && !$cs && !$mid && !$ms)
		{
			sys_header(HOME_PAGE);
		}
		$pageid = $this->trans_lib->int(SYS_PAGEID);
		$this->pageid = $pageid;
		if($cid || $cs)
		{
			$this->cate_m->set_langid($_SESSION["sys_lang_id"]);
			$this->pageurl = site_url("list","cid=".$cid);
			if($cs && !$cid)
			{
				$cid = $this->cate_m->get_cid_from_code($cs);
			}
			if(!$cid)
			{
				sys_header(HOME_PAGE);
			}
			$this->index_cate($cid);
		}
		else
		{
			$this->pageurl = site_url("list","mid=".$mid);
			if($ms && !$mid)
			{
				$mid = $this->module_m->get_mid_from_code($ms);
			}
			if(!$mid)
			{
				sys_header(HOME_PAGE);
			}
			$this->index_module($mid);
		}
		return true;
	}

	//分类列表页或是封面页
	function index_cate($cid)
	{
		$rs = $this->cate_m->get_one($cid);
		$this->phpok_seo($rs);
		$pageurl = list_url($rs,0,true,false);//
		$module_rs = $this->module_m->get_one($rs["module_id"]);
		$this->tpl->assign("mid",$rs["module_id"]);
		$this->tpl->assign("cid",$cid);
		$this->tpl->assign("m_rs",$module_rs);
		$this->tpl->assign("cateid",$cid);//分类ID
		//指定模板文件
		if($rs["if_index"])
		{
			$this->tplfile = $rs["tpl_index"] ? $rs["tpl_index"] : "index_".$module_rs["identifier"];
			//判断封页面
			$create_html_type = "index";
		}
		else
		{
			$this->tplfile = $rs["tpl_list"] ? $rs["tpl_list"] : "list_".$module_rs["identifier"];
			$create_html_type = "list";
		}
		//分类未启用
		if(!$rs["status"])
		{
			sys_header(HOME_PAGE);
		}
		//分类下的模块未启用
		if(!$module_rs["status"])
		{
			sys_header(HOME_PAGE);
		}
		$rs["note"] = sys_format_content($rs["note"]);
		$this->tpl->assign("rs",$rs);
		//导航信息
		$this->load_cate_msg($rs);
		//通过递归获取子分类ID
		$array = array($cid);
		$this->cate_m->get_sonid_array($array,$cid);
		//判断权限
		$popedom = sys_user_popedom("read");//获取阅读权限
		if(!$popedom || !$popedom["category"])
		{
			error($this->lang["not_popedom"],site_url("usercp"));
		}
		if($popedom != "all")
		{
			$array = array_intersect($array,$popedom["category"]);
			if(!$array || count($array)<1)
			{
				error($this->lang["not_popedom"],site_url("usercp"));
			}
		}
		$idstring = sys_id_string($array);
		if(!$idstring) error($this->lang["not_popedom"],site_url("usercp"));
		$this->idstring = $idstring;
		//如果存在分类至主题
		if(!$module_rs["if_list"] && $module_rs["if_msg"])
		{
			//读取当前第一个主题
			$rslist = $this->cate_m->get_cate2sub($idstring,$rs["ordertype"]);
			$header_url = msg_url($rslist);
			sys_header($header_url);
		}
		//判断是否启用单页面信息
		if($rs["ifspec"])
		{
			$newtpl = $rs["tpl_file"] ? $rs["tpl_file"] : "msg_".$module_rs["identifier"]."_spec";
			$this->tpl->display($newtpl.".".$this->tpl->ext);
			exit;
		}
		//如果启用封面页功能，将停止下一步操作的执行
		if($rs["if_index"])
		{
			$this->tpl->display($this->tplfile.".".$this->tpl->ext);
		}
		else
		{
			//读取列表数据
			$this->list_m->set_cate($rs);
			$this->list_m->set_idstring($idstring);
			$this->list_m->set_module($module_rs);
			$total = $this->list_m->get_count_from_cate();//取得总数量
			$this->tpl->assign("total",$total);
			$psize = $rs["psize"] ? $rs["psize"] : SYS_PSIZE;
			$offset = $this->pageid>0 ? ($this->pageid-1)*$psize : 0;
			$this->page_lib->set_psize($psize);
			$pagelist = $this->page_lib->page_www($pageurl,$total,true);//分页数组
			$this->tpl->assign("pagelist",$pagelist);
			$rslist = $this->list_m->get_list_from_cate($offset,$psize);
			$this->tpl->assign("rslist",$rslist);
			$this->tpl->display($this->tplfile.".".$this->tpl->ext);
		}
	}

	function index_module($mid)
	{
		//判断权限
		$popedom = sys_user_popedom("read");//获取阅读权限
		if(!$popedom || !$popedom["module"])
		{
			error($this->lang["not_popedom"],site_url("usercp"));
		}
		if($popedom != "all" && !in_array($mid,$popedom["module"]))
		{
			error($this->lang["not_popedom"],site_url("usercp"));
		}

		$rs = $this->module_m->get_one($mid);
		$this->tpl->assign("mid",$mid);
		$this->tpl->assign("m_rs",$rs);
		$this->phpok_seo($rs);
		$this->tplfile = "list_".$rs["identifier"];
		if(!$rs["status"])
		{
			error($this->lang["module_is_close"],HOME_PAGE);
		}
		//判断是否有分类，且分类路至主题
		if(!$rs["if_list"] && $rs["if_msg"])
		{
			$msg_rs = $this->module_m->get_module_sub_one($mid);
			$header_url = msg_url($msg_rs);
			sys_header($header_url);
		}
		else
		{
			$list_rs = $this->module_m->get_module_cateid($mid);
			if($list_rs)
			{
				$header_url = list_url($list_rs);
				sys_header($header_url);
				exit;
			}
			$sitetitle = $rs["title"];
			$this->tpl->assign("sitetitle",$sitetitle);
			$leader[0] = array("title"=>$rs["title"]);
			$this->tpl->assign("leader",$leader);
			//读取列表信息
			$pageid = $this->trans_lib->int(SYS_PAGEID);
			$pageurl = module_url($rs,0,true,false);
			//读取列表数据
			$this->list_m->set_module($rs);
			$this->list_m->langid($_SESSION["sys_lang_id"]);
			$total = $this->list_m->get_count_from_cate();//取得总数量
			$this->tpl->assign("total",$total);
			$psize = $rs["psize"] ? $rs["psize"] : SYS_PSIZE;
			$offset = $pageid>0 ? ($pageid-1)*$psize : 0;
			$this->page_lib->set_psize($psize);
			$pagelist = $this->page_lib->page_www($pageurl,$total,true);//分页数组
			$this->tpl->assign("pagelist",$pagelist);
			$rslist = $this->list_m->get_list_from_cate($offset,$psize);
			$this->tpl->assign("rslist",$rslist);
			$this->tpl->display("list_".$rs["identifier"].".".$this->tpl->ext);
		}
	}

	function load_cate_msg($rs)
	{
		$array = array();
		$array[0] = $rs;
		if($rs["parentid"])
		{
			$this->cate_m->get_parent_array($array,$rs["parentid"]);
		}
		$rslist = array();
		$site_title_array = array();
		if(!$array) $array = array();
		foreach($array AS $key=>$value)
		{
			$tmp = array();
			$tmp["title"] = $value["cate_name"];
			$ext = $value["identifier"] ? "cs=".$value["identifier"] : "id=".$value["id"];
			$tmp["url"] = site_url("list",$ext);
			$rslist[$key] = $tmp;
			$site_title_array[] = $value["cate_name"];
		}
		$sitetitle = implode(" - ",$site_title_array);
		$this->tpl->assign("sitetitle",$sitetitle);
		//倒序数组
		krsort($rslist);
		unset($array);
		$this->tpl->assign("leader",$rslist);
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
		$this->tpl->assign("_sys",$_sys);
	}
}
?>