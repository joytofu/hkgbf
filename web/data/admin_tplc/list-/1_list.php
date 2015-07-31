<?php if(!defined('PHPOK_SET')){die('<h3>Error...</h3>');}?><?php $APP->tpl->p("header","","0");?>
<script type="text/javascript">
function set_price_currency(val)
{
	var gourl = "<?php echo site_url('index,set_default');?>code="+val+"&";
	var backurl = "<?php echo rawurlencode(site_url('list','module_id='.$module_id));?>";
	gourl += "backurl="+backurl+"&";
	direct(gourl);
}
</script>
<div class="notice"><div class="p">
	<table width="100%" cellpadding="0" cellspacing="0">
	<tr>
		<td>
			<table cellpadding="0" cellspacing="0">
			<form method="post" id="search_list" onsubmit="return search_list('search_list','<?php echo $module_id;?>',<?php echo $ifcate ? 'true' : 'false';?>);">
			<tr>
				<td>&nbsp;搜索：</td>
				<td>
					<select name="status">
						<option value="">全部</option>
						<option value="1"<?php if($status == 1){?> selected<?php } ?>>已审核</option>
						<option value="2"<?php if($status == 2){?> selected<?php } ?>>未审核</option>
					</select>
				</td>
				<?php if($ifcate){?><td><?php echo $cate_html;?></td><?php } ?>
				<td>
					<select name="keytype">
						<option value="title"<?php if($keytype == "keywords"){?> selected<?php } ?>><?php echo $m_rs[title_nickname] ? $m_rs[title_nickname] : '主题';?></option>
						<?php if($m_rs[if_subtitle]){?>
							<option value="subtitle"<?php if($keytype == "subtitle"){?> selected<?php } ?>><?php echo $m_rs[subtitle_nickname] ? $m_rs[subtitle_nickname] : '副主题';?></option>
						<?php } ?>
						<?php if($m_rs[if_des]){?>
							<!--<option value="note"<?php if($keytype == "note"){?> selected<?php } ?>>代理商</option>-->
						<?php } ?>
						
					</select>
					
				</td>
			<!--		<td>
					<select name="isbest">
						<option value="10" <?php if($isbest == "10"){?> selected<?php } ?>>全部</option>
						<option value="0" <?php if($isbest == "0"){?> selected<?php } ?>>未售</option>
						<option value="1" <?php if($isbest == "1"){?> selected<?php } ?>>热销</option>
						
					</select>
					
				</td>-->
				<td><input type="text" name="keywords" id="keywords" value="<?php echo $keywords;?>"></td>
				<td><input type="submit" class="btn2" value="查询"></td>
			</tr>
			</form>
			</table>
		</td>
		<?php if($m_rs[if_biz] && $currency_list){?>
		<td align="left" style="display:none;">
			货币：
			<select name="this_currency" id="this_currency" onchange="set_price_currency(this.value)">
			<?php $_i=0;$currency_list=(is_array($currency_list))?$currency_list:array();foreach($currency_list AS  $key=>$value){$_i++; ?>
			<option value="<?php echo $value[code];?>"<?php if($value[code] == $default_currency[code]){?> selected<?php } ?>><?php echo $value[title];?>（<?php echo $value[code];?>）</option>
			<?php } ?>
			</select>
		</td>
		<?php } ?>
		<td align="right"><a href="<?php echo site_url('list,set');?>module_id=<?php echo $m_rs[id];?>&" class="button">添加内容</a></td>
	</tr>
	</table>
</div></div>

<script type="text/javascript" src="js/smart-menu.js"></script>
<?php if($m_rs[tplset] == 'pic'){?>
	<?php $APP->tpl->p("list/list_pic","","0");?>
<?php }else{ ?>
	<?php $APP->tpl->p("list/list_txt","","0");?>
<?php } ?>

<div class="table">
	<table width="100%">
	<tr>
		<td>
			<input type="button" value="全选" onclick="select_all()" class="btn2">
			<input type="button" value="全不选" onclick="select_none()" class="btn3">
			<input type="button" value="反选" onclick="select_anti()" class="btn2">
			<select name="act_plset" id="act_plset">
				<option value="">请选择操作方案</option>
				<optgroup label="批处理">
					<?php if($m_rs[if_propety]){?>
					<option value="istop:1">置顶</option>
					<option value="isvouch:1">推荐</option>
					<!--<option value="isbest:1">热销</option>-->
					<?php } ?>
					<option value="status:1">审核</option>
					<option value="copy:20">复制20个主题</option>
					<?php if($m_rs[if_propety]){?>
					<option value="istop:0">取消置顶</option>
					<option value="isvouch:0">取消推荐</option>
				<!--	<option value="isbest:0">取消热销</option>-->
					<?php } ?>
					<option value="status:0">取消审核</option>
					<option value="taxis">更新排序</option>
					<option value="del">批量删除</option>
				</optgroup>
				<?php if($ifcate){?>
				<optgroup label="移动主题分类">
					<?php $_i=0;$cate_list_array=(is_array($cate_list_array))?$cate_list_array:array();foreach($cate_list_array AS  $key=>$value){$_i++; ?>
						<option value="cate:<?php echo $value[id];?>"><?php echo $value[space];?><?php echo $value[cate_name];?><?php if(!$value[status]){?>【暂停使用】<?php } ?></option>
					<?php } ?>
				</optgroup>
				<?php } ?>
			</select>
			<input type="button" value="提交" onclick="update_pl()" class="btn2">
		</td>
		<td align="right"><?php echo $pagelist;?></td>
	</tr>
	</table>
</div>
<?php $APP->tpl->p("footer","","0");?>
<script language="">
function show_user(id)
{
	var url = base_file + "?"+base_ctrl+"=user&"+base_func+"=view&id="+id;
	Layer.init(url,550,400);
}
</script>