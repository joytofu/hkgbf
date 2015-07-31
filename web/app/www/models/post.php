<?php
/***********************************************************
	Filename: app/www/models/post.php
	Note	: 内容模块管理器
	Version : 3.0
	Author  : qinggan
	Update  : 2011-04-06
***********************************************************/
class post_m extends Model
{
	var $sql_ext;
	var $condition = " WHERE 1=1 ";
	var $psize = 20;
	function __construct()
	{
		parent::Model();
		$this->psize = defined("SYS_PSIZE") ? SYS_PSIZE : 20;
	}

	function post_m()
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

	//查询文章数
	function get_list($pageid=0,$iscate=false,$ifbiz=false,$ifthumb=false)
	{
		$this->db->close_cache();
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
		$sql = "SELECT ".$sql_fields." ".$this->sql_ext." ".$this->condition." ORDER BY m.post_date DESC,m.id DESC ";
		$sql.= " LIMIT ".$offset.",".$this->psize;
		$rslist = $this->db->get_all($sql);
		$this->db->open_cache();
		return $rslist;
	}

	//查询数量
	function get_count()
	{
		$this->db->close_cache();
		$this->sql_ext = " FROM ".$this->db->prefix."list m ";
		$sql = "SELECT count(m.id) total ".$this->sql_ext." ".$this->condition;
		$rs = $this->db->count($sql);
		$this->db->open_cache();
		return $rs;
	}

	//取得值
	function get_one($id)
	{
		$this->db->close_cache();
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
		$this->db->open_cache();
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

	function set_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."list SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}
	//删除数据
	function del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."list WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."list_ext WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."list_c WHERE id='".$id."'";
		$this->db->query($sql);
		//删除回复信息
		$sql = "DELETE FROM ".$this->db->prefix."reply WHERE tid='".$id."'";
		$this->db->query($sql);
		return true;
	}

	//取得分类下的数目
	function get_count_from_cate($idstring)
	{
		$this->db->close_cache();
		$sql = "SELECT count(l.id) FROM ".$this->db->prefix."list l WHERE l.status='1' AND l.hidden='0' AND l.cate_id IN(".$idstring.") ";
		$this->db->open_cache();
		return $this->db->count($sql);
	}

	function set_taxis($id,$taxis=0)
	{
		$sql = "UPDATE ".$this->db->prefix."list SET taxis='".$taxis."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}
}
?>