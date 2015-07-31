<?php
session_start();
 require_once('app/conn.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>-----</title>
</head>

<body>
<?php

$fullname=$_POST["fullname"];
if($fullname==false){
	echo "非法操作";
	exit;
	}

$title="保险建议书在线索取";
$module_id=23; 
$cateid=$_POST["cate_id"];
$status=0;
$post_date=time("ymdhis");
mysql_select_db("db_cencencen", $webconn);
mysql_query("INSERT INTO juniu_list(module_id,title,post_date,status) VALUES ('$module_id','$title','$post_date','$status')");

$result=mysql_query("select * from juniu_list order by id asc");
while ($rs=mysql_fetch_array($result)){
	$rsid=$rs['id'];
	}


$fullname = $_POST["fullname"];
$aa="fullname";
mysql_query("INSERT INTO juniu_list_ext(id,field,val) VALUES ('$rsid','$aa','$fullname')");

$mobile = $_POST["mobile"];
$aa="mobile";
mysql_query("INSERT INTO juniu_list_ext(id,field,val) VALUES ('$rsid','$aa','$mobile')");

$bname = $_POST["bname"];
$aa="bname";
mysql_query("INSERT INTO juniu_list_ext(id,field,val) VALUES ('$rsid','$aa','$bname')");

$riqi = $_POST["riqi"];
$aa="riqi";
mysql_query("INSERT INTO juniu_list_ext(id,field,val) VALUES ('$rsid','$aa','$riqi')");

$sex = $_POST["sex"];
$aa="sex";
mysql_query("INSERT INTO juniu_list_ext(id,field,val) VALUES ('$rsid','$aa','$sex')");


$email = $_POST["email"];
$aa="email";
mysql_query("INSERT INTO juniu_list_ext(id,field,val) VALUES ('$rsid','$aa','$email')");

$mubiao = $_POST["mubiao"];
$aa="mubiao";
mysql_query("INSERT INTO juniu_list_ext(id,field,val) VALUES ('$rsid','$aa','$mubiao')");


$yusuan = $_POST["yusuan"];
$aa="yusuan";
mysql_query("INSERT INTO juniu_list_ext(id,field,val) VALUES ('$rsid','$aa','$yusuan')");

$mudi = $_POST["mudi"];
$aa="mudi";
mysql_query("INSERT INTO juniu_list_ext(id,field,val) VALUES ('$rsid','$aa','$mudi')");

$hbs = $_POST["hbs"];
$aa="hbs";
mysql_query("INSERT INTO juniu_list_ext(id,field,val) VALUES ('$rsid','$aa','$hbs')");

$content = $_POST["content"];
$aa="content";
mysql_query("INSERT INTO juniu_list_ext(id,field,val) VALUES ('$rsid','$aa','$content')");


mysql_close($webconn);

	echo "<script language=javascript>
window.alert('您的信息我们已经收到，我们会尽快处理联系您!');
window.location.href='index.php?c=msg&ts=suoqu';
</script>";

?>

</body>
</html>