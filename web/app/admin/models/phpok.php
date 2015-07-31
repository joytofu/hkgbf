<?php
#=====================================================================
#	Filename: app/admin/models/phpok.php
#	Note	: 数据调用模块层
#	Version : 3.0
#	Author  : qinggan
#	Update  : 2009-12-30
#=====================================================================
class phpok_m extends Model
{
	var $langid = "zh";
	var $psize = 20;
	function __construct()
	{
		parent::Model();
		$this->psize = defined("SYS_PSIZE") ? SYS_PSIZE : 20;
	}

	function langid($langid="zh")
	{
		$this->langid = $langid;
	}

	function phpok_m()
	{
		$this->__construct();
	}

	function psize($psize=20)
	{
		$this->psize = $psize;
	}
	//通过ID取得数据（此操作用于后台）
	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."phpok WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function get_list($pageid=0,$condition="")
	{
		$offset = $pageid>0 ? ($pageid-1)*$this->psize : 0;
		//获取调用数据的列表
		$sql = "SELECT id,title,note,identifier,status FROM ".$this->db->prefix."phpok WHERE langid='".$this->langid."' ";
		if($condition)
		{
			$sql .= " AND ".$condition." ";
		}
		$sql.= " ORDER BY id DESC LIMIT ".$offset.",".$this->psize;
		return $this->db->get_all($sql);
	}

	function get_count($condition="")
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."phpok WHERE langid='".$this->langid."' ";
		if($condition)
		{
			$sql .= " AND ".$condition." ";
		}
		return $this->db->count($sql);
	}

	function chk_identifier($val)
	{
		return $this->get_one_sign($val);
	}

	//通过标识串取得调用的配置数据
	function get_one_sign($val)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."phpok WHERE identifier='".$val."' AND langid='".$this->langid."'";
		return $this->db->get_one($sql);
	}

	//检测标识串是否存在
	function chksign($val)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."phpok WHERE identifier='".$val."' AND langid='".$this->langid."'";
		$rs = $this->db->get_one($sql);
		if($rs)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function save($data,$id=0)
	{
		if($id)
		{
			$this->db->update_array($data,"phpok",array("id"=>$id));
			return true;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"phpok");
			return $insert_id;
		}
	}

	function set_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."phpok SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	function del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."phpok WHERE id='".$id."'";
		return $this->db->query($sql);
	}
}
?>