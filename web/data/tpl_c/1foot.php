<?php if(!defined('PHPOK_SET')){die('<h3>Error...</h3>');}?><?php $dibu = phpok_msg("contact");?>
<img id="none" src="<?php echo nl2br($dibu[ewm]);?>" width="150">
<div class="index_1">
<?php $dibu = phpok_msg("contact");?>
  <a style="cursor:pointer" onmouseover="gg1()" onmouseout="gg2()"><img src="tpl/www/images/w1.jpg"></a>
  <a href="<?php echo nl2br($dibu[weibo]);?>" target="_blank"><img style="margin:0 50px;" src="tpl/www/images/w2.jpg"></a>
 <a href="tencent://message/?uin=<?php echo nl2br($dibu[qq]);?>&amp;Site=im.qq.com&amp;Menu=yes" onfocus="blur()" target="_blank"><img src="tpl/www/images/w3.jpg"></a>

</div>
<script>
function gg1(){
x=document.getElementById("none")  //找到元素
x.style.display="block";
}

function gg2(){
x=document.getElementById("none")  //找到元素
x.style.display="none";
}

</script>

<div class="index_2">全国热线：<?php echo nl2br($dibu[tel]);?></div>


<div class="foot" style="margin-bottom:100px;">
 <?php $dibu = phpok_msg("dibu");?>
<?php echo nl2br($dibu[note]);?> 

</div>

<div id="bottom" style="position:fixed; bottom:0; left:0; width:100%; z-index:99999">
  <div class="bottom">
     <?php if($_SESSION["username"]){?><a href="index.php?c=msg&id=<?php echo $_SESSION["userid"];?>"><img src="tpl/www/images/f11.jpg"></a><?php }else{ ?><a href="index.php?c=list&cs=kehu&login"><img src="tpl/www/images/f1.jpg"></a><?php } ?>
     <a href="index.php?c=msg&ts=down&"><img src="tpl/www/images/f2.jpg"></a>
     <a href="index.php?c=list&ms=kaihui"><img src="tpl/www/images/f3.jpg"></a>
     <a href="index.php?c=msg&ts=suoqu"><img src="tpl/www/images/f4.jpg"></a>
     <a href="index.php?c=msg&ts=contact"><img src="tpl/www/images/f5.jpg"></a>
  </div>
</div>

</body>
</html>