/*!
 * http://www.phpok.com/
 *
 * Copyright 2011, phpok.com
 * Released under the MIT, BSD, and LGPL Licenses.
 * 字符串编码，使用方法： $.str.encode(string);
 * 字符串合并，使用方法： $.str.join(str1,str2);
 *
 * Date: 2011-12-01 11:47
 */
;(function($){

	$.str = {
		join: function(str1,str2){
			if(str1 == "" && str2 == "" ) return false;
			if(str1 == "") return str2;
			if(str2 == "") return str1;
			var string = str1 + "," +str2;
			var array = string.split(",");
			array = $.unique(array);
			var string = array.join(",");
			return string ? string : false;
		},
		encode: function(s1){
			var s = escape(s1);
			var sa = s.split("%");
			var retV ="";
			if(sa[0] != "")
			{
				retV = sa[0];
			}
			for(var i = 1; i < sa.length; i ++)
			{
				if(sa[i].substring(0,1) == "u")
				{
					retV += this.StringHex2Utf8(this.Str2Hex(sa[i].substring(1,5)));
					if(sa[i].length>5)
					{
						retV += sa[i].substring(5);
					}
				}
				else
				{
					retV += "%" + sa[i];
				}
			}
			return retV;
		},
		
		StringHex2Utf8: function(s){
			var retS = "";
			var tempS = "";
			var ss = "";
			if(s.length == 16)
			{
				tempS = "1110" + s.substring(0, 4);
				tempS += "10" +  s.substring(4, 10);
				tempS += "10" + s.substring(10,16);
				var sss = "0123456789ABCDEF";
				for(var i = 0; i < 3; i ++)
				{
					retS += "%";
					ss = tempS.substring(i * 8, (eval(i)+1)*8);
					retS += sss.charAt(this.Dig2Dec(ss.substring(0,4)));
					retS += sss.charAt(this.Dig2Dec(ss.substring(4,8)));
				}
				return retS;
			}
			return "";
		},

		Dig2Dec: function(s){
			var retV = 0;
			if(s.length == 4)
			{
				for(var i = 0; i < 4; i ++)
				{
					retV += eval(s.charAt(i)) * Math.pow(2, 3 - i);
				}
				return retV;
			}
			return -1;
		},

		Dec2Dig: function(n1){
			var s = "";
			var n2 = 0;
			for(var i = 0; i < 4; i++)
			{
				n2 = Math.pow(2,3 - i);
				if(n1 >= n2)
				{
					s += '1';
					n1 = n1 - n2;
				}
				else
				{
					s += '0';
				}
			}
			return s;
		},

		Str2Hex: function(s){
			var c = "";
			var n;
			var ss = "0123456789ABCDEF";
			var digS = "";
			for(var i = 0; i < s.length; i ++)
			{
				c = s.charAt(i);
				n = ss.indexOf(c);
				digS += this.Dec2Dig(eval(n));
			}
			return digS;
		}
	};

	$.rawurlencode = function(str){
		return $.str.encode(str);
	}
})(jQuery);