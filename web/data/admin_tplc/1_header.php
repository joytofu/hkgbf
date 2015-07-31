<?php if(!defined('PHPOK_SET')){die('<h3>Error...</h3>');}?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>网站后台管理 </title>

<script type="text/javascript">
var base_file = "<?php echo HOME_PAGE;?>";
var base_url = "<?php echo $sys_app->url;?>";
var base_ctrl = "<?php echo $sys_app->config->c;?>";
var base_func = "<?php echo $sys_app->config->f;?>";
var base_dir = "<?php echo $sys_app->config->d;?>";
var phpok_data = "";
</script>
<link href="./app/admin/view/images/style.css" rel="stylesheet" type="text/css" />
<link href="libs/xheditor/xheditor_plugins/plugin.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/admin.js"></script>
<script type="text/javascript" src="./app/admin/view/images/global.js"></script>
<?php if($sys_app->control_name && file_exists(ROOT."./app/admin/view/images/admin/".$sys_app->control_name.".js")){?>
<script type="text/javascript" src="./app/admin/view/images/admin/<?php echo $sys_app->control_name;?>.js"></script>
<?php } ?>
<script type="text/javascript" src="libs/xheditor/xheditor.js"></script>
</head>
<body>