<?php
/***********************************************************
	Filename: app/www/control/subject.php
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

		$m_rs = $this->module_m->get_one($id);
		$this->list_m->set_module($m_rs);
		$this->list_m->langid($_SESSION["sys_lang_id"]);
		//取得主题列表
		$pageurl = $this->url("subject,module");
		$pageurl.= "input=".rawurlencode($input_id)."&id=".$id."&";
		$iframe_id = $this->trans_lib->safe("iframe_id");
		if($iframe_id)
		{
			$this->tpl->assign("iframe_id",$iframe_id);
			$pageurl .= "iframe_id=".rawurlencode($iframe_id)."&";
		}
		$pageid = $this->trans_lib->int(SYS_PAGEID);
		$total_count = $this->list_m->get_count_from_cate();//取得总数量
		$pagelist = $this->page_lib->page($pageurl,$total_count);
		$psize = $m_rs["psize"] ? $m_rs["psize"] : SYS_PSIZE;
		$offset = $pageid>0 ? ($pageid-1) * $psize : 0;
		$rslist = $this->list_m->get_list_from_cate($offset,$psize);
		$this->tpl->assign("rslist",$rslist);
		$this->tpl->assign("pagelist",$pagelist);
		$this->tpl->display("open/subject_module.".$this->tpl->ext);
	}

	function ajax_module_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error");
		}
		$this->load_model("msg");
		$rs = $this->msg_m->get_one($id);
		if(!$rs)
		{
			exit("error");
		}
		$array = array();
		$array["id"] = $rs["id"];
		$array["title"] = $rs["title"];
		exit($this->json_lib->encode($array));
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