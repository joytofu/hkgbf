<?php
/***********************************************************
	Filename: libs/autoload/page.php
	Note	: 分页对象
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-25
***********************************************************/
class page_lib
{
	var $psize = 20;
	var $pageid = 0;
	var $half = 5;
	var $lang = "";

	function __construct()
	{
		$this->psize = defined("SYS_PSIZE") ? SYS_PSIZE : 20;
		$this->pageid = intval($_GET[SYS_PAGEID]);
		if(!$this->pageid)
		{
			$this->pageid = 1;
		}
		$this->half = 5;
	}

	#[兼容PHP4]
	function page_lib()
	{
		$this->__construct();
	}

	function __destruct()
	{
		return true;
	}

	function set_psize($count=20)
	{
		$this->psize = intval($count);
	}

	function set_half($count=5)
	{
		$this->half = intval($count);
	}

	function langs($lang="")
	{
		if(!$lang || !is_array($lang))
		{
			$app = sys_init();
			$lang["home"] = $app->lang["page_home"];
			$lang["prev"] = $app->lang["page_prev"];
			$lang["next"] = $app->lang["page_next"];
			$lang["last"] = $app->lang["page_last"];
		}
		if(!$lang || !is_array($lang))
		{
			$this->lang["home"] = "首页";
			$this->lang["prev"] = "上一页";
			$this->lang["next"] = "下一页";
			$this->lang["last"] = "尾页";
		}
		else
		{
			$this->lang = $lang;
		}
	}

	function page_www($url,$total,$ifarray=false,$ifselect=true)
	{
		if(!$this->lang)
		{
			$this->langs();
		}
		if(!$total || !$url)
		{
			return false;
		}
		$app = sys_init();
		if($app->sys_config["site_type"] != "html" && $app->sys_config["site_type"] != "rewrite")
		{
			return $this->page($url,$total,$ifarray,$ifselect);
		}
		if($app->sys_config["site_type"] == "html" && !$app->sys_config["sitehtml"])
		{
			$app->sys_config["site_type"] = "rewrite";
		}
		if($app->sys_config["site_type"] == "rewrite")
		{
			return $this->page_rewrite($url,$total,$ifarray,$ifselect);
		}
		if(substr($url,-1) != "/")
		{
			$url .= "/";
		}
		$pageid = $this->pageid;
		if($pageid<1)
		{
			$pageid = 1;
		}
		$half_page = $this->half;
		#[共有页数]
		$total_page = intval($total/$this->psize);
		if($total%$this->psize)
		{
			$total_page++;#[判断是否存余，如存，则加一
		}
		#[判断如果分页ID超过总页数时]
		if($pageid > $total_page)
		{
			$pageid = $total_page;
		}
		#[Html]
		$array_m = 0;
		if($pageid > 0)
		{
			$returnlist[$array_m]["url"] = $url."index.html";
			$returnlist[$array_m]["name"] = $this->lang["home"];
			$returnlist[$array_m]["status"] = 0;
			if($pageid>1)
			{
				$array_m++;
				$returnlist[$array_m]["url"] = $pageid > 2 ? $url.($pageid-1).".html" : $url."index.html";
				$returnlist[$array_m]["name"] = $this->lang["prev"];
				$returnlist[$array_m]["status"] = 0;
			}
		}
		if($half_page>0)
		{
			#[添加中间项]
			for($i=$pageid-$half_page,$i>0 || $i=0,$j=$pageid+$half_page,$j<$total_page || $j=$total_page;$i<$j;$i++)
			{
				$l = $i + 1;
				$array_m++;
				$returnlist[$array_m]["url"] = $l == 1 ? $url."index.html" : $url.$l.".html";
				$returnlist[$array_m]["name"] = $l;
				$returnlist[$array_m]["status"] = ($l == $pageid) ? 1 : 0;
			}
		}
		if($half_page <1)
		{
			$half_page = 5;
		}
		#[添加尾项]
		if($pageid < $total_page)
		{
			$array_m++;
			$t_pageid = ($pageid+1)>1 ? ($pageid+1) : "index";
			$returnlist[$array_m]["url"] = $url.$t_pageid.".html";
			$returnlist[$array_m]["name"] = $this->lang["next"];
			$returnlist[$array_m]["status"] = 0;
		}
		$array_m++;
		if($pageid != $total_page)
		{
			$returnlist[$array_m]["url"] = $url.$total_page.".html";
			$returnlist[$array_m]["name"] = $this->lang["last"];
			$returnlist[$array_m]["status"] = 0;
		}
		if($ifarray)
		{
			return $returnlist;
		}
		#[组织样式]
		$msg = "<table class='pagelist' cellpadding='0' cellspacing='1'><tr><td class='n'>".$this->psize."/".$total."</td>";
		foreach($returnlist AS $key=>$value)
		{
			if($value["status"])
			{
				$msg .= "<td class='m'>".$value["name"]."</td>";
			}
			else
			{
				$msg .= "<td class='n'><a href='".$value["url"]."'>".$value["name"]."</a></td>";
			}
		}
		$msg .= "</tr></table>";
		unset($returnlist);
		return $msg;
	}

	function page_rewrite($url,$total,$ifarray=false,$ifselect=true)
	{
		if(!$this->lang)
		{
			$this->langs();
		}
		if(!$total || !$url)
		{
			return false;
		}
		$app = sys_init();
		//$urlend = ".html";
		$url = substr($url,0,-5);
		$pageid = $this->pageid;
		if($pageid<1)
		{
			$pageid = 1;
		}
		$half_page = $this->half;
		#[共有页数]
		$total_page = intval($total/$this->psize);
		if($total%$this->psize)
		{
			$total_page++;#[判断是否存余，如存，则加一
		}
		#[判断如果分页ID超过总页数时]
		if($pageid > $total_page)
		{
			$pageid = $total_page;
		}
		#[Html]
		$array_m = 0;
		if($pageid > 0)
		{
			$returnlist[$array_m]["url"] = $url.".html";
			$returnlist[$array_m]["name"] = $this->lang["home"];
			$returnlist[$array_m]["status"] = 0;
			if($pageid>1)
			{
				$array_m++;
				$returnlist[$array_m]["url"] = $pageid > 2 ? $url."-".($pageid-1).".html" : $url.".html";
				$returnlist[$array_m]["name"] = $this->lang["prev"];
				$returnlist[$array_m]["status"] = 0;
			}
		}
		if($half_page>0)
		{
			#[添加中间项]
			for($i=$pageid-$half_page,$i>0 || $i=0,$j=$pageid+$half_page,$j<$total_page || $j=$total_page;$i<$j;$i++)
			{
				$l = $i + 1;
				$array_m++;
				$returnlist[$array_m]["url"] = $l == 1 ? $url.".html" : $url."-".$l.".html";
				$returnlist[$array_m]["name"] = $l;
				$returnlist[$array_m]["status"] = ($l == $pageid) ? 1 : 0;
			}
		}
		if($half_page <1)
		{
			$half_page = 5;
		}
		#[添加尾项]
		if($pageid < $total_page)
		{
			$array_m++;
			$t_pageid = ($pageid+1)>1 ? ($pageid+1) : "index";
			$returnlist[$array_m]["url"] = ($pageid+1)>1 ? $url."-".($pageid+1).".html" : $url.".html";
			$returnlist[$array_m]["name"] = $this->lang["next"];
			$returnlist[$array_m]["status"] = 0;
		}
		$array_m++;
		if($pageid != $total_page)
		{
			$returnlist[$array_m]["url"] = $url."-".$total_page.".html";
			$returnlist[$array_m]["name"] = $this->lang["last"];
			$returnlist[$array_m]["status"] = 0;
		}
		if($ifarray)
		{
			return $returnlist;
		}
		#[组织样式]
		$msg = "<table class='pagelist' cellpadding='0' cellspacing='1'><tr><td class='n'>".$this->psize."/".$total."</td>";
		foreach($returnlist AS $key=>$value)
		{
			if($value["status"])
			{
				$msg .= "<td class='m'>".$value["name"]."</td>";
			}
			else
			{
				$msg .= "<td class='n'><a href='".$value["url"]."'>".$value["name"]."</a></td>";
			}
		}
		$msg .= "</tr></table>";
		unset($returnlist);
		return $msg;
	}

	function page($url,$total,$ifarray=false,$ifselect=true)
	{
		if(!$this->lang)
		{
			$this->langs();
		}
		if(!$total || !$url)
		{
			return false;
		}
		if(substr($url,-1) != "&")
		{
			$url .= "&";
		}
		$pageid = $this->pageid;
		$half_page = $this->half;
		#[共有页数]
		$total_page = intval($total/$this->psize);
		if($total%$this->psize)
		{
			$total_page++;#[判断是否存余，如存，则加一
		}
		#[判断如果分页ID超过总页数时]
		if($pageid > $total_page)
		{
			$pageid = $total_page;
		}
		#[Html]
		$array_m = 0;
		if($pageid > 0)
		{
			$returnlist[$array_m]["url"] = $url;
			$returnlist[$array_m]["name"] = $this->lang["home"];
			$returnlist[$array_m]["status"] = 0;
			if($pageid > 1)
			{
				$array_m++;
				$returnlist[$array_m]["url"] = $url."pageid=".($pageid-1);
				$returnlist[$array_m]["name"] = $this->lang["prev"];
				$returnlist[$array_m]["status"] = 0;
			}
		}
		if($half_page>0)
		{
			#[添加中间项]
			for($i=$pageid-$half_page,$i>0 || $i=0,$j=$pageid+$half_page,$j<$total_page || $j=$total_page;$i<$j;$i++)
			{
				$l = $i + 1;
				$array_m++;
				$returnlist[$array_m]["url"] = $url."pageid=".$l;
				$returnlist[$array_m]["name"] = $l;
				$returnlist[$array_m]["status"] = ($l == $pageid) ? 1 : 0;
			}
		}
		if($half_page <1)
		{
			$half_page = 5;
		}
		#[添加select里的中间项]
		for($i=$pageid-$half_page*3,$i>0 || $i=0,$j=$pageid+$half_page*3,$j<$total_page || $j=$total_page;$i<$j;$i++)
		{
			$l = $i + 1;
			$select_option_msg = "<option value='".$l."'";
			if($l == $pageid)
			{
				$select_option_msg .= " selected";
			}
			$select_option_msg .= ">".$l."</option>";
			$select_option[] = $select_option_msg;
		}
		#[添加尾项]
		if($pageid < $total_page)
		{
			$array_m++;
			$returnlist[$array_m]["url"] = $url."pageid=".($pageid+1);
			$returnlist[$array_m]["name"] = $this->lang["next"];
			$returnlist[$array_m]["status"] = 0;
		}
		$array_m++;
		if($pageid != $total_page)
		{
			$returnlist[$array_m]["url"] = $url."pageid=".$total_page;
			$returnlist[$array_m]["name"] = $this->lang["last"];
			$returnlist[$array_m]["status"] = 0;
		}
		if($ifarray)
		{
			return $returnlist;
		}
		#[组织样式]
		$msg = "<table class='pagelist' cellpadding='0' cellspacing='1'><tr><td class='n'>".$this->psize."/".$total."</td>";
		foreach($returnlist AS $key=>$value)
		{
			if($value["status"])
			{
				$msg .= "<td class='m'>".$value["name"]."</td>";
			}
			else
			{
				$msg .= "<td class='n'><a href='".$value["url"]."'>".$value["name"]."</a></td>";
			}
		}
		if($ifselect)
		{
			$msg .= "<td><select onchange=\"window.location.href='".$url."pageid='+this.value\">".implode("",$select_option)."</option></select></td>";
		}
		$msg .= "</tr></table>";
		unset($returnlist);
		return $msg;
	}
}
?>