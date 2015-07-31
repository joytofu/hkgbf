<?php
/***********************************************************
	Filename: app/admin/control/tpl.php
	Note	: 模板管理
	Version : 3.0
	Author  : qinggan
	Update  : 2009-12-21
***********************************************************/
class tpl_c extends Control
{
	var $module_sign = "tpl";
	function __construct()
	{
		parent::Control();
		$this->load_model("tpl");//读取模块列表
	}

	function tpl_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		sys_popedom($this->module_sign.":list","tpl");
		$rslist = $this->tpl_m->get_list($_SESSION["sys_lang_id"]);
		$this->tpl->assign("rslist",$rslist);
		$this->tpl->display("tpl/list.html");
	}

	function set_f()
	{
		$id = $this->trans_lib->int("id");
		if($id)
		{
			sys_popedom($this->module_sign.":modify","tpl");
			$rs = $this->tpl_m->get_one($id);
			$this->tpl->assign("rs",$rs);
		}
		else
		{
			sys_popedom($this->module_sign.":add","tpl");
		}
		$this->tpl->display("tpl/set.html");
	}

	//信息存储
	function setok_f()
	{
		$id = $this->trans_lib->int("id");
		$array = array();
		$array["title"] = $this->trans_lib->safe("title");
		$array["folder"] = $this->trans_lib->safe("folder");
		if($id)
		{
			sys_popedom($this->module_sign.":modify","tpl");
		}
		else
		{
			sys_popedom($this->module_sign.":add","tpl");
			$array["langid"] = $_SESSION["sys_lang_id"];
		}
		$array["taxis"] = $this->trans_lib->int("taxis");
		$array["autoimg"] = $this->trans_lib->int("autoimg");
		$array["ext"] = $this->trans_lib->safe("ext");
		$array["taxis"] = $this->trans_lib->int("taxis");
		//存储分类信息
		$this->tpl_m->save($array,$id);
		error("模板信息添加/存储成功",site_url("tpl"));
	}

	function ajax_status_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error:没有指定ID");
		}
		sys_popedom($this->module_sign.":check","ajax");
		$rs = $this->tpl_m->get_one($id);
		$status = $rs["status"] ? 0 : 1;
		$this->tpl_m->set_status($id,$status);
		exit("ok");
	}

	function ajax_default_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error:没有指定ID");
		}
		sys_popedom($this->module_sign.":check","ajax");
		$this->tpl_m->set_default($id,$_SESSION["sys_lang_id"]);
		exit("ok");
	}

	function ajax_del_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error:没有指定ID");
		}
		sys_popedom($this->module_sign.":delete","ajax");
		$rs = $this->tpl_m->get_one($id);
		if($rs["ifsystem"])
		{
			exit("error: 对不起，系统模板不允许删除");
		}
		if($rs["ifdefault"])
		{
			exit("error: 对不起，默认模板不允许删除");
		}
		$this->tpl_m->del($id);
		exit("ok");
	}

	//读取模板文件
	function list_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			error("操作错误，没有指定ID",site_url("tpl"));
		}
		$rs = $this->tpl_m->get_one($id);
		if(!$rs)
		{
			error("没有找到信息",site_url("tpl"));
		}
		$this->tpl->assign("rs",$rs);
		$folder = $rs["folder"] == "default" ? APP_WWW."view" : ROOT."tpl/".$rs["folder"];
		if(!file_exists($folder) || !is_dir($folder))
		{
			error("模板目录不存在或这不是有一个有效的目录",site_url("tpl"));
		}
		$parentfolder = $this->trans_lib->safe("parent");
		$this->tpl->assign("parent",$parentfolder);
		$leader_array = array();
		if($parentfolder)
		{
			$parent_array = explode("/",$parentfolder);
			$sub_e = "";
			foreach($parent_array AS $key=>$value)
			{
				if($value)
				{
					$mysubject = $sub_e ? $sub_e ."/".$value : $value;
					$sub_e .= $mysubject;
					$leader_array[$key]["subject"] = $value;
					$leader_array[$key]["url"] = site_url("tpl,list","id=".$id."&parent=".rawurlencode($mysubject));
				}
			}
			$this->tpl->assign("leader",$leader_array);
		}
		if($parentfolder && substr($parentfolder,-1) != "/")
		{
			$parentfolder .= "/";
		}
		if($parentfolder)
		{
			$folder = substr($folder,-1) != "/" ? $folder."/".$parentfolder : $folder.$parentfolder;
		}
		$tmplist = $this->file_lib->ls($folder);
		if(!is_array($tmplist)) $tmplist = array();
		$rslist = array();
		$edit_yes = array("asp","jsp","html","js","c","cpp","css","java","perl","php","py","txt","sql","vb","xml");
		foreach($tmplist AS $key=>$value)
		{
			if($value != "." && $value != "..")
			{
				$pathinfo = pathinfo($value);
				$val = array();
				$val["filename"] = basename($value);
				$extend = strtolower($pathinfo["extension"]);
				$val["filetype"] = file_exists($this->tpl->tpldir."/images/filetype/".$extend.".gif") ? $extend : "unknown";
				if(is_dir($value))
				{
					$val["filetype"] = "dir";
					$val["filesize"] = "4 KB";
					$val["url"] = site_url("tpl,list")."parent=".rawurlencode($parentfolder.basename($value))."&id=".$id;
					$dirlist[] = $val;
				}
				else
				{
					$filesize = $this->trans_lib->num_format(filesize($value));
					$val["filesize"] = $filesize;
					if($extend == $rs["ext"])
					{
						if($val["filetype"] == "unknow") $val["filetype"] = "html";
						$val["url"] = site_url("tpl,tpl_set")."id=".$id."&filename=".rawurlencode(basename($value))."&parent=".rawurlencode($parentfolder);
					}
					elseif(in_array($extend,$edit_yes))
					{
						$val["url"] = site_url("tpl,tpl_set")."id=".$id."&filename=".rawurlencode(basename($value))."&parent=".rawurlencode($parentfolder);
					}
					else
					{
						$val["url"] = "javascript:alert('该文件不支持在线编辑功能');void(0);";
					}
					$rslist[] = $val;
				}
			}
		}
		$this->tpl->assign("rslist",$rslist);
		$this->tpl->assign("dirlist",$dirlist);
		$this->tpl->display("tpl/tpl_list.html");
	}

	function tpl_set_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			Error("操作有错误",site_url("tpl"));
		}
		$parentfolder = $this->trans_lib->safe("parent");
		$this->tpl->assign("parent",$parentfolder);
		$leader_array = array();
		if($parentfolder)
		{
			$parent_array = explode("/",$parentfolder);
			$sub_e = "";
			foreach($parent_array AS $key=>$value)
			{
				if($value)
				{
					$mysubject = $sub_e ? $sub_e ."/".$value : $value;
					$sub_e .= $mysubject;
					$leader_array[$key]["subject"] = $value;
					$leader_array[$key]["url"] = site_url("tpl,list","id=".$id."&parent=".rawurlencode($mysubject));
				}
			}
			$this->tpl->assign("leader",$leader_array);
		}
		$tplfile = $this->trans_lib->safe("filename");
		$rs = $this->tpl_m->get_one($id);
		$this->tpl->assign("rs",$rs);
		$dirlist = $rs["folder"] == "default" ? APP_WWW."view/" : ROOT."tpl/".$rs["folder"]."/";
		if(!file_exists($dirlist))
		{
			Error("操作错误，找不到相关的模板目录",site_url("tpl,list","id=".$id));
		}
		if($parentfolder)
		{
			$dirlist.= $parentfolder."/";
			if(!file_exists($dirlist))
			{
				Error("没有找到相关的目录",site_url("tpl,list","id=".$id));
			}
		}
		#[判断是否有这个文件]
		$content = "";
		if($tplfile && file_exists($dirlist.$tplfile))
		{
			$content = $this->file_lib->cat($dirlist.$tplfile);
			#[转化HTML代码能正常在文本框中显示的代码]
			$content = $this->trans_lib->html_edit($content);
		}
		$this->tpl->assign("tplfile",$tplfile);
		$this->tpl->assign("content",$content);
		$this->tpl->display("tpl/setfile.html");
	}

	function tpl_setok_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			error("操作错误，没有指定ID",site_url("tpl"));
		}
		$parent = $this->trans_lib->safe("parent");
		$tplfile = $this->trans_lib->safe("tplfile");
		$filename = $this->trans_lib->safe("filename");
		$this->trans_lib->setting(true,true,true);
		$content = $this->trans_lib->html("content");
		$content = $this->trans_lib->edit_html($content);
		$r_url = site_url("tpl,tpl_set","id=".$id."&parent=".rawurlencode($parent)."&filename=".rawurlencode($filename));
		if(!$tplfile || !$content)
		{
			error("error: 操作错误，模板文件名或内容至少有一个为空",$r_url);
		}
		$rs = $this->tpl_m->get_one($id);
		if(!$rs)
		{
			error("error: 没有找到相关模板信息",site_url("tpl"));
		}
		$folder = $rs["folder"] == "default" ? APP_WWW."view/" : ROOT."tpl/".$rs["folder"]."/";
		if($parent)
		{
			$folder .= $parent;
		}
		if(!file_exists($folder))
		{
			Error("error: 操作错误，找不到相关的模板目录",$r_url);
		}
		if(substr($folder,-1) != "/")
		{
			$folder .= "/";
		}
		$this->file_lib->vim($content,$folder.$tplfile);
		$myurl = site_url("tpl,list","id=".$id."&parent=".rawurlencode($parent));
		Error("数据添加/更新成功！",$myurl);
	}

	//检测模板文件名是否重复
	function ajax_chk_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error: 检测失败，没有指定ID");
		}
		$parent = $this->trans_lib->safe("parent");
		$rs = $this->tpl_m->get_one($id);
		if(!$rs)
		{
			exit("error: 检测失败，无法获取模板信息");
		}
		$folder = $rs["folder"] == "default" ? APP_WWW."view/" : ROOT."tpl/".$rs["folder"]."/";
		if($parent)
		{
			$folder .= $parent;
		}
		if(!file_exists($folder) || is_file($folder))
		{
			exit("error: 检测失败，指定目录不存在");
		}
		$filename = $this->trans_lib->safe("filename");
		$tplfile = $this->trans_lib->safe("tplfile");
		if(!$tplfile)
		{
			exit("error: 模板文件不允许为空");
		}
		if($filename && $filename == $tplfile)
		{
			exit("ok");
		}
		$tplfile = strtolower($tplfile);
		if(!ereg("[a-z][a-z0-9\_]+",$tplfile))
		{
			exit("error: 模板文件不符合系统限制：字母、数字、下划线且必须是字母开头");
		}
		$tmplist = $this->file_lib->ls($folder);
		if(!is_array($tmplist)) $tmplist = array();
		$rslist = array();
		foreach($tmplist AS $key=>$value)
		{
			$rslist[] = strtolower(basename($value));
		}
		if(in_array($tplfile,$rslist))
		{
			exit("error: 模板文件名已经存在！");
		}
		exit("ok");
	}

	function ajax_file_del_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error: 操作失败，没有指定ID");
		}
		$tplfile = $this->trans_lib->safe("tplfile");
		if(!$tplfile)
		{
			exit("error: 没有指定模板文件");
		}
		$parent = $this->trans_lib->safe("parent");
		$rs = $this->tpl_m->get_one($id);
		if(!$rs)
		{
			exit("error: 操作失败，无法获取模板信息");
		}
		$folder = $rs["folder"] == "default" ? APP_WWW."view/" : ROOT."tpl/".$rs["folder"]."/";
		if($parent)
		{
			$folder .= $parent;
		}
		if(!file_exists($folder) || is_file($folder))
		{
			exit("error: 操作失败，指定目录不存在");
		}
		$del_file = substr($folder,-1) != "/" ? $folder."/".$tplfile : $folder.$tplfile;
		if(!is_file($del_file))
		{
			$this->file_lib->rm($del_file,"folder");
		}
		else
		{
			$this->file_lib->rm($del_file);
		}
		exit("ok");
	}
}
?>