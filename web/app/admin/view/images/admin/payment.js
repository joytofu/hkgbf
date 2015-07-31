//检测标识串是否有被使用
var payment_url = base_file + "?"+base_ctrl+"=payment&"+base_func+"=";
function to_chk(pic,rAlert)
{
	var m = getid(pic).value;
	if(!m)
	{
		alert("Error: 错误，标识符不允许为空");
		return false;
	}
	var url = payment_url + "code_chk&code="+EncodeUtf8(m);
	var msg = get_ajax(url);
	if(!rAlert || rAlert == "" || rAlert == "undefined")
	{
		rAlert = "true";
	}
	if(msg == "ok")
	{
		if(rAlert == "true")
		{
			alert("当前标识符："+m+" 可以使用");
		}
		return true;
	}
	else
	{
		if(!msg) msg = "Error: 操作有误";
		alert(msg);
		return false;
	}
}
//[对输入的数据进行检测]
function to_setpayment(id)
{
	if(!id || id == "0" || id == "undefined" || id == "")
	{
		//[检测唯一标识串]
		var chk = to_chk("code","false");
		if(!chk)
		{
			alert("检测标识符不过关");
			return false;
		}
	}
	//
	var subject = getid("title").value;
	if(!subject || subject == "undefined")
	{
		alert("标题不允许为空");
		return false;
	}
	$("_phpok_submit").disabled = true;
	return true;
}

function to_setfields(id,payid)
{
	if(!id || id == "0" || id == "undefined" || id == "")
	{
		//[检测唯一标识串]
		//
		var chk = to_fields_chk(payid,"code","false");
		if(!chk)
		{
			alert("检测标识符不过关");
			return false;
		}
	}
	//
	var subject = getid("title").value;
	if(!subject || subject == "undefined")
	{
		alert("标题不允许为空");
		return false;
	}
	$("_phpok_submit").disabled = true;
	return true;
}

function to_fields_chk(tid,pic,rAlert)
{
	var m = getid(pic).value;
	if(!m)
	{
		alert("Error: 错误，标识符不允许为空");
		return false;
	}
	var url = payment_url + "fields_chk&id="+tid+"&code="+EncodeUtf8(m);
	var msg = get_ajax(url);
	if(!rAlert || rAlert == "" || rAlert == "undefined")
	{
		rAlert = "true";
	}
	if(msg == "ok")
	{
		if(rAlert == "true")
		{
			alert("当前标识符："+m+" 可以使用");
		}
		return true;
	}
	else
	{
		if(!msg) msg = "Error: 操作有误";
		alert(msg);
		return false;
	}
}


function to_status(id)
{
	var url = payment_url + "ajax_status&id="+id;
	var msg = get_ajax(url);
	if(msg == "ok")
	{
		window.location.href = window.location.href;
	}
	else
	{
		if(!msg) msg = "error: 操作错误";
		alert(msg);
		return false;
	}
}

function to_del(id)
{
	var qc = confirm("确定要删除该付款方式吗？删除后不能恢复");
	if(qc == "0")
	{
		return false;
	}
	var url = payment_url + "del&id="+id;
	var msg = get_ajax(url);
	if(msg == "ok")
	{
		window.location.href = window.location.href;
	}
	else
	{
		if(!msg) msg = "error: 操作错误";
		alert(msg);
		return false;
	}
}

function to_fdel(id)
{
	var qc = confirm("确定要删除该字段属性吗？删除后不能恢复");
	if(qc == "0")
	{
		return false;
	}
	var url = payment_url + "fields_del&id="+id;
	var msg = get_ajax(url);
	if(msg == "ok")
	{
		window.location.href = window.location.href;
	}
	else
	{
		if(!msg) msg = "error: 操作错误";
		alert(msg);
		return false;
	}
}