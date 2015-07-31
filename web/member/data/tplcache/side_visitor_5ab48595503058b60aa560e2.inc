<?php
if(!isset($GLOBALS['_vars'])) $GLOBALS['_vars'] = array(); 
$fields = array();
?><!-- //最近访客 -->
<dl class="border mT10">
	<dt class="caption"><strong>最近访客</strong></dt>
	<dd class="body">
	<?php
$atts = array();
$atts['tagname'] = 'newvisitor';
$atts['row'] = '6';
$blockValue = MakePublicTag($atts,$this->refObj,$fields); 
if(is_array($blockValue) && count($blockValue) > 0){
foreach( $blockValue as $key=>$fields )
{
?>
		<div class="allList mB10">
				<div class="img"> <a href="<?php echo $fields['url']; ?>" title="<?php echo $fields['uname']; ?>" target="_blank"><img src="<?php echo $fields['face']; ?>" width="50" height="50" alt="<?php echo $fields['uname']; ?>的头像" /></a> </div>
				<div class="txt">
					<h5 class="p1 mB5 blackA"><a href="<?php echo $fields['url']; ?>" target="_blank" title="<?php echo $fields['uname']; ?>"><?php echo $fields['uname']; ?></a></h5>
					<p class="p2"><?php echo cn_substr($fields['sign'],60); ?></p>
					<p class="p2 clear">访问：<?php echo MyDate('m-d h:i',$GLOBALS['_vars']['logintime']); ?></p>
				</div>
			<div class="clearfix"></div>
		</div>
	<?php
}
}
?>
	</dd>
</dl>