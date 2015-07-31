<?php
require_once('email.class.php');
//##########################################
		  $smtpserver = "smtp.163.com";//SMTP服务器
		  $smtpserverport = "25";//SMTP服务器端口
		  $smtpusermail = "juniu888@163.com";//SMTP服务器的用户邮箱
		  $smtpemailto1 = "236341040@qq.com";//发送给谁
		  $smtpemailto2 = "cm@hkgbf.com";//发送给谁
		  $smtpuser = "juniu888@163.com";//SMTP服务器的用户帐号
		  $smtppass = "juniu8888";//SMTP服务器的用户密码
		  $mailsubject = $title;//邮件主题
		  $mailbody = iconv("UTF-8", "gbk", $content);//邮件内容
		  $mailtype = "HTML";//邮件格式（HTML/TXT）,TXT为文本邮件
##########################################
$smtp = new smtp($smtpserver,$smtpserverport,true,$smtpuser,$smtppass);//这里面的一个true是表示使用身份验证,否则不使用身份验证.
$smtp->debug = false;//是否显示发送的调试信息
//$smtp->sendmail($smtpemailto, $smtpusermail, $mailsubject, $mailbody, $mailtype);
$smtp->sendmail($smtpemailto1, $smtpusermail, $mailsubject, $mailbody, $mailtype);
$smtp->sendmail($smtpemailto2, $smtpusermail, $mailsubject, $mailbody, $mailtype);
?>