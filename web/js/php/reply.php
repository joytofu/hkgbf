<?php
/***********************************************************
	Filename: js/php/reply.php
	Note	: 回复点评操作
	Version : 3.0
	Author  : qinggan
	Update  : 2010-01-08
***********************************************************/
$id = $this->trans_lib->int("id");
if(!$id)
{
	sys_html2js('false');
}
$this->load_model("msg");
$this->load_model("reply");
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
//读取已回复主题
$rslist = $this->reply_m->get_best_list($id);
$this->tpl->assign("rslist",$rslist);
$count = $this->msg_m->get_count_reply($id);
$count = intval($count);
$this->tpl->assign("reply_count",$count);
$msg = $this->tpl->fetch("js_tpl/reply.".$this->tpl->ext);
sys_html2js($msg);
?>