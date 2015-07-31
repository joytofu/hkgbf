<?php
/***********************************************************
	Filename: app/admin/control/collection.php
	Note	: 采集管理中心
	Version : 3.0
	Author  : qinggan
	Update  : 2011-04-06
***********************************************************/
class collection_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("collection");
	}

	function collection_c()
	{
		$this->__construct();
	}

	//采集项目列表
	function index_f()
	{
		sys_popedom("collection:list","tpl");
		$psize = defined("SYS_PSIZE") ? SYS_PSIZE : 30;
		$pageid = $this->trans_lib->int(SYS_PAGEID);
		$condition = "1=1";
		$this->collection_m->set_psize($psize);
		$rslist = $this->collection_m->get_all($condition,$pageid);
		$this->tpl->assign("rslist",$rslist);
		$total = $this->collection_m->get_count($condition);
		$page_url = $this->url("collection");
		$pagelist = $this->page_lib->page($page_url,$total);
		$this->tpl->assign("pagelist",$pagelist);
		$this->tpl->display("collection/list.html");
	}

	function set_f()
	{
		$id = $this->trans_lib->int("id");
		if($id)
		{
			sys_popedom("collection:modify","tpl");
			$rs = $this->collection_m->get_one($id);
			$rs["list_tags_start"] = $this->trans_lib->html_edit($rs["list_tags_start"]);
			$rs["list_tags_end"] = $this->trans_lib->html_edit($rs["list_tags_end"]);
			$rs["url_tags"] = $this->trans_lib->html_edit($rs["url_tags"]);
			$this->tpl->assign("rs",$rs);
			$this->tpl->assign("id",$id);
		}
		else
		{
			sys_popedom("collection:add","tpl");
		}
		//指定模块信息
		$this->load_model("module");//读取模块
		$modulelist = $this->module_m->module_list(0);
		$this->tpl->assign("modulelist",$modulelist);

		//加载分类
		$this->load_model("cate");
		$this->cate_m->langid($_SESSION["sys_lang_id"]);
		$this->cate_m->get_all();
		$this->cate_m->format_list(0,0);
		$catelist = $this->cate_m->flist();
		if(!$catelist) $catelist = array();
		foreach($catelist AS $key=>$value)
		{
			$value["space"] = "";
			for($i=0;$i<$value["level"];$i++)
			{
				$value["space"] .= "　　";
			}
			$catelist[$key] = $value;
		}
		$this->tpl->assign("catelist",$catelist);
		$this->tpl->display("collection/set.html");
	}

	function setok_f()
	{
		$id = $this->trans_lib->int("id");
		//授权
		$id ? sys_popedom("collection:modify","tpl") : sys_popedom("collection:add","tpl");
		$title = $this->trans_lib->safe("title");
		if(!$title)
		{
			error("没有指定要采集的网站名称！",$this->url("collection,set","id=".$id));
		}
		$linkurl = $this->trans_lib->safe("linkurl");
		if(!$linkurl)
		{
			error("没有指定要采集的网址",$this->url("collection,set","id=".$id));
		}
		$cateid = $this->trans_lib->safe("cateid");
		if(!$cateid)
		{
			error("请选择要发布的目标分类！",$this->url("collection,set","id=".$id));
		}
		$tmp_array = explode(":",$cateid);
		$cid = $tmp_array[1];
		$mid = $tmp_array[0];
		$listurl = $this->trans_lib->safe("listurl");
		$list_tags_start = $this->trans_lib->html("list_tags_start");
		$list_tags_end = $this->trans_lib->html("list_tags_end");
		$url_tags = $this->trans_lib->html("url_tags");
		$url_charset = $this->trans_lib->safe("url_charset");
		$array = array();
		$array["title"] = $title;
		$array["linkurl"] = $linkurl;
		$array["cateid"] = $cid;
		$array["mid"] = $mid;
		$array["listurl"] = $listurl;
		$array["list_tags_start"] = $this->trans_lib->edit_html($list_tags_start);
		$array["list_tags_end"] = $this->trans_lib->edit_html($list_tags_end);
		$array["url_tags"] = $this->trans_lib->edit_html($url_tags);
		$array["url_charset"] = $url_charset;
		$array["is_gzip"] = $this->trans_lib->int("is_gzip");
		$array["is_proxy"] = $this->trans_lib->int("is_proxy");
		$array["proxy_service"] = $this->trans_lib->safe("proxy_service");
		$array["proxy_user"] = $this->trans_lib->safe("proxy_user");
		$array["proxy_pass"] = $this->trans_lib->safe("proxy_pass");
		if(!$id)
		{
			$insert_id = $this->collection_m->save($array);
			if(!$insert_id)
			{
				error("采集站点信息设置失败，请检查！",$this->url("collection,set"));
			}
			//增加主题字段
			$array = array();
			$array["cid"] = $insert_id;
			$array["title"] = "主题";
			$array["identifier"] = "title";
			$array["tags_type"] = "var";
			$array["rules_start"] = "<title>";
			$array["rules_end"] = "</title>";
			$array["del_html"] = 1;
			//
			$this->collection_m->save_tags($array);
			$sys_key = $this->collection_m->get_list_field();//取得核心字段
			$title_key = array();
			$title_key["status"] = "状态";
			$title_key["author"] = "发布人员";
			$title_key["author_type"] = "发布人性质";
			$title_key["keywords"] = "关键字";
			$title_key["description"] = "SEO描述";
			$title_key["note"] = "简要描述";
			$title_key["hits"] = "查看次数";
			$title_key["post_date"] = "发布时间";
			$title_key["langid"] = "语言ID";
			$title_key["ip"] = "访问者IP";
			$title_key["qty"] = "产品数量";
			$title_key["is_qty"] = "是否启用数量";
			$title_key["price"] = "产品价格";
			$title_key["price_currency"] = "货币类型";
			$title_key["weight"] = "重量";
			$title_key["qty_unit"] = "数量单位";
			foreach($sys_key AS $key=>$value)
			{
				if($value == "id") continue;
				if($value == "module_id") continue;
				if($value == "cate_id") continue;
				if($value == "title") continue;
				if($value == "subtitle") continue;
				if($value == "style") continue;
				if($value == "link_url") continue;
				if($value == "target") continue;
				if($value == "identifier") continue;
				if($value == "good_hits") continue;
				if($value == "bad_hits") continue;
				if($value == "modify_date") continue;
				if($value == "thumb_id") continue;
				if($value == "istop") continue;
				if($value == "isvouch") continue;
				if($value == "isbest") continue;
				if($value == "points") continue;
				if($value == "replydate") continue;
				if($value == "taxis") continue;
				if($value == "tplfile") continue;
				if($value == "hidden") continue;
				if($value == "htmltype") continue;
				$array = array();
				$array["cid"] = $insert_id;
				$array["title"] = $title_key[$value] ? $title_key[$value] : $value;
				$array["identifier"] = $value;
				if($value == "ip")
				{
					$array["rules"] = "{ip}";
				}
				elseif($value == "author")
				{
					$array["rules"] = "{session.admin_name}";
				}
				elseif($value == "author_type")
				{
					$array["rules"] = "admin";
				}
				elseif($value == "langid")
				{
					$array["rules"] = $_SESSION["sys_lang_id"];
				}
				elseif($value == "htmltype")
				{
					$array["rules"] = "date";
				}
				elseif($value == "status")
				{
					$array["rules"] = "1";
				}
				elseif($value == "post_date")
				{
					$array["rules"] = "{post_date}";
				}
				elseif($value == "qty" || $value == "is_qty" || $value["price"] || $value["weight"])
				{
					$array["rules"] = "0";
				}
				elseif($value == "price_currency")
				{
					$array["rules"] = "CNY";
				}
				$array["ifsystem"] = "list";
				$array["tags_type"] = "string";
				$this->collection_m->save_tags($array);
			}
			error("采集站点信息设置操作成功！",$this->url("collection"));
		}
		else
		{
			$this->collection_m->save($array,$id);
			error("模块信息已经修改成功！",$this->url("collection"));
		}
	}

	function list_f()
	{
		$id = $this->trans_lib->int("id");
		$psize = defined("SYS_PSIZE") ? SYS_PSIZE : 30;
		$this->collection_m->set_psize($psize);
		$pageid = $this->trans_lib->int(SYS_PAGEID);
		$page_url = $this->url("collection,list");
		if($id)
		{
			$rs = $this->collection_m->get_one($id);
			if(!$rs)
			{
				error("没有找到相关主题",$this->url("collection"));
			}
			$this->tpl->assign("rs",$rs);
			$this->tpl->assign("id",$id);
			$keytype = $this->trans_lib->safe("keytype");
			$keywords = $this->trans_lib->safe("keywords");
			$rslist = $this->collection_m->get_all_list($id,$pageid,$keytype,$keywords);
			$total = $this->collection_m->get_count_list($id,$keytype,$keywords);
			$page_url .= "id=".$id."&";
			if($keytype && $keywords)
			{
				$page_url .= "keytype=".rawurlencode($keytype)."&keywords=".rawurlencode($keywords);
				$this->tpl->assign("keytype",$keytype);
				$this->tpl->assign("keywords",$keywords);
			}
			$taglist = $this->collection_m->get_all_tags($id);
			$this->tpl->assign("taglist",$taglist);
		}
		else
		{
			$rslist = $this->collection_m->get_all_not_ok($pageid);
			$total = $this->collection_m->get_count_not_ok();
		}
		$this->tpl->assign("rslist",$rslist);
		$pagelist = $this->page_lib->page($page_url,$total);
		$this->tpl->assign("pagelist",$pagelist);

		$this->tpl->display("collection/tlist.html");
	}

	function del_f()
	{
		sys_popedom("collection:delete","tpl");
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			error("没有指定要删除的采集项目。",$this->url("collection"));
		}
		$this->collection_m->del($id);
		if(file_exists(ROOT.SYS_UP_PATH."/tmp".$id))
		{
			$this->file_lib->rm(ROOT.SYS_UP_PATH."/tmp".$id."/","folder");
		}
		error("采集项目删除成功！",$this->url("collection"));
	}

	function tags_del_f()
	{
		sys_popedom("collection:delete","tpl");
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			error("没有指定要删除的字段。",$this->url("collection"));
		}
		$rs = $this->collection_m->get_one_tags($id);
		$cid = $rs["cid"];
		$this->collection_m->del_tags($id);
		error("字段：".$rs["title"]." 删除操作成功！",$this->url("collection,tags_list","id=".$cid));
	}

	function list_del_f()
	{
		sys_popedom("collection:delete","ajax");
		$id = $this->trans_lib->safe("id");
		if(!$id)
		{
			exit("Error: 没有指定要删除的ID串！");
		}
		$id = sys_id_string($id,",","intval");
		$this->collection_m->del_list($id);
		exit("ok");
	}

	function tags_list_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			error("没有指定采集主题ID",$this->url("collection"));
		}
		$this->tpl->assign("id",$id);
		$rs = $this->collection_m->get_one($id);
		if(!$rs)
		{
			error("没有找到相关主题",$this->url("collection"));
		}
		$this->tpl->assign("rs",$rs);
		$rslist = $this->collection_m->get_all_tags($id);
		$this->tpl->assign("rslist",$rslist);
		$this->tpl->display("collection/tags_list.html");
	}

	//字段管理
	function tags_set_f()
	{
		$tid = $this->trans_lib->int("tid");
		if($tid)
		{
			$trs = $this->collection_m->get_one_tags($tid);
			if(!$trs)
			{
				error("没有取得数据！",$this->url("collection"));
			}
			$trs["rules"] = $this->trans_lib->html_edit($trs["rules"]);
			$trs["rules_start"] = $this->trans_lib->html_edit($trs["rules_start"]);
			$trs["rules_end"] = $this->trans_lib->html_edit($trs["rules_end"]);
			$trs["suburl_start"] = $this->trans_lib->html_edit($trs["suburl_start"]);
			$trs["suburl_end"] = $this->trans_lib->html_edit($trs["suburl_end"]);
			if($trs["del"])
			{
				$trs["del"] = $this->trans_lib->html_edit($trs["del"]);
			}
			$id = $trs["cid"];
			$this->tpl->assign("trs",$trs);
			$this->tpl->assign("tid",$tid);
		}
		else
		{
			$id = $this->trans_lib->int("id");
		}
		if(!$id)
		{
			error("没有指定ID",$this->url("collection"));
		}
		$this->tpl->assign("id",$id);
		$rs = $this->collection_m->get_one($id);
		if(!$rs)
		{
			error("没有取得内容信息",$this->url("collection"));
		}
		$this->tpl->assign("rs",$rs);
		$this->tpl->display("collection/tags_set.html");
	}

	function tags_setok_f()
	{
		$tid = $this->trans_lib->int("tid");
		if($tid)
		{
			$trs = $this->collection_m->get_one_tags($tid);
			if(!$trs)
			{
				error("没有取得数据！",$this->url("collection"));
			}
			$id = $trs["cid"];
		}
		else
		{
			$id = $this->trans_lib->int("id");
		}
		if(!$id)
		{
			error("无法获取采集项目！",$this->url("collection"));
		}
		//取得内容
		$title = $this->trans_lib->safe("title");
		$identifier = $this->trans_lib->safe("identifier");
		$tags_type = $this->trans_lib->safe("tags_type");
		$rules_start = $rules_end = $suburl_start = $suburl_end = $rules = "";
		if($tags_type == "string")
		{
			$rules = $this->trans_lib->edit_html($this->trans_lib->html("rules"));
		}
		else
		{
			$rules_start = $this->trans_lib->edit_html($this->trans_lib->html("rules_start"));
			$rules_end = $this->trans_lib->edit_html($this->trans_lib->html("rules_end"));
			$suburl_start = $this->trans_lib->edit_html($this->trans_lib->html("suburl_start"));
			$suburl_end = $this->trans_lib->edit_html($this->trans_lib->html("suburl_end"));
		}
		$del = $this->trans_lib->html("del");
		$del = $this->trans_lib->edit_html($del);
		$del_url = $this->trans_lib->checkbox("del_url");
		$del_html = $this->trans_lib->checkbox("del_html");
		$del_font = $this->trans_lib->checkbox("del_font");
		$del_table = $this->trans_lib->checkbox("del_table");
		$del_span = $this->trans_lib->checkbox("del_span");
		$del_bold = $this->trans_lib->checkbox("del_bold");
		$ifsystem = $this->trans_lib->safe("ifsystem");
		$post_save = $this->trans_lib->safe("post_save");
		//存储信息
		$array = array();
		$array["cid"] = $id;
		$array["title"] = $title;
		$array["identifier"] = $identifier;
		$array["tags_type"] = $tags_type;
		$array["rules"] = $rules;
		$array["rules_start"] = $rules_start;
		$array["rules_end"] = $rules_end;
		$array["suburl_start"] = $suburl_start;
		$array["suburl_end"] = $suburl_end;
		$array["del"] = $del;
		$array["del_url"] = $del_url;
		$array["del_html"] = $del_html;
		$array["del_font"] = $del_font;
		$array["del_table"] = $del_table;
		$array["del_span"] = $del_span;
		$array["del_bold"] = $del_bold;
		$array["ifsystem"] = $ifsystem;
		$array["post_save"] = $post_save;
		$this->collection_m->save_tags($array,$tid);
		error("字段创建/编辑操作成功！",$this->url("collection,tags_list","id=".$id));
	}

	function test_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			error("没有指定采集的项目！",$this->url("collection"));
		}
		$rs = $this->collection_m->get_one($id);
		if(!$rs)
		{
			error("没有取得内容信息",$this->url("collection"));
		}
		$this->tpl->assign("rs",$rs);
		$this->tpl->display("collection/testing.html");
	}

	function cj_url($id,$listurl)
	{
		$array = array();
		if(!$id)
		{
			$array["status"] = "error";
			$array["note"] = "没有指定项目！";
			return sys_json_encode($array,false);
		}
		if(!$listurl)
		{
			$array["status"] = "error";
			$array["note"] = "没有指定要采集的网址！";
			return sys_json_encode($array,false);
		}
		$rs = $this->collection_m->get_one($id);
		if(!$rs)
		{
			$array["status"] = "error";
			$array["note"] = "项目不存在！";
			return sys_json_encode($array,false);
		}
		//采集内容
		$this->load_lib("html");//加载生成静态页信息（通过HTML获取）
		$this->html_lib->setting("is_gzip",$rs["is_gzip"]);
		$this->html_lib->setting("is_proxy",$rs["is_proxy"]);
		$this->html_lib->setting("proxy_service",$rs["proxy_service"]);
		$this->html_lib->setting("proxy_user",$rs["proxy_user"]);
		$this->html_lib->setting("proxy_pass",$rs["proxy_pass"]);
		$content = $this->html_lib->get_content($listurl);
		if($rs["url_charset"] != "utf-8")
		{
			$content = $this->trans_lib->charset($content,$rs["url_charset"],"utf-8");
		}
		//截取开始
		if($rs["list_tags_start"])
		{
			$content = $this->tags_split($content,$rs["list_tags_start"],"start");
		}
		//截取结束
		if($rs["list_tags_end"])
		{
			$content = $this->tags_split($content,$rs["list_tags_end"],"end");
		}
		//取得列表
		preg_match_all("/<a(.*)href=[\"|'|](.*)[\"|'|](.*)>(.+)<\/a>/isU",$content,$matches);
		$array["status"] = "ok";
		$list_array = array();
		$i = 0;
		foreach($matches[0] AS $key=>$value)
		{
			$url = $matches[2][$key];
			if(!$url)
			{
				continue;
			}
			if($rs["url_tags"])
			{
				$tmp_array = explode("|",$rs["url_tags"]);
				$ok = false;
				foreach($tmp_array As $k=>$v)
				{
					if(strpos($url,$v) !== false)
					{
						$ok = true;
					}
				}
				if($ok == false)
				{
					continue;
				}
			}
			if(strtolower(substr($url,0,7)) != "http://" && strtolower(substr($url,0,8)) != "https://")
			{
				if(substr($rs["linkurl"],-1) != "/") $rs["linkurl"] .= "/";
				$url = $rs["linkurl"].$url;
			}
			$match_subject = sys_cutstring($matches[4][$key]);
			if($match_subject)
			{
				$list_array[$i]["title"] = $match_subject;
				$list_array[$i]["url"] = $url;
				$i++;
			}
		}
		$array["content"] = $list_array;
		return sys_json_encode($array,false);
	}

	function url2_f()
	{
		$id = $this->trans_lib->int("id");
		$listurl = $this->trans_lib->safe("listurl");
		$json_msg = $this->cj_url($id,$listurl);
		$rs = $this->json_lib->decode($json_msg);
		if($rs["status"] != "ok")
		{
			exit($json_msg);
		}
		else
		{
			$list = $rs["content"];
			if(!$list || count($list)<1)
			{
				$array = array();
				$array["status"] = "error";
				$array["note"] = "网址：".$listurl." 采集结果为空！";
				sys_json_encode($array);
			}
			$i = 0;
			foreach($list As $key=>$value)
			{
				$chk_url = $this->collection_m->chk_url($value["url"]);
				if($chk_url)
				{
					continue;
				}
				$data = array();
				$data["cid"] = $id;
				$data["url"] = $value["url"];
				$data["title"] = $value["title"];
				$data["status"] = 0;
				$data["postdate"] = $this->system_time;
				$this->collection_m->save_list($data);
				$i++;
			}
			$array["status"] = "ok";
			$array["note"] = "网址：".$listurl." 采集完成，共采集网址：<span class='red'><b>".count($list)."</b></span> 条，其中有效：<span class='red'><b>".$i."</b></span> 条";
			sys_json_encode($array);
		}
	}

	//采集网址
	function url_f()
	{
		$id = $this->trans_lib->int("id");
		$listurl = $this->trans_lib->safe("listurl");
		exit($this->cj_url($id,$listurl));
	}

	//重新采集
	function re_content_f()
	{
		$idstring = $this->trans_lib->safe("idstring");
		$idstring = sys_id_string($idstring,",","intval");
		$this->collection_m->reupdate_list($idstring);
		exit("ok");
	}


	function content2_f()
	{
		$array = array();
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			$array["status"] = "error";
			$array["note"] = "没有指定项目！";
			exit($this->json_lib->encode($array));
		}
		$rs = $this->collection_m->get_one($id);
		if(!$rs)
		{
			$array["status"] = "error";
			$array["note"] = "项目不存在！";
			exit($this->json_lib->encode($array));
		}
		$tid = $this->trans_lib->int("tid");
		if(!$tid)
		{
			$t_rs = $this->collection_m->get_start_url($id);
			if(!$t_rs)
			{
				$array["status"] = "error";
				$array["note"] = "该项目没有符合条件的采集网址。";
				exit($this->json_lib->encode($array));
			}
			$tid = $t_rs["id"];
		}
		else
		{
			$t_rs = $this->collection_m->get_this_url($tid,$id);
			if(!$t_rs)
			{
				$array = array();
				$array["status"] = "ok";
				$array["note"] = "项目：<span class='red'>".$rs["title"]."</span> 已采集结束！";
				$array["nextid"] = "end";
				exit($this->json_lib->encode($array));
			}
		}
		$json_msg = $this->cj_content($id,$t_rs["url"],$rs);
		$e_rs = $this->json_lib->decode($json_msg);
		if($e_rs["status"] != "ok")
		{
			exit($json_msg);
		}
		$list = $e_rs["content"];
		if(!$list)
		{
			$array["status"] = "error";
			$array["note"] = "网址：".$rs["url"]."，没有采集到有效数据！";
			$array["nextid"] = $tid;
			exit($this->json_lib->encode($array));
		}
		$tmp_array = array();
		foreach($list As $key=>$value)
		{
			if($value["keytype"] == "string") continue; //为固定值时不写入采集结果
			$value["content"] = str_replace("'",'"',$value["content"]);
			$t_array = array();
			$t_array["lid"] = $t_rs["id"];
			$t_array["tag"] = $value["identifier"];
			$t_array["content"] = $value["content"];
			$tmp_rs = $this->collection_m->chk_content_format($t_rs["id"],$value["identifier"]);
			if($tmp_rs)
			{
				$this->collection_m->save_format($t_array,$tmp_rs["id"]);
				//格式化内容，判断是否有图片需要存储进来
				$this->file_save($rs["id"],$tmp_rs["id"],$value["identifier"],$value["content"],$rs);
			}
			else
			{
				$insert_id = $this->collection_m->save_format($t_array);
				//格式化内容，判断是否有图片需要存储进来
				$this->file_save($rs["id"],$insert_id,$value["identifier"],$value["content"],$rs);
			}
			unset($t_array);
		}
		//更新状态：
		$t_array = array();
		$t_array["status"] = 1;
		$this->collection_m->save_list($t_array,$t_rs["id"]);
		//取得下一个要采集的主题
		$next_rs = $this->collection_m->get_next_url($tid,$id);
		$array["status"] = "ok";
		$array["note"] = "网址：<span class='red'>".$t_rs["url"]."</span>，内容采集完成！";
		$array["nextid"] = $next_rs["id"] ? $next_rs["id"] : "end";
		//exit($this->json_lib->encode($tmp_array));
		exit($this->json_lib->encode($array));
	}

	function content_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			$array["status"] = "error";
			$array["note"] = "没有指定项目！";
			exit($this->json_lib->encode($array));
		}
		$rs = $this->collection_m->get_one($id);
		if(!$rs)
		{
			$array["status"] = "error";
			$array["note"] = "项目不存在！";
			exit($this->json_lib->encode($array));
		}
		$msgurl = $this->trans_lib->safe("msgurl");
		exit($this->cj_content($id,$msgurl,$rs));
	}

	function cj_content($id,$msgurl,$rs)
	{
		@set_time_limit(0);#[设置防止超时]
		$array = array();
		if(!$msgurl)
		{
			$array["status"] = "error";
			$array["note"] = "没有指定要采集的网址！";
			return sys_json_encode($array,false);
		}
		$rslist = $this->collection_m->get_all_tags($id);
		if(!$rslist)
		{
			$array["status"] = "error";
			$array["note"] = "没有设定要采集的字段";
			return sys_json_encode($array,false);
		}
		$this->load_lib("html");
		$this->html_lib->setting("is_gzip",$rs["is_gzip"]);
		$this->html_lib->setting("is_proxy",$rs["is_proxy"]);
		$this->html_lib->setting("proxy_service",$rs["proxy_service"]);
		$this->html_lib->setting("proxy_user",$rs["proxy_user"]);
		$this->html_lib->setting("proxy_pass",$rs["proxy_pass"]);
		$content = $this->html_lib->get_content($msgurl);
		if($rs["url_charset"] != "utf-8")
		{
			$content = $this->trans_lib->charset($content,$rs["url_charset"],"utf-8");
		}
		$list_array = array();
		foreach($rslist AS $key=>$value)
		{
			//采集子页
			if($value["suburl_start"] && $value["suburl_end"] && $value["tags_type"] == "var")
			{
				$content_array = array();
				$content_array[] = $content;
				$url_list = $this->get_sub_list($value["suburl_start"],$value["suburl_end"],$content);
				foreach(($url_list ? $url_list : array()) As $k=>$v)
				{
					$tmp_content = $this->html_lib->get_content($v);
					if(!$tmp_content) continue;
					if($rs["url_charset"] != "utf-8")
					{
						$tmp_content = $this->trans_lib->charset($tmp_content,$rs["url_charset"],"utf-8");
					}
					$content_array[] = $tmp_content;
					unset($tmp_content);
				}
				$content_array = array_unique($content_array);
				//格式化字段信息
				$msg = $this->format_content($content_array,$value,$rs["linkurl"]);
			}
			else
			{
				$msg = $this->format_content($content,$value,$rs["linkurl"]);
			}
			$list_array[$key]["identifier"] = $value["identifier"];
			$list_array[$key]["keytype"] = $value["tags_type"];
			$list_array[$key]["title"] = $value["title"];
			$list_array[$key]["content"] = $msg;
			sleep(1);//沉睡一秒
		}
		$array["status"] = "ok";
		$array["content"] = $list_array;
		return sys_json_encode($array,false);
	}

	//取得子页采集
	function get_sub_list($start,$end,$content)
	{
		$content = str_replace(array("\r","\n","\t"),"",$content);
		$start = str_replace(array("\r","\n","\t"),"",$start);
		$start = str_replace(array("(*)","/"),array(".*?","\/"),$start);
		//file_put_contents("ok_".rand(0,99).".txt",$content);
		$tmp_array = preg_split("/".$start."/is",$content);
		if(count($tmp_array)<2)
		{
			return false;
		}
		unset($tmp_array);
		$content = $this->tags_split($content,$start,"start");
		$content = $this->tags_split($content,$end,"end");
		//取得URL
		preg_match_all("/<a(.*)href=[\"|'|](.*)[\"|'|](.*)>(.+)<\/a>/isU",$content,$matches);
		unset($tmp_content);
		$url_list = array();
		foreach($matches[0] AS $k=>$v)
		{
			$url = $matches[2][$k];
			if(!$url)
			{
				continue;
			}
			if(strtolower(substr($url,0,7)) != "http://" && strtolower(substr($url,0,8)) != "https://")
			{
				if(substr($rs["linkurl"],-1) != "/") $rs["linkurl"] .= "/";
				$url = $rs["linkurl"].$url;
			}
			$url_list[] = $url;
		}
		$url_list = array_unique($url_list);
		if(count($url_list)>0)
		{
			return $url_list;
		}
		else
		{
			return false;
		}
	}

	//格式化内容
	function format_content($content,$rs,$siteurl="")
	{
		//如果是固定字符串，直接返回结果集
		if($rs["tags_type"] == "string")
		{
			return $rs["rules"];
		}
		if(!$content || !$rs)
		{
			return false;
		}
		if($rs["del"]) $rs["del"] = str_replace("\r","",$rs["del"]);
		$array = is_array($content) ? $content : array($content);
		$msg = array();
		foreach($array AS $key=>$value)
		{
			$value = $this->tags_split($value,$rs["rules_start"],"start");
			$value = $this->tags_split($value,$rs["rules_end"],"end");
			if(!$value) continue;
			//去除必定不采集的信息
			$value = preg_replace("/<form(.*)>(.*)<\/form>/isU","\\2",$value);
			$value = preg_replace("/<input(.*)>/isU","",$value);
			$value = preg_replace("/<textarea(.*)>(.*)<\/textarea>/isU","",$value);
			$value = preg_replace("/<select(.*)>(.*)<\/select>/isU","",$value);
			$value = preg_replace("/<scrip(.*)>(.*)<\/script>/isU","",$value);
			$value = preg_replace("/<iframe(.*)>(.*)<\/iframe>/isU","",$value);
			$value = preg_replace("/<style(.*)>(.*)<\/style>/isU","",$value);
			//
			if($rs["del_html"] && $value)
			{
				$value = preg_replace("/<(.*)>/isU","",$value);
			}
			else
			{
				//删除网址
				if($rs["del_url"] && $value)
				{
					$value = preg_replace("/<a(.*)>(.*)<\/a>/isU","\\2",$value);
				}
				//删除字体
				if($rs["del_font"] && $value)
				{
					$value = preg_replace("/<font(.*)>(.*)<\/font>/isU","\\2",$value);
				}
				//删除表格
				if($rs["del_table"] && $value)
				{
					$value = preg_replace("/<table(.*)>(.*)<\/table>/isU","\\2",$value);
					$value = preg_replace("/<tr(.*)>(.*)<\/tr>/isU","\\2",$value);
					$value = preg_replace("/<td(.*)>(.*)<\/td>/isU","\\2",$value);
					$value = preg_replace("/<thead(.*)>(.*)<\/thead>/isU","\\2",$value);
					$value = preg_replace("/<tbody(.*)>(.*)<\/tbody>/isU","\\2",$value);
					$value = preg_replace("/<tfoot(.*)>(.*)<\/tfoot>/isU","\\2",$value);
					$value = preg_replace("/<th(.*)>(.*)<\/\th>/isU","\\2",$value);
				}
				//删除SPAN信息
				if($rs["del_span"] && $value)
				{
					$value = preg_replace("/<span(.*)>(.*)<\/span>/isU","\\2",$value);
				}
				//删除加粗样式
				if($rs["del_bold"] && $value)
				{
					$value = preg_replace("/<strong(.*)>(.*)<\/strong>/isU","\\2",$value);
					$value = preg_replace("/<b(.*)>(.*)<\/b>/isU","\\2",$value);
				}
			}
			if(!$value) continue;
			//删除要去除的文字
			if($rs["del"])
			{
				$rs["del"] = str_replace("[&amp;]","&",$rs["del"]);
				$rs["del"] = str_replace("[&]","&",$rs["del"]);
				$del_array = explode("\n",$rs["del"]);
				foreach($del_array As $k=>$v)
				{
					//如果有替换
					if(strpos($v,"[:phpok:]") !== false)
					{
						$tmp = explode("[:phpok:]",$v);
						$t1 = $tmp[0];
						$t2 = $tmp[1] ? $tmp[1] : " ";
						$t1 = $this->safe_code($t1);
						$value = preg_replace("/".$t1."/is",$t2,$value);
					}
					else
					{
						$v = $this->safe_code($v);
						$value = preg_replace("/".$v."/is","",$value);
					}
				}
			}
			if(!$value) continue;
			//格式化图片地址
			if($siteurl)
			{
				if(substr($siteurl,-1) != "/") $siteurl .= "/";
				preg_match_all("/<img(.*)src=(.*)[ |>]/isU",$value,$matches);
				$picurl = array();
				foreach($matches[0] AS $k=>$v)
				{
					$mypic_url = str_replace('"',"",$matches[2][$k]);
					if(substr($mypic_url,-1) == "/") $mypic_url = substr($mypic_url,0,-1);
					$picurl[] = str_replace('"',"",$mypic_url);
				}
				$picurl = array_unique($picurl);
				$new_picurl = array();
				foreach($picurl AS $k=>$v)
				{
					if(strtolower(substr($v,0,7)) != "http://" && strtolower(substr($v,0,8)) != "https://")
					{
						$new_picurl[$k] = $siteurl.$v;
					}
					else
					{
						$new_picurl[$k] = $v;
					}
				}
				$value = str_replace($picurl,$new_picurl,$value);
			}
			$value = str_replace(array("  ","&nbsp;","&amp;nbsp;"),"",$value);
			$msg[] = $value;
		}
		if($msg && count($msg)>0)
		{
			return implode("<br />",$msg);
		}
		else
		{
			return false;
		}
	}

	function file_save($cid,$lid,$tag,$content,$rs)
	{
		if(!$cid || !$lid || !$tag || !$content)
		{
			return false;
		}
		$this->load_lib("html");
		$this->html_lib->setting("is_gzip",$rs["is_gzip"]);
		$this->html_lib->setting("is_proxy",$rs["is_proxy"]);
		$this->html_lib->setting("proxy_service",$rs["proxy_service"]);
		$this->html_lib->setting("proxy_user",$rs["proxy_user"]);
		$this->html_lib->setting("proxy_pass",$rs["proxy_pass"]);
		preg_match_all("/<img(.*)src=(.*)[ |>]/isU",$content,$matches);
		$picurl = array();
		foreach($matches[0] AS $k=>$v)
		{
			$mypic_url = str_replace('"',"",$matches[2][$k]);
			if(substr($mypic_url,-1) == "/") $mypic_url = substr($mypic_url,0,-1);
			$picurl[] = str_replace('"',"",$mypic_url);
		}
		$save_path = ROOT.SYS_UP_PATH."/tmp".$cid."/";
		$this->file_lib->make($save_path);//创建存储目录
		if(!file_exists($save_path))
		{
			$save_path = ROOT.SYS_UP_PATH."/tmp/";
		}
		$picurl = array_unique($picurl);
		foreach($picurl As $key=>$value)
		{
			if(!trim($value)) continue;
			$chk_rs = $this->collection_m->chk_file($lid,$tag,$value);
			if($chk_rs)
			{
				if(!$chk_rs["content"])
				{
					$imgurl = str_replace("../","",$value);
					$img = $this->html_lib->get_content($imgurl);
					$tmp_array = array();
					$tmp_array["cid"] = $cid;
					$tmp_array["lid"] = $lid;
					$tmp_array["tag"] = $tag;
					$tmp_array["srcurl"] = $value;
					//存储图片数据
					$ext = strtolower(substr($value,-3));
					if(!in_array($ext,array("gif","png","jpg")))
					{
						$ext = "png";
					}
					$tmp_array["ext"] = $ext;
					$filename = $this->system_time."_".$key."_".rand(100,999).".".$ext;
					$this->file_lib->save_pic($img,$save_path.$filename);
					$tmp_array["newurl"] = str_replace(ROOT,"",$save_path.$filename);
					$this->collection_m->save_files($tmp_array,$chk_rs["id"]);
				}
			}
			else
			{
				$imgurl = str_replace("../","",$value);
				$img = $this->html_lib->get_content($imgurl);
				$tmp_array = array();
				$tmp_array["cid"] = $cid;
				$tmp_array["lid"] = $lid;
				$tmp_array["tag"] = $tag;
				$tmp_array["srcurl"] = $value;
				$ext = strtolower(substr($value,-3));
				if(!in_array($ext,array("gif","png","jpg")))
				{
					$ext = "png";
				}
				$tmp_array["ext"] = $ext;
				$filename = $this->system_time."_".$key."_".rand(100,999).".".$ext;
				$this->file_lib->save_pic($img,$save_path.$filename);
				$tmp_array["newurl"] = str_replace(ROOT,"",$save_path.$filename);
				$this->collection_m->save_files($tmp_array);
			}
			sleep(1);//沉睡一秒
		}
		return true;
	}

	function safe_code($tag)
	{
		if(!$tag)
		{
			return false;
		}
		$tag = str_replace("[&]","&",$tag);
		$old = array("\r","\n","\t","/","|","[","]",".","?","(",")");
		$new = array("","","","\/","\|","\[","\]","\.","\?","\(","\)");
		$tag = str_replace($old,$new,$tag);
		$tag = str_replace("\(*\)",".*?",$tag);
		return $tag;
	}

	//获取范围信息
	function tags_split($content,$tag,$type="start")
	{
		if(!$content || !$tag)
		{
			return false;
		}
		$content = str_replace(array("\r","\n","\t"),"",$content);
		$tag = $this->safe_code($tag);
		$tmp_array = preg_split("/".$tag."/is",$content);
		if($type == "start")
		{
			$tmp_count = count($tmp_array);
			if($tmp_count>1)
			{
				$content = "";
				for($i=0;$i<$tmp_count;$i++)
				{
					if($i>0)
					{
						$content .= $tmp_array[$i];
					}
				}
			}
		}
		else
		{
			$content = $tmp_array[0];
		}
		return $content;
	}

	//显示采集的内容字段
	function show_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			error("未指定ID！");
		}
		$rslist = $this->collection_m->get_one_format($id);
		if(!$rslist) $rslist = array();
		foreach($rslist AS $key=>$value)
		{
			$srclist = $this->collection_m->get_all_files_id($value["id"]);
			if($srclist)
			{
				foreach($srclist AS $k=>$v)
				{
					$value["content"] = str_replace($v["srcurl"],$v["newurl"],$value["content"]);
				}
			}
			$rslist[$key] = $value;
		}
		$this->tpl->assign("rslist",$rslist);
		$this->tpl->display("collection/show.html");
	}


	//显示图片内容
	function img_f()
	{
		header("Pragma:no-cache");
		header("Cache-control:no-cache");
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			header("Content-type: image/gif");
			echo file_get_contents("images/nopic.gif");
			exit;
		}
		$rs = $this->collection_m->get_one_files($id);
		if(!$rs)
		{
			header("Content-type: image/gif");
			echo file_get_contents("images/nopic.gif");
			exit;
		}
		$ext = strtolower(substr($rs["srcurl"],-3));
		if($ext == "jpg")
		{
			header("Content-type: image/jpeg");
		}
		else
		{
			header("Content-type: image/".$ext);
		}
		echo $rs["content"];
		exit;
	}

	function post_f()
	{
		$id = $this->trans_lib->safe("id");
		$tid = $this->trans_lib->safe("tid");
		if(!$id && !$tid)
		{
			error("没有指定要发布有采集项目！",$this->url("collection"));
		}
		$id = sys_id_string($id,",","intval");
		//返回上一级
		$goback = $tid ? $this->url("collection,list","id=".$id) : $this->url("collection");
		if(!$tid)
		{
			$rs = $this->collection_m->get_id_nostatus($id);
			if(!$rs)
			{
				error("信息已发布完成，或没有可以发布的信息！",$goback);
			}
			$nexturl = $this->url("collection,post","id=".rawurlencode($id));
		}
		else
		{
			$startid = $this->trans_lib->int("startid");
			$newlist = sys_id_list($tid,"intval",",");
			if(!$newlist[$startid])
			{
				error("信息已发布完成，或没有可以发布的信息！",$goback);
			}
			$rs = $this->collection_m->get_one_list($newlist[$startid]);
			$nexturl = $this->url("collection,post","id=".rawurlencode($id)."&tid=".rawurlencode($tid)."&startid=".($startid+1));
		}
		if(!$rs["status"] || $rs["status"] == 2)
		{
			if(!$rs["status"])
			{
				error("主题：<span class='red'>".$rs["title"]."</span> 尚未采集……",$nexturl);
			}
			else
			{
				error("主题：<span class='darkblue'>".$rs["title"]."</span> 已经发布过，不允许再发布！",$nexturl);
			}
		}
		//取得标签列表
		$tags_tmp_list = $this->collection_m->get_all_tags($rs["cid"]);
		$tagslist = array();
		foreach($tags_tmp_list AS $key=>$value)
		{
			$tagslist[$value["identifier"]] = $value;
		}
		unset($tags_tmp_list);
		$msglist = $this->collection_m->get_content_list($rs["id"]);
		if(!$msglist["title"])
		{
			$msglist["title"] = $rs["title"];
		}
		$format_idlist = $this->collection_m->get_sub_idlist();
		$tagslist = $this->collection_m->get_all_tags($rs["cid"]);
		$array_sys = $array_biz = $array_ext = $array_c = array();
		$cm_rs = $this->collection_m->get_one_cate_module($rs["cid"]);
		$array_sys["module_id"] = $cm_rs["module_id"];
		$array_sys["cate_id"] = $cm_rs["cateid"];
		$this->load_model("list");
		foreach($tagslist AS $k=>$v)
		{
			if($v["tags_type"] == "string")
			{
				$msglist[$v["identifier"]] = $v["rules"];
				$msglist[$v["identifier"]] = str_replace("{session.admin_name}",$_SESSION["admin_name"],$msglist[$v["identifier"]]);
				$msglist[$v["identifier"]] = str_replace("{ip}",sys_ip(),$msglist[$v["identifier"]]);
				$msglist[$v["identifier"]] = str_replace("{post_date}",$this->system_time,$msglist[$v["identifier"]]);
			}
			else
			{
				//判断目标存储格式
				$pformat = $v["post_save"];
				if($pformat == "safe")
				{
					$msglist[$v["identifier"]] = $this->trans_lib->st_safe($msglist[$v["identifier"]]);
				}
				elseif($pformat == "int")
				{
					$msglist[$v["identifier"]] = intval($msglist[$v["identifier"]]);
				}
				elseif($pformat == "float")
				{
					$msglist[$v["identifier"]] = floatval($msglist[$v["identifier"]]);
				}
				elseif($pformat == "datetime")
				{
					$msglist[$v["identifier"]] = strtotime($msglist[$v["identifier"]]);
				}
				elseif($pformat == "img")
				{
					$subject_id = $format_idlist[$v["identifier"]];
					$srclist = $this->collection_m->get_all_files_id($subject_id);
					if($srclist)
					{
						$return_array = $this->get_img_array($msglist[$v["identifier"]],$v["identifier"],$srclist);
						if($return_array)
						{
							$msglist[$v["identifier"]] = $return_array[$v["identifier"]];
							$array_sys["thumb_id"] = $return_array["thumb_id"]; //补充缩略图
						}
						else
						{
							$msglist[$v["identifier"]] = "";
						}
					}
					else
					{
						$msglist[$v["identifier"]] = "";
					}
				}
				elseif($pformat == "html")
				{
					$subject_id = $format_idlist[$v["identifier"]];
					$srclist = $this->collection_m->get_all_files_id($subject_id);
					if($srclist)
					{
						$return_array = $this->format_html($msglist[$v["identifier"]],$v["identifier"],$srclist);
						if($return_array && is_array($return_array))
						{
							if(!$array_sys["thumb_id"] && $return_array["thumb_id"])
							{
								$array_sys["thumb_id"] = $return_array["thumb_id"]; //补充缩略图
							}
							$msglist[$v["identifier"]] = $return_array[$v["identifier"]];
						}
						else
						{
							$msglist[$v["identifier"]] = $return_array;
						}
					}
				}
			}
			if($v["ifsystem"] == "list")
			{
				$array_sys[$v["identifier"]] = sys_cutstring($msglist[$v["identifier"]],240);
			}
			elseif($v["ifsystem"] == "ext")
			{
				$array_ext[$v["identifier"]] = sys_cutstring($msglist[$v["identifier"]],240);
			}
			else
			{
				$array_c[$v["identifier"]] = $msglist[$v["identifier"]];
			}
		}
		//exit;
		//存储核心数据
		$insert_id = $this->list_m->save_sys($array_sys);//存储数据
		if(!$insert_id)
		{
			error("异常：<span class='red'>".$rs["title"]."</span>",$nexturl);
		}
		//存储分类
		if($cm_rs["cateid"])
		{
			$ext_catelist = sys_id_list($cm_rs["cateid"],"intval");
			$this->list_m->save_catelist($insert_id,$ext_catelist);
		}
		//存储扩展数据
		foreach($array_ext AS $k=>$v)
		{
			$tmp_array = array();
			$tmp_array["id"] = $insert_id;
			$tmp_array["field"] = $k;
			$tmp_array["val"] = $v;
			$this->list_m->save_ext($tmp_array,"ext");
		}
		foreach($array_c AS $k=>$v)
		{
			$tmp_array = array();
			$tmp_array["id"] = $insert_id;
			$tmp_array["field"] = $k;
			$tmp_array["val"] = $v;
			//存储图片
			$this->list_m->save_ext($tmp_array,"c");
		}
		//更新主题状态
		$update_array = array();
		$update_array["status"] = 2;
		$this->collection_m->save_list($update_array,$rs["id"]);
		error("入库：<span class='red'>".$rs["title"]."</span> 完成！",$nexturl);
	}

	function clear_post_f()
	{
		$idstring = $this->trans_lib->safe("idstring");
		$idstring = sys_id_string($idstring,",","intval");
		$this->collection_m->reupdate_post($idstring);
		exit("ok");
	}

	function set_post2_f()
	{
		$idstring = $this->trans_lib->safe("idstring");
		$idstring = sys_id_string($idstring,",","intval");
		$this->collection_m->reupdate_post2($idstring);
		exit("ok");
	}

	function get_img_array($content,$var,$srclist)
	{
		if(!$srclist) return false;
		$newlist = array();
		foreach($srclist As $key=>$value)
		{
			if($value["tag"] == $var)
			{
				$newlist[] = $value;
			}
		}
		if(!$newlist || count($newlist)<1)
		{
			return $content;
		}
		$newlist = $this->save_img($newlist);
		if(!$newlist || count($newlist)<1 || !is_array($newlist))
		{
			return $content;
		}
		$thumb_id = 0;
		$idlist = array();
		foreach($newlist AS $key=>$value)
		{
			if($key<1)
			{
				$thumb_id = $value["picid"];
			}
			$idlist[] = $value["picid"];
		}
		return array("thumb_id"=>$thumb_id,$var=>sys_id_string($idlist));
	}

	function format_html($content,$var,$srclist)
	{
		if(!$srclist) return false;
		$srclist = $this->save_img($srclist);
		$thumb_id = 0;
		foreach($srclist AS $key=>$value)
		{
			if($key<1)
			{
				$thumb_id = $value["picid"];
			}
			$content = str_replace($value["srcurl"],$value["newurl"],$content);
		}
		if($thumb_id)
		{
			return array("thumb_id"=>$thumb_id,$var=>$content);
		}
		else
		{
			return $content;
		}
	}

	function save_img($srclist)
	{
		if(!$srclist) return false;
		//加载配置文件
		$_sys = array();
		if(file_exists(ROOT_DATA."attachment.php"))
		{
			include(ROOT_DATA."attachment.php");
		}
		//设置存储的图片路径
		$save_path = ROOT.SYS_UP_PATH."/";
		if($_sys["file_save_type"])
		{
			$save_path .= date($_sys["file_save_type"],$this->system_time)."/";
			$this->file_lib->make($save_path);//创建存储目录
			if(!file_exists($save_path))
			{
				$save_path = ROOT.SYS_UP_PATH."/";
			}
		}
		$this->load_model("upfile");
		$this->load_lib("gd");
		$this->load_lib("upload");
		$this->upload_lib->setting();
		foreach($srclist AS $key=>$value)
		{
			$array = array();
			$ext = $value["ext"];
			$array["title"] = basename($value["srcurl"]);
			$array["ftype"] = $value["ext"];
			//存储新的图片名称
			$filename = $this->system_time."_".$value["id"].".".$ext;
			$this->file_lib->cp($value["newurl"],$save_path.$filename);
			$array["filename"] = str_replace(ROOT,"",$save_path.$filename);
			$array["postdate"] = $this->system_time;
			$insert_id = $this->upfile_m->save($array);
			if(!$insert_id)
			{
				$this->file_lib->rm($save_path.$filename);
				continue;
			}
			//生成缩略图
			$thumbfile = $this->gd_lib->thumb($save_path.$filename,$insert_id);
			if($thumbfile)
			{
				//[存储数据]
				$update_array = array();
				$update_array["thumb"] = str_replace(ROOT,"",$save_path).$thumbfile;
				$this->upfile_m->save($update_array,$insert_id);
			}
			$this->upload_lib->gd_create($insert_id,false);
			$value["newurl"] = $array["filename"];
			$value["picid"] = $insert_id;
			$srclist[$key] = $value;
		}
		return $srclist;
	}

	//复制采集规则及字段
	function copy_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			error("没有指定ID！",$this->url("collection"));
		}
		$rs = $this->collection_m->get_one($id);
		if(!$rs)
		{
			error("没有采集项目！",$this->url("collection"));
		}
		unset($rs["id"]);
		$insert_id = $this->collection_m->save($rs);
		//获取内容规则
		$rslist = $this->collection_m->get_all_tags($id);
		if(!$rslist) $rslist = array();
		foreach($rslist AS $key=>$value)
		{
			$tmp = array();
			$tmp = $value;
			unset($tmp["id"]);
			$tmp["cid"] = $insert_id;
			$this->collection_m->save_tags($tmp);
		}
		error("已成功复制采集规则！",$this->url("collection"));
	}
}
?>