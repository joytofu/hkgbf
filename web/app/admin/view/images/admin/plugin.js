function to_modify_ok(id,identifier)
{
	var subject = $("#title").val();
	if(!subject)
	{
		alert("插件名称不允许为空");
		return false;
	}
	//检查扩展插件是否为空
	if($.isFunction(identifier))
	{
		var chkrs = identifier();
		if(!chkrs)
		{
			return false;
		}
	}
	return true;
}


function to_modify(id)
{
	var url = base_url + base_func + "=set&id="+id;
	direct(url);
}

function to_status(id,t)
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
		$("#status_"+id+" > a").attr("href","javascript:to_status("+id+","+n_t+");void(0)");
		return true;
	}
	else
	{
		if(!msg) msg = "error: 操作非法";
		alert(msg);
		return false;
	}
}

function to_install(id,title)
{
	if(!id)
	{
		alert("操作非法");
		return false;
	}
	var q = confirm("确定要安装插件："+title);
	if(q != 0)
	{
		var url = base_url + base_func + "=install&id="+id;
		var msg = get_ajax(url);
		if(!msg) msg = "error: 操作非法";
		if(msg == "ok")
		{
			alert("插件："+title+" 安装成功！");
			direct(window.location.href);
		}
		else
		{
			alert(msg);
			return false;
		}
	}
}

function to_uninstall(id,title)
{
	if(!id)
	{
		alert("操作非法");
		return false;
	}
	var q = confirm("确定要删除插件："+title+" 信息吗？\n\n删除后是不能恢复的");
	if(q != 0)
	{
		var url = base_url + base_func + "=del&id="+id;
		var msg = get_ajax(url);
		if(!msg) msg = "error: 操作非法";
		if(msg == "ok")
		{
			alert("插件："+title+" 删除成功！");
			direct(window.location.href);
		}
		else
		{
			alert(msg);
			return false;
		}
	}
}