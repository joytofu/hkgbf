<?php
/***********************************************************
	Filename: app/admin/models_mysql/upfile.php
	Note	: 附件管理模块
	Version : 3.0
	Author  : qinggan
	Update  : 2009-11-24
***********************************************************/
class upfile_m extends Model
{
	var $condition = " WHERE 1=1 ";
	var $psize = 20;
	function __construct()
	{
		parent::Model();
	}

	function upfile_m()
	{
		$this->__construct();
	}

	function set_psize($psize=20)
	{
		$this->psize = $psize;
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
	function get_list($pageid=0)
	{
		$offset = $pageid>0 ? ($pageid-1)*$this->psize : 0;
		$sql = "SELECT *  FROM ".$this->db->prefix."upfiles ".$this->condition." ORDER BY postdate DESC,id DESC LIMIT ".$offset.",".$this->psize;
		$rslist = $this->db->get_all($sql);
		return $rslist;
	}

	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."upfiles WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function get_one_c($condition="")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."upfiles ";
		if($condition)
		{
			$sql.= " WHERE ".$condition;
		}
		$sql.= " ORDER BY id ASC";
		return $this->db->get_one($sql);
	}

	//预览扩展图片信息
	function get_one_gd($id)
	{
		$sql = "SELECT u.*,gd.picsubject FROM ".$this->db->prefix."upfiles_gd u LEFT JOIN ".$this->db->prefix."gd gd ON(u.gdtype=gd.pictype) WHERE pid='".$id."'";
		return $this->db->get_all($sql);
	}

	//查询数量
	function get_count()
	{
		$sql = "SELECT count(id) total FROM ".$this->db->prefix."upfiles  ".$this->condition;
		$rs = $this->db->count($sql);
		return $rs;
	}

	//save
	function save($data,$id=0)
	{
		if($id)
		{
			$this->db->update_array($data,"upfiles",array("id"=>$id));
			return true;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"upfiles");
			return $insert_id;
		}
	}

	function save_gd($data,$id=0)
	{
		return $this->db->insert_array($data,"upfiles_gd","replace");
	}


	//通过ID串获取图片信息，这里获取的带有ID的缩略图
	function piclist($idstring="")
	{
		if(!$idstring)
		{
			return false;
		}
		$sql = "SELECT id,thumb url FROM ".$this->db->prefix."upfiles WHERE id IN(".$idstring.") ORDER BY substring_index('".$idstring."',id,1)";
		return $this->db->get_all($sql);
	}

	//读取图片的内容信息，这里获取的是图片的直接地址信息
	function pic_list($idstring,$gdtype="")
	{
		if(!$idstring)
		{
			return false;
		}
		if($gdtype)
		{
			$sql = "SELECT filename url FROM ".$this->db->prefix."upfiles_gd WHERE pid IN(".$idstring.") AND gdtype='".$gdtype."' ORDER BY substring_index('".$idstring."',pid,1)";
			$rslist = $this->db->get_all($sql);
			if(!$rslist)
			{
				$sql = "SELECT filename url FROM ".$this->db->prefix."upfiles WHERE id IN(".$idstring.") ORDER BY substring_index('".$idstring."',id,1)";
				return $this->db->get_all($sql);
			}
			else
			{
				return $rslist;
			}
		}
		else
		{
			$sql = "SELECT filename url FROM ".$this->db->prefix."upfiles WHERE id IN(".$idstring.") ORDER BY substring_index('".$idstring."',id,1)";
			return $this->db->get_all($sql);
		}
	}

	//删除图片
	function del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."upfiles WHERE id IN(".$id.")";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."upfiles_gd WHERE pid IN(".$id.")";
		$this->db->query($sql);
		return true;
	}

	function get_filelist($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."upfiles WHERE id IN(".$id.")";
		$rslist = $this->db->get_all($sql);
		if(!$rslist)
		{
			return false;
		}
		$array = array();
		foreach($rslist AS $key=>$value)
		{
			$array[] = $value["filename"];
			$array[] = $value["thumb"];
		}
		$sql = "SELECT * FROM ".$this->db->prefix."upfiles_gd WHERE pid IN(".$id.")";
		$tmplist = $this->db->get_all($sql);
		if($tmplist)
		{
			foreach($tmplist AS $key=>$value)
			{
				$array[] = $value["filename"];
			}
		}
		return $array;
	}
}
?>