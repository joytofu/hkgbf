// 由PHPOK整理重新编写的常见的input属性操作
;(function($){

	$.input = {
		//全选，调用方法：$.input.checkbox_all(id);
		checkbox_all: function(id){
			var t = id && id != "undefined" ? $("#"+id+" input[type*=checkbox]") : $("input[type*=checkbox]");
			t.each(function(){$(this).attr("checked",true);});
			t = null;
		},
		//全不选，调用方法：$.input.checkbox_none(id);
		checkbox_none: function(id){
			var t = id && id != "undefined" ? $("#"+id+" input[type*=checkbox]") : $("input[type*=checkbox]");
			t.each(function(){$(this).attr("checked",false);});
			t = null;
		},
		//每次选5个（total默认值为5） $.input.checkbox_not_all(id,5);
		checkbox_not_all: function(id,total){
			var t = id && id != "undefined" ? $("#"+id+" input[type*=checkbox]") : $("input[type*=checkbox]");
			var num = 0;
			if(!total || parseInt(total)<5) total = 5;
			t.each(function(){
				if($(this).attr("checked") != true && num<total)
				{
					$(this).attr("checked",true);
					num++;
				}
			});
			t = num = total = null;
		},
		//反选，调用方法：$.input.checkbox_anti(id);
		checkbox_anti: function(id){
			var t = id && id != "undefined" ? $("#"+id+" input[type*=checkbox]") : $("input[type*=checkbox]");
			t.each(function(){if($(this).attr("checked") == true){$(this).attr("checked",false);}else{$(this).attr("checked",true);}});
			t = null;
		},

		//合并复选框值信息，以英文逗号隔开
		checkbox_join: function(id,type){
			var cv = id && id != "undefined" ? $("#"+id+" input[type*=checkbox]") : $("input[type*=checkbox]");
			var idarray = new Array();
			var m = 0;
			cv.each(function()
			{
				if(type == "all"){idarray[m] = $(this).val();m++;}
				else if(type == "unchecked")
				{
					if($(this).attr("checked") == false){idarray[m] = $(this).val();m++;}
				}
				else
				{
					if($(this).attr("checked") == true){idarray[m] = $(this).val();m++;}
				}
			});
			var tid = idarray.join(",");
			cv = idarray = m = null;
			return tid;
		}

	};

})(jQuery);