<?php
/***********************************************************
	Filename: app/admin/models_mysql/list.php
	Note	: 内容模块管理器
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-24
***********************************************************/
class list_m extends Model
{
	var $sql_ext;
	var $condition = " WHERE 1=1 ";
	var $psize = 20;
	function __construct()
	{
		parent::Model();
		$this->psize = defined("SYS_PSIZE") ? SYS_PSIZE : 20;
	}

	function list_m()
	{
		$this->__construct();
	}

	function set_condition($string="")
	{
		if($string)
		{
			$this->condition .= " AND ".$string." ";
		}
		return true;
	}

	function set_psize($psize=20)
	{
		$this->psize = $psize;
	}

	function set_keywords($keytype="title",$keywords="",$mid=0,$isbest=0)
	{       
		/* if(!$keytype || !$keywords)
		{
			return false;
		}
		 */
		//设置关键字查询
		$keytype_in = array("title","note","subtitle","link_url","author","seotitle","keywords","description");
		if(in_array($keytype,$keytype_in))
		{
			$keywords_array = explode(" ",$keywords);
			$c_array = array();
			foreach($keywords_array AS $key=>$value)
			{
				$c_array[] = "m.".$keytype." LIKE '%".$value."%' ";
			}
			$condition = implode(" OR ",$c_array);
			if($isbest !=10){
			$this->condition .= " AND (".$condition.") AND m.isbest='$isbest' "; 
             }else{
			 $this->condition .= " AND (".$condition.")"; 
			 }		
		}
		else
		{
			//取得扩展关键字的查询结果
			$sql = "SELECT * FROM ".$this->db->prefix."module_fields WHERE module_id='".$mid."' AND identifier='".$keytype."'";
			$rs = $this->db->get_one($sql);
			if(!$rs)
			{
				return false;
			}
			$condition = "SELECT id FROM ".$this->db->prefix."list_ext WHERE `field`='".$keytype."' AND id IN(SELECT id FROM ".$this->db->prefix."list WHERE module_id='".$mid."') ";
			$keywords_array = explode(" ",$keywords);
			$c_array = array();
			foreach($keywords_array AS $key=>$value)
			{
				$c_array[] = "`val` LIKE '%".$value."%' ";
			}
			$condition .= " AND (".implode(" OR ",$c_array).") ";
			$this->condition .= " AND m.id IN(".$condition.")";
		}		      		//echo $this->condition;
	}

	function chk_sign($sign,$id=0,$langid="zh")
	{
		$sql = "SELECT id FROM ".$this->db->prefix."list WHERE identifier='".$sign."'";
		if($id)
		{
			$sql .= " AND id!='".$id."'";
		}
		$sql.= " AND langid='".$langid."'";
		return $this->db->get_one($sql);
	}

	//查询文章数
	function get_list($pageid=0,$iscate=false,$ifthumb=false)
	{
		$this->sql_ext = " FROM ".$this->db->prefix."list m ";
		$offset = $pageid>0 ? ($pageid-1)*$this->psize : 0;
		$sql_fields = "m.*";
		if($iscate)
		{
			$this->sql_ext .= " LEFT JOIN ".$this->db->prefix."cate c ON(m.cate_id=c.id) ";
			$sql_fields .= ",c.cate_name";
		}
		if($ifthumb)
		{
			$this->sql_ext .= " LEFT JOIN ".$this->db->prefix."upfiles u ON(m.thumb_id=u.id) ";
			$sql_fields .= ",u.thumb";
		}
		$sql = "SELECT ".$sql_fields." ".$this->sql_ext." ".$this->condition." ORDER BY m.istop DESC,m.taxis DESC,m.post_date DESC,m.id DESC ";
		$sql.= " LIMIT ".$offset.",".$this->psize;      //echo $sql;	  
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist) return false;
		$idlist = implode(",",array_keys($rslist));
		$sql = "SELECT * FROM ".$this->db->prefix."list_ext WHERE id IN(".$idlist.")";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist) $tmplist = array();
		foreach($tmplist AS $key=>$value)
		{
			$rslist[$value["id"]][$value["field"]] = $value["val"];
		}
		unset($tmplist);
		$tmplist = array();
		foreach($rslist AS $key=>$value)
		{
			$tmplist[] = $value;
		}
		return $tmplist;
	}

	//取得链接数列表
	function get_link($pageid=0,$condition="")
	{
		$offset = $pageid>0 ? ($pageid-1)*$this->psize : 0;
		$sql = "SELECT l.*,m.title m_title,c.cate_name,m.identifier m_sign,c.identifier c_sign FROM ".$this->db->prefix."list l LEFT JOIN ".$this->db->prefix."module m ON(l.module_id=m.id) LEFT JOIN ".$this->db->prefix."cate c ON(l.cate_id=c.id) WHERE 1=1 ";
		if($condition)
		{
			$sql .= " AND ".$condition;
		}
		$sql.= " LIMIT ".$offset.",".$this->psize;
		return $this->db->get_all($sql);
	}

	function get_link_count($condition="")
	{
		$sql = "SELECT count(l.id) FROM ".$this->db->prefix."list l LEFT JOIN ".$this->db->prefix."module m ON(l.module_id=m.id) LEFT JOIN ".$this->db->prefix."cate c ON(l.cate_id=c.id) WHERE 1=1 ";
		if($condition)
		{
			$sql .= " AND ".$condition;
		}
		return $this->db->count($sql);
	}

	//查询数量
	function get_count()
	{
		$this->sql_ext = " FROM ".$this->db->prefix."list m ";
		$sql = "SELECT count(m.id) total ".$this->sql_ext." ".$this->condition;
		$rs = $this->db->count($sql);
		return $rs;
	}

	//取得值
	function get_one($id)
	{
		$sql = " SELECT m.*,u.thumb ";
		$sql.= " FROM ".$this->db->prefix."list m ";
		$sql.= " LEFT JOIN ".$this->db->prefix."upfiles u ON(m.thumb_id=u.id) ";
		$sql.= " WHERE m.id='".$id."'";
		$rs = $this->db->get_one($sql);
		//取得扩展字段信息
		$sql = "SELECT field,val FROM ".$this->db->prefix."list_ext WHERE id='".$id."'";
		$tmp_rs = $this->db->get_all($sql);
		if($tmp_rs && is_array($tmp_rs) && count($tmp_rs)>0)
		{
			foreach($tmp_rs AS $key=>$value)
			{
				$rs[$value["field"]] = $value["val"];
			}
		}
		unset($tmp_rs);
		$sql = "SELECT field,val FROM ".$this->db->prefix."list_c WHERE id='".$id."'";
		$tmp_rs = $this->db->get_all($sql);
		if($tmp_rs && is_array($tmp_rs) && count($tmp_rs)>0)
		{
			foreach($tmp_rs AS $key=>$value)
			{
				$rs[$value["field"]] = $value["val"];
			}
		}
		return $rs;
	}

	//存储核心数据
	function save_sys($array,$id=0)
	{
		if($id)
		{
			$this->db->update_array($array,"list",array("id"=>$id));
			return $id;
		}
		else
		{
			$insert_id = $this->db->insert_array($array,"list");
			return $insert_id;
		}
	}

	//存储扩展信息
	function save_ext($array,$tbltype="ext")
	{
		return $this->db->insert_array($array,"list_".$tbltype,"replace");
	}

	function set_pl($id,$field,$val)
	{
		$sql = "UPDATE ".$this->db->prefix."list SET ".$field."='".$val."' WHERE id IN(".$id.")";
		return $this->db->query($sql);
	}

	//更新分类
	function set_cate($id,$cateid)
	{
		if(!$id || !$cateid)
		{
			return false;
		}
		$sql = "UPDATE ".$this->db->prefix."list SET cate_id='".$cateid."' WHERE id IN(".$id.")";
		return $this->db->query($sql);
	}

	//删除数据
	function del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."list WHERE id IN(".$id.")";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."list_ext WHERE id IN(".$id.")";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."list_c WHERE id IN(".$id.")";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."reply WHERE tid IN(".$id.")";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."list_cate WHERE id IN(".$id.")";
		$this->db->query($sql);
		return true;
	}

	//取得分类下的数目
	function get_count_from_cate($idstring)
	{
		$sql = "SELECT count(l.id) FROM ".$this->db->prefix."list l WHERE l.status='1' AND l.hidden='0' AND l.cate_id IN(".$idstring.") ";
		return $this->db->count($sql);
	}

	function get_count_from_module($mid)
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."list WHERE status='1' AND hidden='0' AND module_id='".$mid."'";
		return $this->db->count($sql);
	}

	//取得指定分类下的最大主题和最小主题ID
	function max_min_id($cateid="",$mid=0,$langid="zh",$if_msg=0)
	{
		$sql = "SELECT max(l.id) max_id,min(l.id) min_id FROM ".$this->db->prefix."list l JOIN ".$this->db->prefix."module m ON(l.module_id=m.id) WHERE l.status='1'";
		if($cateid)
		{
			$sql .= " AND l.cate_id IN(".$cateid.") ";
		}
		if($mid)
		{
			$sql .= " AND l.module_id='".$mid."' ";
		}
		$sql.= " AND l.langid='".$langid."' ";
		if($if_msg)
		{
			$sql.= " AND m.if_msg='1' ";
		}
		return $this->db->get_one($sql);
	}

	function get_next_id($cateid="",$mid=0,$langid="zh",$tid=0,$if_msg=0)
	{
		$sql = "SELECT l.id FROM ".$this->db->prefix."list l JOIN ".$this->db->prefix."module m ON(l.module_id=m.id) WHERE l.status='1'";
		if($cateid)
		{
			$sql .= " AND l.cate_id IN(".$cateid.") ";
		}
		if($mid)
		{
			$sql .= " AND l.module_id='".$mid."' ";
		}
		$sql.= " AND l.langid='".$langid."' ";
		if($tid)
		{
			$sql .= " AND l.id>'".$tid."' ";
		}
		if($if_msg)
		{
			$sql.= " AND m.if_msg='1' ";
		}
		$sql .= " ORDER BY l.id ASC LIMIT 1";
		$rs = $this->db->get_one($sql);
		return $rs["id"] ? $rs["id"] : false;
	}

	function set_taxis($id,$taxis=0)
	{
		$sql = "UPDATE ".$this->db->prefix."list SET taxis='".$taxis."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}


	function ext_catelist($id,$cateid=0)
	{
		if(!$id)
		{
			return false;
		}
		$sql = "SELECT cateid FROM ".$this->db->prefix."list_cate WHERE id='".$id."'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist) return false;
		$rs = array();
		foreach($rslist AS $key=>$value)
		{
			if($value["cateid"] != $cateid)
			{
				$rs[] = $value["cateid"];
			}
		}
		return $rs;
	}

	//存储扩展分类
	function save_catelist($id,$catelist)
	{
		if(!$id)
		{
			return false;
		}
		//删除原有主题下的分类信息
		$sql = "DELETE FROM ".$this->db->prefix."list_cate WHERE id='".$id."'";
		$this->db->query($sql);
		if(!$catelist || !is_array($catelist)) $catelist = array();;
		foreach($catelist AS $key=>$value)
		{
			$sql = "REPLACE INTO ".$this->db->prefix."list_cate(id,cateid) VALUES('".$id."','".$value."')";
			$this->db->query($sql);
		}
		return true;
	}

	//迁移主分类
	function update_main_cate($id,$cateid)
	{
		if(!$id || !$cateid) return false;
		$rs = $this->db->get_one("SELECT cate_id FROM ".$this->db->prefix."list WHERE id='".$id."'");
		if($rs && $rs["cate_id"])
		{
			//删除主分类
			$sql = "DELETE FROM ".$this->db->prefix."list_cate WHERE id='".$id."' AND cateid='".$rs["cate_id"]."'";
			$this->db->query($sql);
		}
		$sql = "UPDATE ".$this->db->prefix."list SET cate_id='".$cateid."' WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "REPLACE INTO ".$this->db->prefix."list_cate(id,cateid) VALUES('".$id."','".$cateid."')";
		$this->db->query($sql);
		return true;
	}

	//读取指定主题下的列表
	function get_list_from_id($id,$keys="*")
	{
		$sql = "SELECT ".$keys." FROM ".$this->db->prefix."list WHERE id IN(".$id.")";
		return $this->db->get_all($sql);
	}
}
?>