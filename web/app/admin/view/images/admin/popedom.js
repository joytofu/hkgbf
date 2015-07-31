/*权限操作涉及到的脚本*/
if(!base_url)
{
	alert("操作有错误，没有获取当前目录");
}

function del(id)
{
	if(!id)
	{
		alert("操作非法");
		return false;
	}
	var q = confirm("确定要删除此权限吗？删除后是不能恢复的");
	if(q != 0)
	{
		var url = base_url + base_func + "=del&id="+id;
		var msg = get_ajax(url);
		if(!msg) msg = "error: 操作非法";
		if(msg == "ok")
		{
			alert("权限脚本删除成功");
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
function status(id,t)
{
	if(!id)
	{
		alert("操作非法");
		return false;
	}
	var url = base_url + base_func + "=status&id="+id;
	var msg = get_ajax(url);
	if(msg == "ok")
	{
		var n_t = t == 1 ? 0 : 1;
		$("#status_"+id+" > a").attr("class","status"+n_t);
		$("#status_"+id+" > a").attr("href","javascript:status("+id+","+n_t+");void(0)");
		return true;
	}
	else
	{
		if(!msg) msg = "error: 操作非法";
		alert(msg);
		return false;
	}
}

//添加或编辑
function set(id)
{
	var url = base_url + base_func + "=set&";
	if(id && id != "undefined")
	{
		url += "id="+id;
	}
	Layer.init(url,550,350);
	return true;
}