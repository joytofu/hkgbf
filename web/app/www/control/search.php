<?php
/***********************************************************
	Filename: app/www/control/search.php
	Note	: 信息搜索
	Version : 3.0
	Author  : qinggan
	Update  : 2010-05-20
***********************************************************/
class search_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("search");
		$this->load_model("module");
		$this->load_model("cate");
	}

	function search_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		$pageurl = $this->url("search");
		//搜索条件之：语言包
		$list_condition = "langid='".$_SESSION["sys_lang_id"]."'";
		//搜索条件之：分类
		$cateid = $this->trans_lib->int("cateid");
		if($cateid)
		{
			$this->tpl->assign("cid",$cateid);
			$cate_rs = $this->cate_m->get_one($cateid);
			$this->tpl->assign("cate_rs",$cate_rs);
			$cate_array = array($cateid);
			$this->cate_m->get_sonid_array($cate_array,$cateid);
			$cate_string = sys_id_string($cate_array);
			$list_condition .= " AND cate_id IN(".$cate_string.")";
			$pageurl .= "cateid=".$cateid."&";
			$mid = $cate_rs["module_id"];
		}
		else
		{
			$mid = $this->trans_lib->int("mid"); //模块ID
		}
		if($mid)
		{
			$mod_rs = $this->module_m->get_one($mid);
			$this->tpl->assign("m_rs",$mod_rs);
			$list_condition .= " AND module_id ='".$mid."'";
		}
		//搜索条件之：模块
		$mid_string = $mid;
		if(!$mid)
		{
			$modlist = $this->module_m->get_all_module();//取得所有支持搜索的模块引挈
			if(!$modlist)
			{
				error($this->lang["search_not_rs"],$this->url());
			}
			$mid_rs = array();
			foreach($modlist AS $key=>$value)
			{
				$mid_rs[] = $value["id"];
			}
			$mid_string = sys_id_string($mid_rs);
			$list_condition .= " AND module_id IN(".$mid_string.")";
		}
		//搜索条件之：关键字
		//兼容旧版操作
		$keytype = $this->trans_lib->safe("keytype");
		$keywords = $this->trans_lib->safe("keywords");
		if($keywords && (!$keytype || $keytype == "title"))
		{
			$list_condition .= " AND (title LIKE '%".$keywords."%' OR subtitle LIKE '%".$keywords."%' OR keywords LIKE '%".$keywords."%' OR description LIKE '%".$keywords."%' OR note LIKE '%".$keywords."%')";
			$pageurl .= "keywords=".rawurlencode($keywords)."&";
		}
		//搜索条件之：扩展字段
		$ext_keywords = $this->trans_lib->safe("ext_keywords");
		if(!$ext_keywords || !is_array($ext_keywords))
		{
			$ext_keywords = array();
			//兼容旧版操作，这里是关键数据类型
			if($mid)
			{
				if($mod_rs["link_id"] && $mod_rs["search_id"])
				{
					$ext_keywords[$mod_rs["search_id"]] = $this->trans_lib->safe($mod_rs["search_id"]);
				}
			}
			if($keytype && $keytype != "title")
			{
				$ext_keywords[$keytype] = $this->trans_lib->safe($keywords);
			}
		}
		$this->tpl->assign("ext_keywords",$ext_keywords);
		foreach($ext_keywords AS $key=>$value)
		{
			$pageurl .= "ext_keywords[".rawurlencode($key)."]=".rawurlencode($value)."&";
		}
		//设置每页显示数量
		$pageid = $this->trans_lib->int(SYS_PAGEID);
		$psize = $this->sys_config["search_page"];
		$this->search_m->set_psize($psize);
		$this->search_m->set_pageid($pageid);
		$gdtype = $this->sys_config["search_thumb"] ? $this->sys_config["search_thumb"] : false;//判断是否有启用缩略图
		$this->search_m->set_thumb($gdtype);
		//取得内容
		$this->search_m->get_all_id($list_condition,$ext_keywords);
		$rslist = $this->search_m->get_list();
		$total = $this->search_m->get_count();
		$this->tpl->assign("rslist",$rslist);
		$this->page_lib->set_psize($psize);
		$pagelist = $this->page_lib->page($pageurl,$total,true,false);
		$this->tpl->assign("pagelist",$pagelist);
		$sitetitle = $this->lang["search"];
		$this->tpl->assign("sitetitle",$sitetitle);
		$array[0]["title"] = $this->lang["search"];
		$this->tpl->assign("leader",$array);
		//定制搜索模板
		$tplfile = "search";
		$chk_tplfile = ROOT.$this->tpl->tpldir."/search.".$this->tpl->ext;
		if($mid && $mod_rs)
		{
			$chk_tplfile = ROOT.$this->tpl->tpldir."/search_".$mod_rs["identifier"].".".$this->tpl->ext;
			if(file_exists($chk_tplfile))
			{
				$tplfile = "search_".$mod_rs["identifier"];
			}
		}
		$this->tpl->display($tplfile.".".$this->tpl->ext);
	}

	//取得某个模块下的分类
	function catelist_f()
	{
		$this->load_model("cate");
		$mid = $this->trans_lib->int("mid");
		if(!$mid)
		{
			sys_html2js("false");
		}
		$catestring = $this->trans_lib->safe("catestring");
		$extstring = $this->trans_lib->safe("extstring");
		$cateid = $this->trans_lib->int("cateid");
		$condition = " m.insearch='1' ";
		$rs = $this->cate_m->get_catelist($mid,$condition);
		if(!$rs)
		{
			sys_html2js("false");
		}
		$html = $this->cate_m->html_select($catestring,$cateid,$this->lang["all_category"],$extstring);
		sys_html2js($html);
	}

	//读取模块下的联动数据
	function datalink_f()
	{
		$mid = $this->trans_lib->int("mid");
		if(!$mid)
		{
			exit("false");
		}
		$rs = $this->module_m->get_one($mid);
		if(!$rs["link_id"] || !$rs["search_id"])
		{
			sys_html2js("false");
		}
		$this->load_model("datalink");
		$rslist = $this->datalink_m->get_list($rs["link_id"]);
		if(!$rslist)
		{
			sys_html2js("false");
		}
		$t_rs = array();
		$t_rs["input"] = $rs["search_id"];
		foreach($rslist AS $key=>$value)
		{
			if(!$value["pid"])
			{
				$t_rs["parent"][] = $value;
			}
			else
			{
				$t_rs["son"][] = $value;
			}
		}
		sys_html2js($this->json_lib->encode($t_rs));
	}

	//嵌入式代码
	function iframe_f()
	{
		$this->cate_m->set_langid($_SESSION["sys_lang_id"]);
		$cateid = $this->trans_lib->int("cateid");
		$mid = $this->trans_lib->int("mid");
		$keywords = $this->trans_lib->safe("keywords");
		$this->tpl->assign("keywords",$keywords);
		$this->tpl->assign("mid",$mid);
		$this->tpl->assign("cateid",$cateid);
		$ext_keywords = $this->trans_lib->safe("ext_keywords");
		$this->tpl->assign("ext_keywords",$ext_keywords);
		if($mid)
		{
			$rs = $this->cate_m->get_catelist($mid,$condition);
			if($rs)
			{
				$catelist = $this->cate_m->html_select_array();
				$this->tpl->assign("catelist",$catelist);
			}
			//扩展字段搜索
			$tmplist = $this->module_m->fields_index($mid,1);
			$input = array("text","radio","textarea","select","opt","module");
			if($tmplist)
			{
				$jslist = $rslist = array();
				$this->load_lib("phpok_input");
				foreach($tmplist AS $key=>$value)
				{
					if(in_array($value["input"],$input) && $value["if_search"])
					{
						$tmp_value = $value;
						if($ext_keywords[$value["identifier"]])
						{
							$tmp_value["default_val"] = $ext_keywords[$value["identifier"]];
						}
						//判断类型
						if($value["input"] == "module")
						{
							$this->load_model("list");
							$my_tmplist = $this->list_m->getlist_for_input($value["link_id"],100);
							if(!$my_tmplist) continue;
							$list_val = array();
							$list_val[] = ",".$this->lang["datalink_select"];
							foreach($my_tmplist AS $k=>$v)
							{
								if($v["title"])
								{
									$list_val[] = $v["id"].",".str_replace(",","，",$v["title"]);
								}
							}
							$tmp_value["list_val"] = implode("\n",$list_val);
							$tmp_value["input"] = "select";
						}
						$tmp_value["identifier"] = "ext_keywords[".$value["identifier"]."]";
						$tmp_value["sub_note"] = "";
						$tmp_value["width"] = "";
						//d($tmp_value);
						$extlist = $this->phpok_input_lib->get_html($tmp_value);
						$rslist[] = $extlist;
						$jslist[] = $tmp_value;
					}
				}
				$this->tpl->assign("rslist",$rslist);
				$this->tpl->assign("jslist",$jslist);
			}
		}
		$bgcolor = $this->trans_lib->safe("bgcolor");
		$this->tpl->assign("bgcolor",$bgcolor);
		$tplfile = $this->trans_lib->safe("tplfile");
		if($tplfile && file_exists(ROOT.$this->tpl->tpldir."/".$tplfile.".".$this->tpl->ext))
		{
			$this->tpl->display($tplfile.".".$this->tpl->ext);
		}
		else
		{
			$this->tpl->display("iframe_search.".$this->tpl->ext);
		}
	}

	//搜索页面
	function page_f()
	{
		$sitetitle = $this->lang["search"];
		$this->tpl->assign("sitetitle",$sitetitle);
		$array[0]["title"] = $this->lang["search"];
		$this->tpl->assign("leader",$array);
		$this->tpl->display("search.".$this->tpl->ext);
	}
}
?>