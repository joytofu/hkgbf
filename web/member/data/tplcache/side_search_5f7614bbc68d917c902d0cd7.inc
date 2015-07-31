<?php
if(!isset($GLOBALS['_vars'])) $GLOBALS['_vars'] = array(); 
$fields = array();
?><!-- //begin search -->
<dl class="border mT10">
  <dt class="caption"><strong>文档搜索</strong></dt>
  <dd class="body lh20 pB10">
  	<form action="../plus/search.php" method="post" target="_blank">
	  <input type="hidden" name="mid" value="<?php echo $GLOBALS['_vars']['userid']; ?>" />
	  <div class="text">
		<label>
		  <input class="ip" type="text" name="keyword" size="15" style="width:100px" />
		  <input class="bt" name="submit" type="submit" value=" Go "/>
		</label>
	  </div>
	  </form>
  </dd>
</dl>
<!-- //end search -->