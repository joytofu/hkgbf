<?php
/***********************************************************
	Filename: app/admin/control/plugin.php
	Note	: 插件管理中心
	Version : 3.0
	Author  : qinggan
	Update  : 2011-03-12
***********************************************************/
class plugin_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("plugin");
	}

	function plugin_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		sys_popedom("plugin:list","tpl");
		$psize = SYS_PSIZE;
		$pageid = $this->trans_lib->int(SYS_PAGEID);
		$rslist = $this->plugin_m->get_list($pageid,$psize);
		$this->tpl->assign("rslist",$rslist);
		//取得总数量
		$total_count = $this->plugin_m->get_count();
		$this->page_lib->set_psize($psize);
		$page_url = $this->url("plugin");
		$pagelist = $this->page_lib->page($page_url,$total_count);
		$this->tpl->assign("pagelist",$pagelist);
		//判断是否有编辑权限
		$ifmodify = sys_popedom("plugin:modify");
		$ifdel = sys_popedom("plugin:delete");
		$ifcheck = sys_popedom("plugin:check");
		$this->tpl->assign("ifmodify",$ifmodify);
		$this->tpl->assign("ifdel",$ifdel);
		$this->tpl->assign("ifcheck",$ifcheck);
		$this->tpl->display("plugin/list.html");//插件管理
	}

	//编辑插件设置
	function set_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			error("没有指定插件ID！",$this->url("plugin"));
		}
		//判断是否有编辑权限
		sys_popedom("plugin:modify","tpl");
		$rs = $this->plugin_m->get_one($id);
		if(!$rs)
		{
			error("插件不存在！",$this->url("plugin"));
		}
		$this->tpl->assign("rs",$rs);
		//插件扩展配置
		$plugin = $this->plugin($rs["identifier"]);
		if($plugin)
		{
			$ext = $plugin->set($rs);
			$this->tpl->assign("ext",$ext);
		}
		$this->tpl->display("plugin/set.html");
	}

	function setok_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			error("没有指定插件ID！",$this->url("plugin"));
		}
		//判断是否有编辑权限
		sys_popedom("plugin:modify","tpl");
		$goback = $this->url("plugin,set","id=".$id);
		$title = $this->trans_lib->safe("title");
		if(!$title)
		{
			error("插件标题不允许为空！",$goback);
		}
		$array = array();
		$array["title"] = $title;
		$array["note"] = $this->trans_lib->safe("note");
		$array["taxis"] = $this->trans_lib->int("taxis");
		$array["hooks"] = $this->trans_lib->safe("hooks");
		$array["uninstall_sql"] = $this->trans_lib->html("uninstall_sql");
		$array["install_sql"] = $this->trans_lib->html("install_sql");
		//更新插件配置
		$rs = $this->plugin_m->get_one($id);
		$plugin = $this->plugin($rs["identifier"]);
		if($plugin)
		{
			$ext = $plugin->setok($id);
			$array["ext"] = $ext;
		}
		$this->plugin_m->save($array,$id);
		$this->_config($id);
		error("插件 <span class='red'>".$title."</span> 编辑成功！",$this->url("plugin"));
	}

	function del_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("对不起，你的操作有错误！");
		}
		sys_popedom("plugin:delete","ajax");
		$rs = $this->plugin_m->get_one($id);
		//执行删除SQL语句
		if($rs["uninstall_sql"])
		{
			$this->plugin_m->sql($rs["uninstall_sql"]);
		}
		//执行删除后台配置
		$this->plugin_m->del($id);
		exit("ok");
	}

	function ajax_status_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("对不起，你的操作有错误！");
		}
		sys_popedom("plugin:check","ajax");
		$rs = $this->plugin_m->get_one($id);
		$status = $rs["status"] ? 0 : 1;
		$this->plugin_m->set_status($id,$status);
		//更新配置文件信息
		$rs["status"] = $status;
		$this->_config($id);
		exit("ok");
	}

	//检测未安装插件
	function check_f()
	{
		//所有已安装的插件
		$installed_list = array();
		$tmplist = $this->plugin_m->get_all("identifier");
		if($tmplist)
		{
			foreach($tmplist AS $key=>$value)
			{
				$installed_list[] = $value["identifier"];
			}
			unset($tmplist);
		}
		//读取插件目录
		$plugin_list = array();
		$tmplist = $this->file_lib->ls(ROOT_PLUGIN);
		foreach($tmplist AS $key=>$value)
		{
			$plugin_list[] = basename($value);
		}
		//比较两个数组中不一致的标签
		$not_install_list = array_diff($plugin_list,$installed_list);
		if(!$not_install_list || count($not_install_list)<1)
		{
			error("没有插件到未安装的插件！",$this->url("plugin"));
		}
		//获取未安装插件的信息
		$rslist = array();
		foreach($not_install_list AS $key=>$value)
		{
			//检查配置文件
			$config_file = ROOT_PLUGIN.$value."/config.php";
			if(file_exists($config_file))
			{
				$config = array();
				include_once($config_file);
				$config["identifier"] = $value;
				$rslist[] = $config;
			}
		}
		if(!$rslist || count($rslist)<1)
		{
			error("没有插件到未安装的有效插件！",$this->url("plugin"));
		}
		$this->tpl->assign("rslist",$rslist);
		$this->tpl->display("plugin/install.html");
	}

	function install_f()
	{
		$id = $this->trans_lib->safe("id");
		if(!$id)
		{
			exit("Error:没有指定插件信息！");
		}
		$rs = $this->model("plugin")->chksign($id);
		if($rs)
		{
			exit("Error:该插件已经安装过了！请检查！");
		}
		//加载配置文件
		$config_file = ROOT_PLUGIN.$id."/config.php";
		if(!$config_file)
		{
			exit("Error:插件异常，没有相关配置文件！");
		}
		$config = array();
		include_once($config_file);
		$array = array();
		$array["title"] = $config["title"];
		$array["note"] = $config["note"];
		$array["identifier"] = $id;
		$array["langid"] = $config["langid"] ? $config["langid"] : "-";
		$array["hooks"] = $config["hooks"];
		$array["version"] = $config["version"];
		$array["author"] = $config["author"];
		$array["postdate"] = $this->system_time;
		$array["status"] = 0;
		$array["uninstall_sql"] = addslashes($config["uninstall_sql"]);
		$array["install_sql"] = addslashes($config["install_sql"]);
		$insert_id = $this->model("plugin")->save($array);
		if($insert_id)
		{
			//执行SQL语句
			if($config["install_sql"])
			{
				$this->model("plugin")->sql($config["install_sql"]);
			}
			exit("ok");
		}
		else
		{
			exit("Error：安装不成功！");
		}
	}

	function action_f()
	{
		$identifier = $this->trans_lib->safe("identifier");
		if(!$identifier)
		{
			error("操作异常！没有取得插件参数！");
		}
		$plugin = $this->plugin($identifier);
		if(!$plugin)
		{
			error("插件异常，可能不存在！");
		}
		$this->tpl->assign("inputs",$plugin->config);
		$act = $this->trans_lib->safe("act");
		if(!$act)
		{
			error("插件动作异常，没有指定！");
		}
		$list = get_class_methods($plugin);
		if(in_array($act,$list))
		{
			$plugin->$act();
		}
		else
		{
			error("异常方法，请检查！");
		}
	}

	function _config($id)
	{
		$rs = $this->plugin_m->get_one($id);
		$plugin = $this->plugin($rs["identifier"]);
		if($plugin)
		{
			$plugin->config($rs);
		}
	}
}
?>