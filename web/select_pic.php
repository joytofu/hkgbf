<?php
error_reporting(E_ALL & ~E_NOTICE); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>上传附件</title>
  <script type="text/javascript">
 function selectvalue(values)
 {
  opener.document.getElementById('values').value=values;
  window.close();
 }
  </script>
</head>
<body>
<div  style="text-align:center; padding-top:20px; border:1px solid #CCC; margin:2px auto; width:400px; overflow:hidden; padding-bottom:20px; background:#f1f1f1">
<form id="form1" name="form1" method="POST" enctype="multipart/form-data" action="?oo=1">
<input type="hidden" name="picname" value="<?php echo $picname;?>">
        <input type="hidden" name="formname" value="<?php echo $formname;?>">

          请上传附件：
            <input name="uppic" id="uppic" type="file"  style="width:200;border:1 solid #9a9999; font-size:9pt; background-color:#ffffff" size="17">
<input type="submit" value="上传" style="width:30;border:1 solid #9a9999; font-size:9pt; background-color:#ffffff" size="17"></td>

</form>

<?php
$oo=$_GET["oo"];
if ($oo==true){
$date = date(ymdhis);
//echo($date);
//if ((($_FILES["uppic"]["type"] == "image/gif")
//|| ($_FILES["uppic"]["type"] == "image/jpeg")
//|| ($_FILES["uppic"]["type"] == "image/pjpeg")
//|| ($_FILES["uppic"]["type"] == "image/zip")
//)
//&& ($_FILES["uppic"]["size"] < 2000000))
//{
if ($_FILES["uppic"]["error"] > 0)
    {
    echo "Return Code: " . $_FILES["uppic"]["error"] . "<br />";
    }
else
    {
		
			if( $_FILES["uppic"]["type"]=="text/html"){
		echo "您上传的文件格式有误，请重新上传";
		exit;
		}
		if($_FILES["uppic"]["size"]/1024>5120){
			
			echo "文件大小不能超过1MB".$_FILES["uppic"]["size"];
			exit;
			}
		
    //echo "Upload: " . $_FILES["uppic"]["name"] . "<br />";
    //echo "Type: " . $_FILES["uppic"]["type"] . "<br />";
    //echo "Size: " . ($_FILES["uppic"]["size"] / 1024) . " Kb<br />";
    //echo "Temp file: " . $_FILES["uppic"]["tmp_name"] . "<br />";
   $uptype = explode(".", $_FILES["uppic"]["name"]);
   $newname = $date.".".$uptype[1];
   //echo($newname);
   $_FILES["uppic"]["name"] = $newname;

    if (file_exists("upload/" . $_FILES["uppic"]["name"]))
      {
      echo $_FILES["uppic"]["name"] . " already exists. ";
      }
    else
      {
      $_FILES["uppic"]["name"] = 
      move_uploaded_file($_FILES["uppic"]["tmp_name"],
      "upload/" . $_FILES["uppic"]["name"]);

      }
    }
//}
//else
//{
//echo "Invalid file";
//}

?>
<?php }?>
<P align="center">
<?php if($newname==true) {
	
?>


 <input type="radio" name="radio" id="selectlist" value="<?php echo $newname;?>"  onclick="selectvalue(this.value)"/>选择此文件<br /><br />

<?php 

}?>
</P>
</div>
</body>
</html>