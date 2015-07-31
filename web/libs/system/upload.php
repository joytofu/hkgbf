<?php
/***********************************************************
	Filename: libs/system/upload.php
	Note	: 上传文件类
	Version : 3.0
	Author  : qinggan
	Update  : 2011-03-14
***********************************************************/
//引入phpmail控件发送邮件
class upload_lib
{
	var $type_video;
	var $type_img;
	var $type_file;
	var $file_save_type;
	var $file_ext;//所有扩展
	var $app;
	var $ifset = false;
	function __construct()
	{
		//$this->app = sys_init();
	}

	function upload_lib()
	{
		$this->__construct();
	}

	//上传模式
	function upload_mode($type="swf")
	{
		$this->upload_mode = $type;
	}

	function auto_app()
	{
		$app = sys_init();
		$this->app = $app;
	}

	//开始上传文件
	function upload($inputname,$uid=0,$sid="")
	{
		$this->auto_app();
		if(!$inputname) return false;
		if(!$this->ifset) $this->setting();//设置常规配置
		$path = $this->path();//取得存储的路径
		if(!isset($_FILES[$inputname]))
		{
			return false;
		}
		//生成新的文件名称
		$file_name = substr(md5(time().rand(0,9999)),9,16);
		$zip_filename = $file_name;//如果是zip压缩包
		$path_info = pathinfo($_FILES[$inputname]['name']);
		$file_extension = strtolower($path_info["extension"]);
		$file_name .= ".".$file_extension;
		$tmp_title = $_FILES[$inputname]['name'];
		if(!@copy($_FILES[$inputname]["tmp_name"],$path.$file_name))
		{
			return false;
		}
		if(!in_array($file_extension,$this->file_ext))
		{
			if(function_exists("gzcompress"))
			{
				//生成zip压缩包
				$this->app->load_lib("zip");
				$tmp_contents = $this->app->file_lib->cat($path.$file_name);
				$this->app->zip_lib->addFile($tmp_contents,$tmp_title);
				unset($tmp_contents);
				//存储
				$this->app->zip_lib->output($path.$zip_filename.".zip");
				$this->app->file_lib->rm($path.$file_name);//删除上传的文件
				$file_name = $zip_filename.".zip";
			}
			else
			{
				//如果不支持在线压缩，则重命名上传的附件为zip
				$this->file_lib->mv($path.$file_name,$path.$file_name.".link.zip");
				$file_name = $zip_filename.".link.zip";
			}
			$file_extension = "zip";
		}
		$array = array();
		//存储数据
		$array["title"] = $tmp_title;
		$array["filename"] = str_replace(ROOT,"",$path.$file_name);
		$array["postdate"] = time();
		$array["ftype"] = $file_extension;
		$array["uid"] = $uid;
		$array["sessid"] = $sid;
		$insert_id = $this->app->upfile_m->save($array);
		if(!$insert_id)
		{
			$this->app->file_lib->rm($path.$file_name);
			return false;
		}
		//生成缩略图及各种规格图片
		if(in_array($file_extension,$this->type_img))
		{
			$this->go_picture($insert_id,$path.$file_name,$path);
		}
		return $insert_id;
	}

	function save_pic($pic)
	{
		$this->auto_app();
		if(!$pic || !file_exists($pic))
		{
			return false;
		}
		$path = $this->path();//取得存储的路径
		$file_name = substr(md5($pic.rand(0,999)),9,16);
		$path_info = pathinfo($pic);
		$file_extension = strtolower($path_info["extension"]);
		$file_name .= ".".$file_extension;
		$tmp_title = basename($pic);
		$img = $this->app->html_lib->get_content($pic);//存储新图片
		$this->app->file_lib->save_pic($img,$path.$file_name);//存储新图片
		//存储图片
		$array = array();
		//存储数据
		$array["title"] = $tmp_title;
		$array["filename"] = str_replace(ROOT,"",$path.$file_name);
		$array["postdate"] = time();
		$array["ftype"] = $file_extension;
		$array["uid"] = "";
		$array["sessid"] = "";
		$this->app->load_model("upfile");
		$insert_id = $this->app->upfile_m->save($array);
		if(!$insert_id)
		{
			$this->app->file_lib->rm($path.$file_name);
			return false;
		}
		//生成缩略图及各种规格图片
		if(in_array($file_extension,$this->type_img))
		{
			$this->go_picture($insert_id,$path.$file_name,$path);
		}
		return $insert_id;
	}

	function go_picture($id,$filename,$path)
	{
		$this->auto_app();
		$this->app->load_lib("gd");
		$thumbfile = $this->app->gd_lib->thumb($filename,$id);
		if($thumbfile)
		{
			$update_array = array();
			$update_array["thumb"] = str_replace(ROOT,"",$path.$thumbfile);
			$this->app->upfile_m->save($update_array,$id);
		}
		$this->_gd_create($id,false);
	}

	function _gd_create($picid,$if_create_thumb=true)
	{
		$this->auto_app();
		@set_time_limit(0);#[设置防止超时]
		$this->app->load_lib("gd");
		$this->app->load_model("gdtype_model",true);
		$this->app->load_model("upfile_model",true);
		$gdlist = $this->app->gdtype_model->get_all(1);
		if(!$gdlist)
		{
			return false;
		}
		$rs = $this->app->upfile_model->get_one($picid);
		if(!$rs || !in_array($rs["ftype"],$this->type_img))
		{
			return false;
		}
		if($if_create_thumb)
		{
			$this->app->gd_lib->thumb($rs["filename"],$picid);
		}
		$gd_rslist = $this->app->upfile_model->pic_gd_list($picid);
		if(!$gd_rslist) $gd_rslist = array();
		foreach($gd_rslist AS $key=>$value)
		{
			if(file_exists($value["filename"]) && is_file($value["filename"]))
			{
				$this->app->file_lib->rm($value["filename"]);
			}
		}
		unset($gd_rslist);
		foreach($gdlist AS $key=>$value)
		{
			$quality = $value["quality"] ? $value["quality"] : 80;
			$this->app->gd_lib->Set("quality",$quality);#[设置图片质量]
			//$cuttype = $value["cuttype"] ? true : false;
			$this->app->gd_lib->SetCut($value["cuttype"]);
			$this->app->gd_lib->Filler(intval($value["border"]),$value["bordercolor"],$value["bgcolor"],$value["bgimg"],intval($value["padding"]));
			$iscopyright = ($value["water"] && file_exists(ROOT.$value["water"])) ? true : false;
			$this->app->gd_lib->iscopyright($iscopyright);
			$this->app->gd_lib->CopyRight($value["water"],$value["picposition"],$value["trans"]);
			$newfile = $value["pictype"]."_".$picid;#[新图片名称]
			$width = intval($value["width"]);
			if(!$width)
			{
				$width = 100;
			}
			$height = intval($value["height"]);
			if(!$height)
			{
				$height = 100;
			}
			$newpic = $this->app->gd_lib->Create($rs["filename"],$newfile,$width,$height);
			$array = array();
			$array["gdtype"] = $value["pictype"];
			$array["pid"] = $picid;
			$array["filename"] = str_replace(basename($rs["filename"]),basename($newpic),$rs["filename"]);
			$_tmp_id = $rslist[$value["pictype"]];
			$this->app->upfile_model->save_gd($array);
		}
		return true;
	}

	function gd_create($picid,$if_create_thumb=true)
	{
		return $this->_gd_create($picid,$if_create_thumb);
	}


	//设置存储路径
	function setting()
	{
		$this->type_video = array("wma","mp3","wmv","asf","mpg","mpeg","avi","asx","rm","rmvb","ram","ra","swf","flv","dat");
		$this->type_img = array("jpg","gif","png","jpeg");
		$this->type_file = array("zip","rar","txt","tgz","tar","gz","pdf");
		$this->file_save_type = "Ym/d";
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
			$this->file_save_type = $_sys["file_save_type"];//重设存储目录
			$this->file_uptype = $_sys["file_uptype"];
		}
		$this->file_ext = array_merge($this->type_video,$this->type_img,$this->type_file);
		$this->ifset = true;
	}

	function path()
	{
		$this->auto_app();
		if(!defined("SYS_UP_PATH")) define("SYS_UP_PATH","upfiles");
		$save_path = ROOT.SYS_UP_PATH."/";
		if($this->file_save_type)
		{
			$save_path .= date($this->file_save_type,$this->app->system_time)."/";
		}
		$this->app->file_lib->make($save_path);//创建存储目录
		if(!file_exists($save_path))
		{
			$save_path = ROOT.SYS_UP_PATH."/";
		}
		return $save_path;
	}

	//设置上传参数
	function setting_button($upload_filetype="all",$note="All Files")
	{
		$this->setting();//获取设置
		if($upload_filetype == "video")
		{
			$uploadtype = $this->type_video;
		}
		elseif($upload_type == "img")
		{
			$uploadtype = $this->type_img;
		}
		else
		{
			$uploadtype = $this->file_ext;
		}
		$rslist = array();
		foreach($uploadtype AS $key=>$value)
		{
			$rslist[] = "*.".$value;
		}
		$swf["type"] = implode(";",$rslist);
		$swf["note"] = $note;
		$this->swf = $swf;
		return $swf;
	}
}
?>