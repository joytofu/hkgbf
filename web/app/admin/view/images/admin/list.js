// 内容控制层信息
var str_right = '<span style="color:darkblue;">&#8730;</span>';
var str_wrong = '<span style="color:darkred;">&#215;</span>';

function open_cate(mid)
{
	var turl = base_file + "?"+base_ctrl+"=list&"+base_func+"=open_cate";
	turl += "&mid="+mid;
	Layer.init(turl,550,400);
}

function search_list(fomrid,mid,ifcate)
{
	var url = base_file + "?"+base_ctrl+"=list&module_id="+mid;
	var st = $("#"+formid+"[name=status]").val();
	if(st && st>0) url += "&status="+st;
	if(ifcate)
	{
		var cateid = $("#"+formid+"[name=cate_id]").val();
		if(cateid && cateid>0) url += "&cate_id="+cateid;
	}
	var keytype = $("#"+formid+"[name=keytype]").val();
	var keywords = $("#"+formid+"[name=keywords]").val();
	var isbest = $("#"+formid+"[name=isbest]").val();
	
	if(keytype && keywords)
	{
		url += "&keytype="+$.str.encode(keytype);
		url += "&keywords="+$.str.encode(keywords);
	}
	if(isbest){
	   url += "&isbest="+$.str.encode(isbest);
	}
	direct(url);
	return false;
}

function tab_set(v)
{
	var tab_array = new Array("main","ext");
	var tab_length = tab_array.length;
	for(var i=0;i<tab_length;i++)
	{
		if(tab_array[i] == v)
		{
			$("#_tab_"+tab_array[i]).attr("class","over");
			$("#_msg_"+tab_array[i]).show();
		}
		else
		{
			$("#_tab_"+tab_array[i]).attr("class","out");
			$("#_msg_"+tab_array[i]).hide();
		}
	}
}


//样式管理器
function style_set(val)
{
	var st = $("#style").val();
	if(!st)
	{
		$("#style").attr("value",val + ";");
		return true;
	}
	//定义数组
	var array = st.split(";");
	var len = array.length;
	var n_array = new Array();
	var m = 0;
	for(var i=0;i<len;i++)
	{
		//如果存在其值，则清空吧
		if(array[i] == val)
		{
			return true;
		}
		if(array[i] != "")
		{
			n_array[m] = array[i];
			m++;
		}
	}
	//重新组成字符串
	var n_array = n_array.join(";");
	$("#style").attr("value",n_array+";"+val+";");
	return true;
}

//颜色管理器
function style_color(val)
{
	var style = getid("style").value;
	if(!style)
	{
		if(!val)
		{
			getid("style").value = "";
		}
		else
		{
			getid("style").value = "color:"+val+";";
		}
		return true;
	}
	var array = style.split(";");
	var len = array.length;
	var n_array = new Array();
	var m = 0;
	for(var i=0;i<len;i++)
	{
		//定义切割
		if(array[i] == "")
		{
			continue;
		}
		var t = array[i].split(":");
		if(t[0] == "color")
		{
			continue;
		}
		n_array[m] = array[i];
		m++;
	}
	//重新组成字符串
	var n_array = n_array.join(";");
	if(n_array != "")
	{
		getid("style").value = n_array+";color:"+val+";";
	}
	else
	{
		getid("style").value = "color:"+val+";";
	}
	return true;
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

function del(id)
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

function taxis_pl()
{
	var id = join_checkbox();
	if(!id)
	{
		alert("请选择要操作的主题");
		return false;
	}
	var url = base_url + base_func + "=taxis_pl&";
	//获取taxis值
	var id_array = id.split(",");
	for(var i=0;i<id_array.length;i++)
	{
		var taxis = getid("taxis_"+id_array[i]).value;
		url += "taxis["+id_array[i]+"]="+taxis+"&";
	}
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

function update_pl()
{
	var id = join_checkbox();
	if(!id)
	{
		alert("请选择要操作的主题");
		return false;
	}
	//获取执行操作的ID值
	var act = getid("act_plset").value;
	if(!act)
	{
		alert("请选择要执行的动作！");
		return false;
	}
	if(act == "del")
	{
		del_pl();
		return true;
	}
	else if(act == "taxis")
	{
		taxis_pl();
		return true;
	}
	else
	{
		var array = act.split(":");
		if(array[0] == "cate")
		{
			update_cate(array[1]);
			return true;
		}
		else if(array[0] == "copy")
		{
			copy_list(array[1]);
			return true;
		}
		else
		{
			var url = base_url + base_func + "=ajax_pl&field="+array[0]+"&val="+array[1]+"&id="+id;
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
	}
}

//批量生成主题操作
function copy_list(tc)
{
	var id = join_checkbox();
	if(!id)
	{
		alert("请选择要操作的主题");
		return false;
	}
	var array = id.split(",");
	if(array.length>1)
	{
		alert("每次仅允许批量一个主题！请去除多余的复选框");
		return false;
	}
	var qc = confirm("确定要复制选中的主题吗？");
	if(qc == "0")
	{
		return false;
	}
	var url = base_url + base_func + "=copy_list&total="+tc+"&id="+id;
	var msg = get_ajax(url);
	if(msg == "ok")
	{
		alert("主题批量生成成功！");
		direct(window.location.href);
	}
	else
	{
		if(!msg) msg = "error: 操作非法";
		alert(msg);
		return false;
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
	var url = base_url + base_func + "=ajax_status&id="+id;
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

function set_taxis_time(id)
{
	var time = new Date().getTime();
	time = parseInt(time/1000);
	getid(id).value = time;
	return true;
}

function update_cate(cate_id)
{
	var id = join_checkbox();
	if(!id)
	{
		alert("请选择要操作的主题");
		return false;
	}
	var url = base_url + base_func + "=ajax_update_cate&cateid="+cate_id+"&id="+id;
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

function to_check_one(id,vid,syn,must)
{
	getid("identifier_note").innerHTML = "";
	var val = getid(vid).value;
	if(!must || must == "undefined")
	{
		must = false;
	}
	if(!val)
	{
		if(!syn)
		{
			alert("验证码不允许为空！");
		}
		return (must ? false : true);
	}
	var ajax_url = base_url + base_func + "=chkone&sign="+encode_utf8(val)+"&id="+id;
	if(syn)
	{
		get_ajax(ajax_url,js_tocheck_one)
		return false;
	}
	else
	{
		var msg = get_ajax(ajax_url);
		if(msg == "ok")
		{
			return true;
		}
		else
		{
			if(!msg) msg = "error: 标识串错误！";
			msg = msg.replace("error: ","");
			alert(msg);
			return false;
		}
	}
}

function js_tocheck_one(msg)
{
	if(msg)
	{
		if(msg == "ok")
		{
			getid("identifier_note").innerHTML = str_right;
			return true;
		}
		else
		{
			if(!msg) msg = "error: 标识串错误！";
			getid("identifier_note").innerHTML = str_wrong + msg;
			return false;
		}
	}
	return true;
}