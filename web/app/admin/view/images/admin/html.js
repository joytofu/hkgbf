function html_notice(array)
{
	if(array["status"] == "error")
	{
		$("#creat_html_status").css("color","red");
	}
	else if(array["status"] == "ok")
	{
		$("#creat_html_status").css("color","darkgreen");
	}
	else if(array["status"] == "next")
	{
		$("#creat_html_status").css("color","darkblue");
	}
	else
	{
		$("#creat_html_status").css("color","darkred");
	}
	if(!array["subject"])
	{
		array["subject"] = "操作异常，请检查！";
	}
	$("#creat_html_status").html(array["subject"]);
	return true;
}

function post_html(url,content)
{
	$.ajax({
		type: "post",
		cache: false,
	});
}
function index_html(link_url)
{
	if(!link_url || link_url == "undefined")
	{
		alert("未设置好首页！");
		return false;
	}
	$("#creat_html_status").html("正在生成 <span class='darkblue'>首页</span>，请稍候……，您可以<a href='"+link_url+"' target='_blank'>点此访问网站首页</a>");
	get_ajax(link_url,index_html_rs,"creat_html_status");
}
//生成首页成功提示
function index_html_rs(rs,ext)
{
	var create_index_url = base_file + "?"+base_ctrl+"=html&"+base_func+"=create_index";
	if(rs)
	{
		$.post(create_index_url,{content: rs},function(html_url){
			$("#"+ext).html("网站首页静态页创建成功，<a href='"+html_url+"' target='_blank'>您可以点此访问！</a>");
		});
	}
}

function list_html()
{
	var typeid = $("#typeid").val();
	var link_url = base_file + "?"+base_ctrl+"=html&"+base_func+"=create_list_set&typeid="+EncodeUtf8(typeid);
	get_ajax(link_url,create_list_start);
}

function create_list_start(msg)
{
	if(msg)
	{
		var rs = $.evalJSON(msg);
		if(rs["status"] == "ok" || rs["status"] == "error")
		{
			return html_notice(rs);
		}
		else
		{
			var save_url = base_file + "?"+base_ctrl+"=html&"+base_func+"=html_save";
			var url = base_file + "?"+base_ctrl+"=html&"+base_func+"=create_list&";
			url+= "mid="+rs["mid"]+"&";
			url+= "endmid="+rs["endmid"]+"&";
			url+= "cid="+rs["cid"]+"&";
			url+= "endcid="+rs["endcid"]+"&";
			if(rs["pageid"] && rs["pageid"] != "undefined")
			{
				url+= "pageid="+rs["pageid"];
			}
			html_notice(rs);
			get_ajax(url,create_list);
		}
	}
}

function create_list(msg)
{
	if(msg)
	{
		var rs = $.evalJSON(msg);
		var save_url = base_file + "?"+base_ctrl+"=html&"+base_func+"=html_save";
		var url = base_file + "?"+base_ctrl+"=html&"+base_func+"=create_list&";
		url+= "mid="+rs["mid"]+"&";
		url+= "endmid="+rs["endmid"]+"&";
		url+= "cid="+rs["cid"]+"&";
		url+= "endcid="+rs["endcid"]+"&";
		if(rs["pageid"] && rs["pageid"] != "undefined")
		{
			url+= "pageid="+rs["pageid"];
		}
		html_notice(rs);
		if(rs["fsurl"] && rs["html_file"])
		{
			$.get(rs["fsurl"],{},function(c){
				$.post(save_url,{htmlfile: rs["html_file"],content: c},function(t){
					if(rs["status"] != "ok" && rs["status"] != "error")
					{
						get_ajax(url,create_list);
					}
				});
			});
		}
		return true;
	}
}

//生成内容页
function msg_html()
{
	var typeid = $("#typeid").val();
	var url = base_file + "?"+base_ctrl+"=html&"+base_func+"=create_msg&typeid="+EncodeUtf8(typeid);
	var sid = $("#startid").val();
	if(sid && parseInt(sid)>0)
	{
		url += "&sid="+sid;
	}
	var eid = $("#endid").val();
	if(eid && parseInt(eid)>0)
	{
		url += "&eid="+eid;
	}
	$("#creat_html_status").html("正在生成内容页对列！");
	get_ajax(url,js_create_msg);
}

//开始执行html信息

function js_create_msg(msg)
{
	var rs = $.evalJSON(msg);
	var url = base_file + "?"+base_ctrl+"=html&"+base_func+"=create_msg&";
	url+= "mid="+rs["mid"]+"&";
	url+= "cid="+rs["cid"]+"&";
	url+= "sid="+rs["sid"]+"&";
	url+= "eid="+rs["eid"]+"&";
	url+= "tid="+rs["tid"]+"&";
	html_notice(rs);
	if(rs["fsurl"] && rs["html_file"])
	{
		var save_url = base_file + "?"+base_ctrl+"=html&"+base_func+"=html_save";
		$.get(rs["fsurl"],{},function(c){
			$.post(save_url,{htmlfile: rs["html_file"],content: c},function(t){
				if(rs["status"] != "ok" && rs["status"] != "error")
				{
					get_ajax(url,js_create_msg);
				}
			});
		});
	}
	return true;
}
