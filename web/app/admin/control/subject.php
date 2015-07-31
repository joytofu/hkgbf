<?php
/***********************************************************
	Filename: app/admin/control/subject.php
	Note	: 主题列表，用于调用
	Version : 3.0
	Author  : qinggan
	Update  : 2011-04-02
***********************************************************/
class subject_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("module");
		$this->load_model("list");
	}

	function subject_c()
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
		$input_id = $this->trans_lib->safe("input");
		if(!$input_id)
		{
			error("未定义标识串！");
		}
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			error("未定义模块！");
		}
		$this->tpl->assign("input_id",$input_id);
		$this->tpl->assign("id",$id);
		//取得主题列表
		$pageurl = $this->url("subject,module");
		$pageurl.= "input=".rawurlencode($input_id)."&id=".$id."&";
		//
		$this->list_m->set_condition("m.langid='".$_SESSION["sys_lang_id"]."'");//区分语言
		$this->list_m->set_condition("m.module_id='".$id."'");
		$pageid = $this->trans_lib->int(SYS_PAGEID);
		$rslist = $this->list_m->get_list($pageid);
		$this->tpl->assign("rslist",$rslist);
		$total_count = $this->list_m->get_count();//取得总数
		$pagelist = $this->page_lib->page($page_url,$total_count);
		$this->tpl->assign("pagelist",$pagelist);
		$this->tpl->display("subject/module.html");
	}

	function ajax_module_f()
	{
		$id = $this->trans_lib->safe("id");
		if(!$id)
		{
			exit("error");
		}
		$id = sys_id_string($id,",","intval");
		//取得主题列表
		$rslist = $this->list_m->get_list_from_id($id,"id,title");
		if(!$rslist)
		{
			exit("error");
		}
		exit($this->json_lib->encode($rslist));
	}

	function ajax_file_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error");
		}
		$this->load_model("upfile");//读取附件操作类
		$rs = $this->upfile_m->get_one($id);
		if(!$rs)
		{
			exit("error");
		}
		$array = array();
		$array["id"] = $rs["id"];
		$array["title"] = $rs["title"];
		exit($this->json_lib->encode($array));
	}

}
?>