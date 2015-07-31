// 由PHPOK编写的基于jQuery的桌面，支持虚弹，最大化，最小化，关闭，缩放等操作
// 支持展示部分的DIV内容及iFrame内容
// 本JS插件提供大量备注功能

;(function($){

	$.desktop = {
		//初始化参数信息
		init: function(options) {
			var defaults = {
				btn_max		:true,
				btn_min		:true,
				btn_close	:true,
				iframe_url	:"",
				out_link	:false,
				//设定要引用的JS路径
				js_path		:"js",
				img_path	:"images/desktop",
				win_lock	:false,//窗口锁定
				bgcolor		:"#fff",//锁定窗口时的背景颜色
				opacity		:"0.5",//透明度
				fixed		:false,//启用静止定位
				drag		:true,
				//窗口标题
				title		:"欢迎使用PHPOK3.x",
				resize		:true,
				loading		:'\u7A97\u53E3\u5185\u5BB9\u52A0\u8F7D\u4E2D\uFF0C\u8BF7\u7A0D\u7B49...',
				//位置
				left		:'center',
				top			:'center',
				taskbar		:true, //是否启用TaskBar
				//加入窗口的宽和高
				width		:"920",
				height		:"450"
			};
			//$.desktop.defaults = defaults;//设置初始化
			this.opts = $.extend(defaults, options);
			if(this.opts.js_path.substr(-1,1) != "/")
			{
				this.opts.js_path += "/";
			}
			if(this.opts.img_path.substr(-1,1) != "/")
			{
				this.opts.img_path += "/";
			}
			//判断是否是浏览器IE6
			this.ie6 = ($.browser.msie && $.browser.version < 7) ? true : false;
			this.zIndex = 1000;
			//释放内存
			defaults = null;
			options = null;
		},
		//返回指定的参数信息
		opt: function(arguments_name){return $.desktop.opts[arguments_name];},
		//重新设置opt参数
		opt_set: function(arguments_name,val){
			$.desktop.opts[arguments_name] = val;
		},
		//加载桌面背景，此桌面背景自动缩放，非平铺
		load_bg: function(picurl){
			var bg_width = $(window).width();
			var bg_height = $(window).height();
			this.create_div("desktop_bg","bg");
			$("#desktop_bg").css({"width":bg_width.toString()+"px","height":bg_height.toString()+"px"});
			//绑定桌面右键菜单选项
			var RightMenu = [[
				{
					text:"刷新",
					func:function(){$.phpok.refresh()}
				},{
					text:"新建快捷方式",
					func:function(){$.phpok.shortcut()}
				},{
					text:"修改管理密码",
					func:function(){$.phpok.admin_pass()}
				}
			]];
			$("#desktop_bg").smartMenu(RightMenu, {name: "desktop_bg"});
			RightMenu = null;
			//隐藏开始开菜
			$("#desktop_bg").click(function(){
				$("#desktop_startmenu").hide("fast");
			});
			$(window).resize(function(){
				$.desktop.load_bg();
			});
			if(!picurl || picurl == "undefined"){
				bg_width = bg_height = null;
				return true;
			}
			var img = new Image();
			img.src = picurl;
			if(img.complete)
			{
				var pic_width = img.width;
				var pic_height = img.height;
				var ratio = pic_height / pic_width;
				if ((bg_height/bg_width) > ratio)
				{
					var e_pic_height = bg_height;
					var e_pic_width = bg_height / ratio;
				}
				else
				{
					var e_pic_height = bg_width * ratio;
					var e_pic_width = bg_width;
				}
				var ihtml = "<img src='"+picurl+"' width='"+e_pic_width.toString()+"px' height='"+e_pic_height.toString()+"px' border='0' />";
				$("#desktop_bg").html(ihtml);
				pic_width = pic_height = ratio = bg_height = bg_width = e_pic_height = e_pic_width = null;
				img = null;
			}
			else
			{
				img.onload = function()
				{
					var pic_width = img.width;
					var pic_height = img.height;
					var ratio = pic_height / pic_width;
					if ((bg_height/bg_width) > ratio)
					{
						var e_pic_height = bg_height;
						var e_pic_width = bg_height / ratio;
					}
					else
					{
						var e_pic_height = bg_width * ratio;
						var e_pic_width = bg_width;
					}
					var ihtml = "<img src='"+picurl+"' width='"+e_pic_width.toString()+"px' height='"+e_pic_height.toString()+"px' border='0' />";
					$("#desktop_bg").html(ihtml);
					pic_width = pic_height = ratio = bg_height = bg_width = e_pic_height = e_pic_width = null;
					img = null;
				}
			}
		},
		//排列图标
		//from_id，取得HTML内容的来源信息
		//class_name，生成新的图标的样式
		sort: function(from_id,class_name){
			$("#"+from_id).hide();
			$.smartMenu.remove();
			//生成新的HTML重新插入
			var html = '<ul class="'+class_name+'">';
			$("#"+from_id).find("li").each(function(i){
				if(this.ie6) html += '<a href="#">';
				html += "<li id='"+from_id+"_"+i+"'>" + $(this).html() + "</li>";
				if(this.ie6) html += '</a>';
			});
			html += "</ul>";
			//重新整理新的桌面图片
			$.desktop.create_div("desktop_"+from_id,"icons");//创建任务栏
			$("#desktop_"+from_id).html(html);
			//自定义右键
			$("#"+from_id).find("li").each(function(i){
				var quick_id = $(this).attr("quick_id");
				var quick_name = $(this).attr("quick_name");
				var quick_url = $(this).attr("quick_url");
				var right_menu = [[
						{
							text:"打开",
							func: function(){$.desktop.win({"title":quick_name,"iframe_url":quick_url})}
						},{
							text:"快捷键属性",
							func:function(){$.phpok.quick_attr("modify","修改快捷方式",quick_id)}
						},{
							text:"删除快捷键",
							func: function(){$.phpok.quick_attr("delete",quick_name,quick_id)}
						}
				]];
				$("#"+from_id+"_"+i).smartMenu(right_menu,{name:from_id+"_"+i});
				//绑定双击动作
				$("#"+from_id+"_"+i).dblclick(function(){
					$.desktop.win({'title':quick_name,'iframe_url':quick_url});
				});
				//quick_id = quick_name = quick_url = right_menu = null;
			});
		},
		//在HTML中创建DIV信息，仅限HTML
		create_div: function(div_id,div_class){
			if($("#"+div_id).length>0)
			{
				if(!$("#"+div_id).hasClass(div_class))
				{
					$("#"+div_id).addClass(div_class);
				}
			}
			else
			{
				var c_div = $("<div></div>");
				c_div.attr("id",div_id);
				c_div.addClass(div_class);
				c_div.appendTo("body");
				c_div = null;
			}
		},
		win: function(opts){
			var my_opts = $.extend({},this.opts,opts);
			//重新计算高试，防止超过浏览器的最大高度
			if( parseInt(my_opts.height,10) > ($(window).height - 30))
			{
				my_opts.height = $(window).height - 30;
			}
			var dlist = this.get_desktop_list();
			var d_count = dlist.length;
			if(d_count>0){
				//计算Tab的宽度
				var taskbar_width = $("#desktop_taskbar").width() - 230;
				var tab_width = 140 * (d_count+2);
				if(tab_width>taskbar_width)
				{
					alert("桌面标签太多了，请先关闭一些不用的标签！\n\n抱歉，由于水平不够，暂时无法提供滑动标签功能！");
					taskbar_width = tab_width = null;
					return false;
				}
				taskbar_width = tab_width = null;
			}

			this.divid = $.desktop.create_div_id();
			var new_html = this.dialog(this.divid,my_opts);
			$(new_html).appendTo("body");
			//计算Loading里的DIV宽高
			var load_height = my_opts.height - $("#desktop_title_"+this.divid).height() -10;
			$("#load_"+this.divid).css({"height":load_height+"px","line-height":load_height+"px"});
			$("#desktop_body_"+this.divid).css("height",load_height+"px");
			this.zindex(this.divid);
			this.position(this.divid,my_opts);
			this.set_dialog(this.divid);//设置该标签下允许的鼠标触发
			this.end_loading(this.divid);//当Loading结束后，
			//在导航栏中增加一个Tab标签
			if($("#desktop_taskbar").html() != "" && this.opts.taskbar)
			{
				this.create_tab(this.divid,my_opts.title);
			}
			my_opts = dlist = d_count = new_html = load_height = null;
		},
		end_loading: function(divid){
			var func_iframe = $("#desktop_iframe_"+divid);
			$("#desktop_iframe_"+divid).load(function(){
				$("#load_"+divid).fadeOut("fast",function(){
					//计算iframe里的高度
					$("#desktop_iframe_"+divid).fadeIn("fast");
					$.desktop.set_index(divid);
				});
				if($.desktop.ie6)
				{
					var arg = $.desktop.format_arg(divid);
					if(!arg["ismax"] || parseInt(arg["ismax"])<1)
					{
						$.desktop.action("max",divid);
					}
					return false;
				}
				var height = $(this).contents().find("body").height() + 5;
				if(height && height>0)
				{
					//计算Arg
					var arg = $.desktop.format_arg(divid);
					if(arg["ismax"] && parseInt(arg["ismax"])>0)
					{
						//--当系统最大化时，不执行计算脚本
					}
					else
					{
						if(height > ($(window).height() - $("#desktop_taskbar").height() - $("#desktop_title_"+divid).height() -10))
						{
							height = $(window).height() - $("#desktop_taskbar").height() - $("#desktop_title_"+divid).height() -10;
							arg["top"] = 0;
							$("#desktop_"+divid).css("top","0px");
						}
						else
						{
							if(height<300)
							{
								height = 300;
							}
						}
						arg["height"] = height + $("#desktop_title_"+divid).height() + 10;
						$.desktop.string_arg(arg,divid);
						$("#desktop_"+divid).css("height",arg["height"]+"px");
						$("#desktop_body_"+divid).css("height",height.toString()+"px");
						$("#load_"+divid).css({"height":height+"px","line-height":height+"px"});
						height = arg = load_height = null;
					}
				}
				/*$("#load_"+divid).fadeOut("fast",function(){
					//计算iframe里的高度
					$("#desktop_iframe_"+divid).fadeIn("fast");
					$.desktop.set_index(divid);
				});*/
			});
		},
		//定义div的层位置
		zindex: function(divid){
			$("#desktop_"+divid).css("z-index",this.zIndex++);
		},
		position: function(divid,opts){
			if(!opts) opts = this.opts;
			var div_x,div_y;
			//定义X轴信息
			if(opts.left == "center"){
				div_x = parseInt(($(window).width() - opts.width)/2);
			} else if (opts.left == "left") {
				div_x = 0;
			} else if (opts.left == "right") {
				div_x = parseInt($(window).width() - opts.width);
			} else {
				div_x = parseInt(opts.left);
			}
			//定义Y轴信息
			if(opts.top == "center"){
				div_y = parseInt(($(window).height() - opts.height)*(1-0.618));
			} else if(opts.top == "top") {
				div_y = 0;
			} else if(opts.top == "bottom") {
				div_y = parseInt($(window).height() - opts.height);
			} else {
				div_y = parseInt(opts.top);
			}
			//计算当前有多少窗口
			var dlist = this.get_desktop_list();
			var d_count = dlist.length;
			if(d_count>0){
				div_x = div_x + (d_count-1) * 30;
				div_y = div_y + (d_count-1) * 30;
			}
			$("#desktop_"+divid).css({"left":div_x.toString()+"px","top":div_y.toString()+"px"});
			var arg = this.format_arg(divid);
			arg["left"] = div_x.toString();
			arg["top"] = div_y.toString();
			this.string_arg(arg,divid);
			div_x = div_y = dlist = d_count = arg = null;
		},
		show_desktop: function(){
			$(".desktop").hide();
			$.desktop.set_index();
		},
		//最大化，最小化，关闭动作
		action: function(act,divid){
			if(act == "close"){
				$("#desktop_"+divid).fadeOut("fast",function(){
					$("#desktop_"+divid).remove();
					$("#tab_"+divid).remove();
					$.desktop.set_index();
				});
			} else if(act == "min") {
				$("#desktop_"+divid).fadeOut("fast",function(){
					$("#desktop_"+divid).hide("fast",function(){
						$.desktop.set_index();
					});
				});
			} else if(act == "refresh") {
				window.frames['desktop_iframe_'+divid].location.reload();
			} else if(act == "add") {
				var arg = this.format_arg(divid);
				if(arg["ismax"] && parseInt(arg["ismax"],10) > 0)
				{
					arg = null;
					return false;
				}
				else
				{
					var this_width = $(window).width();
					var this_height = $(window).height() - $("#desktop_taskbar").height();
					var width = (parseInt(arg["width"]) + 50)>=this_width ? this_width : (parseInt(arg["width"]) + 50);
					var height = (parseInt(arg["height"]) + 50)>=this_height ? this_height : (parseInt(arg["height"]) + 50);
					if(width == this_width && height == this_height)
					{
						this.action("max",divid);
					}
					else
					{
						arg["width"] = width;
						arg["height"] = height;
						this.string_arg(arg,divid);
						$("#desktop_"+divid).css({
							"width":width.toString()+"px",
							"height":height.toString()+"px"
						});
						var load_height = height - $("#desktop_title_"+divid).height() -10;
						$("#desktop_body_"+divid).css("height",load_height.toString()+"px");
						$("#load_"+divid).css({"height":load_height+"px","line-height":load_height+"px"});
						load_height = null;
					}
					arg = this_width = this_height = width = height = null;
				}
			} else if(act == "reduce") {
				var arg = this.format_arg(divid);
				if(arg["ismax"] && parseInt(arg["ismax"])>0)
				{
					if(this.ie6)
					{
						alert("IE6浏览器不支持缩放！");
						return false;
					}
					$("#max_"+divid).removeClass("btn_rev").addClass("btn_max");
					var this_width = $(window).width();
					var this_height = $(window).height() - $("#desktop_taskbar").height();
					var width = this_width - 50;
					var height = this_height - 50;
					this_width = this_height = null;
				}
				else
				{
					var width = (parseInt(arg["width"]) - 50) < this.opts.width ? this.opts.width : (parseInt(arg["width"]) - 50);
					var height = (parseInt(arg["height"]) - 50) < this.opts.height ? this.opts.height : (parseInt(arg["height"]) - 50);
				}
				arg["ismax"] = 0;
				arg["width"] = width;
				arg["height"] = height;
				this.string_arg(arg,divid);
				$("#desktop_"+divid).css({
					"width":width.toString()+"px",
					"height":height.toString()+"px"
				});
				var load_height = height - $("#desktop_title_"+divid).height() -10;
				$("#desktop_body_"+divid).css("height",load_height.toString()+"px");
				$("#load_"+divid).css({"height":load_height+"px","line-height":load_height+"px"});
				load_height = arg = width = height = null;
			} else if(act == "max") {
				var arg = this.format_arg(divid);
				//如果当前窗口是最大化
				if(arg["ismax"] && parseInt(arg["ismax"],10) > 0)
				{
					if(this.ie6)
					{
						alert("IE6浏览器不支持缩放！");
						return false;
					}
					var this_width = arg['width'];
					var this_height = arg["height"];
					arg["ismax"] = "0";
					this.string_arg(arg,divid);
					var body_height = this_height - $("#desktop_title_"+divid).height() - 10;
					$("#desktop_body_"+divid).css("height",body_height.toString()+"px");
					$("#desktop_"+divid).css({"left":arg["left"]+"px","top":arg["top"]+"px"});
					$("#max_"+divid).removeClass("btn_rev").addClass("btn_max");
				}
				else
				{
					//计算当前窗口的大小
					var this_width = $(window).width();
					var this_height = $(window).height() - $("#desktop_taskbar").height();
					arg["ismax"] = "1";
					this.string_arg(arg,divid);
					//计算内容的高度
					var body_height = this_height - $("#desktop_title_"+divid).height() - 10;
					$("#desktop_body_"+divid).css("height",body_height.toString()+"px");
					$("#desktop_"+divid).css({"left":"0","top":"0"});
					$("#max_"+divid).removeClass("btn_max").addClass("btn_rev");
				}
				//更新存储参数
				$("#desktop_"+divid).css({
					"width":this_width.toString()+"px",
					"height":this_height.toString()+"px"
				});
				body_height = arg = this_width = this_height = null;
			}
		},
		//将参数组成字串
		string_arg: function(arg,divid){
			var m = new Array();
			var i=0;
			for (var key in arg){
				m[i] = key.toString() + "=" + arg[key];
				i++;
			}
			var string = m.join(";");
			if(divid && divid != "undefined")
			{
				$("#desktop_arg_"+divid).html(string);
			}
			return string;
		},
		//格式化参数
		format_arg: function(divid){
			var arg = new Array();
			var tmp_arg = $("#desktop_arg_"+divid).html();
			if(tmp_arg)
			{
				var t = tmp_arg.split(";");
				for(var i=0;i<t.length;i++)
				{
					var t_sp = t[i].split("=");
					arg[t_sp[0]] = t_sp[1];
				}
			}
			else
			{
				//计算当前窗口的窗和高
				arg["width"] = $("#desktop_"+divid).width();
				arg["height"] = $("#desktop_"+divid).height();
				//计算当前的窗口
				if($(window).width() == arg["width"] && ($(window).height()-30) == arg["height"])
				{
					arg["ismax"] = "1";
					arg["width"] = $.desktop.opts.width;
					arg["height"] = $.desktop.opts.height;
				}
				else
				{
					arg["ismax"] = "0";
				}
			}
			return arg;
		},
		//设置面板中常用到的动作
		set_dialog: function(divid){
			$("#desktop_"+divid).click(function(){
				$.desktop.set_index(divid);
			});
			$("#min_"+divid).click(function(){
				 $.desktop.action('min',divid);
			});
			$("#max_"+divid).click(function(){
				 $.desktop.action('max',divid);
			});
			$("#cls_"+divid).click(function(){
				 $.desktop.action('close',divid);
			});
			$("#refresh_"+divid).click(function(){
				 $.desktop.action('refresh',divid);
			});
			$("#add_"+divid).click(function(){
				 $.desktop.action('add',divid);
			});
			$("#reduce_"+divid).click(function(){
				 $.desktop.action('reduce',divid);
			});
			//绑定双击
			$("#desktop_title_"+divid+" .left").dblclick(function(){
				$.desktop.action("max",divid);
			});
			var _move=false;//移动标记
			var _x,_y;//鼠标离控件左上角的相对位置
			$("#desktop_title_"+divid).click(function(){
				//alert("click");//点击（松开后触发）
			}).mousedown(function(e){
				$.desktop.set_index(divid);
				var arg = $.desktop.format_arg(divid);
				if(arg["ismax"] == "0")
				{
					_move=true;
				}
				_x=e.pageX-parseInt($("#desktop_"+divid).css("left"));
				_y=e.pageY-parseInt($("#desktop_"+divid).css("top"));
				//加载并变灰
			});
			$(document).mousemove(function(e){
				if(_move){
					var x=e.pageX-_x;//移动时根据鼠标位置计算控件左上角的绝对位置
					var y=e.pageY-_y;
					//当x y 小于0时，强制为0
					if(y<0){y=0}
					if(x<0){x=0}
					//如果x y 大于某值时，则锁定不让移动
					var max_width = $(window).width();
					var max_height = $(window).height() - 30;
					if((max_width -100)<x)
					{
						x = max_width -100;
					}
					if((max_height-22)<y)
					{
						y = max_height -22;
					}
					$("#desktop_"+divid).css({top:y.toString()+"px",left:x.toString()+"px"});//控件新位置
					var arg = $.desktop.format_arg(divid);
					arg["left"] = (parseInt(x,10)).toString();
					arg["top"] = (parseInt(y,10)).toString();
					$.desktop.string_arg(arg,divid);
					$("#load_"+divid).fadeIn();
					y = x = max_width = max_height = arg = null;
				}
			}).mouseup(function(){
				_move=false;
				$("#load_"+divid).fadeOut();
			});
		},
		//创建Tab桌面标签
		dialog: function(divid,opts){

			var ie6html = '';
			var iframe_scroll = "auto";
			if(this.ie6)
			{
				ie6html += '<iframe style="position:absolute;z-index:-1;width:100%;height:100%;left:0;top:0;"><\/iframe>';
				iframe_scroll = "yes";
			}
			var innerDoc = '<iframe id="desktop_iframe_' + divid + '" name="desktop_iframe_' + divid + '"';
			innerDoc += ' frameborder="0" src="' + opts.iframe_url + '" scrolling="'+iframe_scroll+'"';
			innerDoc += ' style="width:100%;height:100%;z-index:1;overflow-x:hidden;overflow-y:auto;"></iframe>';
			var dialogTpl = [
				"<div class='desktop' id='desktop_", divid, "' style='width:", opts.width, "px;height:", opts.height,"px;'>",
					'<div class="box">',
						'<div class="title" id="desktop_title_',divid,'">',
							'<table width="100%" cellpadding="0" cellspacing="0" border="0"><tr>',
								'<td class="left" onselectstart="javascript:return false;">&nbsp;',opts.title,'</td>',
								'<td class="right" valign="top">',
									'<a class="btn btn_refresh" id="refresh_',divid,'" target="_self" href="#" title="刷新，如果正在提交数据，请不要点此按钮！"></a>',
									'<a class="btn btn_add" id="add_',divid,'" target="_self" href="#" title="增加页面宽高"></a>',
									'<a class="btn btn_reduce" id="reduce_',divid,'" target="_self" href="#" title="减小页面宽高"></a>',
									'<a class="btn btn_min" id="min_',divid,'" target="_self" href="#" title="最小化"></a>',
									'<a class="btn btn_max" id="max_',divid,'" target="_self" href="#" title="最大化"></a>',
									'<a class="btn btn_close" id="cls_',divid,'" target="_self" href="#" title="关闭窗口"></a>',
								'</td>',
							'</tr></table>',
						'</div>',
						'<div class="content_w" id="desktop_body_',divid,'">',
							"<div class='content' id='desktop_content_",divid,"'>",
								innerDoc,'<div id="load_', divid, '" class="loading"><span>', opts.loading, '</span></div>',
							"</div>",
						'</div>','<div id="desktop_arg_',divid,'" style="display:none;">width=',opts.width,';height=',opts.height,';ismax=0</div>',
					'</div>',ie6html,
				'</div>'
			].join('');
			iframe_scroll = ie6html = innerDoc = null;
			return dialogTpl;
		},
		//创建一个Tab标签，取得相应的li信息
		create_tab: function(tabid,title){
			var taskbar_width = $("#desktop_taskbar").width() - 230;
			if(tabid && tabid != "undefined" && $("#tab_"+tabid).length<1)
			{
				var tmp = "<li id='tab_"+tabid+"' class='tab_out' onclick='$.desktop.tab_action(\""+tabid+"\")'><a title='"+title+"'>"+title+"</a></li>";
				$("#desktop_taskbar .tablist").append(tmp);
			}
		},
		//Tab控制操作，仅限左键
		tab_action: function(tid){
			var this_max_id = this.get_max_id();//取得当前桌面的最上一层ID
			if($("#desktop_"+tid).is(":hidden")){
				this.set_index(tid);
			} else {
				$("#desktop_"+tid).fadeOut("fast").hide("fast",function(){$.desktop.set_index()});
			}
		},
		//设置当前项目
		set_index: function(tid){
			if(!tid || tid == "undefined")
			{
				tid = this.get_max_id();
				if(!tid || tid == "undefined")
				{
					return false;
				}
			}
			this.zindex(tid);
			var desktop_list = this.get_desktop_list();//取得桌面的ID列表
			//整理桌面显示样式
			for(var i=0; i<desktop_list.length;i++){
				if(desktop_list[i] != tid) {
					$("#load_"+desktop_list[i]).fadeIn("fast",function(){
						$(this).animate({"opacity":"0"});
					});
					$("#tab_"+desktop_list[i]).removeClass("tab_over").addClass("tab_out");
				} else {
					$("#load_"+tid).fadeOut();
					$("#desktop_"+tid).show();
					$("#tab_"+tid).removeClass("tab_out").addClass("tab_over");
				}
			}
			//每执行一次，将task菜单栏及开始按钮增加一层
			$("#desktop_start").css("z-index",(parseInt($("#desktop_start").css("z-index"),10) +1).toString());
			$("#desktop_taskbar").css("z-index",(parseInt($("#desktop_taskbar").css("z-index"),10) +1).toString());
			//隐藏开始菜单
			$.phpok.start("hide");
		},
		//取得当前桌面的ID列表
		get_desktop_list: function(){
			$.desktop.id_list = new Array();
			$(".desktop").each(function(i){
				$.desktop.id_list[i] = $(this).attr("id").replace("desktop_","");
			});
			return $.desktop.id_list;
		},
		//取得桌面当前最上位层的ID
		get_max_id: function(divid){
			//当没有取得桌面列表时，尝试读取当前桌面的ID列表
			if(!this.id_list || this.id_list.length<1){
				this.get_desktop_list();
			}
			//当桌面列表数量为空时，返回为空
			if(this.id_list.length<1){
				return false;
			}
			//分拣出需要的桌面列表
			var zindex_list = new Array();
			var zindex_id_list = new Array();
			var m = 0;
			for(var i=0; i<this.id_list.length; i++){
				var obj_desktop = $("#desktop_"+this.id_list[i]);
				if(obj_desktop.css("display") != "none")
				{
					zindex_list[m] = obj_desktop.css("z-index");
					zindex_id_list[m] = this.id_list[i];
					m++;
				}
			}
			//如果取得的数组小于1，则返回为空
			if(zindex_list.length<1) return false;
			//对比，取得最大值
			var max_zindex = zindex_list[0];//层级
			var max_zindex_id = zindex_id_list[0];//当前层的ID
			for(var i=0; i<zindex_list.length; i++){
				if(zindex_list[i]>max_zindex){
					max_zindex = zindex_list[i];
					max_zindex_id = zindex_id_list[i];//取得当前最大层的ID
				}
			}
			//如果max_zindex为空时
			if(!max_zindex || max_zindex == "undefined") return false;
			return max_zindex_id;
		},
		//关闭窗口
		cancel: function(tid){
			$("#tab_"+tid).remove();
		},
		//根据参数生成一个唯一的ID值
		create_div_id: function(){
			var str = (Math.random()).toString();
			return str.replace(".","");
		},
		quick_icons: function(){
			$.phpok.refresh();
		}
	};
})(jQuery);