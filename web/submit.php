<?php
require_once("app/conn.php");
header("Content-type:text/html;charset=utf-8");
session_start();

    $fullname=$_POST["fullname"];
    if($fullname==false){
        echo "非法操作";
        exit;
    }

//title从表单传入,判断module_id和cate_id

switch($_POST['title']){
    case "合伙人申请":
        $module_id=135;//合伙人/渠道申请模块
        $cate_id=236;
        break;

    case "渠道代理商申请":
        $module_id=135;//合伙人/渠道申请模块
        $cate_id=237;
        break;

    case "预约开户":
        $module_id=118;//预约开户模块
        $cate_id=240;
        break;

    case "保险建议书索取":
        $module_id=23;//保险建议书索取模块
        $cate_id=253;
        break;

    default:
        echo "不存在相关模块";
}

    $title=$_POST["title"];
    $status=0;
    $post_date=time("ymdhis");
    mysql_select_db("db_cencencen", $webconn);
       mysql_query("INSERT INTO juniu_list(module_id,cate_id,title,post_date,status) VALUES ('$module_id','$cate_id','$title','$post_date','$status')");
    $result=mysql_query("select * from juniu_list order by id asc");
    while ($rs=mysql_fetch_array($result)){
        $rsid=$rs['id'];
    }

   //循环写入数据库
   $items=$_POST;
   foreach($items AS $field=>$val){
       if($field!=="module_id"&&$field!=="cate_id"&&$field!=="title") {
           mysql_query("INSERT INTO juniu_list_ext(id,field,val) VALUES ('$rsid','$field','$val')");
       }
   }
    mysql_close($webconn);

echo "<script language=javascript>
window.alert('您的信息我们已经收到，我们会尽快联系您!');
window.location.href='index.php?c=list&ms=joinus';
</script>";

?>