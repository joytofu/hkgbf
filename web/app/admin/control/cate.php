<?php
/***********************************************************
	Filename: app/admin/control/cate.php
	Note	: 分类管理
	Version : 3.0
	Author  : qinggan
	Update  : 2009-12-18
***********************************************************/
class cate_c extends Control
{
	var $module_sign = "cate";
	function __construct()
	{
		parent::Control();
		$this->load_model("cate");//读取分类
		$this->load_model("module");//读取模块
	}

	function cate_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		sys_popedom($this->module_sign.":list","tpl");
		$this->cate_m->langid($_SESSION["sys_lang_id"]);
		$this->cate_m->get_all();
		$this->cate_m->format_list(0,0);
		$catelist = $this->cate_m->flist();
		if(!is_array($catelist)) $catelist = array();
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
		//判断是否有编辑权限
		$ifmodify = sys_popedom($this->module_sign.":modify");
		$this->tpl->assign("ifmodify",$ifmodify);

		$this->tpl->display("cate/list.html");
	}

	function chk_f()
	{
		$this->cate_m->langid($_SESSION["sys_lang_id"]);
		$id = $this->trans_lib->int("id");
		$sign = $this->trans_lib->safe("sign");
		if(!$sign)
		{
			exit("error: 标识串为空");
		}
		//检测标识串是否符合要求
		if(!ereg("[a-z][a-z0-9\_]+",$sign))
		{
			exit("error: 标识串仅限小写英文字母，数字及下划线，且第一位必须是字母");
		}
		//检测唯一性
		$rs = $this->cate_m->chksign($sign,$id);
		if($rs)
		{
			exit("error: 标识串已被使用，请返回修改");
		}
		else
		{
			exit("ok");
		}
	}

	function set_f()
	{
		$order_list["post_date:desc"] = "最新发布排前";
		$order_list["post_date:asc"] = "最新发布排后";
		$order_list["modify_date:desc"] = "最新修改排前";
		$order_list["modify_date:asc"] = "最新修改排后";
		$order_list["replydate:desc"] = "最新回复排前";
		$order_list["replydate:asc"] = "最新回复排后";
		$order_list["hits:desc"] = "热门主题排前";
		$order_list["hits:asc"] = "冷门主题排前";
		$this->tpl->assign("order_list",$order_list);
		$id = $this->trans_lib->int("id");
		$tmp_popedom = $id ? $this->module_sign.":modify" : $this->module_sign.":add";
		sys_popedom($tmp_popedom,"tpl");//判断是否有相应的权限
		unset($tmp_popedom);
		$cateid = $this->trans_lib->int("cateid");
		if($cateid)
		{
			$p_rs = $this->cate_m->get_one($cateid);
			$mid = $p_rs["module_id"];
		}
		else
		{
			$mid = $this->trans_lib->int("mid");
		}
		if(!$id && !$mid)
		{
			//取得模块列表
			$modulelist = $this->module_m->module_list();
			$this->tpl->assign("modulelist",$modulelist);
			$this->tpl->display("cate/set_module.html");
			exit;
		}
		if($id)
		{
			$rs = $this->cate_m->get_one($id);
			$rs["note"] = $this->trans_lib->html_fck($rs["note"]);
			$this->tpl->assign("rs",$rs);
			$mid = $rs["module_id"];
			$cateid = $rs["parentid"];//父分类ID
			//如果有扩展字段
			if($rs["fields"])
			{
				$extlist = sys_id_list($rs["fields"]);
				$this->tpl->assign("extlist",$extlist);
			}
		}
		$this->tpl->assign("mid",$mid);
		//取得模块下的分类
		$this->cate_m->langid($_SESSION["sys_lang_id"]);
		$this->cate_m->get_catelist($mid);
		if(!$id)
		{
			$cate_html = $this->cate_m->html_select("cateid",$cateid,"设为根分类");
		}
		else
		{
			if($rs["parentid"])
			{
				$cate_html = $this->cate_m->html_select("cateid",$cateid,"",$cateid);
			}
			else
			{
				$cate_html = "<select name='cateid' id='cateid'><option value='0'>根分类不允许修改</option></select>";
			}
		}
		$this->tpl->assign("cate_html",$cate_html);
		//读取模块信息
		$m_rs = $this->module_m->get_one($mid);
		$this->tpl->assign("m_rs",$m_rs);
		//关联图片类型
		$this->load_model("gdtype");
		$gdlist = $this->gdtype_m->get_all();
		$this->tpl->assign("gdlist",$gdlist);

		$this->tpl->display("cate/set.html");
	}

	//存储分类信息
	function setok_f()
	{
		$id = $this->trans_lib->int("id");
		$array = array();
		$array["cate_name"] = $this->trans_lib->safe("cate_name");
		$array["identifier"] = $this->trans_lib->safe("identifier");
		$tmp_popedom = $id ? $this->module_sign.":modify" : $this->module_sign.":add";
		sys_popedom($tmp_popedom,"tpl");//判断是否有相应的权限
		unset($tmp_popedom);
		$parentid = $this->trans_lib->int("cateid");
		if(!$id)
		{
			$mid = $this->trans_lib->int("mid");
			if(!$mid)
			{
				error("没有指到模块ID！",$this->url("cate,set"));
			}
			$array["module_id"] = $mid;
			$array["langid"] = $_SESSION["sys_lang_id"];
			$array["parentid"] = $parentid;
		}
		$array["tpl_index"] = $this->trans_lib->safe("tpl_index");
		$array["tpl_list"] = $this->trans_lib->safe("tpl_list");
		$array["tpl_file"] = $this->trans_lib->safe("tpl_file");
		$array["if_index"] = isset($_POST["if_index"]) ? 1 : 0;
		$array["status"] = isset($_POST["status"]) ? 1 : 0;
		$array["taxis"] = $this->trans_lib->int("taxis");
		$array["if_hidden"] = $this->trans_lib->int("if_hidden");
		$array["seotitle"] = $this->trans_lib->safe("seotitle");
		$array["keywords"] = $this->trans_lib->safe("keywords");
		$array["description"] = $this->trans_lib->safe("description");
		$array["note"] = $this->trans_lib->html("note");
		$array["ifspec"] = $this->trans_lib->safe("ifspec");//开启/关闭分类为单页
		$array["inpic"] = $this->trans_lib->safe("inpic");//读取默认图片
		$array["psize"] = $this->trans_lib->int("psize");
		if($array["psize"]<1)
		{
			$array["psize"] = 30;
		}
		$array["target"] = $this->trans_lib->int("target");
		$array["linkurl"] = $this->trans_lib->safe("linkurl");
		$array["ordertype"] = $this->trans_lib->safe("ordertype");
		$array["subcate"] = $this->trans_lib->safe("subcate");
		$array["ico"] = $this->trans_lib->safe("ico");//图标
		$array["small_pic"] = $this->trans_lib->safe("small_pic");//小图
		$array["medium_pic"] = $this->trans_lib->safe("medium_pic");//中图
		$array["big_pic"] = $this->trans_lib->safe("big_pic");//大图
		//$array["taxis_asc"] = $this->trans_lib->checkbox("taxis_asc");//自定义排序从小排到大
		//存储要显示的扩展字段
		$extlist = $this->trans_lib->safe("extlist");
		//echo "<pre>".print_r($extlist,true)."</pre>";
		//exit;
		$array["fields"] = sys_id_string($extlist);
		//存储分类信息
		$this->cate_m->save($array,$id);
		if(!$id)
		{
			error("分类信息添加成功！",$this->url("cate","mid=".$mid));
		}
		else
		{
			//判断如果更改了父分类信息
			$rs = $this->cate_m->get_one($id);
			if($rs["parentid"] && $parentid && $rs["parentid"] != $parentid && $parentid != $id)
			{
				$update_array = array();
				$update_array["parentid"] = $parentid;
				$this->cate_m->save($update_array,$id);
			}
			//更新下级分类下的字段配置
			$next_fields_ok = $this->trans_lib->checkbox("next_fields_ok");
			if($next_fields_ok)
			{
				$this->cate_m->update_son_fields($array["fields"],$id);
			}
			error("分类信息更新操作成功",$this->url("cate","mid=".$rs["module_id"]));
		}
	}

	function ajax_status_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error:没有指定ID");
		}
		sys_popedom($this->module_sign.":check","ajax");
		$rs = $this->cate_m->get_one($id);
		$status = $rs["status"] ? 0 : 1;
		$this->cate_m->set_status($id,$status);
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
		$rs = $this->cate_m->chk_son($id);
		if($rs)
		{
			exit("error: 对不起，您要删除的分类带有子分类，不允许删除");
		}
		//检测是否有内容
		unset($rs);
		$rs = $this->cate_m->chk_msg($id);
		if($rs)
		{
			exit("error: 对不起，您要删除的分类已经有内容了，不允许删除");
		}
		$this->cate_m->del($id);
		exit("ok");
	}

	function to_pinyin_f()
	{
		$title = $this->trans_lib->safe("title");
		if(!$title)
		{
			exit("false");
		}
		//加载拼音控件
		$this->load_lib("pingyin");
		$title = $this->trans_lib->charset($title,"UTF-8","GBK");
		$rs = $this->pingyin_lib->c($title);
		if(!$rs)
		{
			exit("false");
		}
		$rs = strtolower($rs);
		$rs = str_replace(" ","_",$rs);
		exit($rs);
	}

	function ajax_psize_f()
	{
		$id = $this->trans_lib->int("id");
		$val = $this->trans_lib->int("val");
		sys_popedom($this->module_sign.":modify","ajax");
		if(!$id)
		{
			exit("没有指定ID！");
		}
		$array = array();
		$array["psize"] = $val;
		$this->cate_m->save($array,$id);
		exit("ok");
	}

	function ajax_taxis_f()
	{
		$id = $this->trans_lib->int("id");
		$val = $this->trans_lib->int("val");
		sys_popedom($this->module_sign.":modify","ajax");
		if(!$id)
		{
			exit("没有指定ID！");
		}
		$array = array();
		$array["taxis"] = $val;
		$this->cate_m->save($array,$id);
		exit("ok");
	}

	//取得子分类ID号
	function get_sonid_array(&$array,$id=0)
	{
		if(!$id)
		{
			return $array;
		}
		$sql = "SELECT id FROM ".$this->db->prefix."cate WHERE parentid='".$id."'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist)
		{
			return $array;
		}
		foreach($rslist AS $key=>$value)
		{
			$array[] = $value["id"];
			$this->get_sonid_array($array,$value["id"]);
		}
		return $array;
	}


}
?>