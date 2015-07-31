<?php
/***********************************************************
	Filename: app/admin/control/files.php
	Note	: 附件管理
	Version : 3.0
	Author  : qinggan
	Update  : 2010-05-12
***********************************************************/
class files_c extends Control
{
	var $type_video = array("wma","mp3","wmv","asf","mpg","mpeg","avi","asx","rm","rmvb","ram","ra","swf","flv","dat");
	var $type_img = array("jpg","gif","png","jpeg");
	var $type_file = array("zip","rar","txt","tgz","tar","gz");
	var $file_uptype = "swf";
	function __construct()
	{
		parent::Control();
		$this->load_model("upfile");//读取附件操作类
		if(file_exists(ROOT_DATA."attachment.php"))
		{
			include(ROOT_DATA."attachment.php");
			if($_sys["picture_type"])
			{
				$this->type_img = sys_id_list($_sys["picture_type"]);
			}
			if($_sys["video_type"])
			{
				$this->type_video = sys_id_list($_sys["video_type"]);
			}
			if($_sys["file_type"])
			{
				$this->type_file = sys_id_list($_sys["file_type"]);
			}
			$this->file_uptype = $_sys["file_uptype"];
		}
		$this->tpl->assign("type_video",$this->type_video);
		$this->tpl->assign("type_img",$this->type_img);
		$this->tpl->assign("type_file",$this->type_file);
		$this->tpl->assign("file_uptype",$this->file_uptype);
	}

	function files_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		sys_popedom("files:list","tpl");
		$input_type = $this->trans_lib->safe("type");
		if($input_type != "video" && $input_type != "img" && $input_type != "download")
		{
			$input_type = "all";
		}
		$this->tpl->assign("input_type",$input_type);
		$page_url = site_url("files")."type=".rawurlencode($input_type)."&";
		if($input_type == "video")
		{
			$condition = "ftype IN('".implode("','",$this->type_video)."')";
			$this->upfile_m->set_condition($condition);
		}
		elseif($input_type == "img")
		{
			$condition = "ftype IN('".implode("','",$this->type_img)."')";
			$this->upfile_m->set_condition($condition);
		}
		elseif($input_type== "download")
		{
			$condition = "ftype IN('".implode("','",$this->type_file)."')";
			$this->upfile_m->set_condition($condition);
		}
		//查看postdate数据
		$postdate = $this->trans_lib->safe("postdate");
		if($postdate)
		{
			$condition = "postdate>='".strtotime($postdate)."'";
			$this->upfile_m->set_condition($condition);
			$page_url .= "postdate=".rawurlencode($postdate)."&";
		}
		$keywords = $this->trans_lib->safe("keywords");
		if($keywords)
		{
			$condition = "(title LIKE '%".$keywords."%' OR filename LIKE '%".$keywords."%')";
			$this->upfile_m->set_condition($condition);
			$page_url .= "keywords=".rawurlencode($keywords)."&";
		}
		$total = $this->upfile_m->get_count();//取得总数
		$pagelist = $this->page_lib->page($page_url,$total);
		$this->tpl->assign("pagelist",$pagelist);
		$pageid = $this->trans_lib->int(SYS_PAGEID);
		$rslist = $this->upfile_m->get_list($pageid);
		$this->tpl->assign("rslist",$rslist);
		$this->tpl->assign("page_url",$page_url.SYS_PAGEID."=".$pageid);
		$this->tpl->display("upfiles.html");
	}

	function update_name_f()
	{
		sys_popedom("files:modify","ajax");
		$id = $this->trans_lib->int("id");
		$tmpname = $this->trans_lib->safe("tmpname");
		if(!$id)
		{
			exit("error:没有指定ID");
		}
		if(!$tmpname)
		{
			exit("error:名称不允许为空！");
		}
		$array = array();
		$array["title"] = $tmpname;
		//判断是否有FLV
		$array["flv_pic"] = $this->trans_lib->safe("flv_pic");
		$this->upfile_m->save($array,$id);
		exit("ok");
	}

	//批量删除操作
	function del_f()
	{
		sys_popedom("files:delete","ajax");
		$id = $this->trans_lib->safe("id");
		if(!$id)
		{
			exit("error:没有指定删除ID！");
		}
		$rslist = $this->upfile_m->get_filelist($id);
		if(!$rslist)
		{
			exit("error:没有找到相关附件信息！");
		}
		foreach($rslist AS $key=>$value)
		{
			$this->file_lib->rm($value);
		}
		$this->upfile_m->del($id);
		exit("ok");
	}

	//设置附件上传参数
	function set_f()
	{
		if(file_exists(ROOT_DATA."attachment.php"))
		{
			include(ROOT_DATA."attachment.php");
			$this->tpl->assign("rs",$_sys);
		}
		$this->tpl->display("attachment.html");
	}

	function setok_f()
	{
		sys_popedom("files:setting","tpl");
		$rs = array();
		if($_POST && is_array($_POST) && count($_POST)>0)
		{
			foreach($_POST AS $key=>$value)
			{
				$rs[$key] = $this->trans_lib->safe($key);
			}
		}
		//判断如何附件使用activex上传时，则创建两个目录
		if($rs["file_uptype"] == "activex")
		{
			$this->file_lib->make(ROOT.SYS_UP_PATH."/xu_temp_");
			$this->file_lib->make(ROOT.SYS_UP_PATH."/xu_temp");
		}
		$this->file_lib->vi($rs,ROOT_DATA."attachment.php","_sys");
		error("附件参数配置成功！",site_url("files"));
	}
}
?>