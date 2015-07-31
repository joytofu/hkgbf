<?php
error_reporting(E_ALL & ~E_NOTICE); 
?>
<?php
 require_once('app/conn.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>留言处理</title>

</head>
<body>
<?php

$cc=$_POST["cc"];
if($cc){
	$jtgj=$_POST["jtgj"];
	$fggk=$_POST["fggk"];
	if($jtgj=="飞机"){
		$jtgj=$jtgj.",航班编号：".$_POST["jtgj2"];
		}
		if($jtgj=="其他"){
		$jtgj=$_POST["jtgj3"];
		}
		if($fggk=="其他"){
		$fggk=$_POST["fggk2"];
		}
	
$content = "<strong>到港日期</strong>：".$_POST["dgrq"]."<br /><strong>离港日期</strong>：".$_POST["lgrq"]."<br /><strong>行程联络人</strong>：".$_POST["fullname"]."<br /><strong>联络电话</strong>：".$_POST["mobile"]."<br /><strong>预计过关时间</strong>：".$_POST["ggsj"]."<br /><strong>预计抵达保险公司的时间</strong>：".$_POST["ddsj"]."<br /><strong>签单保险公司及分部</strong>：".$_POST["bxgs"]."<br /><strong>总人数</strong>：".$_POST["zrs"]."<br /><strong>访港交通工具</strong>：".$jtgj."<br /><strong>访港关口</strong>：".$fggk."<br /><strong>开户银行</strong>：".$_POST["khyh"]."<br /><strong>银行名称</strong>：".$_POST["yhmc"]."<br /><strong>其他特别安排：</strong>".$_POST["qtap"]."<br /><strong>备注信息</strong>：<br />".$_POST["content"];

$title="保险签单预约";


$module_id=128; 
$cateid=$_POST["cate_id"];
$status=0;
$post_date=time("ymdhis");
mysql_select_db("db_cencencen", $webconn);
mysql_query("INSERT INTO juniu_list(module_id,title,post_date,status) VALUES ('$module_id','$title','$post_date','$status')");

$result=mysql_query("select * from juniu_list order by id asc");
while ($rs=mysql_fetch_array($result)){
	$rsid=$rs['id'];
	}


$values = $_POST["values"];
$aa="values";
mysql_query("INSERT INTO juniu_list_ext(id,field,val) VALUES ('$rsid','$aa','$values')");



$aa="content";
mysql_query("INSERT INTO juniu_list_c(id,field,val) VALUES ('$rsid','$aa','$content')");

require_once('email.php');

	echo "<script language=javascript>
window.alert('您的信息我们已经收到，我们会尽快联系您!');
window.location.href='index.php?c=list&ms=hehbuser&p=1';
</script>";

}else{
	echo "<p align=center><br />非法操作！</p>";
	
	}

?>
</body>
</html>