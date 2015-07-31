<?php
header("content-Type: text/html; charset=Utf-8"); 
include('app/conn.php');

$user_name=$_POST["name"];
$pass=$_POST["newpass"];

$fullname=$_POST["fullname"];

$mobile=$_POST["mobile"];
$sfz=$_POST["sfz"];

$email=$_POST["email"];

$cate=$_POST["cate"];
if($cate==false){
	echo "非法操作";
	exit;
	}

$result=mysql_query("select * from juniu_list where module_id=119 and title='".$user_name."'",$webconn);

$rs=mysql_fetch_array($result);

if ($rs==true){

	echo "<script language='javascript'>alert('此用户名已经存在，请换一个用户名！');history.back();</script>";

exit;

	}

	else{

$module_id=119; 
if($cate=="kehu"){
$cate_id=189;
}
elseif($cate=="huoban"){
	
	$cate_id=188;
	}
	else{
		$cate_id=190;
		}
$status=0;
$post_date=time("ymdhis");
mysql_select_db("db_cencencen", $webconn);
mysql_query("INSERT INTO juniu_list(module_id,title,subtitle,post_date,status,cate_id,youxiang) VALUES ('$module_id','$user_name','$pass','$post_date','$status','$cate_id','$email')");

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


$email = $_POST["email"];
$aa="email";
mysql_query("INSERT INTO juniu_list_ext(id,field,val) VALUES ('$rsid','$aa','$email')");
mysql_close($webconn);

		}

		

		

?> 

<SCRIPT language="javascript">
window.alert("注册成功，等待审核。");
window.location = ('index.php');

</SCRIPT>";

</body>

</html>

