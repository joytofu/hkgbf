<?php
header("content-Type: text/html; charset=Utf-8"); 
include('app/conn.php');
$cc=$_POST["cc"];
if ($cc==false){
	echo "非法操作";
	exit;
	}
$pass=$_POST["pass"];

$fullname=$_POST["fullname"];

$mobile=$_POST["mobile"];

$email=$_POST["email"];
$sfz=$_POST["sfz"];

mysql_select_db("db_cencencen", $webconn);
mysql_query("UPDATE juniu_list SET subtitle = '$pass' WHERE id =$cc");

mysql_query("UPDATE juniu_list_ext SET val = '$fullname' WHERE id =$cc and field='fullname'");
mysql_query("UPDATE juniu_list_ext SET val = '$mobile' WHERE id =$cc and field='mobile'");
mysql_query("UPDATE juniu_list_ext SET val = '$email' WHERE id =$cc and field='email'");
mysql_query("UPDATE juniu_list_ext SET val = '$sfz' WHERE id =$cc and field='sfz'");



mysql_close($webconn);
?>
<SCRIPT language="javascript">
window.alert("信息修改成功。");
window.location = ('index.php?c=list&ms=kehuuser&p=1');

</SCRIPT>

</body>

</html>

