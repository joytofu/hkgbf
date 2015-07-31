<?php
/***********************************************************
	Filename: sitemap.php
	Note	: 创建网站地图
	Version : 3.0
	Author  : qinggan
	Update  : 2010-10-15
***********************************************************/
class sitemap_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("cate");//读取模块列表
		$this->load_model("list");//读取产品下的列表
	}

	//兼容PHP4的写法
	function sitemap_c()
	{
		$this->__construct();
	}

	//在前台展示用的
	function index_f()
	{
		//
	}

	//生成Google 专用的sitemap
	function sitemap_f()
	{
		//设置语言包
		$this->cate_m->set_langid($_SESSION["sys_lang_id"]);
		//$this->cate_m->get_catelist(0,"c.if_hidden='0' AND (m.if_list='1' OR m.if_msg='1') ");
		$this->cate_m->get_catelist(0,"c.if_hidden='0' AND (m.if_list='1') ");
		$catelist = $this->cate_m->catelist;
		$cate_count = count($catelist);
		$rslist = array();
		if($this->sys_config['site_type'] != "html")
		{
			$myurl_pre = $this->sys_config["siteurl"];
		}
		else
		{
			$myurl_pre = $this->sys_config["sitehtml"] ? $this->sys_config["sitehtml"] : $this->sys_config["siteurl"]."html/".$_SESSION["sys_lang_id"]."/";
		}
		if($catelist && is_array($catelist) && $cate_count>0)
		{
			foreach($catelist AS $key=>$value)
			{
				$myurl = list_url($value);
				$chke_url = strtolower(substr($myurl,0,7));
				if($chke_url != "http://" && $chke_url != "https:/")
				{
					$myurl = $myurl_pre . $myurl;
				}
				$rslist[] = array("url"=>$myurl,"datetime"=>$this->system_time,"changefreq"=>"monthly","priority"=>"0.9");
			}
		}
		$max = 49999 - $cate_count;
		//取得主题列表
		$this->list_m->langid($_SESSION["sys_lang_id"]);
		$sublist = $this->list_m->getlist_for_sitemap($max);
		if($sublist && is_array($sublist) && count($sublist)>0)
		{
			foreach($sublist AS $key=>$value)
			{
				$myurl = msg_url($value);
				$chke_url = strtolower(substr($myurl,0,7));
				if($chke_url != "http://" && $chke_url != "https:/")
				{
					$myurl = $myurl_pre . $myurl;
				}
				$rslist[] = array("url"=>$myurl,"datetime"=>$value["post_date"],"changefreq"=>"weekly","priority"=>"0.9");
			}
		}
		$xml = '<?xml version="1.0" ?>'."\n";
		$xml.= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
		foreach($rslist AS $key=>$value)
		{
			$xml.= "\t<url>\n";
			$xml.= "\t\t<loc>".$value["url"]."</loc>\n";
			$xml.= "\t\t<lastmod>".date('Y-m-d\TH:i:s+00:00',$value['datetime'])."</lastmod>\n";
			$xml.= "\t\t<changefreq>".$value["changefreq"]."</changefreq>\n";
			$xml.= "\t\t<priority>".$value["priority"]."</priority>\n";
			$xml.= "\t</url>\n";
		}
		$xml.= "</urlset>";
		exit($xml);
	}

	//生成Baidu 专用的baidu_sitemap
	function baidu_f()
	{
		//设置语言包
		$this->cate_m->set_langid($_SESSION["sys_lang_id"]);
		$this->cate_m->get_catelist(0,"c.if_hidden='0' AND (m.if_list='1' OR m.if_msg='1') ");
		$catelist = $this->cate_m->catelist;
		$cate_count = count($catelist);
		$rslist = array();
		if($this->sys_config['site_type'] != "html")
		{
			$myurl_pre = $this->sys_config["siteurl"];
		}
		else
		{
			$myurl_pre = $this->sys_config["sitehtml"] ? $this->sys_config["sitehtml"] : $this->sys_config["siteurl"]."html/".$_SESSION["sys_lang_id"]."/";
		}
		if($catelist && is_array($catelist) && $cate_count>0)
		{
			foreach($catelist AS $key=>$value)
			{
				$myurl = list_url($value);
				$chke_url = strtolower(substr($myurl,0,7));
				if($chke_url != "http://" && $chke_url != "https:/")
				{
					$myurl = $myurl_pre . $myurl;
				}
				$rslist[] = array("title"=>$value["cate_name"],"url"=>$myurl,"datetime"=>$this->system_time,"changefreq"=>"weekly","priority"=>"1.0");
			}
		}
		$max = 49999 - $cate_count;
		//取得主题列表
		$this->list_m->langid($_SESSION["sys_lang_id"]);
		$sublist = $this->list_m->getlist_for_sitemap($max);
		if($sublist && is_array($sublist) && count($sublist)>0)
		{
			foreach($sublist AS $key=>$value)
			{
				$myurl = msg_url($value);
				$chke_url = strtolower(substr($myurl,0,7));
				if($chke_url != "http://" && $chke_url != "https:/")
				{
					$myurl = $myurl_pre . $myurl;
				}
				$rslist[] = array("title"=>$value["title"],"url"=>$myurl,"datetime"=>$value["post_date"],"changefreq"=>"weekly","priority"=>"1.0");
			}
		}
		$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$xml.= "<urlset>\n";
		foreach($rslist AS $key=>$value)
		{
			$xml.= "\t<url>\n";
			$xml.= "\t\t<loc>".$value["url"]."</loc>\n";
			$xml.= "\t\t<lastmod>".date('Y-m-d',$value['datetime'])."</lastmod>\n";
			$xml.= "\t\t<changefreq>".$value["changefreq"]."</changefreq>\n";
			$xml.= "\t\t<priority>".$value["priority"]."</priority>\n";
			$xml.= "\t</url>\n";
		}
		$xml.= "</urlset>";
		exit($xml);
	}

	//生成ROR 专用的ror.xml
	function ror_f()
	{
		//设置语言包
		$this->cate_m->set_langid($_SESSION["sys_lang_id"]);
		$this->cate_m->get_catelist(0,"c.if_hidden='0' AND (m.if_list='1' OR m.if_msg='1') ");
		$catelist = $this->cate_m->catelist;
		$cate_count = count($catelist);
		$rslist = array();
		if($this->sys_config['site_type'] != "html")
		{
			$myurl_pre = $this->sys_config["siteurl"];
		}
		else
		{
			$myurl_pre = $this->sys_config["sitehtml"] ? $this->sys_config["sitehtml"] : $this->sys_config["siteurl"]."html/".$_SESSION["sys_lang_id"]."/";
		}
		if($catelist && is_array($catelist) && $cate_count>0)
		{
			foreach($catelist AS $key=>$value)
			{
				$myurl = list_url($value);
				$chke_url = strtolower(substr($myurl,0,7));
				if($chke_url != "http://" && $chke_url != "https:/")
				{
					$myurl = $myurl_pre . $myurl;
				}
				$rslist[] = array("title"=>$value["cate_name"],"note"=>$value["note"],"keywords"=>$value["keywords"],"url"=>$myurl,"datetime"=>$this->system_time,"updatePeriod"=>"weekly","sortOrder"=>"0");
			}
		}
		$max = 49999 - $cate_count;
		//取得主题列表
		$this->list_m->langid($_SESSION["sys_lang_id"]);
		$sublist = $this->list_m->getlist_for_sitemap($max);
		if($sublist && is_array($sublist) && count($sublist)>0)
		{
			foreach($sublist AS $key=>$value)
			{
				$myurl = msg_url($value);
				$chke_url = strtolower(substr($myurl,0,7));
				if($chke_url != "http://" && $chke_url != "https:/")
				{
					$myurl = $myurl_pre . $myurl;
				}
				$rslist[] = array("title"=>$value["title"],"note"=>$value["note"],"keywords"=>$value["keywords"],"url"=>$myurl,"datetime"=>$value["post_date"],"updatePeriod"=>"weekly","sortOrder"=>"0");
			}
		}

		$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$xml.= '<rss version="2.0" xmlns:ror="http://rorweb.com/0.1/" >'."\n";
		$xml.= '<channel>'."\n";
		$xml.= "  <title>".$this->sys_config["sitename"]."</title>\n";
		$xml.= "  <link>".$this->sys_config["siteurl"]."</link>\n";
		$xml.= "  <description>".$this->sys_config["seotitle"]."</description>\n";
		$xml.= "  <item>\n";
		$xml.= "\t<title>".$this->sys_config["siteurl"]."</title>\n";
		$xml.= "\t<link>".$this->sys_config["siteurl"]."</link>\n";
		$xml.= "\t<ror:about>sitemap</ror:about>\n";
		$xml.= "\t<ror:type>SiteMap</ror:type>\n";
		$xml.= "  </item>\n\n";
		foreach($rslist AS $key=>$value)
		{
			$xml.= "<item>\n";
			$xml.= "\t <link>".$value["url"]."</link>\n";
			$xml.= "\t <title>".$value["title"]."</title>\n";
			$xml.= "\t <pubDate>".date('Y-m-d H:i:s',$value['datetime'])."</pubDate>\n";
			$xml.= "\t <description><![CDATA[";
			if($value["note"])
			{
				$xml .= sys_cutstring($value["note"],500)."...";
				if($value["keywords"])
				{
					$xml .= "<br/><br/>关键词： ".$value["keywords"]."<br/>";
				}
			}
			$xml.= "]]></description>\n";
			$xml.= "\t <ror:updatePeriod>".$value["updatePeriod"]."</ror:updatePeriod>\n";
			$xml.= "\t <ror:sortOrder>".$value["sortOrder"]."</ror:sortOrder>\n";
			$xml.= "\t <ror:resourceOf>sitemap</ror:resourceOf>\n";
			$xml.= "</item>\n";
		}
		$xml.= "</channel>\n";
		$xml.= "</rss>";
		exit($xml);
	}

	//生成URLLIST
	function urllist_f()
	{
		//设置语言包
		$this->cate_m->set_langid($_SESSION["sys_lang_id"]);
		$this->cate_m->get_catelist(0,"c.if_hidden='0' AND (m.if_list='1' OR m.if_msg='1') ");
		$catelist = $this->cate_m->catelist;
		$cate_count = count($catelist);
		$rslist = array();
		if($this->sys_config['site_type'] != "html")
		{
			$myurl_pre = $this->sys_config["siteurl"];
		}
		else
		{
			$myurl_pre = $this->sys_config["sitehtml"] ? $this->sys_config["sitehtml"] : $this->sys_config["siteurl"]."html/".$_SESSION["sys_lang_id"]."/";
		}
		if($catelist && is_array($catelist) && $cate_count>0)
		{
			foreach($catelist AS $key=>$value)
			{
				$myurl = list_url($value);
				$chke_url = strtolower(substr($myurl,0,7));
				if($chke_url != "http://" && $chke_url != "https:/")
				{
					$myurl = $myurl_pre . $myurl;
				}
				$rslist[] = array("title"=>$value["title"],"url"=>$myurl);
			}
		}
		$max = 49999 - $cate_count;
		//取得主题列表
		$this->list_m->langid($_SESSION["sys_lang_id"]);
		$sublist = $this->list_m->getlist_for_sitemap($max);
		if($sublist && is_array($sublist) && count($sublist)>0)
		{
			foreach($sublist AS $key=>$value)
			{
				$myurl = msg_url($value);
				$chke_url = strtolower(substr($myurl,0,7));
				if($chke_url != "http://" && $chke_url != "https:/")
				{
					$myurl = $myurl_pre . $myurl;
				}
				$rslist[] = array("title"=>$value["title"],"url"=>$myurl);
			}
		}
		foreach($rslist AS $key=>$value)
		{
			$txt.= "".$value["url"]."\n";
		}
		exit($txt);
	}
}
?>