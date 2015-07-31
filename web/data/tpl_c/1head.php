<?php if(!defined('PHPOK_SET')){die('<h3>Error...</h3>');}?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php if($sitetitle){?><?php echo $sitetitle;?> - <?php } ?><?php if($_sys[seotitle]){?> <?php echo $_sys[seotitle];?> - <?php } ?><?php echo $_sys[sitename];?></title>
<?php if($_sys[google_site_verification]){?><meta name="google-site-verification" content="<?php echo $_sys[google_site_verification];?>" /><?php } ?>
<?php if($_sys[yahoo_site_key]){?><meta name="y_key" content="<?php echo $_sys[yahoo_site_key];?>" /><?php } ?>
<?php if($_sys[ms_site_validate]){?><meta name="msvalidate.01" content="<?php echo $_sys[ms_site_validate];?>" /><?php } ?>
<?php if($_sys[baidu_union_verify]){?><meta name="baidu_union_verify" content="<?php echo $_sys[baidu_union_verify];?>" /><?php } ?>
<meta name="keywords" content="<?php echo $_sys[keywords];?>">
<meta name="description" content="<?php echo $_sys[description];?>">
<link href="tpl/www/images/style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="tpl/www/images/jquery-1.4.2.js"></script>
<script type="text/javascript">
function MM_jumpMenu(targ,selObj,restore){ //v3.0
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
</script>
</head>

<body>

<div id="head">
  <div class="ssearch">
  <form method="post" action="<?php echo site_url('search');?>" name="formx"id="formx"  onsubmit="return to_submit()">
  
  <input name="bgui" type="image" src="tpl/www/images/sbutton.jpg" style="float:right" />
    <input onblur="this.style.color='#999';if(this.value=='') this.value='输入关键字...';" 
onkeyup="if(event.keyCode==13){doSearch();}" onfocus="this.style.color='#000';if(this.value=='输入关键字...') this.value='';" value="输入关键字..."  name="keywords" class="stext">


</form>
  </div>
  <div class="ssr" <?php if($_SESSION["username"]){?>style="width:318px;"<?php } ?> >
  
    <form name="form" id="form">
    <?php if($_SESSION["username"]){?>欢迎您：<?php echo $_SESSION["username"];?>，<a href="index.php?c=msg&id=<?php echo $_SESSION["userid"];?>">会员中心</a> | <a href="index.php?c=msg&ts=exit">注销退出</a><?php }else{ ?>
      <select name="jumpMenu" id="jumpMenu" onchange="MM_jumpMenu('parent',this,0)">
       <option>登录</option>
        <option value="index.php?c=list&cs=hezuohuoban&login">合作伙伴</option>
        <option value="index.php?c=list&cs=kehu&login">客户</option>
        <option value="index.php?c=list&cs=yuangong&login">员工</option>
      </select> | <a href="index.php?c=register&k=kehu">注册</a><?php } ?> | <a href="mailto:<?php echo $_sys[contactus_email];?>">企业邮箱</a>
    </form>
  </div>

</div>
<div id="top">
<div class="top">
<div class="logo"><a href="<?php echo $_sys[siteurl];?>" title="<?php echo $_sys[sitename];?>"><img src="<?php echo $_sys[logo] ? $_sys[logo] : 'tpl/www/images/logo.png';?>" alt="<?php echo $_sys[sitename];?>" /></a></div>
<div class="rtop">

<?php $menulist = phpok_menu($id,$cid,$mid);?>
		<?php $_i=0;$menulist=(is_array($menulist))?$menulist:array();foreach($menulist AS  $key=>$value){$_i++; ?>
<a href="<?php echo $value[link];?>"<?php if($value[target]){?> target="_blank"<?php } ?> title="<?php echo $value[title];?>" <?php if($value[my_highlight]){?>id=menubg<?php } ?>><?php echo $value[title];?></a>
		<?php } ?>
		<?php unset($menulist);?>


</div>
</div></div>