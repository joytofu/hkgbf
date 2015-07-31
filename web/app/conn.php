<?php
$webconn=mysql_connect("localhost","a0424143501","91782000")or die("数据库服务器连接错误".mysql_error());
mysql_select_db("a0424143501",$webconn) or die("数据库访问错误".mysql_error());
mysql_query("set character set utf8");
mysql_query("set names 'utf8'");


?>