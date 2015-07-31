<?php if(!defined('PHPOK_SET')){die('<h3>Error...</h3>');}?><?php $APP->tpl->p("head","","0");?>
 <div id="banner">
 <div id="flashBg" >
<div id="flashLine">
<div id="flash">
        <?php $kflist = phpok('picplayer');?>
	<?php $_i=0;$kflist[rslist]=(is_array($kflist[rslist]))?$kflist[rslist]:array();foreach($kflist[rslist] AS  $key=>$value){$_i++; ?>
<a href="<?php echo $value[link_url];?>" <?php if($value[target]){?> target="_blank" <?php } ?> id="flash<?php echo $key+1;?>"  style="background-image: url(<?php echo $value[picture];?>);<?php if($key==0){?>display: block; <?php }else{ ?> display: none;<?php } ?> background-position: 50% 0%; background-repeat: no-repeat no-repeat; " name="#0b0b0b"></a>		
    	<?php } ?>

  <div class="flash_bar">
	

<?php $kflist = phpok('picplayer');?>
	<?php $_i=0;$kflist[rslist]=(is_array($kflist[rslist]))?$kflist[rslist]:array();foreach($kflist[rslist] AS  $key=>$value){$_i++; ?>
    <div <?php if($key==0){?>class="dq" <?php }else{ ?> class="no"<?php } ?>  id="f<?php echo $key+1;?>" onclick="changeflash(<?php echo $key+1;?>)"></div>
    	<?php } ?>
  </div>
</div>
</div>
</div>
</div>

<div class="jingxuan">
<p align="center" style="padding:40px 0"><img src="tpl/www/images/01.jpg"></p>
<div class="clear"></div>
<?php $catelist = phpok_s_catelist('pros','ts');?>  
	<?php $_i=0;$catelist=(is_array($catelist))?$catelist:array();foreach($catelist AS  $key=>$value){$_i++; ?>
<div class="index_aa" id="aa<?php echo $key;?>"><a href="<?php echo list_url($value);?>" title="<?php echo $value[cate_name];?>"><img src="<?php echo $value[ico];?>"></a><p><a href="<?php echo list_url($value);?>" title="<?php echo $value[cate_name];?>"><?php echo $value[cate_name];?></a></p></div>
     <?php } ?>

</div>

<div id="shibao">
  <div class="shibao">
     <p align="center" style="padding:40px 0"><img src="tpl/www/images/02.jpg"></p>
     <div class="clear"></div>
     <div class="ipro"><img src="tpl/www/images/pro.jpg"><p><a href="">环球股票</a></p></div>
     <div class="ipro"><img src="tpl/www/images/pro.jpg"><p><a href="">环球股票</a></p></div>
     <div class="ipro" id="aa3"><img src="tpl/www/images/pro.jpg"><p><a href="">环球股票</a></p></div>
  </div>
</div>

<div id="index_news">
   
   <div class="index_news">
      <div class="news_top"><a href="index.php?c=list&cs=juniugonggao">巨牛公告</a></div>
      <div class="clear"></div>   
      <?php $any = phpok_c_list('juniugonggao',2,'','','isvouch');?>
<?php $_i=0;$any[rslist]=(is_array($any[rslist]))?$any[rslist]:array();foreach($any[rslist] AS  $key=>$value){$_i++; ?>
<A title="<?php echo $value[title];?>" href="<?php echo msg_url($value);?>" target="_blank"><img src="<?php echo $value[picture];?>"></a>
      <div class="news_main">
         <div class="ititle"><A title="<?php echo $value[title];?>" href="<?php echo msg_url($value);?>" target="_blank"><?php echo sys_cutstring($value[title],39,'..');?></A></div>
         <div class="news_txt">
           <?php echo $value[note];?>
         </div>
         <div class="i_more"><A title="<?php echo $value[title];?>" href="<?php echo msg_url($value);?>" target="_blank" style="color:#376db7">了解更多>></a></div>
      </div>

<?php } ?>
      
      
      
       
   </div>
   
    <div class="index_news" style="float:right">
      <div class="news_top"><a href="index.php?c=list&cs=shichangdongtai">市场动态</a></div>
      <div class="clear"></div>   
            <?php $any = phpok_c_list('shichangdongtai',2,'','','isvouch');?>
<?php $_i=0;$any[rslist]=(is_array($any[rslist]))?$any[rslist]:array();foreach($any[rslist] AS  $key=>$value){$_i++; ?>
<A title="<?php echo $value[title];?>" href="<?php echo msg_url($value);?>" target="_blank"><img src="<?php echo $value[picture];?>"></a>
      <div class="news_main">
         <div class="ititle"><A title="<?php echo $value[title];?>" href="<?php echo msg_url($value);?>" target="_blank"><?php echo sys_cutstring($value[title],39,'..');?></A></div>
         <div class="news_txt">
           <?php echo $value[note];?>
         </div>
         <div class="i_more"><A title="<?php echo $value[title];?>" href="<?php echo msg_url($value);?>" target="_blank" style="color:#376db7">了解更多>></a></div>
      </div>

<?php } ?>
       
   </div>

</div>

<div class="clear"></div>
<div class="index_tu">
  <div class="index_tu1"><a href="index.php?c=list&ms=login&sf=kehu" target="_blank"><img src="tpl/www/images/01.png"></a></div>
</div>

<?php $APP->tpl->p("foot","","0");?>


<script>
var currentindex=1;
var length = $(".flash_bar div").length;
//$("#flashBg").css("background-color",$("#flash1").attr("name"));
function changeflash(i) {	
currentindex=i;
for (j=1;j<=length;j++){
	if (j==i) 
	{$("#flash"+j).fadeIn("normal");
	$("#flash"+j).css("display","block");
	$("#f"+j).removeClass();
	$("#f"+j).addClass("dq");
	//$("#flashBg").css("background-color",$("#flash"+j).attr("name"));
	}
	else
	{$("#flash"+j).css("display","none");
	$("#f"+j).removeClass();
	$("#f"+j).addClass("no");}
}}
function startAm(){
timerID = setInterval("timer_tick()",3000);
}
function stopAm(){
clearInterval(timerID);
}
function timer_tick() {
    currentindex=currentindex>=length?1:currentindex+1;
	changeflash(currentindex);}
$(document).ready(function(){
$(".flash_bar div").mouseover(function(){stopAm();}).mouseout(function(){startAm();});
startAm();
});
</script>