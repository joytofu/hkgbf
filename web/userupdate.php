
<?php require_once('app/conn.php'); ?>

<?php 
$id=$_GET["id"];
if($id){
	
	$jb=$_POST["jb"];
	$ygzj=$_POST["ygzj"];


mysql_select_db("db_cencencen", $webconn);

	mysql_query("UPDATE jinghainew_user SET jb = '$jb',ygzj = '$ygzj' WHERE id =$id");
	
mysql_close($webconn);
echo "<script language=javascript>
window.alert('修改成功!');
window.location.href='websitesystem.php?c=user';
</script>";
}
?>