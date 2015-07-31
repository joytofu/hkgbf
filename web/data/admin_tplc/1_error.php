<?php if(!defined('PHPOK_SET')){die('<h3>Error...</h3>');}?><?php $APP->tpl->p("header","","0");?>
<style type="text/css">
.error{position:absolute;top:30%;left:50%;margin:-40px 0 0 -230px;width:460px;height:80px;border:1px solid #8F8F8F;}
.error .div{background:#D6D6D6;line-height:130%;margin:1px;padding:5px;border:1px #FFF solid;height:66px;text-align:center}
.error .div p{line-height:150%;padding-top:5px;}
#foot{position:absolute;margin:0 auto;left:50%;top:30%;width:600px;margin:-35px 0 0 -300px;}
</style>
<div class="error">
	<div class="div">
		<?php if($error_url){?>
		<p style="padding-top:10px;"><?php echo $msg;?></p>
		<p><a href="<?php echo $error_url;?>"><?php echo $error_note;?></a></p>
		<?php }else{ ?>
		<p style="padding:24px 0px;"><?php echo $msg;?></p>
		<?php } ?>
	</div>
</div>
<?php if($error_url){?>
<script type="text/javascript">
var url = "<?php echo $error_url;?>";
var micro_time = "<?php echo $micro_time;?>";
window.setTimeout("refresh()",micro_time);
function refresh()
{
	direct(url);
}
</script>
<?php } ?>
<?php $APP->tpl->p("footer_open","","0");?>