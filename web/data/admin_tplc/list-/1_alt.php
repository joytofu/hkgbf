<?php if(!defined('PHPOK_SET')){die('<h3>Error...</h3>');}?><table width="100%">
<tr>
	<td width="130px">ID号：</td>
	<td><?php echo $rs[id];?> <?php if($rs[identifier]){?>【标识串：<span class="darkblue"><?php echo $rs[identifier];?></span>】<?php } ?></td>
</tr>
<tr>
	<td><?php echo $m_rs[title_nickname] ? $m_rs[title_nickname] : '主题';?>：</td>
	<td style="<?php echo $rs[style];?>">
		<?php echo $rs[title];?>
		<?php if($rs[istop]){?> <span class="red">[顶]</span><?php } ?>
		<?php if($rs[isvouch]){?> <span class="darkblue">[荐]</span><?php } ?>
		<?php if($rs[isbest]){?> <span class="darkred">[热销]</span><?php } ?>
	</td>
</tr>
<?php if($cate_rs){?>
<tr>
	<td>分类：</td>
	<td class="darkred"><?php echo $cate_rs[cate_name];?></td>
</tr>
<?php } ?>

<?php if($ext_catelist){?>
<tr>
	<td>扩展分类：</td>
	<td>
		<?php $_i=0;$ext_catelist=(is_array($ext_catelist))?$ext_catelist:array();foreach($ext_catelist AS  $key=>$value){$_i++; ?>
		<div style="line-height:22px;height:22px;"><?php echo $value[cate_name];?></div>
		<?php } ?>
	</td>
</tr>
<?php } ?>	
<tr>
	<td>发布时间：</td>
	<td><?php echo date('Y-m-d H:i',$rs[post_date]);?></td>
</tr>
<?php if($rs[modify_date]){?>
<tr>
	<td>最后修改时间：</td>
	<td><?php echo date('Y-m-d H:i',$rs[modify_date ]);?></td>
</tr>
<?php } ?>
<?php if($m_rs[if_des]){?>
<tr>
	<td>代理商：</td>
	<td><?php echo $rs[note];?></td>
</tr>
<?php } ?>
<tr>
	<td>查看次数：</td>
	<td><?php echo $rs[hits];?></td>
</tr>
<?php if($rs[thumb]){?>
<tr>
	<td>图片：</td>
	<td><img src="<?php echo $rs[thumb];?>" /></td>
</tr>
<?php } ?>
<?php if($m_rs[if_biz]){?>
<tr>
	<td>售价：</td>
	<td><?php if($rs[price]){?><?php echo sys_format_price($rs[price],$rs[price_currency]);?><?php }else{ ?>未设定<?php } ?></td>
</tr>
	<?php if($rs[weight]>0){?>
	<tr>
		<td>重量：</td>
		<td><?php echo $rs[weight];?> Kg</td>
	</tr>
	<?php } ?>
	<?php if($rs[is_qty]){?>
	<tr>
		<td>数量：</td>
		<td><?php echo $rs[qty];?> <?php echo $rs[qty_unit];?></td>
	</tr>
	<?php } ?>
<?php } ?>
<?php $_i=0;$extlist=(is_array($extlist))?$extlist:array();foreach($extlist AS  $key=>$value){$_i++; ?>
<tr>
	<td><?php echo $value[title];?>：</td>
	<td><?php echo $value[default_val];?></td>
</tr>
<?php } ?>
</table>