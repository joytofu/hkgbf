/* 仅供后台使用的js代码 */

//动态加载js
function loadjs(url,callback){
	var head = document.getElementsByTagName("head")[0];
	//判断JS是否已存在
	var script = document.createElement('script');
	script.onload = script.onreadystatechange = script.onerror = function (){
		if (script && script.readyState && /^(?!(?:loaded|complete)$)/.test(script.readyState))
		{
			return;
		}
		script.onload = script.onreadystatechange = script.onerror = null;
		script.src = '';
		script.parentNode.removeChild(script);
		script = null;
		if(callback && callback != "undefined")
		{
			callback();
		}
	}
	script.charset = "utf-8";
	script.src = url;
	try {
		head.appendChild(script);
	} catch (exp) {}
}



/*!
 * http://www.phpok.com/
 *
 * Copyright 2011, phpok.com
 * Released under the MIT, BSD, and LGPL Licenses.
 * 取得cookie：$.cookie.get(id)
 * 设置cookie：$.cookie.set(id,value,time)
 * 删除cookie：$.cookie.del(id)
 * id就是指cookie的变量名，不允许为空
 *
 * Date: 2011-12-01 12:10
 */

(function(a){a.cookie={get:function(c){var f="";var d=c+"=";if(document.cookie.length>0){var e=document.cookie.indexOf(d);if(e!=-1){e+=d.length;var b=document.cookie.indexOf(";",e);if(b==-1){b=document.cookie.length}f=unescape(document.cookie.substring(e,b));b=null}d=e=null}return f},set:function(f,d,e){var b="";var c=1;if(e!=null){c=e}b=new Date((new Date()).getTime()+c*86400000);b="; expires="+b.toGMTString();document.cookie=f+"="+escape(d)+";path=/"+b;f=d=e=c=b=null},del:function(c){var b="";b=new Date((new Date()).getTime()-1);b="; expires="+b.toGMTString();document.cookie=c+"="+escape("")+";path=/"+b;c=b=null}}})(jQuery);


/*!
 * http://www.phpok.com/
 *
 * Copyright 2011, phpok.com
 * Released under the MIT, BSD, and LGPL Licenses.
 * 全选：$.input.checkbox_all(id)
 * 全不选：$.input.checkbox_none(id)
 * 反选：$.input.checkbox_anti(id)
 * 部分选：$.input.checkbox_not_all(id,total)
 * 合并复选框值：$.input.checkbox_join(id)
 * 未指定ID时，将读取当前整个页面信息
 *
 * Date: 2011-12-01 12:07
 */

(function(a){a.input={checkbox_all:function(c){var b=c&&c!="undefined"?a("#"+c+" input[type*=checkbox]"):a("input[type*=checkbox]");b.each(function(){a(this).attr("checked",true)});b=null},checkbox_none:function(c){var b=c&&c!="undefined"?a("#"+c+" input[type*=checkbox]"):a("input[type*=checkbox]");b.each(function(){a(this).attr("checked",false)});b=null},checkbox_not_all:function(e,d){var c=e&&e!="undefined"?a("#"+e+" input[type*=checkbox]"):a("input[type*=checkbox]");var b=0;if(!d||parseInt(d)<5){d=5}c.each(function(){if(a(this).attr("checked")!=true&&b<d){a(this).attr("checked",true);b++}});c=b=d=null},checkbox_anti:function(c){var b=c&&c!="undefined"?a("#"+c+" input[type*=checkbox]"):a("input[type*=checkbox]");b.each(function(){if(a(this).attr("checked")==true){a(this).attr("checked",false)}else{a(this).attr("checked",true)}});b=null},checkbox_join:function(g,d){var c=g&&g!="undefined"?a("#"+g+" input[type*=checkbox]"):a("input[type*=checkbox]");var f=new Array();var b=0;c.each(function(){if(d=="all"){f[b]=a(this).val();b++}else{if(d=="unchecked"){if(a(this).attr("checked")==false){f[b]=a(this).val();b++}}else{if(a(this).attr("checked")==true){f[b]=a(this).val();b++}}}});var e=f.join(",");c=f=b=null;return e}}})(jQuery);


/*!
 * jquery IncludeFile
 * Date: 2011-11-22 15:32
 * http://www.94this.com.cn/myCode/jqueryIncludefile/
 * (c) 2009-2011 TangBin, http://www.94this.com.cn/
 *
 * This is licensed under the GNU LGPL, version 2.1 or later.
 * For details, see: http://creativecommons.org/licenses/LGPL/2.1/
 */

(function($){$.extend({ImportBasePath:'',fileinfo:function(data){data=data.replace(/^\s|\s$/g,"");var m;if(/\.\w+$/.test(data)){m=data.match(/([^\/\\]+)\.(\w+)$/);if(m){if(m[2]=='js'){return{filename:m[1],ext:m[2],tag:'script'}}else if(m[2]=='css'){return{filename:m[1],ext:m[2],tag:'link'}}else{return{filename:m[1],ext:m[2],tag:null}}}else{return{filename:null,ext:null}}}else{m=data.match(/([^\/\\]+)$/);if(m){return{filename:m[1],ext:null,tag:null}}else{return{filename:null,ext:null,tag:null}}}},fileExist:function(filename,filetype,attrCheck){var elementsArray=document.getElementsByTagName(filetype);for(var i=0;i<elementsArray.length;i++){if(elementsArray[i].getAttribute(attrCheck)==$.ImportBasePath+filename){return true}}return false},createElement:function(filename,filetype){switch(filetype){case'script':if(!$.fileExist(filename,filetype,'src')){var scriptTag=document.createElement(filetype);scriptTag.setAttribute('language','javascript');scriptTag.setAttribute('type','text/javascript');scriptTag.setAttribute('src',$.ImportBasePath+filename);return scriptTag}else{return false}break;case'link':if(!$.fileExist(filename,filetype,'href')){var styleTag=document.createElement(filetype);styleTag.setAttribute('type','text/css');styleTag.setAttribute('rel','stylesheet');styleTag.setAttribute('href',$.ImportBasePath+filename);return styleTag}else{return false}break;default:return false;break}},cssReady:function(index,callback){function check(){if(document.styleSheets[index]){if(typeof callback=='function'){callback()}window.clearInterval(checkInterval)}}var checkInterval=window.setInterval(check,200)},include:function(file,callback){var headerTag=document.getElementsByTagName('head')[0];var fileArray=[];typeof file=='string'?fileArray[0]=file:fileArray=file;for(var i=0;i<fileArray.length;i++){var elementTag=$.fileinfo(fileArray[i]).tag;var el=[];if(elementTag!==null){el[i]=$.createElement(fileArray[i],elementTag);if(el[i]){headerTag.appendChild(el[i]);if($.browser.msie){el[i].onreadystatechange=function(){if(this.readyState==='loaded'||this.readyState==='complete'){if(typeof callback=='function'){callback()}}}}else{if(elementTag=='link'){$.cssReady(i,callback)}else{el[i].onload=function(){if(typeof callback=='function'){callback()}}}}}}else{return false}}}})})(jQuery);

/*!
 * http://www.phpok.com/
 *
 * Copyright 2011, phpok.com
 * Released under the MIT, BSD, and LGPL Licenses.
 * 字符串编码：$.str.encode(string);
 * 字符串合并：$.str.join(str1,str2);
 *
 * Date: 2011-12-01 11:47
 */
(function($){$.str={join:function(str1,str2){if(str1==""&&str2==""){return false}if(str1==""){return str2}if(str2==""){return str1}var string=str1+","+str2;var array=string.split(",");array=$.unique(array);var string=array.join(",");return string?string:false},encode:function(s1){var s=escape(s1);var sa=s.split("%");var retV="";if(sa[0]!=""){retV=sa[0]}for(var i=1;i<sa.length;i++){if(sa[i].substring(0,1)=="u"){retV+=this.StringHex2Utf8(this.Str2Hex(sa[i].substring(1,5)));if(sa[i].length>5){retV+=sa[i].substring(5)}}else{retV+="%"+sa[i]}}return retV},StringHex2Utf8:function(s){var retS="";var tempS="";var ss="";if(s.length==16){tempS="1110"+s.substring(0,4);tempS+="10"+s.substring(4,10);tempS+="10"+s.substring(10,16);var sss="0123456789ABCDEF";for(var i=0;i<3;i++){retS+="%";ss=tempS.substring(i*8,(eval(i)+1)*8);retS+=sss.charAt(this.Dig2Dec(ss.substring(0,4)));retS+=sss.charAt(this.Dig2Dec(ss.substring(4,8)))}return retS}return""},Dig2Dec:function(s){var retV=0;if(s.length==4){for(var i=0;i<4;i++){retV+=eval(s.charAt(i))*Math.pow(2,3-i)}return retV}return -1},Dec2Dig:function(n1){var s="";var n2=0;for(var i=0;i<4;i++){n2=Math.pow(2,3-i);if(n1>=n2){s+="1";n1=n1-n2}else{s+="0"}}return s},Str2Hex:function(s){var c="";var n;var ss="0123456789ABCDEF";var digS="";for(var i=0;i<s.length;i++){c=s.charAt(i);n=ss.indexOf(c);digS+=this.Dec2Dig(eval(n))}return digS}};$.rawurlencode=function(str){return $.str.encode(str)}})(jQuery);

/**
 * jQuery JSON Plugin
 * version: 2.3 (2011-09-17)
 *
 * This document is licensed as free software under the terms of the
 * MIT License: http://www.opensource.org/licenses/mit-license.php
 *
 * Brantley Harris wrote this plugin. It is based somewhat on the JSON.org
 * website's http://www.json.org/json2.js, which proclaims:
 * "NO WARRANTY EXPRESSED OR IMPLIED. USE AT YOUR OWN RISK.", a sentiment that
 * I uphold.
 *
 * It is also influenced heavily by MochiKit's serializeJSON, which is
 * copyrighted 2005 by Bob Ippolito.
 * 使用方法：$.evalJSON(string)
 */
(function($){var escapeable=/["\\\x00-\x1f\x7f-\x9f]/g,meta={"\b":"\\b","\t":"\\t","\n":"\\n","\f":"\\f","\r":"\\r",'"':'\\"',"\\":"\\\\"};$.toJSON=typeof JSON==="object"&&JSON.stringify?JSON.stringify:function(o){if(o===null){return"null"}var type=typeof o;if(type==="undefined"){return undefined}if(type==="number"||type==="boolean"){return""+o}if(type==="string"){return $.quoteString(o)}if(type==="object"){if(typeof o.toJSON==="function"){return $.toJSON(o.toJSON())}if(o.constructor===Date){var month=o.getUTCMonth()+1,day=o.getUTCDate(),year=o.getUTCFullYear(),hours=o.getUTCHours(),minutes=o.getUTCMinutes(),seconds=o.getUTCSeconds(),milli=o.getUTCMilliseconds();if(month<10){month="0"+month}if(day<10){day="0"+day}if(hours<10){hours="0"+hours}if(minutes<10){minutes="0"+minutes}if(seconds<10){seconds="0"+seconds}if(milli<100){milli="0"+milli}if(milli<10){milli="0"+milli}return'"'+year+"-"+month+"-"+day+"T"+hours+":"+minutes+":"+seconds+"."+milli+'Z"'}if(o.constructor===Array){var ret=[];for(var i=0;i<o.length;i++){ret.push($.toJSON(o[i])||"null")}return"["+ret.join(",")+"]"}var name,val,pairs=[];for(var k in o){type=typeof k;if(type==="number"){name='"'+k+'"'}else{if(type==="string"){name=$.quoteString(k)}else{continue}}type=typeof o[k];if(type==="function"||type==="undefined"){continue}val=$.toJSON(o[k]);pairs.push(name+":"+val)}return"{"+pairs.join(",")+"}"}};$.evalJSON=typeof JSON==="object"&&JSON.parse?JSON.parse:function(src){return eval("("+src+")")};$.secureEvalJSON=typeof JSON==="object"&&JSON.parse?JSON.parse:function(src){var filtered=src.replace(/\\["\\\/bfnrtu]/g,"@").replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,"]").replace(/(?:^|:|,)(?:\s*\[)+/g,"");if(/^[\],:{}\s]*$/.test(filtered)){return eval("("+src+")")}else{throw new SyntaxError("Error parsing JSON, source is not valid.")}};$.quoteString=function(string){if(string.match(escapeable)){return'"'+string.replace(escapeable,function(a){var c=meta[a];if(typeof c==="string"){return c}c=a.charCodeAt();return"\\u00"+Math.floor(c/16).toString(16)+(c%16).toString(16)})+'"'}return'"'+string+'"'}})(jQuery);


//动态加载颜色选择器
$.include("js/icolor/icolor.css");
$.include("js/icolor/jquery.icolor.min.js");
//动态加载时间控件
$.include("js/dyndatetime/css/calendar-win2k-1.css");
$.include("js/dyndatetime/jquery.dyndatetime.pack.js",function(){
	$.include("js/dyndatetime/lang/calendar-utf8.js");
});

//动态加载虚弹窗口
$.include("js/artdialog/skins/chrome.css");
$.include("js/artdialog/artdialog.js",function(){
	$.include("js/artdialog/plugins/iframetools.js");
});


//合并字符串
function join_str(str1,str2)
{
	return $.str.join(str1,str2);
}


//使用短函数getid替代 document.getElementById
function getid(id)
{
	return document.getElementById(id);
}

//定义跳转网址
//参数 url 要跳转的网址 frameid要跳转到的框架ID isparent是否是父级框架
function direct(url,frameid,isparent)
{
	url = url.replace(/&amp;/g,"&");
	if(!isparent || isparent == "" || isparent == "undefined")
	{
		if(frameid)
		{
			window.frames[frameid].location.href = url;
		}
		else
		{
			window.location.href=url;
		}
	}
	else
	{
		if(!frameid || frameid == "" || frameid == "undefined")
		{
			parent.window.location.href = url;
		}
		else
		{
			window.parent.frames[frameid].location.href = url;
		}
	}
}


//设定多长时间运行脚本
//参数 time 是时间单位是毫秒，为0时表示直接运行 大于0小于10毫秒将自动*1000
//参数 js 要运行的脚本
function eval_js(time,js)
{
	time = parseFloat(time);
	if(time < 0.01)
	{
		eval(js);
	}
	else
	{
		if(time < 10)
		{
			time = time*1000;
		}
		window.setTimeout(js,time);
	}
}


//关闭浏览器错误调试错误
function kill_error()
{
	return true;
}


/*取得当前网址所在的目录，仅限后台*/
function site_url()
{
	var siteurl = window.location.href;
	//去除?后面的相关参数
	siteurl = siteurl.substring(7,siteurl.indexOf("?"));
	//切分 / 符号
	var sitearray = siteurl.split("/");
	var newurl = "http://";
	for(var i=0;i<(sitearray.length-1);i++)
	{
		newurl += sitearray[i]+"/";
	}
	return newurl;
}


//弹出窗口调用
var Layer=
{
	init:function(url,divw,divh)
	{
		var divw = $(window).width() * 0.7;
		var divh = $(window).height() * 0.8;
		if(divw<560) divw = 560;
		if(divh<150) divh = 150;
		$.dialog.open(url,{'width':divw,'height':divh,'fixed': true,'id':'phpok_layer_open','resize': false});
	},
	inline:function(divid,divw,divh)
	{
		$.dialog({'content':document.getElementById(divid)});
	},
	over:function()
	{
		$(".aui_close").click();
	}
}


/* jQuery Ajax */
function get_ajax(turl,ajax_func,ext)
{
	turl = turl.replace(/&amp;/g,"&");
	if(ajax_func && ajax_func != "undefined")
	{
		$.ajax({url:turl,cache:false,success:function(rs){ajax_func(rs,ext)}});
	}
	else
	{
		return $.ajax({url:turl,cache:false,async:false}).responseText;
	}
}

//多功能播放器JS代码，支持swf,wma,wmv,mp3,mp4,mpg,rm,rmvb,mpg,mpeg等多种播放代码
var Media =
{
	init:function(url,width,height,image)
	{
		$.media.init({
			"url":url,
			"width":width,
			"height":height,
			"image":image
		});
	}
}



function show_date(v,formattype)
{
	if(formattype && formattype != "undefined")
	{
		var datetype = "%Y-%m-%d %H:%M";
		var show_time = true;
	}
	else
	{
		var datetype = "%Y-%m-%d";
		var show_time = false;
	}
	jQuery("#"+v).dynDateTime({
		showsTime: show_time,
		ifFormat: datetype,
		timeFormat:"24"
	});
}


//旧版参数，后续版本将不再使用
function encode_utf8(str)
{
	return $.str.encode(str);
}

function EncodeUtf8(s1)
{
	return $.str.encode(s1);
}

function select_all(id)
{
	$.input.checkbox_all(id);
}

function select_not_all(id,total)
{
	$.input.checkbox_not_all(id,total)
}

function select_none(id)
{
	$.input.checkbox_none(id);
}

function select_anti(id)
{
	$.input.checkbox_anti(id);
}

function join_checkbox(id,type)
{
	return $.input.checkbox_join(id,type);
}

