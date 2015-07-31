<?php
/**
*$to_email 发送的人
*$title  邮箱标题
*$content 有些内容
*/
function send_email($to_email,$title,$content){
	  require_once("email.class.php");
	  		 //##########################################

		  $smtpserver = "smtp.163.com";//SMTP服务器
		  $smtpserverport ="25";//SMTP服务器端口
		  $smtpusermail ="morton991@163.com";//SMTP服务器的用户邮箱
		  $smtpemailto = $to_email;//发送给谁
		  $smtpuser = "morton991@163.com";//SMTP服务器的用户帐号
		  $smtppass = "userpeng123";//SMTP服务器的用户密码
		  $mailsubject = iconv("UTF-8", "gbk", $title);//邮件主题
		  $mailbody = iconv("UTF-8", "gbk", $content);//邮件内容
		  $mailtype = "HTML";//邮件格式（HTML/TXT）,TXT为文本邮件
		##########################################
		$smtp = new smtp($smtpserver,$smtpserverport,true,$smtpuser,$smtppass);//这里面的一个true是表示使用身份验证,否则不使用身份验证.
		$smtp->debug = false;//是否显示发送的调试信息
		$smtp->sendmail($smtpemailto, $smtpusermail, $mailsubject, $mailbody, $mailtype);
}

send_email('1337330816@qq.com','测试的11111111','测试的');

?>