<?php
/***********************************************************
	Filename: libs/models/list_model.php
	Note	: 全局数据调用管理
	Version : 3.0
	Author  : qinggan
	Update  : 2011-10-17 12:06
***********************************************************/
class list_model extends Model
{
	var $langid = "zh";
	var $thumbtype = "";
	var $phpok_rs;
	function __construct()
	{
		parent::Model();
	}

	function list_model()
	{
		$this->__construct();
	}

	//指定数据调用模块类型
	function thumbtype($type="")
	{
		$this->thumbtype = $type;
	}

	function set_rs($rs)
	{
		$this->phpok_rs = $rs;
	}

	function exec_sql($sql,$type="get_one")
	{
		if($type == "get_one")
		{
			return $this->db->get_one($sql);
		}
		else
		{
			return $this->db->get_all($sql);
		}
	}

	function order_by($order_by="post_desc")
	{
		$orderby = "l.istop DESC";
		switch($order_by)
		{
			case "post_desc":
				$orderby.= ",l.taxis DESC,l.post_date DESC";
			break;
			case "post_asc":
				$orderby.= ",l.taxis ASC,l.post_date ASC";
			break;
			case "modify_desc":
				$orderby.= ",l.modify_date DESC,l.taxis DESC,l.post_date DESC";
			break;
			case "modify_asc":
				$orderby.= ",l.modify_date ASC,l.taxis DESC,l.post_date DESC";
			break;
			case "reply_desc":
				$orderby.= ",l.reply_date DESC,l.taxis DESC,l.post_date DESC";
			break;
			case "reply_asc":
				$orderby.= ",l.reply_date ASC,l.taxis DESC,l.post_date DESC";
			break;
			case "hits_desc":
				$orderby.= ",l.hits DESC,l.taxis DESC,l.post_date DESC";
			break;
			case "hits_asc":
				$orderby.= ",l.hits ASC,l.taxis DESC,l.post_date DESC";
			break;
			default:
				$orderby.= ",l.taxis DESC,l.post_date DESC";
			break;
		}
		$orderby .= ",l.id DESC";
		return $orderby;
	}

	function langid($langid="zh")
	{
		$this->langid = $langid;
	}

	//读取图片列表时将只读取生成新图片的数据
	function get_picture_list($picid,$type="")
	{
		if($picid)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."upfiles_gd WHERE pid IN(".$picid.") ";
		if($type)
		{
			$sql.= " AND gdtype='".$type."'";
		}
		$rslist = $this->db->get_all($sql);
		if(!$rslist || !is_array($rslist) || count($rslist)<1)
		{
			return false;
		}
		$newlist = array();
		foreach($rslist AS $key=>$value)
		{
			$newlist[$value["pid"]][$value["gdtype"]] = $value["filename"];
		}
		return $newlist;
	}

	//取得第一张图片信息
	function get_picture_one($picid,$type="")
	{
		if(!$picid)
		{
			return false;
		}
		if($type)
		{
			$sql = "SELECT filename FROM ".$this->db->prefix."upfiles_gd WHERE pid='".$picid."' AND gdtype='".$type."'";
			$rs = $this->db->get_one($sql);
		}
		else
		{
			$sql = "SELECT filename FROM ".$this->db->prefix."upfiles WHERE id='".$picid."'";
			$rs = $this->db->get_one($sql);
		}
		$r = $rs["filename"] ? $rs["filename"] : false;
		return $r;
	}

	//取得内容信息
	function get_one($id,$ext="")
	{
		$sql = "SELECT l.*,m.if_thumb _if_thumb,m.inpic _inpic,m.if_biz _if_biz,c.inpic _inpic2 FROM ".$this->db->prefix."list l JOIN ".$this->db->prefix."module m ON(l.module_id=m.id) LEFT JOIN ".$this->db->prefix."cate c ON(l.cate_id=c.id) WHERE l.id='".$id."'";
		if($this->phpok_rs["pic_required"])
		{
			$sql .= " AND l.thumb_id>0 ";
		}
		if($this->phpok_rs["attr"])
		{
			$sql .= " AND l.".$this->phpok_rs["attr"].">0 ";
		}
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		if($ext && is_array($ext))
		{
			$thumb_type = $rs["inpic"];
		}
		else
		{
			$thumb_type = $rs["_inpic2"] ? $rs["_inpic2"] : $rs["_inpic"];//如果有图片配置
		}
		$rs["picture"] = $this->get_picture_one($rs["thumb_id"],$thumb_type);

		//取得扩展字段信息
		$sql = "SELECT e.field,e.val,m.input FROM ".$this->db->prefix."list_ext e JOIN ".$this->db->prefix."list l ON(e.id=l.id) JOIN ".$this->db->prefix."module_fields m ON(l.module_id=m.module_id AND e.field=m.identifier) WHERE e.id='".$id."'";
		$tmp_rs = $this->db->get_all($sql);
		if($tmp_rs && is_array($tmp_rs) && count($tmp_rs)>0)
		{
			foreach($tmp_rs AS $key=>$value)
			{
				$rs[$value["field"]] = sys_format_list($value["val"],$value["input"]);
			}
		}
		unset($tmp_rs);
		$sql = "SELECT field,val FROM ".$this->db->prefix."list_c WHERE id='".$id."'";
		$tmp_rs = $this->db->get_all($sql);
		if($tmp_rs && is_array($tmp_rs) && count($tmp_rs)>0)
		{
			foreach($tmp_rs AS $key=>$value)
			{
				$value["val"] = sys_format_content($value["val"]);
				$rs[$value["field"]] = $value["val"];
			}
		}
		return $rs;
	}

	function get_one_ts($var,$ext="")
	{
		$sql = "SELECT id FROM ".$this->db->prefix."list WHERE identifier='".$var."' AND langid='".$this->langid."'";
		$tmp_rs = $this->db->get_one($sql);
		if(!$tmp_rs)
		{
			return false;
		}
		return $this->get_one($tmp_rs["id"],$ext);
	}

	//取得当前分类下的一条信息
	function get_one_cid($id,$orderby="post_desc")
	{
		if(!$id)
		{
			return false;
		}
		$me_sql = "SELECT c.* FROM ".$this->db->prefix."cate c ";
		$me_sql.= "WHERE c.id='".$id."'";
		$me = $this->db->get_one($me_sql);
		if(!$me)
		{
			return false;
		}
		unset($me_sql);
		if($orderby == "rand")
		{
			$cate_list = explode(",",$id);
			foreach($cate_list AS $key=>$value)
			{
				sys_son_cateid($cate_list,$value);//取得子分类信息
			}
			$cate_in = implode(",",$cate_list);
			$tid = $this->get_rand_id($cate_in,"cate_id");
			if(!$tid)
			{
				return false;
			}
			$rs = $this->get_one($tid);
			return array("me"=>$me,"rs"=>$rs);
		}
		else
		{
			$order_by = $this->order_by($orderby);
			$cate_list = explode(",",$id);
			foreach($cate_list AS $key=>$value)
			{
				sys_son_cateid($cate_list,$value);//取得子分类信息
			}
			$cate_in = implode(",",$cate_list);
			$sql = "SELECT id FROM ".$this->db->prefix."list l WHERE cate_id IN(".$cate_in.") AND langid='".$this->langid."'";
			$sql.= " ORDER BY ".$order_by;
			$rs = $this->db->get_one($sql);
			$tid = $rs["id"];
			$rs = $this->get_one($tid);
			return array("me"=>$me,"rs"=>$rs);
		}
	}

	//取得当前的分类ID
	function get_one_cs($id,$orderby="post_desc")
	{
		if(!$id)
		{
			return false;
		}
		$sql = "SELECT id FROM ".$this->db->prefix."cate WHERE identifier='".$id."' AND langid='".$this->langid."' AND status='1'";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		return $this->get_one_cid($rs["id"],$orderby);
	}

	//取得当前模块下的一条信息
	function get_one_mid($id,$orderby="post_desc")
	{
		if(!$id)
		{
			return false;
		}
		$me_sql = "SELECT * FROM ".$this->db->prefix."module WHERE id='".$id."'";
		$me = $this->db->get_one($me_sql);
		if($orderby == "rand")
		{
			$tid = $this->get_rand_id($id,"module_id");
			if(!$tid)
			{
				return false;
			}
			$rs = $this->get_one($tid);
			return array("me"=>$me,"rs"=>$rs);
		}
		else
		{
			$order_by = $this->order_by($orderby);
			$sql = "SELECT id FROM ".$this->db->prefix."list l WHERE module_id='".$id."' AND langid='".$this->langid."'";
			$sql.= " ORDER BY ".$order_by;
			$rs = $this->db->get_one($sql);
			$tid = $rs["id"];
			$rs = $this->get_one($tid);
			return array("me"=>$me,"rs"=>$rs);
		}
	}

	//通过标识符取得其中一模块下的信息一条
	function get_one_ms($id,$orderby="post_desc")
	{
		if(!$id)
		{
			return false;
		}
		$sql = "SELECT id FROM ".$this->db->prefix."module WHERE identifier='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		return $this->get_one_mid($rs["id"],$orderby);
	}

	//随机获取一个有效的ID
	function get_rand_id($id,$type="cate_id")
	{
		if(!$id)
		{
			return false;
		}
		$sql = "SELECT ROUND(rand()*max(id)) AS id FROM ".$this->db->prefix."list WHERE ".$type." IN(".$id.") AND langid='".$this->langid."'";
		if($this->phpok_rs["pic_required"])
		{
			$sql .= " AND thumb_id>0 ";
		}
		if($this->phpok_rs["attr"])
		{
			$sql .= " AND ".$this->phpok_rs["attr"].">0 ";
		}
		//echo $sql;
		$rs = $this->db->get_one($sql);
		return $rs["id"];
	}

	//获取信息列表
	function get_list_cid($id,$count=10,$orderby="post_desc")
	{
		if(!$id)
		{
			return false;
		}
		//读取当前分类信息
		$sql = "SELECT c.* FROM ".$this->db->prefix."cate c ";
		$sql.= "WHERE c.id='".$id."'";
		$c_rs = $this->db->get_one($sql);
		if(!$c_rs || !$c_rs["status"])
		{
			return false;
		}
		//取得模块信息
		$m_rs = $this->get_one_module_cate($id);
		if(!$m_rs)
		{
			return false;
		}
		if(!$this->thumbtype)
		{
			$thumbtype = $c_rs["inpic"] ? $c_rs["inpic"] : $m_rs["inpic"];//是否同时加载指定的缩略图
		}
		else
		{
			$thumbtype = $this->thumbtype;
		}
		$count = intval($count)<1 ? 10 : intval($count);
		if($orderby == "rand")
		{
			$cate_list = explode(",",$id);
			foreach($cate_list AS $key=>$value)
			{
				sys_son_cateid($cate_list,$value);//取得子分类信息
			}
			$cate_in = implode(",",$cate_list);
			$idlist = array();
			for($i=0;$i<$count;$i++)
			{
				$tid = $this->get_rand_id($cate_in,"cate_id");
				if(!$tid)
				{
					return false;
				}
				$idlist[] = $tid;
			}
			if(count($idlist)<1)
			{
				return false;
			}
			$idin = implode(",",$idlist);
			if($thumbtype)
			{
				$sql = "SELECT l.*,g.filename picture FROM ".$this->db->prefix."list l LEFT JOIN ".$this->db->prefix."upfiles_gd g ON(l.thumb_id=g.pid AND g.gdtype='".$thumbtype."') ";
			}
			else
			{
				$sql = "SELECT l.*,g.filename picture FROM ".$this->db->prefix."list l LEFT JOIN ".$this->db->prefix."upfiles g ON(l.thumb_id=g.id) ";
			}
			$sql.= " WHERE l.id IN(".$idin.") AND l.langid='".$this->langid."' ORDER BY substring_index('".$idin."',l.id,1)";
			$rslist = $this->db->get_all($sql);
			if(!$rslist)
			{
				return false;
			}
		}
		else
		{
			$order_by = $this->order_by($orderby);
			$cate_list = array($id);
			sys_son_cateid($cate_list,$id);//取得子分类信息
			$cate_in = implode(",",$cate_list);
			if($thumbtype)
			{
				$sql = "SELECT l.*,g.filename picture FROM ".$this->db->prefix."list l LEFT JOIN ".$this->db->prefix."upfiles_gd g ON(l.thumb_id=g.pid AND g.gdtype='".$thumbtype."') ";
			}
			else
			{
				$sql = "SELECT l.*,g.filename picture FROM ".$this->db->prefix."list l LEFT JOIN ".$this->db->prefix."upfiles g ON(l.thumb_id=g.id) ";
			}
			$sql .= " WHERE l.status='1' AND l.langid='".$this->langid."' ";
			if($this->phpok_rs["pic_required"])
			{
				$sql .= " AND l.thumb_id>0 ";
			}
			if($this->phpok_rs["attr"])
			{
				$sql .= " AND l.".$this->phpok_rs["attr"].">0 ";
			}
			$sql.= " AND l.cate_id IN(".$cate_in.") ORDER BY ".$order_by;
			$sql.= " LIMIT ".$count;
			$rslist = $this->db->get_all($sql);
			if(!$rslist || !is_array($rslist) || count($rslist)<1)
			{
				return false;
			}
			$idlist = array();
			foreach($rslist AS $key=>$value)
			{
				$idlist[] = $value["id"];
			}
			$idin = implode(",",$idlist);
		}

		//获取扩展数据
		$sql = "SELECT * FROM ".$this->db->prefix."list_ext WHERE id IN(".$idin.")";
		$ext_list = $this->db->get_all($sql);
		if($ext_list && is_array($ext_list) && count($ext_list)>0)
		{
			$e_list = array();
			foreach($ext_list AS $key=>$value)
			{
				$e_list[$value["id"]][$value["field"]] = $value["val"];
			}
			foreach($rslist AS $key=>$value)
			{
				$vid = $value["id"];
				if($e_list[$vid])
				{
					$rslist[$key] = array_merge($value,$e_list[$vid]);
				}
			}
		}

		//获取扩展内容数据
		unset($ext_list);
		$sql = "SELECT * FROM ".$this->db->prefix."list_c WHERE id IN(".$idin.")";
		$ext_list = $this->db->get_all($sql);
		if($ext_list && is_array($ext_list) && count($ext_list)>0)
		{
			$e_list = array();
			foreach($ext_list AS $key=>$value)
			{
				$e_list[$value["id"]][$value["field"]] = $value["val"];
			}
			foreach($rslist AS $key=>$value)
			{
				$vid = $value["id"];
				if($e_list[$vid])
				{
					$rslist[$key] = array_merge($value,$e_list[$vid]);
				}
			}
		}
		return array("me"=>$c_rs,"rslist"=>$rslist);
	}

	//通过标识符取得内容信息
	function get_list_cs($id,$count=10,$orderby="post_desc")
	{
		$sql = "SELECT id FROM ".$this->db->prefix."cate WHERE identifier='".$id."' AND langid='".$this->langid."'";
		$rs = $this->db->get_one($sql);
		if(!$rs || !$rs["id"])
		{
			return false;
		}
		return $this->get_list_cid($rs["id"],$count,$orderby);
	}

	//获取信息列表
	function get_list_mid($id,$count=10,$orderby="post_desc")
	{
		if(!$id)
		{
			return false;
		}
		//取得模块信息
		$m_rs = $this->get_one_module($id);
		if(!$m_rs)
		{
			return false;
		}
		$count = intval($count)<1 ? 10 : intval($count);
		if(!$this->thumbtype)
		{
			$thumbtype = $m_rs["inpic"];
		}
		else
		{
			$thumbtype = $this->thumbtype;
		}
		if($orderby == "rand")
		{
			$idlist = array();
			for($i=0;$i<$count;$i++)
			{
				$tid = $this->get_rand_id($id,"module_id");
				if(!$tid)
				{
					return false;
				}
				$idlist[] = $tid;
			}
			if(count($idlist)<1)
			{
				return false;
			}
			$idin = implode(",",$idlist);
			if($thumbtype)
			{
				$sql = "SELECT l.*,g.filename picture FROM ".$this->db->prefix."list l LEFT JOIN ".$this->db->prefix."upfiles_gd g ON(l.thumb_id=g.pid AND g.gdtype='".$thumbtype."') ";
			}
			else
			{
				$sql = "SELECT l.*,g.filename picture FROM ".$this->db->prefix."list l LEFT JOIN ".$this->db->prefix."upfiles g ON(l.thumb_id=g.id) ";
			}
			$sql.= " WHERE l.id IN(".$idin.") AND l.langid='".$this->langid."' ORDER BY substring_index('".$idin."',l.id,1)";
			$rslist = $this->db->get_all($sql);
			if(!$rslist)
			{
				return false;
			}
		}
		else
		{
			if($thumbtype)
			{
				$sql = "SELECT l.*,g.filename picture FROM ".$this->db->prefix."list l LEFT JOIN ".$this->db->prefix."upfiles_gd g ON(l.thumb_id=g.pid AND g.gdtype='".$thumbtype."') ";
			}
			else
			{
				$sql = "SELECT l.*,g.filename picture FROM ".$this->db->prefix."list l LEFT JOIN ".$this->db->prefix."upfiles g ON(l.thumb_id=g.id) ";
			}
			$order_by = $this->order_by($orderby);
			$sql .= " WHERE l.status='1' ";
			if($this->phpok_rs["pic_required"])
			{
				$sql .= " AND l.thumb_id>0 ";
			}
			if($this->phpok_rs["attr"])
			{
				$sql .= " AND l.".$this->phpok_rs["attr"].">0 ";
			}
			$sql.= " AND l.module_id IN(".$id.") AND l.langid='".$this->langid."' ORDER BY ".$order_by;
			$sql.= " LIMIT ".$count;
			$rslist = $this->db->get_all($sql);
			if(!$rslist || !is_array($rslist) || count($rslist)<1)
			{
				return false;
			}
			$idlist = array();
			foreach($rslist AS $key=>$value)
			{
				$idlist[] = $value["id"];
			}
			$idin = implode(",",$idlist);
		}

		//获取扩展数据
		$sql = "SELECT * FROM ".$this->db->prefix."list_ext WHERE id IN(".$idin.")";
		$ext_list = $this->db->get_all($sql);
		if($ext_list && is_array($ext_list) && count($ext_list)>0)
		{
			$e_list = array();
			foreach($ext_list AS $key=>$value)
			{
				$e_list[$value["id"]][$value["field"]] = $value["val"];
			}
			foreach($rslist AS $key=>$value)
			{
				$vid = $value["id"];
				if($e_list[$vid])
				{
					$rslist[$key] = array_merge($value,$e_list[$vid]);
				}
			}
		}
		//获取扩展内容数据
		unset($ext_list);
		$sql = "SELECT * FROM ".$this->db->prefix."list_c WHERE id IN(".$idin.")";
		$ext_list = $this->db->get_all($sql);
		if($ext_list && is_array($ext_list) && count($ext_list)>0)
		{
			$e_list = array();
			foreach($ext_list AS $key=>$value)
			{
				$e_list[$value["id"]][$value["field"]] = $value["val"];
			}
			foreach($rslist AS $key=>$value)
			{
				$vid = $value["id"];
				if($e_list[$vid])
				{
					$rslist[$key] = array_merge($value,$e_list[$vid]);
				}
			}
		}
		return array("me"=>$m_rs,"rslist"=>$rslist);
	}

	//通过标识符取得内容信息
	function get_list_ms($id,$count=10,$orderby="post_desc")
	{
		$sql = "SELECT id FROM ".$this->db->prefix."module WHERE identifier='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs || !$rs["id"])
		{
			return false;
		}
		return $this->get_list_mid($rs["id"],$count,$orderby);
	}

	//取得模块信息
	function get_one_module($mid)
	{
		if(!$mid)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."module WHERE id='".$mid."'";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		if(!$rs["status"])
		{
			return false;
		}
		return $rs;
	}

	//通过分类取得模块信息
	function get_one_module_cate($cid)
	{
		if(!$cid)
		{
			return false;
		}
		$sql = "SELECT m.* FROM ".$this->db->prefix."cate c JOIN ".$this->db->prefix."module m ON(c.module_id=m.id) WHERE c.id='".$cid."'";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		if(!$rs["status"])
		{
			return false;
		}
		return $rs;
	}

	//通过主题ID取得分类ID
	function get_cateid_from_id($id)
	{
		$sql = "SELECT l.cate_id FROM ".$this->db->prefix."list l LEFT JOIN ".$this->db->prefix."cate c ON(l.cate_id=c.id) WHERE l.id='".$id."' AND l.status='1' AND c.status='1'";
		$rs = $this->db->get_one($sql);
		if(!$rs || !$rs["cate_id"])
		{
			return false;
		}
		else
		{
			return $rs["cate_id"];
		}
	}

	//通过主题标识串取得分类ID
	function get_cateid_from_ts($id)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."list WHERE identifier='".$id."' AND langid='".$this->langid."'";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		return $this->get_cateid_from_id($id);
	}

	//通过分类标识串取得分类ID
	function get_cateid_from_cs($id)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."cate WHERE identifier='".$id."' AND langid='".$this->langid."'";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		else
		{
			return $rs["id"];
		}
	}

	//通过模块标识符得出模块ID
	function get_mid_from_ms($id)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."module WHERE identifier='".$id."' AND langid='".$this->langid."'";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		return $rs["id"];
	}

	//取得分类列表
	function get_catelist($id,$type="cate")
	{
		if(!$id)
		{
			return false;
		}
		$count = intval($this->phpok_rs["maxcount"]);
		if(!$count)
		{
			$count = 1;
		}
		if($type == "cate")
		{
			$sql = "SELECT c.*,p.id p_id,p.parentid p_parentid,s.id s_id,s.parentid s_parentid FROM ".$this->db->prefix."cate c LEFT JOIN ".$this->db->prefix."cate p ON(c.parentid=p.id) LEFT JOIN ".$this->db->prefix."cate s ON(c.id=s.parentid) WHERE c.id='".$id."' LIMIT 1";
			$me = $this->db->get_one($sql);
			if(!$me)
			{
				return false;
			}
			if($count == 1)
			{
				return $me;
			}
			//如果有子分类，那么读取同级分类及当前分类下的子分类
			if($me["s_id"])
			{
				//读取子类数据
				$sql = "SELECT c.* FROM ".$this->db->prefix."cate c WHERE c.parentid='".$id."' AND c.langid='".$this->langid."' AND c.status='1' AND c.if_hidden='0'";
				$sql.= " ORDER BY c.taxis ASC,c.id DESC LIMIT ".$count;
				$sonlist = $this->db->get_all($sql);
				//如果有父级分类读父级分类
				if($me["parentid"])
				{
					$sql = "SELECT c.* FROM ".$this->db->prefix."cate c WHERE c.parentid='".$me["parentid"]."' AND c.langid='".$this->langid."' AND c.status='1' AND c.if_hidden='0' ";
					$sql.= "ORDER BY c.taxis ASC,c.id DESC LIMIT ".$count;
					$parentlist = $this->db->get_all($sql);
					//合并父类及子类
					$rslist = array();
					foreach($parentlist AS $key=>$value)
					{
						if($value["id"] == $sonlist[0]["parentid"])
						{
							$value["sonlist"] = $sonlist;
						}
						$rslist[$key] = $value;
					}
				}
				else
				{
					$rslist = $sonlist;
				}
				return array("me"=>$me,"rslist"=>$rslist);
			}
			if(!$me["parentid"])
			{
				return array("me"=>$me,"rslist"=>false);
			}
			//读取同级分类
			$sql = "SELECT c.* FROM ".$this->db->prefix."cate c WHERE c.parentid='".$me["parentid"]."' AND c.langid='".$this->langid."' AND c.status='1' AND c.if_hidden='0' AND c.module_id='".$me["module_id"]."'";
			$sql.= " ORDER BY c.taxis ASC,c.id DESC LIMIT ".$count;
			$rslist = $this->db->get_all($sql);
			if(!$rslist)
			{
				return false;
			}
			//echo "<pre>".print_r($rslist,true)."</pre>";
			//判断是否有父级分类
			if($me["p_parentid"])
			{
				$sql = "SELECT c.* FROM ".$this->db->prefix."cate c WHERE c.parentid='".$me["p_parentid"]."' AND c.langid='".$this->langid."' AND c.status='1' AND c.if_hidden='0' AND c.module_id='".$me["module_id"]."'";
				$sql.= " ORDER BY c.taxis ASC,c.id DESC LIMIT ".$count;
				$plist = $this->db->get_all($sql);
				if(!$plist)
				{
					return array("me"=>$me,"rslist"=>$rslist);
				}
				//echo "<pre>".print_r($plist,true)."</pre>";
				foreach($plist AS $key=>$value)
				{
					if($value["id"] == $me["parentid"])
					{
						$plist[$key]["sonlist"] = $rslist;
					}
				}
				return array("me"=>$me,"rslist"=>$plist);
			}
			return array("me"=>$me,"rslist"=>$rslist);
		}
		else
		{
			$sql = "SELECT c.* FROM ".$this->db->prefix."cate c WHERE c.module_id='".$id."' AND c.langid='".$this->langid."' AND c.parentid='0' AND c.status='1' AND c.if_hidden='0' ORDER BY c.taxis ASC,c.id DESC LIMIT ".$count;
			$rslist = $count == 1 ? $this->db->get_one($sql) : $this->db->get_all($sql);
			$me = $count == 1 ? $rslist : $rslist[0];
			return array("me"=>$me,"rslist"=>$rslist);
		}
	}

	function get_s_catelist($id,$type="id",$langid="zh")
	{
		if(!$id) return false;
		if($type == "id")
		{
			$sql = "SELECT * FROM ".$this->db->prefix."cate WHERE parentid='".$id."' ORDER BY taxis ASC,id DESC";
			return $this->db->get_all($sql);
		}
		else
		{
			$sql = "SELECT id FROM ".$this->db->prefix."cate WHERE identifier='".$id."' AND langid='".$langid."'";
			$rs = $this->db->get_one($sql);
			if(!$rs || !$rs["id"])
			{
				return false;
			}
			return $this->get_s_catelist($rs["id"]);
		}
	}
}
?>