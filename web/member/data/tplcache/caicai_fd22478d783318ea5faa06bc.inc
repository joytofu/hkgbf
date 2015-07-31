<?php
if(!isset($GLOBALS['_vars'])) $GLOBALS['_vars'] = array(); 
$fields = array();
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>" />
<title>踩客——原来文章还能踩的！ - 会员中心 - <?php echo $cfg_webname; ?></title>
<link href="templets/style/caicai.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="templets/js/j.js"></script>
<script type="text/javascript" src="templets/js/load.js"></script>
<script type="text/javascript" src="templets/js/leftmenu.js"></script>
<script type="text/javascript">
  
  $(document).ready(function(){
	$("div[class*='good'],div[class*='bad']").bind("click", function(){
		var type = $(this).attr('class'),aid = $(this).attr('id');
		var saveId = getCookie('diggid');
		if(saveId != null)
		{
			var saveIds = saveId.split(',');
			var hasId = false;
			j = 1;saveId = '';
			for(i=saveIds.length-1;i>=0;i--)
			{
				if(saveIds[i]==aid && hasId) continue;
				else
				{
					if(saveIds[i]==aid && !hasId) hasId = true;
					saveId += (saveId=='' ? saveIds[i] : ','+saveIds[i]);
					j++;
					if(j==20 && hasId) break;
					if(j==19 && !hasId) break;
				}
			}
			
			if(hasId) { alert("您已经顶过该帖，请不要重复顶帖 ！"); return false; }
			else saveId += ','+aid;
			setCookie('diggid',saveId,1);
		}
		else
		{
			setCookie('diggid',aid,1);
		}		
		$.ajax({type: 'POST',url: "../plus/feedback.php",
			data: "formurl=caicai&action="+type+"&aid="+aid,
			dataType: 'text',
			success: function(result){$("#"+type+aid).html(result);}
		});
		
	});
});

//读写cookie函数
function getCookie(c_name)
{
	if (document.cookie.length > 0)
	{
		c_start = document.cookie.indexOf(c_name + "=")
		if (c_start != -1)
		{
			c_start = c_start + c_name.length + 1;
			c_end   = document.cookie.indexOf(";",c_start);
			if (c_end == -1)
			{
				c_end = document.cookie.length;
			}
			return unescape(document.cookie.substring(c_start,c_end));
		}
	}
	return null
}

function setCookie(c_name,value,expiredays)
{
	var exdate = new Date();
	exdate.setDate(exdate.getDate() + expiredays);
	document.cookie = c_name + "=" +escape(value) + ((expiredays == null) ? "" : ";expires=" + exdate.toGMTString()); //使设置的有效时间正确。增加toGMTString()
}

function postBadGood(type,id){}
</script>
</head>
<body>
<div id="main">
    <?php include(DEDEMEMBER."/templets/head.htm"); ?>
  <div id="content" class="w960 clearfix">
    <!--<div id="temDiv" style="clear:both"></div> -->
    <div id="listbox">
        <ul>
           <?php
$atts = array();
$atts['tagname'] = 'datalist';
$blockValue = $this->refObj->GetArcList($atts,$this->refObj,$fields); 
if(is_array($blockValue)){
foreach( $blockValue as $key=>$fields )
{
?>
		    <li>
            <a href='<?php echo $fields['arcurl']; ?>' target='_blank' class="preview"><img src='<?php echo $fields['litpic']; ?>' alt='<?php echo $fields['title']; ?>' width="80"  height="72"/></a>
            <div class="cbody">
                <a href='<?php echo $fields['arcurl']; ?>' target='_blank' class="title"><?php echo $fields['title']; ?></a>
                <span class="endpl">最后评论：<small><?php echo $fields['lastpost']; ?></small></span>
                <p class="intro"><?php echo cn_substr($fields['description'], 130); ?>...</p>
                <span class="info">
                  <small class="view">浏览：</small><?php echo $fields['click']; ?>
                  <small class="hpd">好评度：</small><?php echo $fields['scores']; ?>
                  <small class="pl"><a href="../plus/feedback.php?aid=<?php echo $fields['id']; ?>">评论</a></small>
                </span>
            </div>
            <div class="caicai">
                  <div class='good' title='好评' id="<?php echo $fields['id']; ?>"> <span id='good<?php echo $fields['id']; ?>'><?php echo $fields['goodpost']; ?></span> </div>
                  <div class='bad' title='差评' id="<?php echo $fields['id']; ?>"> <span id='bad<?php echo $fields['id']; ?>'><?php echo $fields['badpost']; ?></span> </div>
            </div>
			</li>
           <?php
}
}?>
		 </ul>
         <div class="pageList"><span><?php
$atts = array();
$atts['tagname'] = 'pagelist';
$atts['listsize'] = '4';
$atts['listitem'] = 'info,index,end,pre,next,pageno';
 echo $this->refObj->GetPageList($atts,$this->refObj,$fields); 
?>
</span></div>
    </div>
    <!--左侧内容 -->
    <div id="sidebar">
      <div class="s_border">
        <div class="s_title">最受好评的文章 &raquo;</div>
        <ul>
          <?php
$atts = array();
$atts['tagname'] = 'list';
$atts['order'] = 'goodpost';
$atts['orderway'] = 'desc';
$blockValue = $this->refObj->GetSortArc($atts,$this->refObj,$fields); 
if(is_array($blockValue) && count($blockValue) > 0){
foreach( $blockValue as $key=>$fields )
{
?>
            <li><a href='<?php echo $fields['arcurl']; ?>'><?php echo $fields['title']; ?></a></li>
     	  <?php
}
}
?>
        </ul>
      </div> <!--最爱好评的文章 -->
      <div class="s_border mt10">
        <div class="s_title">最受争议文章 &raquo;</div>
        <ul>
          <?php
$atts = array();
$atts['tagname'] = 'list';
$atts['order'] = 'badpost';
$atts['orderway'] = 'desc';
$blockValue = $this->refObj->GetSortArc($atts,$this->refObj,$fields); 
if(is_array($blockValue) && count($blockValue) > 0){
foreach( $blockValue as $key=>$fields )
{
?>
          	<li><a href='<?php echo $fields['arcurl']; ?>'><?php echo $fields['title']; ?></a></li>
          <?php
}
}
?>
        </ul>
      </div>
      <div class="s_border mt10">
        <div class="s_title">最热门的文章 &raquo;</div>
        <ul>
          <?php
$atts = array();
$atts['tagname'] = 'list';
$atts['order'] = 'click';
$atts['orderway'] = 'desc';
$blockValue = $this->refObj->GetSortArc($atts,$this->refObj,$fields); 
if(is_array($blockValue) && count($blockValue) > 0){
foreach( $blockValue as $key=>$fields )
{
?>
     		<li><a href='<?php echo $fields['arcurl']; ?>'><?php echo $fields['title']; ?></a></li>
     	  <?php
}
}
?>
        </ul>
      </div> <!--最爱好评的文章 -->
      <div class="s_member mt10">
        <div class="s_title">活跃会员列表 &raquo;</div>
        <ul>
          <?php
$atts = array();
$atts['tagname'] = 'memberlist';
$atts['row'] = '15';
$blockValue = MakePublicTag($atts,$this->refObj,$fields); 
if(is_array($blockValue) && count($blockValue) > 0){
foreach( $blockValue as $key=>$fields )
{
?>
          	<li><a href='<?php echo $fields['spaceurl']; ?>' target='_blank'><img src='<?php echo $fields['face']; ?>' alt="<?php echo $fields['spacename']; ?>"  height="44" width="44" /><br /><?php echo $fields['uname']; ?></a></li>
          <?php
}
}
?>
        </ul>
      </div> <!--最爱好评的文章 -->
    </div>
    <div id="temDiv" style="clear:both"></div>
	  <!--右侧相关 -->
  </div>
  </div>
<?php include(DEDEMEMBER."/templets/foot.htm"); ?>
</div>
</body>
</html>