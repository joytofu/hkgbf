<?php
/***********************************************************
	Filename: js/php/reply.php
	Note	: 回复点评操作
	Version : 3.0
	Author  : qinggan
	Update  : 2010-01-08
***********************************************************/
$type = $this->trans_lib->safe("type");
$id = $this->trans_lib->int("id");
if(!$id)
{
	sys_html2js('false');
}
$_act = $this->trans_lib->safe("_act");
$this->load_model("msg");
if($_act == "hits")
{
	if(!$_SESSION["hits"]) $_SESSION["hits"] = array();
	if(!in_array($id,$_SESSION["hits"]))
	{
		$this->msg_m->update_digg($id,$type);
	}
	else
	{
		sys_html2js('clicked');
	}
	$_SESSION["hits"][] = $id;
}
$rs = $this->msg_m->chk_reply_hits($id);
if(!$rs["ifhits"] && !$rs["ifreply"])
{
	sys_html2js('false');
}
$total = $rs["good"] + $rs["bad"];
if($total>0)
{
	$rs["good_percent"] = round($rs["good"]/$total,4) * 100 ."%";
	$rs["bad_percent"] = round($rs["bad"]/$total,4) * 100 ."%";
}
else
{
	$rs["good_percent"] = "0%";
	$rs["bad_percent"] = "0%";
}
$this->tpl->assign("rs",$rs);
$this->tpl->assign("id",$id);
//判断主题所在的模块是否支持回复功能
$msg = $this->tpl->fetch("js_tpl/post_digg.".$this->tpl->ext);
sys_html2js($msg);
//$this->tpl->display("js_tpl/post_digg.".$this->tpl->ext);
?>