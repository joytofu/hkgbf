<?php
function checkLogined(){
    if($_SESSION['username']==""&&$_COOKIE['username']==""){
        alertMes("您还没有登录，请先登录","index.php?c=list&cs=kehu&login");
        return false;
    }

}

function alertMes($mes,$url){
echo "<script>alert('{$mes}');</script>";
    echo "<script>window.location.href='{$url}';</script>";
}
?>