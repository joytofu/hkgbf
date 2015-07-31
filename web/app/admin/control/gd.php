<?php
/***********************************************************
	Filename: app/admin/control/gd.php
	Note	: GD库类型管理
	Version : 3.0
	Author  : qinggan
	Update  : 2010-05-07
***********************************************************/
class gd_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("gd");
	}

	function gd_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		sys_popedom("gd:list","tpl");
		$rslist = $this->gd_m->get_list();
		$this->tpl->assign("rslist",$rslist);
		$this->tpl->display("gd/list.html");
	}

	function set_f()
	{
		$id = $this->trans_lib->int("id");
		if($id)
		{
			sys_popedom("gd:modify","tpl");
		}
		else
		{
			sys_popedom("gd:add","tpl");
		}
		$this->tpl->assign("id",$id);
		if($id)
		{
			$rs = $this->gd_m->get_one($id);
			$this->tpl->assign("rs",$rs);
		}
		$this->tpl->display("gd/set.html");
	}

	function setok_f()
	{
		$id = $this->trans_lib->int("id");
		if($id)
		{
			sys_popedom("gd:modify","tpl");
		}
		else
		{
			sys_popedom("gd:add","tpl");
		}
		$array = array();
		if(!$id)
		{
			$array["pictype"] = $this->trans_lib->safe("pictype");
		}
		$array["picsubject"] = $this->trans_lib->safe("picsubject");
		$array["width"] = $this->trans_lib->int("width");
		$array["height"] = $this->trans_lib->int("height");
		$array["water"] = $this->trans_lib->safe("water");
		$array["picposition"] = $this->trans_lib->safe("picposition");
		$array["trans"] = $this->trans_lib->safe("trans");
		$array["cuttype"] = $this->trans_lib->safe("cuttype");
		$array["quality"] = $this->trans_lib->safe("quality");
		$array["border"] = $this->trans_lib->safe("border");
		$array["bordercolor"] = $this->trans_lib->safe("bordercolor");
		$array["padding"] = $this->trans_lib->safe("padding");
		$array["bgcolor"] = $this->trans_lib->safe("bgcolor");
		$array["bgimg"] = $this->trans_lib->safe("bgimg");
		$array["taxis"] = $this->trans_lib->int("taxis");
		$array["status"] = $this->trans_lib->int("status");
		$array["edit_default"] = $this->trans_lib->int("edit_default");
		#[检测相关信息]
		if(!$array["picsubject"])
		{
			error("方案名称不允许为空",site_url("gd,set","id=".$id));
		}
		if(!$array["width"] || !$array["height"])
		{
			error("方案的宽和高都不允许为空",site_url("gd,set","id=".$id));
		}
		$this->gd_m->save($array,$id);
		error("方案操作成功",site_url("gd"));
	}

	//检测编号是否唯一
	function gd_chk_f()
	{
		$pictype = $this->trans_lib->safe("pictype");
		if(!$pictype)
		{
			exit("Error:操作非法，没有填写识别号");
		}
		$rs = $this->gd_m->chk_sign($pictype);
		if($rs)
		{
			exit("ok");
		}
		else
		{
			exit("Error:标识符已经被使用了");
		}
	}

	//删除图片库方案
	function del_gd_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("Error:操作非法，没有指定ID");
		}
		sys_popedom("gd:delete","ajax");
		$rs = $this->gd_m->get_one($id);
		$sign = $rs["pictype"];
		//获取数据
		$rslist = $this->gd_m->gd_list($sign);
		if($rslist && count($rslist) > 0 && is_array($rslist))
		{
			foreach($rslist AS $key=>$value)
			{
				if($value["filename"] && file_exists(ROOT.$value["filename"]) && is_file(ROOT.$value["filename"]))
				{
					$this->file_lib->rm(ROOT.$value["filename"]);
				}
			}
		}
		$this->gd_m->gd_del($sign);
		$this->gd_m->del($id);
		exit("ok");
	}
}
?>
