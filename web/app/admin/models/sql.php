<?php
#=====================================================================
#	Filename: app/admin/models/sql.php
#	Note	: MySQL备份，优化等相关操作
#	Version : 3.0
#	Author  : qinggan
#	Update  : 2009-11-4
#=====================================================================
class sql_m extends Model
{
	var $sql_ext = "WHERE 1=1 ";
	var $psize = 20;
	function __construct()
	{
		parent::Model();
	}

	function sql_m()
	{
		$this->__construct();
	}

	function sql_prefix()
	{
		return $this->db->prefix;
	}

	function get_all()
	{
		$sql = "SHOW TABLE STATUS FROM ".$this->db->data;
		return $this->db->get_all($sql);
	}

	function optimize($table)
	{
		$sql = "OPTIMIZE TABLE ".$table;
		return $this->db->query($sql);
	}

	function repair($table)
	{
		$sql = "REPAIR TABLE ".$table;
		return $this->db->query($sql);
	}

	function show_create_table($table)
	{
		$sql = "SHOW CREATE TABLE ".$table;
		$this->db->set("rs_type","num");
		$rs = $this->db->get_one($sql);
		$rs = $rs[1];
		$this->db->set("rs_type","charet");
		return $rs;
	}

	function field_list($table)
	{
		$sql = "SHOW FIELDS FROM ".$table;
		return $this->db->get_all($sql);
	}

	function keys_list($table)
	{
		$sql = "SHOW KEYS FROM ".$table;
		return $this->db->get_all($sql);
	}

	//判断数据库是否包含
	function tbl_exists($tbl,$idlist)
	{
		return in_array($this->db->prefix.$tbl,$idlist) ? true : false;
	}

	function getsql($tbl,$offset=0,$psize="all")
	{
		$sql = "SELECT * FROM ".$tbl;
		if($psize != "all")
		{
			$sql .= " LIMIT ".$offset.",".$psize;
		}
		return $this->db->get_all($sql);
	}

	function escape_string($rs)
	{
		return $this->db->escape_string($rs);
	}

	function table_count($table)
	{
		$sql = "SELECT count(*) FROM ".$table;
		return $this->db->count($sql);
	}

	function query($sql)
	{
		return $this->db->query($sql);
	}

	function query_create($sql)
	{
		$sql_version = $this->db->get_version();
		$sql = preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).($sql_version > '4.1' ? " ENGINE=MyISAM DEFAULT CHARSET=utf8" : " TYPE=MYISAM");
		return $this->db->query($sql);
	}

	//修正SESSION表操作
	function recover_session()
	{
		$sql = "DROP TABLE IF EXISTS ".$this->db->prefix."session";
		$this->db->query($sql);
		$sql_version = $this->db->get_version();
		$sql = "CREATE TABLE IF NOT EXISTS ".$this->db->prefix."session (`id` varchar(32) NOT NULL COMMENT 'session_id',`data` text NOT NULL COMMENT 'session 内容',`lasttime` int(10) unsigned NOT NULL COMMENT '时间',PRIMARY KEY (`id`)) ";
		$sql .= ($sql_version > '4.1' ? " ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='SESSION操作'" : " TYPE=MYISAM");
		$this->db->query($sql);
		return true;
	}
}
?>