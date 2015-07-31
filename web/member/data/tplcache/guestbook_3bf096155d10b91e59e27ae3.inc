<?php
if(!isset($GLOBALS['_vars'])) $GLOBALS['_vars'] = array(); 
$fields = array();
 include $this->CompilerInclude("header.htm");
 ?>
<div class="wrapper mT10">
  <div class="west">
  <?php include $this->CompilerInclude("side_icon.htm");
  include $this->CompilerInclude("side_news.htm");
  include $this->CompilerInclude("side_visitor.htm");
 ?>
  </div>
  <div class="east">
    <dl class="border">
      <dt class="caption"><strong>留言</strong></dt>
      <dd class="body pB10"> <?php
$atts = array();
$atts['tagname'] = 'datalist';
$blockValue = $this->refObj->GetArcList($atts,$this->refObj,$fields); 
if(is_array($blockValue)){
foreach( $blockValue as $key=>$fields )
{
?>
        <div class="allList pTB10 dashed">
          <div class="img"> <a href="index.php?uid=<?php echo $fields['uname']; ?>&action=infos" title="<?php echo $fields['uname']; ?>" target="_blank">	
          <?php if($fields['face']==''){ 
	         $face=($fields['sex']=='女')? 'templets/images/dfgirl.png' : 'templets/images/dfboy.png';
            echo  '<img src="'.$face.'" width="50" height="50"/>';
		   }else{ 
	           echo  '<img src="'.$fields['face'].'" width="50" height="50" alt="'.$fields['uname'].'的头像" />';
	       } ?> </a> </div>
          <div class="txt">
            <p class="p1 mB5">
            <h5 class="fLeft"><a href='index.php?uid=<?php echo $fields['uname']; ?>'><?php echo $fields['uname']; ?></a>：</h5>
           <?php 
            	if($_vars['userid']==$cfg_ml->M_LoginID){
            		echo "<span style='float:right'><a href='index.php?action=guestbookdel&aid=".$fields['aid']."&uid=".$_vars['userid']."&mid=".$fields['mid']."'>删除</a></span>";
             	}
            ?>
            <span class="aGray fRight"><?php echo MyDate('Y-m-d H:i',$fields['dtime']); ?></span>
            </p>
            <p class="p2 lh22 f14 aBlack clear"><?php echo Text2Html($fields['msg']); ?></p>
          </div>
          <div class="clearfix"></div>
        </div>
        <?php
}
}?>
        <div class="fRight mTB10 pd10"><?php
$atts = array();
$atts['tagname'] = 'pagelist';
 echo $this->refObj->GetPageList($atts,$this->refObj,$fields); 
?>
</div>
        <div class="clearfix"></div>
      </dd>
    </dl>
    <dl class="border mT10">
      <dt class="caption"><strong>给我留言</strong></dt>
      <dd class="body pB10">
        <form action="index.php" name="form1" method="post" class="f14">
          <input type="hidden" name="action" value="guestbooksave" />
          <input type="hidden" name="uidnum" value="<?php echo $GLOBALS['_vars']['mid']; ?>" />
          <input type="hidden" name="uid" value="<?php echo $GLOBALS['_vars']['userid']; ?>" />
		  <label>标题：</label>
		  <input name="title" type="text" id="title"   class="ip" style="width:250px;height:15px;" />
          <textarea name="msg" id="msg" style="width:98%; height:120px;" class="clear mB10 text"></textarea>
          <button class="fRight button buttonBlue2" type="submit">提交</button>
          <label>昵称：</label>
          <input name="uname" type="text" id="uname" value="<?php if(isset($cfg_ml->M_LoginID) && $cfg_ml->M_LoginID!='') echo $cfg_ml->M_LoginID; ?>" class="text" style="width:100px;" />
          <label>验证码：</label>
          <input name="vdcode" type="text" class="text" id="vdcode" style="width:50px;text-transform:uppercase;" />
          <img src='../include/vdimgck.php' width='50' height='20' align="absmiddle" /><a href="resetpassword.php" class="mL10 mR5">找回密码</a><a href="../member/index_do.php?fmdo=user&dopost=regnew" title="注册">注册</a>
        </form>
        <p class="mp10 textCenter aGray">以上网友发言只代表其个人观点，不代表本站的观点或立场。</p>
      </dd>
    </dl>
  </div>
</div>
<?php include $this->CompilerInclude("footer.htm");
 ?>
</body>
</html>