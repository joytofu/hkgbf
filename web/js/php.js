// 动态扩展字段中涉及到的JS信息

// 调用模块中的主题信息
function phpjs_module(input_id,input_val)
{
	var extend = "input="+input_id+"&id="+input_val;
	if(iframe_id)
	{
		extend += "&iframe_id="+EncodeUtf8(iframe_id);
	}
	var url = get_url("subject,module",extend);
	Layer.init(url,550,400);
	return true;
}

function phpjs_module_clear(input_id)
{
	$("#"+input_id).attr("value","");
	$("#"+input_id+"_tmp_show").html("");
	return true;
}

function phpjs_viewmodule(id,tid)
{
	if(!id)
	{
		return false;
	}
	$("#"+tid).attr("value",id);
	var url = get_url('subject,ajax_module')+"id="+encode_utf8(id);
	get_ajax(url,_phpjs_viewmodule,tid);
}

function _phpjs_viewmodule(msg,tid)
{
	if(msg && msg != "error")
	{
		var array = $.evalJSON(msg);
		$("#"+tid).attr("value",array['id']);
		$("#"+tid+"_tmp_show").attr("value",array['title']);
		return true;
	}
}


/** 这一部分都是图片相关的JS操作 **/
//弹出图片选择器窗口
function phpjs_img(input_id,view_id)
{
	var extend = "type=img&input="+input_id+"&view="+view_id;
	if(iframe_id)
	{
		extend += "&iframe_id="+EncodeUtf8(iframe_id);
	}
	var url = get_url("open",extend);
	Layer.init(url,550,400);
	return true;
}

//删除图片
function phpjs_clear_img(input_id,view_id)
{
	$("#"+input_id).attr("value","");
	$("#"+view_id).html("<img src='images/nopic.gif' />");
}

/****这一部分都是视频的操作******/
//弹出视频选择器
function phpjs_video(input_id,view_id)
{
	var extend = "type=video&input="+input_id+"&view="+view_id;
	if(iframe_id)
	{
		extend += "&iframe_id="+EncodeUtf8(iframe_id);
	}
	var url = get_url("open",extend);
	Layer.init(url,550,400);
}


//删除视频选择
function phpjs_clear_video(input_id,view_id)
{
	$("#"+input_id).attr("value","");
	$("#"+view_id).html("");
}


/** 这一部分是下载的操作 *****/
//弹出下载选择窗口
function phpjs_download(input_id,view_id)
{
	var extend = "type=download&input="+input_id+"&view="+view_id;
	if(iframe_id)
	{
		extend += "&iframe_id="+EncodeUtf8(iframe_id);
	}
	var url = get_url("open",extend);
	Layer.init(url,550,400);
	return true;
}
//删除文件下载选择
function phpjs_clear_download(input_id,view_id)
{
	$("#"+input_id).attr("value","");
	$("#"+view_id).html("");
}

//预览窗口
function phpjs_preview(id,pretype)
{
	var extend = "id="+id+"&";
	if(pretype && pretype != "undefined")
	{
		extend += "pretype="+pretype+"&";
	}
	var url = get_url("open,preview",extend);
	Layer.init(url,550,400);
	return true;
}

//通过通过Ajax存储相关信息
function phpjs_parent_opt(val,id,fid,linkid)
{
	if(!id || id == "undefined" || !linkid || linkid == "undefined")
	{
		return false;
	}
	var extend = "fid="+fid+"&";
	extend += "identifier="+id+"&";
	extend += "linkid="+linkid+"&";
	getid(id).value = val;
	//$("#"+id).attr("value",val);
	//alert("VAL:"+val);
	if(val && val != "undefined")
	{
		extend += "val="+EncodeUtf8(val)+"&";
	}
	var url = get_url("datalink,ajax_opt",extend);
	var msg = ajax_get(url);
	if(!msg)
	{
		alert("操作有误");
		return false;
	}
	var array = $.evalJSON(msg);
	if(array["error"])
	{
		getid("_opt_parent_"+id).innerHTML = array["error"];
		return true;
	}
	if(array["parent"] && array["parent"] != "undefined")
	{
		getid("_opt_parent_"+id).innerHTML = array["parent"];
	}
	if(array["son"] && array["son"] != "undefined")
	{
		getid("_opt_son_"+id).innerHTML = array["son"];
	}
	else
	{
		getid("_opt_son_"+id).innerHTML = "";
	}
	return true;
}

function phpjs_son_opt(val,id)
{
	getid(id).value = val;
	//alert("VAL:"+val);
	//$("#"+id).attr("value",val);
}

function phpjs_fck_page(id)
{
	$("#"+id)[0].xheditor.pasteHTML("[:page:]");
}

//Fck编辑器用到的JS，图片
function phpjs_fck_img(id)
{
	var extend = "fck_id="+id+"&type=img";
	if(iframe_id)
	{
		extend += "&iframe_id="+EncodeUtf8(iframe_id);
	}
	var url = get_url("open,fck",extend);
	$("#"+id)[0].xheditor.toggleSource(true);
	$("#"+id)[0].xheditor.toggleSource();
	Layer.init(url,550,400);
	return true;
}
//Fck编辑器用到的JS，下载
function phpjs_fck_download(id)
{
	var extend = "fck_id="+id+"&type=download";
	if(iframe_id)
	{
		extend += "&iframe_id="+EncodeUtf8(iframe_id);
	}
	var url = get_url("open,fck",extend);
	//判断是否使用可视化编辑器
	$("#"+id)[0].xheditor.toggleSource(true);
	$("#"+id)[0].xheditor.toggleSource();
	Layer.init(url,550,400);
	return true;
}
//Fck编辑器用到的JS，视频
function phpjs_fck_video(id)
{
	var extend = "fck_id="+id+"&type=video";
	if(iframe_id)
	{
		extend += "&iframe_id="+EncodeUtf8(iframe_id);
	}
	var url = get_url("open,fck",extend);
	//判断是否使用可视化编辑器
	$("#"+id)[0].xheditor.toggleSource(true);
	$("#"+id)[0].xheditor.toggleSource();
	Layer.init(url,550,400);
	return true;
}

//扩展模块数据预览，图片
function phpjs_viewpic(n_vid,input)
{
	var input_view = "_view_"+input;
	if(!n_vid || n_vid =="" || n_vid == "undefined")
	{
		$("#"+input_view).html("<img src='images/nopic.gif' />");
		return true;
	}
	var url = get_url('open,ajax_preview_img')+"idstring="+n_vid;
	var msg = ajax_get(url);
	if(msg == "empty")
	{
		phpjs_viewpic("",input_view);
		return true;
	}
	var array = $.evajJSON(msg);
	var length_array = array.length;
	//通过Ajax获取图片列表
	var p_html = '<table><tr>';
	for(var i=0;i<length_array;i++)
	{
		var tmp_i = i+1;
		var srcurl = array[i]["url"];
		if(!srcurl || srcurl == "" || srcurl == "undefined")
		{
			srcurl = "images/nopic.gif";
		}
		p_html += '<td><img src="'+srcurl+'" class="hand" width="80px" height="80px" onclick="phpjs_preview(\''+array[i]["id"]+'\')" /></td>';
		if(tmp_i%8 == "")
		{
			p_html += "</tr><tr>";
		}
	}
	p_html += "</tr></table>";
	$("#"+input_view).html(p_html);
}
//扩展模块数据预览，下载
function phpjs_viewdown(id,input)
{
	var input_view = "_view_"+input;
	if(!id || id == "" || id == "undefined")
	{
		return false;
	}
	var viewhtml = '<input type="button" value="详情" class="button" onclick="phpjs_preview(\''+id+'\',\'download\')"> 附件ID：'+id+' <img src="images/download.gif" align="absmiddle" /> ';
	$("#"+input_view).html(viewhtml);
}
//扩展模块数据预览，影音
function phpjs_viewvideo(id,input)
{
	var input_view = "_view_"+input;
	if(!id || id == "" || id == "undefined")
	{
		return false;
	}
	var viewhtml = '<input type="button" value="预览" class="button" onclick="phpjs_preview(\''+id+'\')"> 影音ID：'+id+' <img src="images/video.gif" align="absmiddle" /> ';
	$("#"+input_view).html(viewhtml);
}

//单图按钮
function phpjs_onepic(vid)
{
	var extend = "input="+vid;
	if(iframe_id)
	{
		extend += "&iframe_id="+EncodeUtf8(iframe_id);
	}
	var url = get_url("open,img",extend);
	Layer.init(url,550,400);
	return true;
}

function phpjs_onepic_view(vid)
{
	var url = getid(vid).value;
	if(!url)
	{
		alert("没有相关图片信息！");
		return false;
	}
	Layer.init(url,550,400);
	return true;
}

function phpjs_onepic_clear(vid)
{
	getid(vid).value = "";
	return true;
}


//编辑器中当前日期
function phpjs_fck_date()
{
	var myDate = new Date();
	var y = myDate.getFullYear();
	var m = myDate.getMonth() + 1;
	var d = myDate.getDate() + 1;
	m = m.toString();
	if(m.length < 2)
	{
		m = "0" + m;
	}
	d = d.toString();
	if(d.length < 2)
	{
		d = "0" + d;
	}
	return y.toString() + "-" +m+ "-" +d+ " ";
}

function phpjs_fck_time()
{
	var myDate = new Date();
	return myDate.toLocaleTimeString();
}