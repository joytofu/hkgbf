//分类JS
function tab_set(v)
{
	getid("_tab_main").className = "out";
	getid("_tab_attr").className = "out";
	getid("_tab_pic").className = "out";
	getid("_tab_ext").className = "out";
	getid("_msg_main").style.display = "none";
	getid("_msg_attr").style.display = "none";
	getid("_msg_pic").style.display = "none";
	getid("_msg_ext").style.display = "none";
	getid("_tab_"+v).className = "over";
	getid("_msg_"+v).style.display = "";
}

function cate_del(id)
{
	if(!id)
	{
		alert("操作非法");
		return false;
	}
	var q = confirm("确定要删除此信息吗？删除后是不能恢复的");
	if(q != 0)
	{
		var url = base_url + base_func + "=ajax_del&id="+id;
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
}

//更改权限状态
function cate_status(id,t)
{
	if(!id)
	{
		alert("操作非法");
		return false;
	}
	var url = base_url + base_func + "=ajax_status&id="+id;
	var msg = get_ajax(url);
	if(msg == "ok")
	{
		var n_t = t == 1 ? 0 : 1;
		$("#status_"+id+" > a").attr("class","status"+n_t);
		$("#status_"+id+" > a").attr("href","javascript:cate_status("+id+","+n_t+");void(0)");
		return true;
	}
	else
	{
		if(!msg) msg = "error: 操作非法";
		alert(msg);
		return false;
	}
}


function to_pingyin()
{
	var t = getid("cate_name").value;
	if(!t)
	{
		alert("分类名称为空！");
		return false;
	}
	var url = base_url + base_func + "=to_pinyin&title="+encode_utf8(t);
	var msg = get_ajax(url);
	if(msg && msg != "false")
	{
		getid("identifier").value = msg;
		return true;
	}
	else
	{
		if(!msg) msg = "Error:获取失败！";
		if(msg == "false") msg = "Error：分类名称为空！";
		alert(msg);
		return false;
	}
}

function to_submit(id)
{
	var cate_name = getid("cate_name").value;
	if(!cate_name)
	{
		alert("分类名称不允许为空");
		tab_set("main");
		getid("cate_name").focus();
		return false;
	}
	var identifier = getid("identifier").value;
	if(!identifier)
	{
		alert("标识串不允许为空");
		tab_set("main");
		getid("identifier").focus();
		return false;
	}
	var url = get_url("cate,chk")+"sign="+identifier+"&id="+id;
	var msg = get_ajax(url);
	if(!msg) msg = "error: 操作非法";
	if(msg != "ok")
	{
		alert(msg);
		tab_set("main");
		getid("identifier").focus();
		return false;
	}
	getid("_phpok_submit").disabled = true;
}

function update_psize(id,val)
{
	var url = get_url("cate,ajax_psize")+"id="+id+"&val="+val;
	get_ajax(url,cate_update_ajax_ok);
}

function update_taxis(id,val)
{
	var url = get_url("cate,ajax_taxis")+"id="+id+"&val="+val;
	get_ajax(url,cate_update_ajax_ok);
}

function cate_update_ajax_ok(msg)
{
	if(msg == "ok")
	{
		getid("cate_update_ajax_ok").innerHTML = str_right;
		eval_js(1,"clear_notice()");
	}
	else
	{
		alert("操作失败："+msg);
		return false;
	}
}

function clear_notice()
{
	getid("cate_update_ajax_ok").innerHTML = "";
}