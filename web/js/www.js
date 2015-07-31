/* 前台通过JS获取数据来处理PHP信息 */
function func_php(module,func,id)
{
	var url = get_url("js","act="+module);
	get_ajax(url,func,id);
}

/** 以下是常用的核心JS信息 **/
function js_usercp(msg,id)
{
	if(msg && msg != "false")
	{
		getid(id).innerHTML = msg;
	}
	return true;
}

//退出操作
function logout()
{
	var q = confirm("确定要退出吗？");
	if(q != "0")
	{
		var url = get_url("logout");
		direct(url);
	}
}

//加载点评中心
function js_reply(msg,id)
{
	if(msg && msg != "false")
	{
		getid(id).innerHTML = msg;
	}
	return true;
}

//显示点评模块
function js_show_digg(msg,id)
{
	if(getid(id) && msg != "false" && msg != "clicked" && msg)
	{
		getid(id).style.display = "";
		getid(id).innerHTML = msg;
	}
	else if(msg == "clicked")
	{
		var val = "You have clicked on, please do not repeat it.";
		if(getid("clicked_val") && getid("clicked_val").value)
		{
			val = getid("clicked_val").value;
		}
		alert(val);
		return false;
	}
	return true;
}
//加入购物车
function addcart(id)
{
	var turl = base_file + "?"+base_ctrl+"=cart&"+base_func+"=ajax_add&id="+id;
	get_ajax(turl,"",base_file + "?"+base_ctrl+"=cart");
}

//语言选择
function lang_select(val)
{
	var url = base_file + "?langid="+val;
	window.location.href = url;
}

/*调用flash代码*/
function flash(file,width,height,div_id)
{
	var fcode = "";
	fcode += '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" ';
	fcode += 'codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0" ';
	fcode += 'width="'+width+'" height="'+height+'">';
	fcode += '<param name="movie" value="'+file+'" type="application/x-shockwave-flash">';
	//fcode += '<param name="wmode" value="transparent"><param name="quality" value="high">';
	fcode += '<embed src="'+file+'" wmode="transparent" quality="high" ';
	fcode += 'pluginspage="http://www.macromedia.com/go/getflashplayer" ';
	fcode += 'type="application/x-shockwave-flash" width="'+width+'" height="'+height+'">';
	fcode += '</embed></object>';
	div_id ? $(div_id).innerHTML = fcode : document.write(fcode);
}
