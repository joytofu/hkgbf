/* Jquery 版本的 Video 处理 */

;(function($){

	$.media = {
		//初始化视播放器
		init: function(options)
		{
			var defaults = {
				url			:"",
				width		:"400",
				height		:"300",
				image		:"",
				site		:"",
				autoplay	:false,
				autoload	:false,
				id			:"", // 当系统指定ID时，直接插入数据，反之则返回代码数据
				class		:"video",
				type		:""		//强制指定类型时，系统将直接调用指定类型的脚本，而不进行判断
			};
			this.opts = $.extend(defaults,options);
			defaults = options = null;
			if(this.opts.url)
			{
				var tmp_u = (this.opts.url).substr(0,7).toLowerCase();
				if(tmp_u != "http://" && tmp_u != "https:/")
				{
					if(!this.opts.site)
					{
						this.opts.site = this.site_url();
					}
					tmp_u = this.opts.site + this.opts.url;
					this.opts.url = tmp_u;
				}
				tmp_u = null;
			}
			if(this.opts.image == "undefined")
			{
				this.opts.image = "";
			}
			//分析取得的文件格式类型
			if(!this.opts.type)
			{
				var tmpurl = (this.opts.url).split("?")[0];
				var start = tmpurl.lastIndexOf(".");
				var end = tmpurl.length;
				var type =tmpurl.substring(start+1,end).toLowerCase();
				this.opts.type = type;
				type = tmpurl = start = end = null;
			}

			//常用的播放器类型
			var linktype = new Array();
			//使用 windows media
			linktype['wma'] = 'wmp';
			linktype['mp3'] = 'wmp';
			linktype['wmv'] = 'wmp';
			linktype['asf'] = 'wmp';
			linktype['mpg'] = 'wmp';
			linktype['mpeg'] = 'wmp';
			linktype['avi'] = 'wmp';
			linktype['asx'] = 'wmp';
			linktype['dat'] = 'wmp';

			//使用 real media 
			linktype['rm'] = 'real';
			linktype['rmvb'] = 'real';
			linktype['ram'] = 'real';
			linktype['ra'] = 'real';

			//使用 flash及flv
			linktype['swf'] = 'flash';
			linktype['flv'] = 'flv';

			var chk_radio = linktype[this.opts.type];
			linktype = null;
			if(!chk_radio)
			{
				if(this.opts.id)
				{
					$("#"+id).html("");
				}
				else
				{
					return false;
				}
			}
			else
			{
				this.video(chk_radio);
			}
		},
		
		//取得当前网址
		site_url: function()
		{
			var site = window.location.protocol + "//" + window.location.host + "/";
			var pathname = window.location.pathname;
			if(pathname)
			{
				var array = pathname.split("/");
				var array_len = array.length - 1;
				for(var i=0;i<array_len;i++)
				{
					if(array[i])
					{
						site += array[i] + '/';
					}
				}
				array = array_len = i = null;
			}
			pathname = null;
			return site;
		},
		
		video: function(vtype)
		{
			var string = '';
			if(vtype == "flash")
			{
				string = "<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0' width='"+this.opts.width+"' height='"+this.opts.height+"'>";
				string += "<param name='movie' value='"+this.opts.url+"'>";
				string += "<param name='quality' value='high'>";
				string += "<embed src='"+this.opts.url+"' quality='high' pluginspage='http://www.macromedia.com/go/getflashplayer' type='application/x-shockwave-flash' width='"+this.opts.width+"' height='"+this.opts.height+"'></embed>";
				string += "</object>";
			}
			else if(vtype == "flv")
			{
				string = "<object type='application/x-shockwave-flash' data='images/vcastr.swf' width='"+this.opts.width+"' height='"+this.opts.height+"'>";
				string += "<param name='movie' value='images/vcastr.swf' />";
				string += "<param name='allowFullScreen' value='true' />";
				string += "<param name='FlashVars' value='xml={vcastr}{channel}{item}{source}"+this.opts.url+"{/source}{duration}{/duration}{title}{/title}{/item}{/channel}{config}{isAutoPlay}false{/isAutoPlay}{isLoadBegin}false{/isLoadBegin}{/config}{plugIns}{beginEndImagePlugIn}{url}images/image.swf{/url}{source}"+this.opts.image+"{/source}{type}beginend{/type}{scaletype}exactFil{/scaletype}{/beginEndImagePlugIn}{/plugIns}{/vcastr}'>";
				string += "</object>";
			}
			else if(vtype == "real")
			{
				string = "<object classid='clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA' width='"+this.opts.width+"' height='"+this.opts.height+"'>";
				string += "<param name='src' value='"+this.opts.url+"' />";
				string += "<param name='controls' value='Imagewindow' />";
				string += "<param name='console' value='clip1' />";
				string += "<param name='autostart' value='true' />";
				string += "<embed src='' type='audio/x-pn-realaudio-plugin' autostart='true' console='clip1' controls='Imagewindow' width='"+this.opts.width+"'height='"+this.opts.height+"' />";
				string += "</embed></object>";
			}
			else if(vtype == "wmp")
			{
				string = "<object classid='CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6' width='"+this.opts.width+"' height='"+this.opts.height+"'>";
				string += "<param name='url' value='"+this.opts.url+"' />";
				string += "<embed type='application/x-mplayer2' src='"+this.opts.url+"' width='"+this.opts.width+"' height='"+this.opts.height+"'></embed>";
				string += "</object>";
			}
			//如果有
			if(string && this.opts.id)
			{
				$("#"+this.opts.id).html(string);
				string = null;
			}
			else
			{
				return string;
			}
		}

	};

})(jQuery);