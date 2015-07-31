// PHPOK 应用JS
(function($){
	$.phpok = {
		//初始化PHPOK参数
		init: function(options){
			var defaults = {
				js_path		:"js",
				ajax_file	:"websitesystem.php",
				lang_id		:"cn",
				trigger_c	:"{:$sys_app->config->c}",
				trigger_f	:"{:$sys_app->config->f}",
				trigger_d	:"{:$sys_app->config->d}"
			};
			$.phpok.opts = $.extend(defaults,options);
			if($.phpok.opts.js_path.substr(-1,1) != "/")
			{
				$.phpok.opts.js_path += "/";
			}
			//加载语言包
			$.getScript($.phpok.opts.js_path+"langs/"+$.phpok.opts.lang_id+".js",function(){$.phpok.lang = lang;});
		},
		opt: function(arguments_name){return $.phpok.opts[arguments_name];},
		ajax: function(turl,restype,func){
			if(!turl) return false;
			turl = turl.replace(/&amp;/g,"&");
			if(!restype || restype == "undefined") restype = "text";
			if(restype != "text" && restype != "xml" && restype != "json" && restype != "jsonp" && restype != "script" && restype != "html") restype = "text";
			if(!func || func == "undefined")
			{
				if(restype == "html" || restype == "text")
				{
					return $.ajax({url:turl,cache:false,async:false,dataType:restype}).responseText;
				}
				else if(restype == "xml")
				{
					return $.ajax({url:turl,cache:false,async:false,dataType:"xml",processData:false}).responseXML;
				}
				else
				{
					$.ajax({url:turl,cache:false,async:false,dataType:restype,success:function(msg){return msg;}});
				}
			}
			$.ajax({url:turl,cache:false,async:true,dataType:restype,success:function(msg){return func(msg);}});
		},
		//刷新菜单
		refresh: function(){
			$("#desktop_icons").hide();//清空现在的HTML布局
			var desktop_url = this.opts.ajax_file+"?"+this.opts.trigger_c+"=index&"+this.opts.trigger_f+'=reload_links';
			this.ajax(desktop_url,"html",$.phpok.desktop_html)
		},
		desktop_html: function(msg){
			if(msg){
				$.desktop.create_div("icons","none");
				$("#icons").html(msg);
				//判断是否是IE6
				if($.browser.msie && $.browser.version < 7)
				{
					$("#icons").find("img").each(function(i){
						$(this).attr("src","images/desktop_icons_default.gif");
					});
				}
				$.desktop.sort("icons","desktop_icons");
				$("#desktop_icons").show();
			}
		},
		//创建快捷方式
		shortcut: function(){
			this.quick_attr("add",this.lang.hotlink);
		},
		admin_pass: function(){
			var myUrl = this.opts.ajax_file+"?"+this.opts.trigger_c+"=mypass";
			$.desktop.win({
				"title":this.lang.change_admin_pass,
				"iframe_url":myUrl,
				"btn_max":false,
				"btn_min":false,
				"width":560,
				"height":300
			});
		},
		//开始菜单
		start: function(act){
			if(act == "show"){
				$("#desktop_startmenu").show("fast");
				var zindex = parseInt($("#desktop_start").css("z-index")) -1;
				$("#desktop_startmenu").css("z-index",zindex);
			} else if(act == "hide") {
				$("#desktop_startmenu").hide("slow");
			} else {
				if($("#desktop_startmenu").css("display") == "none"){
					$("#desktop_startmenu").show("fast");
					var zindex = parseInt($("#desktop_start").css("z-index")) -1;
					$("#desktop_startmenu").css("z-index",zindex);
				} else {
					$("#desktop_startmenu").hide("slow");
				}
			}
			return false;
		},
		//管理员退出
		logout: function(){
			var qc = confirm(this.lang.confirm_logout);
			if(qc == "0")
			{
				return false;
			}
			var myUrl = this.opts.ajax_file+"?"+this.opts.trigger_c+"=login&"+this.opts.trigger_f+"=ajax_logout";
			var msg = this.ajax(myUrl);
			if(msg == "ok")
			{
				alert(this.lang.logout_success);
				window.location.href = window.location.href;
			}
			else
			{
				alert(this.lang.logout_error);
				return false;
			}
		},
		//修改快捷建属性
		quick_attr: function(act,title,id){
			//执行编辑或是添加操作
			if(act == "modify" || act == "add"){
				var myUrl = this.opts.ajax_file + "?"+this.opts.trigger_c+"=hotlink&"+this.opts.trigger_f+"=set&";
				if(id && id != "undefined"){
					myUrl += "id="+id.toString();
				}
				$.desktop.win({"title":title,"iframe_url":myUrl});
			} else if(act == "delete"){
				var qc = confirm(this.lang.confirm_delete_hotlink+title);
				if(qc == "0"){
					return false;
				}
				var myUrl = this.opts.ajax_file + "?"+this.opts.trigger_c+"=hotlink&"+this.opts.trigger_f+"=del&id="+id;
				var msg = this.ajax(myUrl);
				if(msg == "ok"){
					alert(this.lang.hotlink_delete_ok);
					$.phpok.refresh();//刷新桌面
				} else {
					alert(this.lang.hotlink_delete_error);
					return false;
				}
			}

		},
		//右键创建热键
		shortcut_ajax: function(title,link){
			var myUrl = this.opts.ajax_file + "?"+this.opts.trigger_c+"=hotlink&"+this.opts.trigger_f+"=ajax&";
			if(!title)
			{
				alert(this.lang.hotlink_title_empty);
				return false;
			}
			if(!link)
			{
				alert(this.lang.hotlink_link_empty);
				return false;
			}
			myUrl += "&title="+title+"&linkurl="+link;
			var c = this.ajax(myUrl,"text");
			if(c == "ok")
			{
				alert(this.lang.hotlink_create_ok);
				this.refresh();
			}
			else
			{
				if(!c) c = "error:"+this.lang.hotlink_create_error;
				alert(c.replace("error:",""));
			}
		}
	};
})(jQuery);