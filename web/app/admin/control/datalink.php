<?php
/***********************************************************
	Filename: app/admin/control/datalink.php
	Note	: 数据联动管理
	Version : 3.0
	Author  : qinggan
	Update  : 2009-11-28
***********************************************************/
class datalink_c extends Control
{
	var $module_sign = "datalink";//权限标识
	function __construct()
	{
		parent::Control();
		$this->load_model("datalink");//读取数据联动信息
	}

	function datalink_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		sys_popedom($this->module_sign.":list","tpl");
		$grouplist = $this->datalink_m->all_group($_SESSION["sys_lang_id"]);
		$this->tpl->assign("grouplist",$grouplist);
		$pageid = $this->trans_lib->int(SYS_PAGEID);
		$condition = array();
		$page_url = $this->url("datalink");
		$groupid = $this->trans_lib->int("groupid");
		if($groupid)
		{
			$condition["groupid"] = $groupid;
			$page_url .= "groupid=".$groupid."&";
			$this->tpl->assign("groupid",$groupid);
			$groupname = "";
			foreach($grouplist AS $key=>$value)
			{
				if($value["id"] == $groupid)
				{
					$groupname = $value["title"];
					$this->tpl->assign("groupname",$groupname);
				}
			}
		}
		$rslist = $this->datalink_m->all_fields($pageid,$condition);
		$this->tpl->assign("rslist",$rslist);
		//取得总数
		$total = $this->datalink_m->get_count();
		$pagelist = $this->page_lib->page($page_url,$total);
		$this->tpl->assign("pagelist",$pagelist);
		$this->tpl->display("datalink/list.html");
	}

	function groupok_f()
	{
		sys_popedom($this->module_sign.":group","tpl");
		$groupid = $this->trans_lib->int("groupid");
		$array = array();
		$array["title"] = $this->trans_lib->safe("groupname");//组名称
		if(!$array["title"])
		{
			error("组名称不允许为空",$this->url("datalink"));
		}
		$array["langid"] = $_SESSION["sys_lang_id"];
		$this->datalink_m->save_group($array,$groupid);
		error("联动数据组更新/添加成功",$this->url("datalink"));
	}

	//添加或修改联动数据
	function set_f()
	{
		$id = $this->trans_lib->int("id");
		if($id)
		{
			sys_popedom($this->module_sign.":modify","tpl");
			$this->tpl->assign("id",$id);
			$rs = $this->datalink_m->get_one($id);
			$this->tpl->assign("rs",$rs);
			//获取父级信息
			$plist = $this->datalink_m->get_parent($rs["gid"]);
			$this->tpl->assign("plist",$plist);
		}
		else
		{
			sys_popedom($this->module_sign.":add","tpl");
		}
		$grouplist = $this->datalink_m->all_group($_SESSION["sys_lang_id"]);
		$this->tpl->assign("grouplist",$grouplist);
		$this->tpl->display("datalink/set.html");
	}

	function setok_f()
	{
		$id = $this->trans_lib->int("id");
		$array = array();
		$array["val"] = $this->trans_lib->safe("val");
		$array["title"] = $this->trans_lib->safe("title");
		$array["taxis"] = $this->trans_lib->int("taxis");
		if(!$id)
		{
			sys_popedom($this->module_sign.":add","tpl");
			$array["gid"] = $this->trans_lib->int("gid");
			$array["pid"] = $this->trans_lib->int("pid");
			$array["langid"] = $_SESSION["sys_lang_id"];
		}
		else
		{
			sys_popedom($this->module_sign.":modify","tpl");
		}
		$this->datalink_m->save($array,$id);
		error("数据添加/更新成功",$this->url("datalink"));
	}

	//删除数据
	function ajaxdel_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error: 没有指定删除ID");
		}
		sys_popedom($this->module_sign.":delete","ajax");
		$this->datalink_m->del_field($id);
		exit("ok");
	}

	//删除组ID
	function groupdel_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			error("没有指定组ID",$this->url("datalink"));
		}
		sys_popedom($this->module_sign.":group","tpl");
		$this->datalink_m->del_group($id);
		error("组信息删除成功",$this->url("datalink"));
	}

	//通过ajax获取父级菜单信息
	function ajax_parent_f()
	{
		$gid = $this->trans_lib->int("gid");
		if($gid)
		{
			$plist = $this->datalink_m->get_parent($gid);
			$this->tpl->assign("plist",$plist);
		}
		$this->tpl->display("datalink/ajax_parent.html");
	}

	//在添加数据时执行菜单内容管理
	function ajax_opt_f()
	{
		$val = $this->trans_lib->safe("val");//被选中的值
		$fid = $this->trans_lib->int("fid");//扩展字段ID
		$identifier = $this->trans_lib->safe("identifier");
		$linkid = $this->trans_lib->int("linkid");//扩展字段ID
		if(!$fid)
		{
			$r = array();
			$r["error"] = "获取联动数据失败";
			exit($this->json_lib->encode($r));
		}
		$rslist = $this->datalink_m->get_parent($linkid);
		if(!$rslist)
		{
			$r = array();
			$r["error"] = "获取联动数据失败";
			exit($this->json_lib->encode($r));
		}
		$pid = 0;
		if($val)
		{
			$rs = $this->datalink_m->val_one($val,$linkid);
			if($rs["pid"])
			{
				$prs = $this->datalink_m->get_one($rs["pid"]);
				$pid = $rs["pid"];
				$p_val = $prs["val"];
				$s_val = $val;
			}
			else
			{
				$pid = $rs["id"];
				$p_val = $val;
				$s_val = "";
			}
		}
		else
		{
			$pid = 0;
			$p_val = "";
			$s_val = "";
		}
		$html = "<select onchange=\"phpjs_parent_opt(this.value,'".$identifier."',".$fid.",'".$linkid."')\">";
		$html.= "<option value=''>请选择…</option>";
		foreach($rslist AS $key=>$value)
		{
			$html.= "<option value='".$value["val"]."'";
			if($value["val"] == $p_val)
			{
				$html.= " selected";
			}
			$html.= ">".$value["title"]."</option>";
		}
		$html.= "</select>";
		$r = array();
		$r["parent"] = $html;
		unset($html,$rslist);
		//判断是否有子类
		if($val && $pid)
		{
			$rslist = $this->datalink_m->get_son($pid);
			if($rslist)
			{
				$html = "<select onchange=\"phpjs_son_opt(this.value,'".$identifier."')\">";
				$html.= "<option value=''>请选择…</option>";
				foreach($rslist AS $key=>$value)
				{
					$html.= "<option value='".$value["val"]."'";
					if($value["val"] == $val)
					{
						$html.= " selected";
					}
					$html.= ">".$value["title"]."</option>";
				}
				$html.= "</select>";
				$r["son"] = $html;
			}
		}
		exit($this->json_lib->encode($r));
	}
}
?>