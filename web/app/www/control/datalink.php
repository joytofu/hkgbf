<?php
/***********************************************************
	Filename: app/www/control/datalink.php
	Note	: 数据联动管理
	Version : 3.0
	Author  : qinggan
	Update  : 2009-11-28
***********************************************************/
class datalink_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("datalink");//读取数据联动信息
	}

	function datalink_c()
	{
		$this->__construct();
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
		$fid = $this->trans_lib->int("fid");//
		$identifier = $this->trans_lib->safe("identifier");
		$linkid = $this->trans_lib->int("linkid");//扩展字段ID
		if(!$fid)
		{
			$r = array();
			$r["error"] = $this->lang["datalink_error"];
			exit("var phpok_data='".$this->json_lib->encode($r)."';");
		}
		$rslist = $this->datalink_m->get_parent($linkid);
		if(!$rslist)
		{
			$r = array();
			$r["error"] = $this->lang["datalink_error"];
			exit("var phpok_data='".$this->json_lib->encode($r)."';");
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
		$html = "<select onchange=\"phpjs_parent_opt(this.value,'".$identifier."',".$linkid.",'".$linkid."')\">";
		$html.= "<option value=''>".$this->lang["datalink_select"]."</option>";
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
				$html.= "<option value=''>".$this->lang["datalink_select"]."</option>";
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
		//exit("var phpok_data='".$this->json_lib->encode($r)."';");
	}
}
?>