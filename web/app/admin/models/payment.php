<?php
#=====================================================================
#	Filename: app/admin/models/payment.php
#	Note	: 付款模块
#	Version : 3.0
#	Author  : qinggan
#	Update  : 2009-12-30
#=====================================================================
class payment_m extends Model
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

	function payment_m()
	{
		$this->__construct();
	}
	//通过ID取得数据（此操作用于后台）
	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."payment WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function get_list($condition="")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."payment WHERE langid='".$this->langid."'";
		if($condition)
		{
			$sql .= " AND ".$condition." ";
		}
		$sql.= " ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql);
	}

	function chk_identifier($val)
	{
		return $this->get_one_sign($val);
	}

	//通过标识串取得调用的配置数据
	function get_one_sign($val)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."payment WHERE code='".$val."' AND langid='".$this->langid."'";
		return $this->db->get_one($sql);
	}

	//检测标识串是否存在
	function chksign($val,$payid)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."payment_val WHERE code='".$val."' AND payid='".$payid."'";
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
			$this->db->update_array($data,"payment",array("id"=>$id));
			return $id;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"payment");
			return $insert_id;
		}
	}

	function set_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."payment SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	function del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."payment WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."payment_val WHERE payid='".$id."'";
		$this->db->query($sql);
		return true;
	}

	function fields($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."payment_val WHERE payid='".$id."' ORDER BY id DESC";
		return $this->db->get_all($sql);
	}

	function fields_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."payment_val WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function save_fields($data,$id=0)
	{
		if($id)
		{
			$this->db->update_array($data,"payment_val",array("id"=>$id));
			return $id;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"payment_val");
			return $insert_id;
		}
	}

	function fields_del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."payment_val WHERE id='".$id."'";
		return $this->db->query($sql);
	}

}
?>