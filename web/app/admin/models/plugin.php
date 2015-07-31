<?php
#=====================================================================
#	Filename: app/admin/models/plugin.php
#	Note	: 插件管理中心
#	Version : 3.0
#	Author  : qinggan
#	Update  : 2009-12-30
#=====================================================================
class plugin_m extends Model
{
	var $langid = "zh";
	function __construct()
	{
		parent::Model();
	}

	function langid($langid="zh")
	{
		$this->langid = $langid;
	}

	function plugin_m()
	{
		$this->__construct();
	}
	//通过ID取得数据（此操作用于后台）
	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."plugins WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function get_list($pageid=0,$psize=20)
	{
		$offset = $pageid>0 ? ($pageid-1)*$psize : 0;
		//获取调用数据的列表
		$sql = "SELECT * FROM ".$this->db->prefix."plugins WHERE langid IN('".$this->langid."','-') ";
		$sql.= " ORDER BY taxis ASC,id DESC LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
	}

	function get_all($fields = "*")
	{
		$sql = "SELECT ".$fields." FROM ".$this->db->prefix."plugins ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql);
	}

	function get_count()
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."plugins WHERE langid IN('".$this->langid."','-') ";
		return $this->db->count($sql);
	}

	//通过标识串取得调用的配置数据
	function get_one_sign($val)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."plugins WHERE identifier='".$val."'";
		return $this->db->get_one($sql);
	}

	//检测标识串是否存在
	function chksign($val)
	{
		$rs = $this->get_one_sign($val);
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
			$this->db->update_array($data,"plugins",array("id"=>$id));
			return true;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"plugins");
			return $insert_id;
		}
	}

	function set_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."plugins SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	function del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."plugins WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	function sql($sql)
	{
		if(!$sql)
		{
			return false;
		}
		$sql = str_replace("\r","\n",$sql);
		$ret = array();
		$num = 0;
		foreach(explode(";\n", trim($sql)) as $query)
		{
			$queries = explode("\n", trim($query));
			foreach($queries as $query)
			{
				$ret[$num] .= $query[0] == '#' || $query[0].$query[1] == '--' ? '' : $query;
			}
			$num++;
		}
		unset($sql);

		foreach($ret as $query)
		{
			$query = trim($query);
			if($query)
			{
				$query = str_replace("qinggan_",$this->db->prefix,$query);
				$this->db->query($query);
			}
		}
		return true;
	}
}
?>