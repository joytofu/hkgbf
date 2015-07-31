<?php
header("content-Type: text/html; charset=Utf-8"); 
 require_once('app/conn.php');

//验证码
if(isset($_REQUEST['ac'])){
    session_start();
    if(strtolower($_REQUEST['ac'])!==strtolower($_SESSION['authcode'])){
        echo "<script>alert('验证码错误，请重新输入');history.back();</script>";
        exit();
    }

}


$names=$_POST["username"];
$pass=$_POST["password"];
$cc=$_POST["cc"];

if($cc==1){
$result=mysql_query("select * from juniu_list where cate_id =188 and title='".$names."'",$webconn);
}

elseif($cc==2){
$result=mysql_query("select * from juniu_list where cate_id =189 and title='".$names."'",$webconn);
}
elseif($cc==3){
$result=mysql_query("select * from juniu_list where cate_id =190 and title='".$names."'",$webconn);
}
else{
	echo "非法操作";
	exit;
	}
	
$rs=mysql_fetch_array($result);
if ($rs==false){
	echo "<script language='javascript'>alert('此用户名不存在！');history.back();</script>";
exit;
	}
	else{
		
		if ($rs["status"]==0){
	echo "<script language='javascript'>alert('您的账号未审核或被限制，请联系管理员！');history.back();</script>";
exit;
	}
		
		if($rs["subtitle"]==$pass){
			session_start();
			$_SESSION[adminname]=$names;
			
			header("location:index.php?c=msg&ts=deal&hid=$rs[id]&username=$names&cc=$cc");
			exit;
			 }    
			else{
					echo "<script language='javascript'>alert('密码输入错误！');history.back();</script>";
                    exit;
				}
		}
		

?> 
</body>
</html>
