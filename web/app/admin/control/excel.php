<?php
/***********************************************************
	Filename: excel.php
	Note	: Excel导入导出操作
	Version : 4.0
	Author  : qinggan
	Update  : 2011-12-04 03:22
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class excel_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("list");
		$this->load_model("module");
		$this->load_model("cate");
	}

	function index_f()
	{
		sys_popedom("excel:list","tpl");
		$condition = "ctrl_init='list'";
		$mlist = $this->module_m->all_module(0,$condition);
		$this->tpl->assign("mlist",$mlist);
		//取得附件信息
		$filelist = $this->file_list();
		$this->tpl->assign("filelist",$filelist);

		//读取excel文件
		$this->tpl->display("excel/list.html");
	}

	function in_f()
	{
		$this->load_model("excel");
		//读取
		$excelfile = $this->trans_lib->int("excelfile");
		if(!$excelfile)
		{
			error("未指定要导入的文件！",site_url("excel"));
		}
		$this->load_model("upfile");
		$rs = $this->upfile_m->get_one($excelfile);
		if(!$rs || !file_exists($rs["filename"]))
		{
			error("没有相应记录或文件不存在！",site_url("excel"));
		}
		$mid = $this->trans_lib->int("mid");
		if(!$mid)
		{
			error("未指定模块ID号！",site_url("excel"));
		}
		$cid = $this->trans_lib->int("cateid");
		$idlist = $this->trans_lib->safe("idlist");
		$this->load_lib("excel_in");
		$charset = $this->trans_lib->safe("charset");
		if(!$charset) $charset = "gbk";
		$this->excel_in_lib->charset($charset);
		$this->excel_in_lib->set_type($rs["ftype"]);
		$this->excel_in_lib->set_idstring($idlist);
		//取得核心字段
		$sys_list = $this->excel_m->system_fields();
		//取得ext扩展字段
		$this->excel_m->ext_c_fields($mid);
		$ext_list = $this->excel_m->extlist();
		$c_list = $this->excel_m->clist();
		$rslist = $this->excel_in_lib->in($rs["filename"]);
		if(!$rslist)
		{
			error("数据不存在！",site_url("excel"));
		}
		$keylist = $this->excel_in_lib->idlist();//取得字段信息
		//检查字段是否存在
		if(!$keylist || !in_array("title",$keylist))
		{
			error("核心字段：title 不存在！请检查",site_url("excel"));
		}
		//取得核心字段
		foreach($rslist AS $key=>$value)
		{
			$value["module_id"] = $mid;
			$value["cate_id"] = $cid;
			$this->save_data($value,$sys_list,$ext_list,$c_list);
		}
		error("数据导入成功！",site_url("excel","mid=".$mid));
	}

	function save_data($rs,$syslist="title",$extlist="",$clist="")
	{
		if(!$rs)
		{
			return false;
		}
		$syslist = (is_string($syslist) && $syslist) ? explode(",",$syslist) : ($syslist ? $syslist : array());
		$extlist = (is_string($extlist) && $extlist) ? explode(",",$extlist) : ($extlist ? $extlist : array());
		$clist = (is_string($clist) && $clist) ? explode(",",$clist) : ($clist ? $clist : array());
		$sys_rs = $ext_rs = $c_rs = array();
		foreach($rs AS $key=>$value)
		{
			if(in_array($key,$syslist))
			{
				if($key == "thumb")
				{
					$sys_rs["thumb_id"] = $this->save_pic($value);
				}
				elseif($key == "post_date")
				{
					$sys_rs["post_date"] = strtotime($value);
				}
				else
				{
					if($key != "id")
					{
						$sys_rs[$key] = $value;
					}
				}
			}
			elseif(in_array($key,$extlist))
			{
				$ext_rs[$key] = $value;
			}
			elseif(in_array($key,$clist))
			{
				$c_rs[$key] = $value;
			}
		}
		if(!$sys_rs["post_date"])
		{
			$sys_rs["post_date"] = $this->system_time;
		}
		if(!$sys_rs["ip"])
		{
			$sys_rs["ip"] = sys_ip();
		}
		if(!$sys_rs["author"])
		{
			$sys_rs["author"] = $_SESSION["admin_name"];
			$sys_rs["author_type"] = "admin";
		}
		$insert_id = $this->list_m->save_sys($sys_rs);//存储数据
		if($insert_id)
		{
			//存储到分类中去
			if($rs["cate_id"])
			{
				$ext_catelist = sys_id_list($rs["cate_id"],"intval");
				$this->list_m->save_catelist($insert_id,$ext_catelist);
			}
			//存储扩展字段
			foreach($extlist AS $key=>$value)
			{
				$array_ext = array();
				$array_ext["id"] = $insert_id;
				$array_ext["field"] = $value;//扩展字段信息
				$array_ext["val"] = $ext_rs[$value];
				$this->list_m->save_ext($array_ext,"ext");
			}
			//
			foreach($clist AS $key=>$value)
			{
				$array_ext = array();
				$array_ext["id"] = $insert_id;
				$array_ext["field"] = $value;//扩展字段信息
				$array_ext["val"] = $c_rs[$value];
				$this->list_m->save_ext($array_ext,"c");
			}
		}
		return $insert_id;
	}

	function save_pic($pic)
	{
		if(!$pic || !file_exists($pic))
		{
			return false;
		}
		return $this->upload_lib->save_pic($pic);
	}

	function in_ajax_f()
	{
		$mid = $this->trans_lib->int("mid");
		if(!$mid)
		{
			exit("未指定模块ID");
		}
		//读取当前模块信息
		$m_rs = $this->module_m->get_one($mid);
		$this->tpl->assign("m_rs",$m_rs);
		//设置分类
		$this->cate_m->langid($_SESSION["sys_lang_id"]);
		$this->cate_m->get_catelist($mid);
		$catelist = $this->cate_m->html_select_array();
		$this->tpl->assign("catelist",$catelist);
		//罗列字段
		$key_list = $this->module_m->fields_index($mid);
		$this->tpl->assign("key_list",$key_list);
		//显示货币类型
		$this->load_model("currency");
		$currency_list = $this->currency_m->get_list();
		$this->tpl->assign("currency_list",$currency_list);

		$this->tpl->display("excel/in.html");
	}

	function filelist_f()
	{
		$rslist = $this->file_list();
		$this->tpl->assign("rslist",$rslist);
		$this->tpl->display("excel/filelist.html");
	}

	function file_list()
	{
		$this->load_model("upfile");
		$this->upfile_m->set_psize(50);
		$condition = "ftype IN('csv','xls','xlsx')";
		$this->upfile_m->set_condition($condition);
		$rslist = $this->upfile_m->get_list($pageid);
		return $rslist;
	}

	//Excel导出
	function out_f()
	{
		$mid = $this->trans_lib->int("mid");
		if(!$mid)
		{
			error("没有指定模块！",site_url("excel"));
		}
		$m_rs = $this->module_m->get_one($mid);
		$type = $this->trans_lib->safe("type");
		if(!$type || ($type && !in_array($type,array("xls","xlsx","csv"))))
		{
			$type = "xls";
		}
		$idlist = $this->trans_lib->safe("idlist");
		if(!$idlist)
		{
			$idlist = "title";
		}
		$outname = $this->trans_lib->safe("outname");
		if(!$outname)
		{
			$outname = $m_rs["title"];
		}
		//取得列表
		$count = $this->trans_lib->int("count");
		if(!$count)
		{
			$count = "1000000";
		}
		$this->list_m->set_condition("m.langid='".$_SESSION["sys_lang_id"]."'");//区分语言
		$this->list_m->set_condition("m.module_id='".$mid."'");
		if($m_rs["if_cate"])
		{
			$cateid = $this->trans_lib->int("cateid");
			if($cateid)
			{
				$cate_array = array($cateid);
				$this->cate_m->get_sonid_array($cate_array,$cateid);
				$this->list_m->set_condition("m.cate_id IN(".implode(",",$cate_array).")");
			}
		}
		$status = $this->trans_lib->int("status");
		if($status)
		{
			$this->list_m->set_condition("m.status='".($status == 1 ? 1 : 0)."'");
		}
		$this->list_m->set_psize($count);
		$rslist = $this->list_m->get_list(0,$m_rs["if_cate"],$m_rs["if_thumb"]);

		//导出结果集
		$this->load_lib("excel_out");
		$this->excel_out_lib->set_type($type);
		$this->excel_out_lib->set_idstring($idlist);
		$this->excel_out_lib->out($rslist,$outname);
	}

	function out_ajax_f()
	{
		$mid = $this->trans_lib->int("mid");
		if(!$mid)
		{
			exit("未指定模块ID");
		}
		//读取当前模块信息
		$m_rs = $this->module_m->get_one($mid);
		$this->tpl->assign("m_rs",$m_rs);
		//设置分类
		$this->cate_m->langid($_SESSION["sys_lang_id"]);
		$this->cate_m->get_catelist($mid);
		$catelist = $this->cate_m->html_select_array();
		$this->tpl->assign("catelist",$catelist);
		//罗列字段
		$key_list = $this->module_m->fields_index($mid);
		$this->tpl->assign("key_list",$key_list);
		//显示货币类型
		$this->load_model("currency");
		$currency_list = $this->currency_m->get_list();
		$this->tpl->assign("currency_list",$currency_list);

		$this->tpl->display("excel/out.html");
	}
}
?>