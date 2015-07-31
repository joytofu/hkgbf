<?php
/***********************************************************
	Filename: app/www/control/open.php
	Note	: 前台弹出窗口应用
	Version : 3.0
	Author  : qinggan
	Update  : 2009-11-24
***********************************************************/
class open_c extends Control
{
	var $type_video = array("wma","mp3","wmv","asf","mpg","mpeg","avi","asx","rm","rmvb","ram","ra","swf","flv","dat");
	var $type_img = array("jpg","gif","png","jpeg");
	var $type_file = array("zip","rar","txt","tgz","tar","gz","pdf");
	var $file_save_type = "Ym/d";
	var $file_uptype = "swf"; //附件上传方式
	function __construct()
	{
		parent::Control();
		$this->load_model("upfile");//读取附件操作类
		$this->load_model("upfile_model",true);//读取附件操作类
		$this->load_lib("upload");
		$this->upload_lib->setting();//初始化
	}

	function open_c()
	{
		$this->__construct();
	}

	//读取列表
	function index_f()
	{
		$input_id = $this->trans_lib->safe("input");
		if(!$input_id) $input_id = "thumb_id";
		$input_view = $this->trans_lib->safe("view");
		if(!$input_view) $input_view = "thumb_view";
		$input_type = $this->trans_lib->safe("type");
		if($input_type != "video" && $input_type != "img" && $input_type != "download")
		{
			$input_type = "img";
		}
		$this->tpl->assign("input_id",$input_id);
		$this->tpl->assign("input_view",$input_view);
		//判断是否有iframe_id
		//非会员，晕示登录窗口
		$page_url = site_url("open")."input=".rawurlencode($input_id)."&";
		$page_url.= "view=".rawurlencode($input_view)."&type=".$input_type."&";
		$iframe_id = $this->trans_lib->safe("iframe_id");
		if($iframe_id)
		{
			$this->tpl->assign("iframe_id",$iframe_id);
			$page_url .= "iframe_id=".rawurlencode($iframe_id)."&";
		}
		$condition = $_SESSION["user_id"] ? " uid='".$_SESSION["user_id"]."' " : " sessid='".$this->session_lib->sessid()."' ";
		if($input_type == "video")
		{
			$condition .= " AND ftype IN('".implode("','",$this->type_video)."')";
			$tmp_array = array();
			foreach($this->upload_lib->type_video AS $key=>$value)
			{
				$tmp_array[] = "*.".$value;
			}
			$this->tpl->assign("swfupload_filetype",implode(";",$tmp_array));
			$this->tpl->assign("swfupload_note","Video Files");
		}
		elseif($input_type == "img")
		{
			$condition .= " AND ftype IN('".implode("','",$this->type_img)."')";
			$tmp_array = array();
			foreach($this->upload_lib->type_img AS $key=>$value)
			{
				$tmp_array[] = "*.".$value;
			}
			$this->tpl->assign("swfupload_filetype",implode(";",$tmp_array));
			$this->tpl->assign("swfupload_note","Image Files");
		}
		else
		{
			$this->tpl->assign("swfupload_filetype","*.*");
			$this->tpl->assign("swfupload_note","All Files");
		}
		$this->upfile_m->set_condition($condition);
		//查看postdate数据
		$page_url_2 = $page_url;
		$postdate = $this->trans_lib->safe("postdate");
		if($postdate)
		{
			$condition = "postdate>='".strtotime($postdate)."'";
			$this->upfile_m->set_condition($condition);
			$page_url_2 .= "postdate=".rawurlencode($postdate)."&";
		}
		$keywords = $this->trans_lib->safe("keywords");
		if($keywords)
		{
			$condition = "(title LIKE '%".$keywords."%' OR filename LIKE '%".$keywords."%')";
			$this->upfile_m->set_condition($condition);
			$page_url_2 .= "keywords=".rawurlencode($keywords)."&";
		}
		$total = $this->upfile_m->get_count();//取得总数
		$this->upfile_m->set_psize(15);
		$this->page_lib->set_psize(15);
		$pagelist = $this->page_lib->page($page_url_2,$total);
		$this->tpl->assign("pagelist",$pagelist);
		$pageid = $this->trans_lib->int(SYS_PAGEID);
		$rslist = $this->upfile_m->get_list($pageid);
		$this->tpl->assign("rslist",$rslist);
		$this->tpl->assign("page_url",$page_url);
		//加载模板
		$tplfile = "list_".$input_type;
		if($input_type == "img" && $input_id == "thumb_id")
		{
			$tplfile = "list_thumb";
		}
		$this->tpl->display("open/".$tplfile.".".$this->tpl->ext);
	}

	function img_f()
	{
		$input_id = $this->trans_lib->safe("input");
		if(!$input_id) $input_id = "ico";
		$input_type = "img";
		$this->tpl->assign("input_id",$input_id);
		$page_url = site_url("open,img")."input=".rawurlencode($input_id)."&";
		$condition = $_SESSION["user_id"] ? " uid='".$_SESSION["user_id"]."' " : " sessid='".$this->session_lib->sessid()."' ";
		$this->upfile_m->set_condition($condition);
		$condition = "ftype IN('".implode("','",$this->type_img)."')";
		$this->upfile_m->set_condition($condition);
		$tmp_array = array();
		foreach($this->type_img AS $key=>$value)
		{
			$tmp_array[] = "*.".$value;
		}
		$this->tpl->assign("swfupload_filetype",implode(";",$tmp_array));
		$this->tpl->assign("swfupload_note","Image Files");
		$page_url_2 = $page_url;
		$postdate = $this->trans_lib->safe("postdate");
		if($postdate)
		{
			$condition = "postdate>='".strtotime($postdate)."'";
			$this->upfile_m->set_condition($condition);
			$page_url_2 .= "postdate=".rawurlencode($postdate)."&";
		}
		$keywords = $this->trans_lib->safe("keywords");
		if($keywords)
		{
			$condition = "(title LIKE '%".$keywords."%' OR filename LIKE '%".$keywords."%')";
			$this->upfile_m->set_condition($condition);
			$page_url_2 .= "keywords=".rawurlencode($keywords)."&";
		}
		$total = $this->upfile_m->get_count();//取得总数
		$pagelist = $this->page_lib->page($page_url_2,$total);
		$this->tpl->assign("pagelist",$pagelist);
		$pageid = $this->trans_lib->int(SYS_PAGEID);
		$rslist = $this->upfile_m->get_list($pageid);
		$this->tpl->assign("rslist",$rslist);
		$this->tpl->assign("page_url",$page_url);
		$this->tpl->display("open/thumb.html");
	}

	//通过swf+php进行附件上传，支持大文件
	function upload_f()
	{
		$uid = $_SESSION["user_id"] ? $_SESSION["user_id"] : 0;
		$sessid = $this->session_lib->sessid();
		$insert_id = $this->upload_lib->upload("Filedata",$uid,$sessid);
		if($insert_id)
		{
			$rs = $this->upfile_m->get_one($insert_id);
			if(!$rs)
			{
				exit("error");
			}
			exit($this->json_lib->encode($rs));
		}
		else
		{
			exit("error");
		}
	}


	//通过Ajax预览图片
	function ajax_preview_img_f()
	{
		$idstring = $this->trans_lib->safe("idstring");
		if(!$idstring)
		{
			exit("empty");
		}
		$idstring = sys_id_string($idstring,",","intval");
		$rslist = $this->upfile_m->piclist($idstring);
		exit($this->json_lib->encode($rslist));
	}

	//通过Ajax来执行图片排序
	function sort_order_f()
	{
		$order_list = $this->trans_lib->safe("taxis");//数组排序
		if(!$order_list)
		{
			exit("error");
		}
		natsort($order_list);//排序
		$keys = array_keys($order_list);
		$string = sys_id_string($keys);
		exit($string);
	}

	//预览图片
	function preview_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			error("没有选择要预览的ID");
		}
		$rs = $this->upfile_m->get_one($id);
		$this->tpl->assign("rs",$rs);
		$ftype = "file";
		if(in_array($rs["ftype"],$this->upload_lib->type_img))
		{
			$rslist = $this->upfile_model->pic_gd_list($id);
			$this->tpl->assign("rslist",$rslist);
			$ftype = "img";
		}
		if(in_array($rs["ftype"],$this->upload_lib->type_video))
		{
			$ftype = "video";
			if(file_exists(ROOT_DATA."system_".$_SESSION["sys_lang_id"].".php"))
			{
				include(ROOT_DATA."system_".$_SESSION["sys_lang_id"].".php");
			}
			else
			{
				$_sys = array();
				$_sys["video_width"] = "500";
				$_sys["video_height"] = "400";
			}
			$this->tpl->assign("_sys",$_sys);
		}
		//判断是否预览
		$pretype = $this->trans_lib->safe("pretype");
		if($pretype && $pretype == "download")
		{
			$ftype = "file";
		}
		$this->tpl->assign("ftype",$ftype);
		$this->tpl->display("open/preview.html");
	}

	function fck_f()
	{
		$fck_id = $this->trans_lib->safe("fck_id");
		if(!$fck_id) $fck_id = "content";
		$input_type = $this->trans_lib->safe("type");
		if($input_type != "video" && $input_type != "img" && $input_type != "download")
		{
			$input_type = "img";
		}
		$this->tpl->assign("fck_id",$fck_id);
		$this->tpl->assign("input_type",$input_type);
		$page_url = site_url("open")."input=".rawurlencode($input_id)."&";
		$page_url.= "view=".rawurlencode($input_view)."&type=".rawurlencode($input_type)."&";
		$page_url = site_url("open,fck")."fck_id=".rawurlencode($fck_id)."&";
		$page_url.= "type=".rawurlencode($input_type)."&";
		$iframe_id = $this->trans_lib->safe("iframe_id");
		if($iframe_id)
		{
			$this->tpl->assign("iframe_id",$iframe_id);
			$page_url .= "iframe_id=".rawurlencode($iframe_id)."&";
		}
		$condition = $_SESSION["user_id"] ? " uid='".$_SESSION["user_id"]."' " : " sessid='".$this->session_lib->sessid()."' ";
		$this->upfile_m->set_condition($condition);
		if($input_type == "video")
		{
			$condition = " ftype IN('".implode("','",$this->upload_lib->type_video)."')";
			$this->upfile_m->set_condition($condition);
			//设置上传的附件类型
			$tmp_array = array();
			foreach($this->type_video AS $key=>$value)
			{
				$tmp_array[] = "*.".$value;
			}
			$this->tpl->assign("swfupload_filetype",implode(";",$tmp_array));
			$this->tpl->assign("swfupload_note","Video Files");
		}
		elseif($input_type == "img")
		{
			$condition = "ftype IN('".implode("','",$this->upload_lib->type_img)."')";
			$this->upfile_m->set_condition($condition);
			$tmp_array = array();
			foreach($this->type_img AS $key=>$value)
			{
				$tmp_array[] = "*.".$value;
			}
			$this->tpl->assign("swfupload_filetype",implode(";",$tmp_array));
			$this->tpl->assign("swfupload_note","Image Files");
		}
		else
		{
			$this->tpl->assign("swfupload_filetype","*.*");
			$this->tpl->assign("swfupload_note","All Files");
		}
		$this->upfile_m->set_condition($condition);
		//查看postdate数据
		$page_url_2 = $page_url;
		$postdate = $this->trans_lib->safe("postdate");
		if($postdate)
		{
			$condition = " AND postdate>='".strtotime($postdate)."'";
			$this->upfile_m->set_condition($condition);
			$page_url_2 .= "postdate=".rawurlencode($postdate)."&";
		}
		$keywords = $this->trans_lib->safe("keywords");
		if($keywords)
		{
			$condition = "(title LIKE '%".$keywords."%' OR filename LIKE '%".$keywords."%')";
			$this->upfile_m->set_condition($condition);
			$page_url_2 .= "keywords=".rawurlencode($keywords)."&";
		}
		$total = $this->upfile_m->get_count();//取得总数
		$pagelist = $this->page_lib->page($page_url_2,$total);
		$this->tpl->assign("pagelist",$pagelist);
		$pageid = $this->trans_lib->int(SYS_PAGEID);
		$rslist = $this->upfile_m->get_list($pageid);
		$this->tpl->assign("rslist",$rslist);
		$this->tpl->assign("page_url",$page_url);
		//加载模板
		$tplfile = "fck_".$input_type.".".$this->tpl->ext;
		//加载GD类型
		$this->load_model("gdtype");
		$gdlist = $this->gdtype_m->get_all();
		$this->tpl->assign("gdlist",$gdlist);
		$this->tpl->display("open/".$tplfile);
	}

	//获取图片信息，返回json字符串
	function fck_img_f()
	{
		$idstring = $this->trans_lib->safe("idstring");
		if(!$idstring)
		{
			exit("empty");
		}
		$idstring = sys_id_string($idstring,",","intval");
		$gd_type = $this->trans_lib->safe("gd_type");
		$rslist = $this->upfile_m->pic_list($idstring,$gd_type);
		if(!$rslist)
		{
			exit("empty");
		}
		exit($this->json_lib->encode($rslist));
	}
}
?>