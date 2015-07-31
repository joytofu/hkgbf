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

$title="在线预约开户";
$module_id=118; 
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

$sfz = $_POST["sfz"];
$aa="sfz";
mysql_query("INSERT INTO juniu_list_ext(id,field,val) VALUES ('$rsid','$aa','$sfz')");

$riqi = $_POST["riqi"];
$aa="riqi";
mysql_query("INSERT INTO juniu_list_ext(id,field,val) VALUES ('$rsid','$aa','$riqi')");

$email = $_POST["email"];
$aa="email";
mysql_query("INSERT INTO juniu_list_ext(id,field,val) VALUES ('$rsid','$aa','$email')");

$address = $_POST["address"];
$aa="address";
mysql_query("INSERT INTO juniu_list_ext(id,field,val) VALUES ('$rsid','$aa','$address')");

$xueli = $_POST["xueli"];
$aa="xueli";
mysql_query("INSERT INTO juniu_list_ext(id,field,val) VALUES ('$rsid','$aa','$xueli')");


$jingli = $_POST["jingli"];
$aa="jingli";
mysql_query("INSERT INTO juniu_list_ext(id,field,val) VALUES ('$rsid','$aa','$jingli')");

$content = $_POST["content"];
$aa="content";
mysql_query("INSERT INTO juniu_list_ext(id,field,val) VALUES ('$rsid','$aa','$content')");


mysql_close($webconn);

	echo "<script language=javascript>
window.alert('您的信息我们已经收到，我们会尽快处理联系您!');
window.location.href='index.php?c=list&ms=kaihui';
</script>";

?>

</body>
</html>