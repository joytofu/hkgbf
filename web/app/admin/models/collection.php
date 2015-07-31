<?php
/***********************************************************
	Filename: app/admin/models/collection.php
	Note	: 采集信息存储操作
	Version : 3.0
	Author  : qinggan
	Update  : 2011-04-06
***********************************************************/
class collection_m extends Model
{
	var $psize = 30;
	var $sub_idlist;
	function __construct()
	{
		parent::Model();
	}

	function collection_m()
	{
		$this->__construct();
	}

	function set_psize($psize=30)
	{
		$this->psize = $psize;
	}

	function get_all($condtion="",$pageid=0)
	{
		$offset = $pageid>0 ? ($pageid-1)*$this->psize : 0;
		$sql = "SELECT c.*,cate.cate_name,m.title m_title FROM ".$this->db->prefix."collection c LEFT JOIN ".$this->db->prefix."cate cate ON(c.cateid=cate.id) ";
		$sql.= " LEFT JOIN ".$this->db->prefix."module m ON(c.mid=m.id) ";
		if($condition)
		{
			$sql .= " WHERE ".$condition;
		}
		$sql .= " ORDER BY c.id DESC LIMIT ".$offset.",".$this->psize;
		return $this->db->get_all($sql);
	}

	function get_all_not_ok($pageid=0)
	{
		$offset = $pageid>0 ? ($pageid-1)*$this->psize : 0;
		$sql = "SELECT * FROM ".$this->db->prefix."collection_list WHERE status!=2 ";
		$sql .= " ORDER BY id DESC LIMIT ".$offset.",".$this->psize;
		return $this->db->get_all($sql);
	}

	function get_all_list($cid,$pageid=0,$keytype="",$keywords="")
	{
		$offset = $pageid>0 ? ($pageid-1)*$this->psize : 0;
		$sql = "SELECT * FROM ".$this->db->prefix."collection_list WHERE cid='".$cid."' ";
		if($keytype && $keywords)
		{
			$sql .= " AND id IN(SELECT lid FROM ".$this->db->prefix."collection_format WHERE tag='".$keytype."' AND content LIKE '%".$keywords."%')";
		}
		$sql .= " ORDER BY id DESC LIMIT ".$offset.",".$this->psize;
		return $this->db->get_all($sql);
	}

	function get_all_list_id($id)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."collection_list WHERE cid='".$id."'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist)
		{
			return false;
		}
		$tmplist = array();
		foreach($rslist AS $key=>$value)
		{
			$tmplist[] = $value["id"];
		}
		return $tmplist;
	}

	function get_all_tags($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."collection_tags WHERE cid='".$id."' ORDER BY id ASC;";
		return $this->db->get_all($sql);
	}

	function get_all_files_id($lid)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."collection_files WHERE lid='".$lid."' ORDER BY id ASC";
		return $this->db->get_all($sql);
	}

	function get_count($condition="")
	{
		$sql = "SELECT count(c.id) FROM ".$this->db->prefix."collection c LEFT JOIN ".$this->db->prefix."cate cate ON(c.cateid=cate.id) ";
		if($condition)
		{
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	function get_count_not_ok()
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."collection_list WHERE status!=2";
		return $this->db->count($sql);
	}

	function get_count_list($cid,$keytype="",$keywords="")
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."collection_list WHERE cid='".$cid."'";
		if($keytype && $keywords)
		{
			$sql .= " AND id IN(SELECT lid FROM ".$this->db->prefix."collection_format WHERE tag='".$keytype."' AND content LIKE '%".$keywords."%')";
		}
		return $this->db->count($sql);
	}

	//取得一条记录
	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."collection WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function get_one_tags($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."collection_tags WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function get_one_format($id)
	{
		$sql = "SELECT f.* FROM ".$this->db->prefix."collection_format f WHERE f.lid='".$id."'";
		return $this->db->get_all($sql);
	}

	function get_one_files($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."collection_files WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function get_one_list($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."collection_list WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	//存储采集的站点信息
	function save($data,$id=0)
	{
		if($id)
		{
			$this->db->update_array($data,"collection",array("id"=>$id));
			return true;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"collection");
			return $insert_id;
		}
	}

	//存储要采集的字段信息
	function save_tags($data,$id=0)
	{
		if($id)
		{
			$this->db->update_array($data,"collection_tags",array("id"=>$id));
			return true;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"collection_tags");
			return $insert_id;
		}
	}

	//存储格式化后的数据
	function save_format($data,$id=0)
	{
		if($id)
		{
			$this->db->update_array($data,"collection_format",array("id"=>$id));
			return true;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"collection_format");
			return $insert_id;
		}
	}

	//存储附件
	function save_files($data,$id=0)
	{
		if($id)
		{
			$this->db->update_array($data,"collection_files",array("id"=>$id));
			return true;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"collection_files");
			return $insert_id;
		}
	}

	function save_list($data,$id=0)
	{
		if($id)
		{
			$this->db->update_array($data,"collection_list",array("id"=>$id));
			return true;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"collection_list");
			return $insert_id;
		}
	}


	//删除记录
	function del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."collection WHERE id='".$id."'";
		$this->db->query($sql);
		//删除配置的字段
		$sql = "DELETE FROM ".$this->db->prefix."collection_tags WHERE cid='".$id."'";
		$this->db->query($sql);
		//删除采集的网址
		$rslist = $this->get_all_list_id($id);
		foreach(($rslist ? $rslist : array()) AS $key=>$value)
		{
			$this->del_list($value);
		}
		return true;
	}


	//删除某条网址
	function del_list($id)
	{
		if(!$id) return false;
		//删除附件
		$sql = "SELECT id FROM ".$this->db->prefix."collection_format WHERE lid IN(".$id.")";
		$rslist = $this->db->get_all($sql,"id");
		if($rslist)
		{
			$k_s = sys_id_string(array_keys($rslist));
			$sql = "DELETE FROM ".$this->db->prefix."collection_files WHERE lid IN(".$k_s.")";
			$this->db->query($sql);
		}
		$sql = "DELETE FROM ".$this->db->prefix."collection_list WHERE id IN(".$id.")";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."collection_format WHERE lid IN(".$id.")";
		$this->db->query($sql);
	}

	//删除某个标签
	function del_tags($id)
	{
		if(!$id) return false;
		$rs = $this->get_one_tags($id);
		if(!$rs || !$rs["cid"] || !$rs["identifier"]) return false;
		$tag = $rs["identifier"];
		$rslist = $this->get_all_list_id($rs["cid"]);
		if($rslist)
		{
			foreach($rslist AS $key=>$value)
			{
				//删除附件
				$sql = "SLECT id FROM ".$this->db->prefix."collection_format WHERE lid='".$value."' AND tag='".$tag."'";
				$tmplist = $this->db->get_all($sql);
				if(!$tmplist) $tmplist = array();
				foreach($tmplist AS $k=>$v)
				{
					$sql = "DELETE FROM ".$this->db->prefix."collection_files WHERE lid='".$v["id"]."'";
					$this->db->query($sql);
				}
				$sql = "DELETE FROM ".$this->db->prefix."collection_format WHERE lid='".$value."' AND tag='".$tag."'";
				$this->db->query($sql);
			}
		}
		$sql = "DELETE FROM ".$this->db->prefix."collection_tags WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//重新采集操作
	function reupdate_list($id)
	{
		if(!$id) return false;
		$sql = "UPDATE ".$this->db->prefix."collection_list SET status='0' WHERE id IN(".$id.")";
		$this->db->query($sql);
		//删除附件
		$sql = "SELECT id FROM ".$this->db->prefix."collection_format WHERE lid IN(".$id.")";
		$rslist = $this->db->get_all($sql,"id");
		if($rslist)
		{
			$k_s = sys_id_string(array_keys($rslist));
			$sql = "DELETE FROM ".$this->db->prefix."collection_files WHERE lid IN(".$k_s.")";
			$this->db->query($sql);
		}
		//删除数据
		$sql = "DELETE FROM ".$this->db->prefix."collection_format WHERE lid IN(".$id.")";
		$this->db->query($sql);
		return true;
	}

	//取消已发布标记操作
	function reupdate_post($id)
	{
		if(!$id) return false;
		$sql = "UPDATE ".$this->db->prefix."collection_list SET status='1' WHERE id IN(".$id.") AND status='2'";
		return $this->db->query($sql);
	}

	//设置为已发布，以防止重复采集
	function reupdate_post2($id)
	{
		if(!$id) return false;
		$sql = "UPDATE ".$this->db->prefix."collection_list SET status='2' WHERE id IN(".$id.")";
		return $this->db->query($sql);
	}


	function chk_url($url)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."collection_list WHERE url='".$url."'";
		return $this->db->get_one($sql);
	}

	function get_start_url($cid)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."collection_list WHERE cid='".$cid."' AND status='0' ORDER BY id ASC";
		return $this->db->get_one($sql);
	}

	function get_next_url($tid,$cid)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."collection_list WHERE cid='".$cid."' AND status='0' AND id>'".$tid."' ORDER BY id ASC";
		return $this->db->get_one($sql);
	}

	function get_this_url($tid)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."collection_list WHERE id='".$tid."' ORDER BY id ASC";
		return $this->db->get_one($sql);
	}

	function chk_content_format($lid,$tag)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."collection_format WHERE lid='".$lid."' AND tag='".$tag."'";
		return $this->db->get_one($sql);
	}

	function chk_file($lid,$tag,$srcurl)
	{
		if(!$lid || !$tag || !$srcurl)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."collection_files WHERE lid='".$lid."' AND tag='".$tag."' AND srcurl='".$srcurl."'";
		return $this->db->get_one($sql);
	}

	function get_id_nostatus($cid)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."collection_list WHERE status='1' AND cid IN(".$cid.") ORDER BY id ASC LIMIT 1";
		return $this->db->get_one($sql);
	}


	function get_content_list($lid)
	{
		$this->sub_idlist = array();
		$sql = "SELECT * FROM ".$this->db->prefix."collection_format WHERE lid='".$lid."'";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist) return false;
		$rslist = array();
		foreach($tmplist As $key=>$value)
		{
			$rslist[$value["tag"]] = $value["content"];
			$this->sub_idlist[$value["tag"]] = $value["id"];
		}
		return $rslist;
	}

	function get_sub_idlist()
	{
		return $this->sub_idlist;
	}

	//获取当前网址ID对应的模块信息、分类信息等
	function get_one_cate_module($cid)
	{
		$sql = "SELECT c.cateid,c.mid,cate.module_id FROM ".$this->db->prefix."collection c ";
		$sql.= " LEFT JOIN ".$this->db->prefix."cate cate ON(cate.id=c.cateid) ";
		$sql.= " LEFT JOIN ".$this->db->prefix."module m ON(m.id=c.mid) ";
		$sql.= " WHERE c.id='".$cid."'";
		$rs = $this->db->get_one($sql);
		if($rs["cateid"])
		{
			return array("cateid"=>$rs["cateid"],"module_id"=>$rs["module_id"]);
		}
		else
		{
			return array("cateid"=>"0","module_id"=>$rs["mid"]);
		}
	}

	function get_list_field()
	{
		return $this->db->list_fields($this->db->prefix."list");
	}

	function get_biz_field()
	{
		return $this->db->list_fields($this->db->prefix."list_biz");
	}
}
?>