<?php
#=====================================================================
#	Filename: app/www/models/user.php
#	Note	: 会员数据，注意关闭掉缓存功能
#	Version : 3.0
#	Author  : qinggan
#	Update  : 2009-12-30
#=====================================================================
class user_m extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function user_m()
	{
		$this->__construct();
	}

	//通过账号登录验证
	function user_from_name($username)
	{
		$this->db->close_cache();
		$sql = "SELECT id FROM ".$this->db->prefix."user WHERE name='".$username."'";
		$tmprs = $this->db->get_one($sql);
		if(!$tmprs) return false;
		$rs = $this->user_from_id($tmprs["id"]);
		$this->db->open_cache();
		return $rs;
	}

	//通过ID登录验证
	function user_from_id($id)
	{
		$this->db->close_cache();
		$sql = "SELECT u.*,f.thumb picture FROM ".$this->db->prefix."user u LEFT JOIN ".$this->db->prefix."upfiles f ON(u.thumb_id=f.id) WHERE u.id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			$this->db->open_cache();
			return false;
		}
		//取得扩展内容
		//取得扩展字段信息
		$sql = "SELECT * FROM ".$this->db->prefix."user_ext WHERE id='".$id."'";
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

	//通过邮箱登录验证
	function user_from_email($email)
	{
		$this->db->close_cache();
		$sql = "SELECT id FROM ".$this->db->prefix."user WHERE email='".$email."'";
		$tmprs = $this->db->get_one($sql);
		if(!$tmprs) return false;
		$rs = $this->user_from_id($tmprs["id"]);
		$this->db->open_cache();
		return $rs;
	}

	//存储会员更新的信息
	function update_info($data,$uid)
	{
		if(!$data || !is_array($data) || count($data)<1)
		{
			return false;
		}
		if(!$uid)
		{
			return false;
		}
		return $this->db->update_array($data,"user",array("id"=>$uid));
	}

	function update_pass($newpass,$uid,$mima)
	{
		if(!$newpass || !$uid)
		{
			return false;
		}
		$sql = "UPDATE ".$this->db->prefix."user SET pass='".$newpass."',mima='".$mima."' WHERE id='".$uid."'";
		return $this->db->query($sql);
	}

	//存储会员信息
	function save($data)
	{
		if(!$data || !is_array($data))
		{
			return false;
		}
		return $this->db->insert_array($data,"user");
	}

	function create_chkcode($id,$time=0)
	{
		$rand = md5(time().$id.rand(10,99));
		$sql = "UPDATE ".$this->db->prefix."user SET codetime='".$time."',chkcode='".$rand."' WHERE id='".$id."'";
		$this->db->query($sql);
		return $rand;
	}

	//显示某组下的会员列表
	function user_list($groupid,$offset=0,$psize=30)
	{
		if(!$groupid) return false;
		$sql = "SELECT u.*,f.thumb picture FROM ".$this->db->prefix."user u LEFT JOIN ".$this->db->prefix."upfiles f ON(u.thumb_id=f.id) ";
		$sql.= " WHERE u.status='1' AND u.ifshow IN(1,0) AND u.groupid='".$groupid."' ORDER BY u.id DESC LIMIT ".$offset.",".$psize;
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist)
		{
			return false;
		}
		$idlist = implode(",",array_keys($rslist));
		$sql = "SELECT * FROM ".$this->db->prefix."user_ext WHERE id IN(".$idlist.")";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist) $tmplist = array();
		foreach($tmplist AS $key=>$value)
		{
			$rslist[$value["id"]][$value["field"]] = $value["val"];
		}
		return $rslist;
	}

	function user_total($groupid)
	{
		if(!$groupid) return false;
		$sql = "SELECT count(id) FROM ".$this->db->prefix."user WHERE status='1' AND ifshow IN(1,0) AND groupid='".$groupid."'";
		return $this->db->count($sql);
	}
}
?>