/*权限操作涉及到的脚本*/
if(!base_url)
{
	alert("操作有错误，没有获取当前目录");
}

function ctrl_set(val)
{
	if(val == "list")
	{
		getid("content_set").style.display = "";
		getid("ctrl_init_input").style.display = "none";
		getid("ctrl_init_list").checked = true;
	}
	else
	{
		getid("content_set").style.display = "none";
		getid("ctrl_init_input").style.display = "";
		getid("ctrl_init_user").checked = true;
	}
	return true;
}

function update_ctrl_pl()
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
	var url = base_url + base_func + "="+act+"&id="+id;
	var msg = get_ajax(url);
	if(!msg) msg = "操作非法";
	if(msg == "ok")
	{
		alert("批量操作成功！");
	}
	else
	{
		alert(msg);
	}
	return false;
}

function del(id)
{
	if(!id)
	{
		alert("操作非法");
		return false;
	}
	var q = confirm("确定要删除此模块吗？删除后是不能恢复的");
	if(q != 0)
	{
		var url = base_url + base_func + "=del&id="+id;
		var msg = get_ajax(url);
		if(!msg) msg = "error: 操作非法";
		if(msg == "ok")
		{
			alert("模块删除成功");
			window.location.href = window.location.href;
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

//检测
function chk(id)
{
	var title = getid("title").value;
	if(!title)
	{
		alert("模块名称不允许为空");
		return false;
	}
	//添加操作时的检测
	if(id == "" || !id || id == "0")
	{
		//检测是否有选择控制层
		if(!getid("ctrl_init_list").checked)
		{
			var ctrl_init = $("#ctrl_init_val").val();
			if(!ctrl_init)
			{
				alert("请选择控制层");
				return false;
			}
		}
		var identifier = getid("identifier").value;
		if(!identifier || identifier == "")
		{
			alert("标识符为空");
			return false;
		}
		//判断内容
		var url = base_url + base_func + "=chk_identifier&identifier="+identifier;
		var msg = get_ajax(url);
		if(msg != "ok")
		{
			if(!msg) msg = "error: 操作错误";
			alert(msg);
			return false;
		}
	}
	return true;
}

//检测是否必填
function if_must_set(val)
{
	if(val == '1')
	{
		getid("error_note_title").style.display = "";
		getid("if_must_1").checked = true;
	}
	else
	{
		getid("error_note_title").style.display = "none";
		getid("if_must_0").checked = true;
	}
}

function input_type(val)
{
	$("#input_module").hide();
	$("#input_width").hide();
	$("#input_height").hide();
	$("#input_defalt_val").hide();
	$("#input_list_val").hide();
	$("#input_link").hide();
	if(val == "text")
	{
		$("#input_width").show();
		$("#input_defalt_val").show();
	}
	else if(val == "opt")
	{
		$("#input_link").show();
	}
	else if(val == "textarea" || val == "edit")
	{
		$("#input_width").show();
		$("#input_height").show();
	}
	else if(val == "radio" || val == "checkbox" || val == "select")
	{
		$("#input_defalt_val").show();
		$("#input_list_val").show();
	}
	else if(val == "module")
	{
		$("#input_module").show();
	}
}

//更改权限状态
function fields_status(id,t)
{
	if(!id)
	{
		alert("操作非法");
		return false;
	}
	var url = base_url + base_func + "=fields_status&id="+id;
	var msg = get_ajax(url);
	if(msg == "ok")
	{
		var n_t = t == 1 ? 0 : 1;
		$("#status_"+id+" > a").attr("class","status"+n_t);
		$("#status_"+id+" > a").attr("href","javascript:fields_status("+id+","+n_t+");void(0)");
		return true;
	}
	else
	{
		if(!msg) msg = "error: 操作非法";
		alert(msg);
		return false;
	}
}

function fields_del(id)
{
	if(!id)
	{
		alert("操作非法");
		return false;
	}
	var q = confirm("确定要删除此字段吗？删除后是不能恢复的");
	if(q != 0)
	{
		var url = base_url + base_func + "=fields_del&id="+id;
		var msg = get_ajax(url);
		if(!msg) msg = "error: 操作非法";
		if(msg == "ok")
		{
			alert("字段删除成功");
			window.location.href = window.location.href;
		}
		else
		{
			alert(msg);
			return false;
		}
	}
}

//检测字段是否必填
function fields_chk(id)
{
	var title = getid("title").value;
	if(!title)
	{
		alert("字段名称不允许为空");
		return false;
	}
	//检测标识符是否符合要求
	if(id == "" || !id || id == "0")
	{
		var identifier = getid("identifier").value;
		if(!identifier || identifier == "")
		{
			alert("标识符为空");
			return false;
		}
		//判断内容
		var module_id = getid("module_id").value;
		if(!module_id || module_id == "0")
		{
			alert("模块ID获取失败，请检查");
			return false;
		}
		var url = base_url + base_func + "=chk_identifier2&identifier="+identifier+"&module_id="+module_id;
		var msg = get_ajax(url);
		if(msg != "ok")
		{
			if(!msg) msg = "error: 操作错误";
			alert(msg);
			return false;
		}
		//判断控制类型
		var input_type = getid("input").value;
		if(!input_type || input_type == "")
		{
			alert("请选择有效的表单控件");
			return false;
		}
	}
	return true;
}

function to_search()
{
	var url = get_url('ctrl,index');
	var groupid = getid("groupid").value;
	if(groupid>0)
	{
		url += "groupid="+groupid+"&";
	}
	window.location.href = url;
}

function to_update_g(val)
{
	if(val == "0" || !val || val == "undefined")
	{
		getid("to_g_title").value = "添加";
		getid("g_del_if").style.display = "none";
	}
	else
	{
		getid("to_g_title").value = "编辑";
		getid("g_del_if").style.display = "";
	}
}

function to_group_set()
{
	var url = get_url('ctrl,gset');
	var groupid = getid("groupid").value;
	if(groupid>0)
	{
		url += "id="+groupid+"&";
	}
	direct(url);
}

function to_group_del()
{
	var url = get_url('ctrl,gdel');
	var groupid = getid("groupid").value;
	if(groupid>0)
	{
		var tq = confirm("确定要删除该模块组吗？删除后不能恢复！");
		if(tq == "0")
		{
			return false;
		}
		url += "id="+groupid+"&";
		var msg = get_ajax(url);
		if(msg == "ok")
		{
			direct(window.location.href);
			return true;
		}
		else
		{
			if(!msg) msg = "error!";
			alert(msg);
			return false;
		}
	}
	else
	{
		alert("请选对要删除的模块！");
		return false;
	}
}
