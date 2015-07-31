<?php
if(!isset($GLOBALS['_vars'])) $GLOBALS['_vars'] = array(); 
$fields = array();
 include $this->CompilerInclude("header.htm");
 ?>
<div class="wrapper mT10">
  <div class="west">
  <?php include $this->CompilerInclude("side_icon.htm");
  include $this->CompilerInclude("side_class.htm");
  include $this->CompilerInclude("side_news.htm");
  include $this->CompilerInclude("side_visitor.htm");
  include $this->CompilerInclude("side_search.htm");
  include $this->CompilerInclude("side_links.htm");
 ?>
  </div>
  <div class="east">
    <dl class="border">
      <dt class="caption"><strong>文章</strong></dt>
      <dd class="body">
<?php
$atts = array();
$atts['tagname'] = 'spacenewart';
$atts['row'] = '10';
$atts['titlelen'] = '60';
$blockValue = MakePublicTag($atts,$this->refObj,$fields); 
if(is_array($blockValue) && count($blockValue) > 0){
foreach( $blockValue as $key=>$fields )
{

	if($fields['channel']==1) {
			$fields['arcurl'] = "index.php?uid={$_vars['userid_e']}&action=viewarchives&aid={$fields['id']}";
	}
?>
        <div class="mp10 dashed">
          <div class="mB10">
          <h3 class="fLeft"><a href="<?php echo $fields['arcurl']; ?>"><?php echo $fields['title']; ?></a></h3>
          <span class="mL5 aGray">(<?php echo MyDate('Y-m-d h:i',$fields['pubdate']); ?>)</span>
          </div>
          <p class="f14 aBlack lh22"><?php echo $fields['description']; ?></p>
          <div id="artInfo">
            <strong class="fLeft"><a href="<?php echo $fields['arcurl']; ?>" target="_blank" title="点击查看全文">点击查看全文</a></strong>
            <ul class="fRight fLeftChild overflow">
              <li><a href="<?php echo $fields['arcurl']; ?>" target="_blank" title="<?php echo $fields['title']; ?>">浏览</a>(<?php echo $fields['click']; ?>)</li>
              <li><a href="<?php echo $GLOBALS['cfg_phpurl']; ?>/feedback.php?aid=<?php echo $fields['id']; ?>" target="_blank">评论</a>(<?php echo $fields['scores']; ?>)</li>
              <li><a href="<?php echo $GLOBALS['cfg_phpurl']; ?>/feedback.php?aid=<?php echo $fields['id']; ?>" target="_blank">好评</a>(<?php echo $fields['goodpost']; ?>)</li>
              <li><a href="<?php echo $GLOBALS['cfg_phpurl']; ?>/feedback.php?aid=<?php echo $fields['id']; ?>" target="_blank">差评</a>(<?php echo $fields['badpost']; ?>)</li>
            </ul>
          </div>
          <div class="clearfix pB10"></div>
        </div>
        <?php
}
}
?>
        <div class="fRight mB10"><a href="index.php?uid=<?php echo $GLOBALS['_vars']['userid_e']; ?>&action=archives">更多内容&gt;&gt;</a></div>
        <div class="clearfix"></div>
      </dd>
    </dl>
  </div>
</div>
<?php include $this->CompilerInclude("footer.htm");
 ?>
</body>
</html>