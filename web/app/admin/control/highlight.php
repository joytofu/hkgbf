<?php
/***********************************************************
	Filename: app/admin/control/highlight.php
	Note	: 菜单导航高亮管理
	Version : 3.0
	Author  : qinggan
	Update  : 2010-09-25
***********************************************************/
class highlight_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("module");
		$this->load_model("cate");
		$this->load_model("list");
	}

	function highlight_c()
	{
		$this->__construct();
	}

	//读取列表
	function index_f()
	{
		exit("ERROR!");
	}

	function module_f()
	{
		$condition = array();
		$condition["langid"] = $_SESSION["sys_lang_id"];
		$condition["ctrl_init"] = "list";
		$condition["array"]["if_list"] = 1;
		$condition["array"]["if_msg"] = 1;
		$pageid = $this->trans_lib->int(SYS_PAGEID);
		$rslist = $this->module_m->get_list($pageid,$condition);
		$this->tpl->assign("rslist",$rslist);
		$total = $this->module_m->get_count();//读取模块总数
		$page_url = $this->url("highlight,module");
		$pagelist = $this->page_lib->page($page_url,$total);
		$this->tpl->assign("pagelist",$pagelist);
		$this->tpl->assign("type","module");
		$id = $this->trans_lib->int("id");
		$this->tpl->assign("id",$id);
		$this->tpl->display("highlight/module.html");
	}

	function cate_f()
	{
		$this->cate_m->langid($_SESSION["sys_lang_id"]);
		$this->cate_m->get_all();
		$this->cate_m->format_list(0,0);
		$catelist = $this->cate_m->flist();
		if(!is_array($catelist)) $catelist = array();
		foreach($catelist AS $key=>$value)
		{
			$value["space"] = "";
			for($i=0;$i<$value["level"];$i++)
			{
				$value["space"] .= "　　";
			}
			$catelist[$key] = $value;
		}
		$this->tpl->assign("catelist",$catelist);
		$id = $this->trans_lib->safe("id");
		$idlist = array();
		if($id)
		{
			$idlist = sys_id_list($id);
		}
		$this->tpl->assign("idlist",$idlist);
		$this->tpl->display("highlight/cate.html");
	}

	function subject_f()
	{
		$page_url = $this->url("highlight,subject");
		$pageid = $this->trans_lib->int(SYS_PAGEID);
		$condition = "l.langid='".$_SESSION["sys_lang_id"]."' ";
		$condition.= " AND m.ctrl_init='list' AND m.if_msg='1' ";
		$keywords = $this->trans_lib->safe("keywords");
		if($keywords)
		{
			$condition .= " AND l.title LIKE '%".$keywords."%' ";
			$page_url .= "keywords=".rawurlencode($keywords)."&";
		}
		$rslist = $this->list_m->get_link($pageid,$condition);
		$this->tpl->assign("rslist",$rslist);
		$total_count = $this->list_m->get_link_count($condition);//取得总数
		$pagelist = $this->page_lib->page($page_url,$total_count);
		$this->tpl->assign("pagelist",$pagelist);
		$id = $this->trans_lib->int("id");
		$this->tpl->assign("id",$id);
		$this->tpl->display("highlight/subject.html");
	}
}
?>