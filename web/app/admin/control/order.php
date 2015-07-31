<?php
/***********************************************************
	Filename: app/admin/order.php
	Note	: 订单管理
	Version : 3.0
	Author  : qinggan
	Update  : 2009-12-23
***********************************************************/
class order_c extends Control
{
	function __construct()
	{
		parent::Control();
		$this->load_model("order");
	}

	//兼容PHP4的写法
	function order_c()
	{
		$this->__construct();
	}

	function index_f()
	{
		sys_popedom("order:list","tpl");
		$pageid = $this->trans_lib->int(SYS_PAGEID);
		$offset = $pageid>0 ? ($pageid-1)*SYS_PSIZE : 0;
		$condition = " 1=1 ";
		$startdate = $this->trans_lib->safe("startdate");
		$page_url = $this->url("order");
		if($startdate)
		{
			$this->tpl->assign("startdate",$startdate);
			$condition .= " AND o.postdate>='".strtotime($startdate)."'";
			$page_url .= "startdate=".rawurlencode($startdate)."&";
		}
		$enddate = $this->trans_lib->safe("enddate");
		if($enddate)
		{
			$this->tpl->assign("enddate",$enddate);
			$condition .= " AND o.postdate<='".strtotime($enddate)."'";
			$page_url .= "enddate=".rawurlencode($enddate)."&";
		}
		//判断是否已付款
		$ifpay = $this->trans_lib->int("ifpay");
		if($ifpay)
		{
			$this->tpl->assign("ifpay",$ifpay);
			$condition .= " AND o.pay_status='".($ifpay == 1 ? 1 : 0)."'";
			$page_url .= "ifpay=".$ifpay."&";
		}
		$status = $this->trans_lib->int("status");
		if($status)
		{
			$this->tpl->assign("status",$status);
			$condition .= " AND o.status='".($status == 1 ? 1 : 0)."'";
			$page_url .= "status=".$status."&";
		}
		$total = $this->order_m->get_count($condition);
		$rslist = $this->order_m->get_list($offset,$condition);
		$this->tpl->assign("total",$total);
		$this->tpl->assign("rslist",$rslist);
		$pagelist = $this->page_lib->page($page_url,$total);
		$this->tpl->assign("pagelist",$pagelist);
		$this->tpl->display("order/list.html");
	}

	function status_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error: 没有指定ID");
		}
		sys_popedom("order:check","ajax");
		$rs = $this->order_m->get_one($id);
		$status = $rs["status"] ? "0" : "1";
		$this->order_m->status($id,$status);
		exit("ok");
	}

	function ajax_status_pl_f()
	{
		$id = $this->trans_lib->safe("id");
		if(!$id)
		{
			exit("error: 没有指定ID");
		}
		$idlist = sys_id_list($id);
		if(!$idlist)
		{
			exit("error: 没有指定ID");
		}
		$status = $this->trans_lib->int("status");
		sys_popedom("order:check","ajax");
		foreach($idlist AS $key=>$value)
		{
			$this->order_m->status($value,$status);
		}
		exit("ok");
	}

	function ajax_del_f()
	{
		$id = $this->trans_lib->safe("id");
		if(!$id)
		{
			exit("error: 没有指定ID");
		}
		$idlist = sys_id_list($id);
		if(!$idlist)
		{
			exit("error: 没有指定ID");
		}
		sys_popedom("order:delete","ajax");
		foreach($idlist AS $key=>$value)
		{
			$this->order_m->del($value);
		}
		exit("ok");
	}

	function del_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error: 没有指定ID");
		}
		sys_popedom("order:delete","ajax");
		$this->order_m->del($id);
		exit("ok");
	}

	function show_f()
	{
		sys_popedom("order:list","tpl");
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			error("错误，没有指这ID");
		}
		sys_popedom("order:list","tpl");
		$rs = $this->order_m->get_one($id);
		$this->tpl->assign("rs",$rs);
		$rslist = $this->order_m->get_products($id);
		$this->tpl->assign("rslist",$rslist);
		if($rs["pay_type"])
		{
			$this->load_model("payment");
			$pay_rs = $this->payment_m->get_one($rs["pay_type"]);
			$this->tpl->assign("pay_rs",$pay_rs);
		}
		if(file_exists(ROOT_DATA."system_".$_SESSION["sys_lang_id"].".php"))
		{
			include(ROOT_DATA."system_".$_SESSION["sys_lang_id"].".php");
			$indexphp = $_sys["indexphp"] ? $_sys["indexphp"] : "index.php";
			$this->tpl->assign("indexphp",$indexphp);
		}
		//取得地址列表
		$address = $this->order_m->get_address($id);
		$this->tpl->assign("address",$address);
		$this->tpl->display("order/show.html");
	}

	function set_f()
	{
		sys_popedom("order:modify","tpl");
		$this->load_model("payment");
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			error("操作错误，没有指定ID",$this->url("order"));
		}
		$this->tpl->assign("id",$id);
		$rs = $this->order_m->get_one($id);
		$this->tpl->assign("rs",$rs);
		if($rs["pay_type"])
		{
			$pay_rs = $this->payment_m->get_one($rs["pay_type"]);
			$this->tpl->assign("pay_rs",$pay_rs);
		}
		$rslist = $this->order_m->get_products($id);
		$this->tpl->assign("rslist",$rslist);
		//取得地址列表
		$address = $this->order_m->get_address($id);
		$this->tpl->assign("address",$address);
		//
		$pay_rslist = $this->payment_m->get_list();//取得付款类型
		if($pay_rslist)
		{
			$this->tpl->assign("pay_rslist",$pay_rslist);
		}
		$this->tpl->display("order/set.html");
	}

	function setok_f()
	{
		sys_popedom("order:modify","tpl");
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			error("操作错误，没有指定ID",$this->url("order"));
		}
		$old_rs = $this->order_m->get_one($id);
		//
		$rs = array();
		$rs["pass"] = $this->trans_lib->safe("pass");
		$rs["fullname"] = $this->trans_lib->safe("fullname");
		$rs["email"] = $this->trans_lib->safe("email");
		$rs["note"] = $this->trans_lib->safe("note");
		$rs["pay_status"] = $this->trans_lib->int("pay_status");
		$rs["pay_type"] = $this->trans_lib->int("pay_type");
		$rs["pay_code"] = $this->trans_lib->safe("pay_code");
		$rs["pay_price"] = $this->trans_lib->safe("pay_price");
		$rs["pay_currency"] = $this->trans_lib->safe("pay_currency");
		$pay_date = $this->trans_lib->safe("pay_date");
		if($pay_date)
		{
			$rs["pay_date"] = strtotime($pay_date);
		}
		$this->order_m->update($rs,$id);
		unset($rs);
		//更新产品信息
		$rslist = $this->order_m->get_products($id);
		if($rslist)
		{
			foreach($rslist AS $key=>$value)
			{
				$pro = array();
				$pro["title"] = $this->trans_lib->safe("protitle_".$value["id"]);
				$pro["price"] = $this->trans_lib->safe("proprice_".$value["id"]);
				$pro["amount"] = $this->trans_lib->int("proamount_".$value["id"]);
				//echo "<pre>".print_r($pro,true)."</pre>";
				$this->order_m->pro_save($pro,$value["id"]);
			}
		}
		//exit;
		//判断是否有添加
		$prolist_array = $this->trans_lib->safe("product_list");
		$protitle = $this->trans_lib->safe("protitle");
		$proprice = $this->trans_lib->safe("proprice");
		$proamount = $this->trans_lib->safe("proamount");
		foreach($prolist_array AS $key=>$value)
		{
			if($protitle[$key])
			{
				$pro = array();
				$pro["orderid"] = $id;
				$pro["proid"] = 0;
				$pro["title"] = $protitle[$key];
				$pro["price"] = $proprice[$key];
				$pro["price_currency"] = $old_rs["price_currency"];
				$pro["amount"] = $proamount[$key];
				$this->order_m->pro_save($pro);
			}
		}
		//更新地址信息
		$address = array();
		$address["order_id"] = $id;
		$address["address_type"] = "shipping";
		$address["fullname"] = $this->trans_lib->safe("s_fullname");
		$address["tel"] = $this->trans_lib->safe("s_tel");
		$address["email"] = $this->trans_lib->safe("s_email");
		$address["country"] = $this->trans_lib->safe("s_country");
		$address["address"] = $this->trans_lib->safe("s_address");
		$address["zipcode"] = $this->trans_lib->safe("s_zipcode");
		$address["note"] = $this->trans_lib->safe("s_note");
		$this->order_m->save_address($address);

		//更新地址信息
		$address = $this->order_m->get_address($id);
		if($address && $address["billing"])
		{
			$address = array();
			$address["order_id"] = $id;
			$address["address_type"] = "billing";
			$address["fullname"] = $this->trans_lib->safe("b_fullname");
			$address["tel"] = $this->trans_lib->safe("b_tel");
			$address["email"] = $this->trans_lib->safe("b_email");
			$address["country"] = $this->trans_lib->safe("b_country");
			$address["address"] = $this->trans_lib->safe("b_address");
			$address["zipcode"] = $this->trans_lib->safe("b_zipcode");
			$address["note"] = $this->trans_lib->safe("b_note");
			$this->order_m->save_address($address);
		}
		$this->order_m->update_totoal_price($id);
		error("订单信息更新成功！",$this->url("order"));
	}

	function pro_del_f()
	{
		$id = $this->trans_lib->int("id");
		if(!$id)
		{
			exit("error: 操作错误，没有指定ID");
		}
		$rs = $this->order_m->get_one_products($id);
		$orderid = $rs["orderid"];
		$this->order_m->pro_del($id);
		$this->order_m->update_totoal_price($orderid);
		exit("ok");
	}
}
?>