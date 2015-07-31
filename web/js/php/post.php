<?php
/***********************************************************
	Filename: js/php/post.php
	Note	: 发布信息
	Version : 3.0
	Author  : qinggan
	Update  : 2010-01-08
***********************************************************/
$this->load_model("post");
$this->load_model("module");
$this->load_model("cate");
$id = $this->trans_lib->int("id");
if($id)
{
	$rs = $this->post_m->get_one($id);
	$this->tpl->assign("rs",$rs);
	$this->tpl->assign("id",$id);
	$module_id = $rs["module_id"];
	$cate_id = $rs["cate_id"];
}
else
{
	$module_id = $this->trans_lib->int("module_id");
	$cate_id = $this->trans_lib->int("cate_id");
}
if(!$module_id)
{
	$ms = $this->trans_lib->safe("ms");
	if($ms)
	{
		$module_id = $this->module_m->get_mid_from_code($ms);
	}
	if(!$module_id)
	{
		sys_html2js('false');
	}
}
$this->tpl->assign("module_id",$module_id);

$m_rs = $this->module_m->get_one($module_id);
if(!$m_rs || !$m_rs["status"] || $m_rs["ctrl_init"] != "list")
{
	sys_html2js('false');
}
$this->tpl->assign("m_rs",$m_rs);
//读取内容
$ifcate = $m_rs["if_cate"] ? true : false;
$this->tpl->assign("ifcate",$ifcate);
if($ifcate)
{
	//$condition = $_SESSION["user_id"] ? "c.ifuser='1'" : "c.ifguest='1'";
	$this->cate_m->get_catelist($module_id,$condition);
	$cate_html = $this->cate_m->html_select("cate_id",$cate_id,$this->lang["category_select"]);
	$this->tpl->assign("cate_html",$cate_html);
}
$ext_list = $this->module_m->fields_index($module_id);
//$ext_list = $this->_load_ext_fields($module_id);//获取扩展信息
if($ext_list && is_array($ext_list) && count($ext_list)>0)
{
	$optlist = array();
	$this->load_lib("phpok_input");
	$extlist_must = $extlist_need = array();
	foreach($ext_list AS $key=>$value)
	{
		if(!$value["if_post"] && !$value["if_guest"])
		{
			continue;
		}
		$_field_name = $value["identifier"];
		$value["default_val"] = $rs[$_field_name] ? $rs[$_field_name] : $value["default_val"];
		$extlist = $this->phpok_input_lib->get_html($value);
		$extlist_must[] = $extlist;
		if($value["input"] == "opt")
		{
			$optlist[] = $value;
		}
		$ext_list[$key] = $value;
	}
	$this->tpl->assign("extlist_must",$extlist_must);
	$this->tpl->assign("optlist",$optlist);
	$this->tpl->assign("extlist",$ext_list);
}
$tplfile = "js_tpl/post_".$m_rs["identifier"];
$chk_tplfile = ROOT.$this->tpl->tpldir."/".$tplfile.".".$this->tpl->ext;
if(file_exists($chk_tplfile))
{
	$msg = $this->tpl->fetch($tplfile.".".$this->tpl->ext);
	//$this->tpl->display($tplfile.".".$this->tpl->ext);
}
else
{
	$msg = $this->tpl->fetch("js_tpl/post_set.".$this->tpl->ext);
}
sys_html2js($msg);
?>