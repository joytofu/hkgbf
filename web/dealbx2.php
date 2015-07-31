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

	
$content = "<strong>预约日期</strong>：".$_POST["yyrq"]."<br /><strong>预约内容</strong>：".$_POST["yynr"]."<br /><strong>联系人</strong>：".$_POST["fullname"]."<br /><strong>联络电话</strong>：".$_POST["mobile"]."<br /><strong>联系地址</strong>：".$_POST["address"]."<br /><strong>备注信息</strong>：<br />".$_POST["content"];


$title="其他预约";


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

$aa="content";
mysql_query("INSERT INTO juniu_list_c(id,field,val) VALUES ('$rsid','$aa','$content')");

require_once('email.php');

	echo "<script language=javascript>
window.alert('您的信息我们已经收到，我们会尽快联系您!');
window.location.href='index.php?c=list&ms=hehbuser&p=2';
</script>";

}else{
	echo "<p align=center><br />非法操作！</p>";
	
	}

?>
</body>
</html>