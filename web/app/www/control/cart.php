<?php
/***********************************************************
	Filename: app/www/control/cart.php
	Note	: 购物车信息
	Version : 3.0
	Author  : qinggan
	Update  : 2010-05-07
***********************************************************/
class cart_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("cart");
		$this->load_model("msg");
		$this->load_model("user");
	}

	function cart_m()
	{
		$this->__construct();
	}

	function auto_cartid()
	{
		$this->db->close_cache();//禁止缓存
		$this->cart_m->sessid($this->session_lib->sessid);
	}

	//购物车页
	function index_f()
	{
		$this->auto_cartid();
		//判断是否有启用图片
		$rslist = $this->cart_m->get_all($this->sys_config["cart_thumb"]);//取得购物车中的产品
		$this->tpl->assign("rslist",$rslist);
		//计算产品总价
		$total_price = 0;
		if($rslist)
		{
			foreach($rslist AS $key=>$value)
			{
				$total_price += sys_format_price($value["price"],$value["price_currency"],true) * $value["amount"];
			}
		}
		$this->tpl->assign("total_price",$total_price);
		$this->tpl->assign("sitetitle",$this->lang["cart"]);
		$leader[0] = array("title"=>$this->lang["cart"]);
		$this->tpl->assign("leader",$leader);
		//判断是否有会员
		$user_rs = ($_SESSION["user_id"] && $_SESSION["user_rs"]) ? $_SESSION["user_rs"] : array();
		$this->tpl->assign("user_rs",$user_rs);
		$this->tpl->display("cart.".$this->tpl->ext);
	}

	function ajax_add_f()
	{
		$this->auto_cartid();
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			sys_html2js($this->lang["cart_error_not_id"]);
		}
		$rs = $this->msg_m->get_one($id);
		if(!$rs)
		{
			sys_html2js($this->lang["cart_error_not_rs"]);
		}
		$this->cart_m->add_array($rs,1,$id);
		sys_html2js('ok');
	}

	function ajax_del_f()
	{
		$this->auto_cartid();
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			sys_html2js($this->lang["cart_error_not_id"]);
		}
		$this->cart_m->del($id);
		sys_html2js('ok');
	}

	function ajax_update_f()
	{
		$this->auto_cartid();
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			sys_html2js($this->lang["cart_error_not_id"]);
		}
		$amount = $this->trans_lib->int("amount");
		$this->cart_m->update($id,$amount);
		sys_html2js('ok');
	}
}
?>