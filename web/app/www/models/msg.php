<?php
#=====================================================================
#	Filename: app/www/models/msg.php
#	Note	: 获取内容数据
#	Version : 3.0
#	Author  : qinggan
#	Update  : 2009-12-30
#=====================================================================
class msg_m extends Model
{
	function __construct()
	{
		parent::Model();
	}

	function msg_m()
	{
		$this->__construct();
	}

	//取得单独内容信息
	function get_c($id,$field)
	{
		if(!$id || !$field)
		{
			return false;
		}
		$sql = "SELECT val FROM ".$this->db->prefix."list_c WHERE id='".$id."' AND `field`='".$field."'";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		return $rs["val"];
	}

	function get_one($id,$status=true,$pageid=1)
	{
		$this->db->close_cache();
		$sql = " SELECT m.* ";
		$sql.= " FROM ".$this->db->prefix."list m ";
		$sql.= " WHERE m.id='".$id."'";
		if($status)
		{
			$sql.= " AND m.status='1' ";
		}
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			$this->db->open_cache();
			return false;
		}
		if($rs["thumb_id"])
		{
			$tmp_thumb = sys_format_list($rs["thumb_id"],"img");
			$rs["thumb"] = $tmp_thumb[0];
			unset($tmp_thumb);
		}
		//取得扩展字段信息，并对内容进行格式化
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
				$value["val"] = sys_format_content($value["val"],$rs,$pageid);
				$rs[$value["field"]] = $value["val"];
			}
		}
		$this->db->open_cache();
		return $rs;
	}

	function get_one_fromtype($typeid,$langid="zh",$pageid=1)
	{
		$sql = "SELECT id,langid FROM ".$this->db->prefix."list WHERE identifier='".$typeid."'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist)
		{
			return false;
		}
		$id = 0;
		foreach($rslist AS $key=>$value)
		{
			if($value["langid"] == $langid)
			{
				$id = $value["id"];
				break;
			}
		}
		if(!$id)
		{
			$id = $rslist[0]["id"];
		}
		return $this->get_one($id,true,$pageid);
	}

	//更新点击率
	function update_hits($id)
	{
		$sql = "UPDATE ".$this->db->prefix."list SET hits=hits+1 WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//获取主题是否支持点评回复操作
	function chk_reply_hits($id)
	{
		$this->db->close_cache();
		$sql = "SELECT l.title,l.hits,l.good_hits,l.bad_hits,m.if_cate FROM ".$this->db->prefix."list l JOIN ".$this->db->prefix."module m ON(l.module_id=m.id) LEFT JOIN ".$this->db->prefix."cate c ON(l.cate_id=c.id) WHERE l.id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		$reply = $hits = true;
		$array = array();
		$array["title"] = $rs["title"];
		$array["hits"] = $rs["hits"];
		$array["good"] = $rs["good_hits"];
		$array["bad"] = $rs["bad_hits"];
		$array["ifhits"] = $hits;
		$array["ifreply"] = $reply;
		unset($rs);
		$this->db->open_cache();
		return $array;
	}

	//更新好评或是差评
	function update_digg($id,$type="good")
	{
		$sql = "UPDATE ".$this->db->prefix."list SET ";
		if($type == "bad")
		{
			$sql.= " bad_hits=bad_hits+1 ";
		}
		elseif($type == "good")
		{
			$sql.= " good_hits=good_hits+1 ";
		}
		else
		{
			$sql.= " hits=hits+1 ";
		}
		$sql .= " WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	//获取评论数量
	function get_count_reply($id)
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."reply WHERE tid='".$id."' AND status='1'";
		return $this->db->count($sql);
	}

	//取得主题信息，根据模块ID，扩展字段及扩展内容
	function get_one_from_mid_field_val($mid,$var,$val)
	{
		if(!$mid || !$var || !$val)
		{
			return false;
		}
		$sql = "SELECT e.id FROM ".$this->db->prefix."list_ext e JOIN ".$this->db->prefix."list l ON(e.id=l.id) WHERE l.module_id='".$mid."' AND e.field='".$var."' AND e.val='".$val."'";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		return $this->get_one($rs["id"]);
	}

	function update_replay_date($id,$time=0)
	{
		$sql = "UPDATE ".$this->db->prefix."list SET replydate='".$time."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//根据主题ID，获取相关内容
	function list_from_id($id)
	{
		//
	}
}
?>