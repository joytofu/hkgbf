<?php
/***********************************************************
	Filename: js/php/logout.php
	Note	: 退出会员操作
	Version : 3.0
	Author  : qinggan
	Update  : 2010-01-08
***********************************************************/
session_destroy();
sys_html2js('ok');
?>