<?php if(!defined('PHPOK_SET')){die('<h3>Error...</h3>');}?><?php $APP->tpl->p("head","","0");?>

<?php $APP->tpl->p("banner","","0");?>


<div class="main">
   <div class="left">
      <div class="left1">
         <div class="lefttop"><?php echo $m_rs[title];?></div>
         <div class="leftmain">
       <?php $APP->tpl->p("inc/list","","0");?>
         </div>
      </div>
<?php $APP->tpl->p("inc/left","","0");?>
   </div>
   
   <div class="right">
      <div class="righttop"><div class="ll"><?php echo $rs[title];?></div><div class="rr">您当前的位置是：<a href="<?php echo $_sys[siteurl];?>">网站首页</a> > <?php echo $m_rs[title];?> > <?php echo $rs[title];?></div></div>
      
      <div class="rightmain">
 
<?php echo $rs[content];?>
 
      </div>
      
   </div>
   
   
   
</div>


<?php $APP->tpl->p("foot","","0");?>