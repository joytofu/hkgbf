//订单相关JS操作
var order_url = base_file + "?"+base_ctrl+"=order&"+base_func+"=";

function show_user(id)
{
	var url = base_file + "?"+base_ctrl+"=user&"+base_func+"=view&id="+id;
	Layer.init(url,550,400);
}

function to_show(id)
{
	var url = order_url + "show&id="+id;
	Layer.init(url,550,400);
}

function del_pl()
{
	var id = join_checkbox();
	if(!id)
	{
		alert("请选择要删除的主题");
		return false;
	}
	var qc = confirm("确定要删除此信息吗？删除后是不能恢复的");
	if(qc == "0")
	{
		return false;
	}
	var url = base_url + base_func + "=ajax_del&id="+EncodeUtf8(id);
	var msg = get_ajax(url);
	if(!msg) msg = "error: 操作非法";
	if(msg == "ok")
	{
		direct(window.location.href);
	}
	else
	{
		alert(msg);
		return false;
	}
}

function to_del(id)
{
	var qc = confirm("确定要删除该订单吗？删除后是不能恢复！");
	if(qc == "0")
	{
		return false;
	}
	var url = order_url + "del&id="+id;
	var msg = get_ajax(url);
	if(msg == "ok")
	{
		direct(window.location.href);
		return true;
	}
	else
	{
		if(!msg) msg = "error: 操作错误";
		alert(msg);
		return false;
	}
}

function to_status(id)
{
	var url = order_url + "status&id="+id;
	var msg = get_ajax(url);
	if(msg == "ok")
	{
		direct(window.location.href);
	}
	else
	{
		if(!msg) msg = "error: 操作错误";
		alert(msg);
		return false;
	}
}

function update_pl(st)
{
	var id = join_checkbox();
	if(!id)
	{
		alert("请选择要操作的主题");
		return false;
	}
	var url = base_url + base_func + "=ajax_status_pl&status="+st+"&id="+id;
	var msg = get_ajax(url);
	if(msg == "ok")
	{
		direct(window.location.href);
	}
	else
	{
		if(!msg) msg = "error: 操作非法";
		alert(msg);
		return false;
	}
}


function pro_del(id)
{
	var qt = confirm("确定要删除此订单产品吗？");
	if(qt == "0")
	{
		return false;
	}
	var url = base_url + base_func + "=pro_del&id="+id;
	var msg = get_ajax(url);
	if(msg == "ok")
	{
		direct(window.location.href);
	}
	else
	{
		if(!msg) msg = "Error: 删除失败！";
		alert(msg);
		return false;
	}
}