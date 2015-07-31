<?php
/***********************************************************
	Filename: libs/system/phpok_input.php
	Note	: 扩展字段后台生成平台
	Version : 3.0
	Author  : qinggan
	Update  : 2009-11-23
***********************************************************/
class phpok_input_lib
{
	var $itype = "text";
	var $iclass = "";
	#[设置要生成的HTML文本属性的参数]
	function __construct()
	{
		$this->itype = "text";
		$this->iclass = "";
	}

	function PHPOK_INPUT()
	{
		$this->__construct();
	}

	function get_html($array)
	{
		if(!array_key_exists($array["input"],$this->InputType()))
		{
			return false;
		}
		$varname = "_".strtoupper($array["input"]);
		//判断是否有插件替换
		$plugin = load_plugin("phpok_input_lib:".$varname,$array,true);
		if($plugin)
		{
			return $plugin;
		}
		return $this->$varname($array);
	}

	function _TEXT($array)
	{
		$msg = $this->_LEFT_($array);
		$msg.= "<td><input type='text' name='".$array["identifier"]."' id='".$array["identifier"]."' ";
		$msg.= " value='".$array["default_val"]."'";
		$width = $array["width"] ? "width:".$array["width"].";" : "";
		if($width)
		{
			$msg.= " style='".$width."'";
		}
		$msg .= "></td>";
		$msg .= $this->_RIGHT_($array);
		return $msg;
	}

	function _TIME($array)
	{
		$msg = $this->_LEFT_($array);
		$msg.= "<td><input type='text' name='".$array["identifier"]."' id='".$array["identifier"]."' ";
		if($array["default_val"])
		{
			$msg.= " value='".date("Y-m-d H:i",$array["default_val"])."'";
		}
		$width = $array["width"] ? "width:".$array["width"].";" : "";
		if($width)
		{
			$msg.= " style='".$width."'";
		}
		$msg .= ' onfocus="show_date(\''.$array["identifier"].'\',true);"';
		$msg .= " /></td>";
		$msg .= $this->_RIGHT_($array);
		return $msg;
	}

	function _SIMG($array)
	{
		$msg = $this->_LEFT_($array);
		$msg.= "<td><input type='text' name='".$array["identifier"]."' id='".$array["identifier"]."' ";
		$msg.= " value='".$array["default_val"]."' class='long_input clue_on' readonly";
		$msg.= "></td>";
		$msg.= "<td>&nbsp;</td>";
		$msg.= '<td><input type="button" class="btn2" value="选择" onclick="phpjs_onepic(\''.$array["identifier"].'\')"></td>';
		$msg.= "<td>&nbsp;</td>";
		$msg.= '<td><input type="button" class="btn2" value="预览" onclick="phpjs_onepic_view(\''.$array["identifier"].'\')"></td>';
		$msg.= "<td>&nbsp;</td>";
		$msg.= '<td><input type="button" class="btn2" value="清空" onclick="phpjs_onepic_clear(\''.$array["identifier"].'\')"></td>';
		$msg .= $this->_RIGHT_($array);
		return $msg;
	}

	function _MODULE($array)
	{
		$msg = $this->_LEFT_($array);
		$msg.= "<td valign='top'><input type='hidden' name='".$array["identifier"]."' id='".$array["identifier"]."' ";
		$msg.= " value='".$array["default_val"]."'>";
		$msg.= "<div id='".$array["identifier"]."_tmp_show'></div>";
		$msg.= "</td>";
		$msg.= "<td>&nbsp;</td>";
		$msg.= '<td valign="top"><input type="button" class="btn2" value="选择" onclick="phpjs_module(\''.$array["identifier"].'\',\''.$array['link_id'].'\')"></td>';
		$msg.= "<td>&nbsp;</td>";
		$msg.= '<td valign="top"><input type="button" class="btn2" value="清空" onclick="phpjs_module_clear(\''.$array["identifier"].'\')"></td>';
		$msg .= $this->_RIGHT_($array);
		return $msg;
	}

	function _SELECT($array)
	{
		$msg = $this->_LEFT_($array);
		$msg.= "<td><select name='".$array["identifier"]."' id='".$array["identifier"]."'>";
		$array["list_val"] = str_replace("\r","",$array["list_val"]);
		$tmparray = explode("\n",$array["list_val"]);
		if(!$tmparray)
		{
			$tmparray = array();
		}
		foreach($tmparray AS $key=>$value)
		{
			$v = explode(",",$value);
			if(!$v[1]) $v[1] = $v[0];
			$msg .= "<option value='".$v[0]."'";
			if($v[0] == $array["default_val"])
			{
				$msg .= " selected";
			}
			$msg .= ">".$v[1]."</option>";
		}
		$msg .= "</select></td>";
		$msg .= $this->_RIGHT_($array);
		return $msg;
	}

	function _RADIO($array)
	{
		$msg = $this->_LEFT_($array);
		$msg.= "<td>";
		$array["list_val"] = str_replace("\r","",$array["list_val"]);
		$tmparray = explode("\n",$array["list_val"]);
		if(!$tmparray)
		{
			$tmparray = array();
		}
		$msg.= "<table cellpadding='0' cellspacing='0'><tr>";
		foreach($tmparray AS $key=>$value)
		{
			$v = explode(",",$value);
			if(!$v[1]) $v[1] = $v[0];
			$msg .= "<td>";
			$msg .= "<input type='radio' name='".$array["identifier"]."' value='".$v[0]."'";
			if($v[0] == $array["default_val"])
			{
				$msg .= " checked";
			}
			$msg .= "> ".$v[1]." &nbsp; ";
			$msg .= "</td>";
			if(($key+1)%4 == "")
			{
				$msg .= "</tr><tr>";
			}
		}
		$msg .= "</tr></table>";
		$msg .= "</td>";
		$msg .= $this->_RIGHT_($array);
		return $msg;
	}

	function _CHECKBOX($array)
	{
		$msg = $this->_LEFT_($array);
		$msg.= "<td>";
		$array["identifier"] = str_replace("[]","",$array["identifier"]);#去除[]
		$array["list_val"] = str_replace("\r","",$array["list_val"]);
		$tmparray = explode("\n",$array["list_val"]);
		if(!$tmparray)
		{
			$tmparray = array();
		}
		$tmpvalue = explode(",",$array["default_val"]);
		if(!$tmpvalue)
		{
			$tmpvalue = array();
		}
		$msg.= "<table cellpadding='0' cellspacing='0'><tr>";
		foreach($tmparray AS $key=>$value)
		{
			$v = explode(",",$value);
			if(!$v[1]) $v[1] = $v[0];
			$msg .= "<td>";
			$msg .= "<input type='checkbox' name='".$array["identifier"]."[]' value='".$v[0]."'";
			if(in_array($v[0],$tmpvalue))
			{
				$msg .= " checked";
			}
			$msg .= "> ".$v[1]." &nbsp; ";
			$msg .= "</td>";
			if(($key+1)%4 == "")
			{
				$msg .= "</tr><tr>";
			}
		}
		$msg .= "</tr></table>";
		$msg .= "</td>";
		$msg .= $this->_RIGHT_($array);
		return $msg;
	}

	function _TEXTAREA($array)
	{
		$msg = $this->_LEFT_($array);
		$msg.= "<td>";
		$msg.= "<textarea style='width:".$array["width"].";height:".$array["height"]."'";
		$msg.= " name='".$array["identifier"]."' id='".$array["identifier"]."'>".$array["default_val"]."</textarea>";
		$msg.= "</td>";
		$msg.= $this->_RIGHT_($array);
		return $msg;
	}

	function _IMG($array)
	{
		$msg = $this->_LEFT_($array);
		$msg.= "<input type='hidden' name='".$array["identifier"]."' id='".$array["identifier"]."' value='".$array["default_val"]."'>";
		$msg.= "<td id='_view_".$array["identifier"]."'><img src='images/nopic.gif' border='0' /></td>";
		$msg .= $this->_RIGHT_($array);
		return $msg;
	}

	function _DOWNLOAD($array)
	{
		$msg = $this->_LEFT_($array);
		$msg.= "<input type='hidden' name='".$array["identifier"]."' id='".$array["identifier"]."' value='".$array["default_val"]."'>";
		$t_msg = "<input type='button' class='btn2' value='选择' onclick=\"phpjs_".$array["input"]."('".$array["identifier"]."','_view_".$array["identifier"]."')\"> ";
		$t_msg .= "<input type='button' class='btn2' value='取消' onclick=\"phpjs_clear_".$array["input"]."('".$array["identifier"]."','_view_".$array["identifier"]."');\"> ";
		$t_msg .= "<span id='_view_".$array["identifier"]."'></span>";
		$msg.= "<td>".$t_msg."</td>";
		$msg .= $this->_RIGHT_($array);
		return $msg;
	}

	//影音
	function _VIDEO($array)
	{
		$msg = $this->_LEFT_($array);
		$msg.= "<input type='hidden' name='".$array["identifier"]."' id='".$array["identifier"]."' value='".$array["default_val"]."'>";
		$t_msg = "<input type='button' class='btn2' value='选择' onclick=\"phpjs_".$array["input"]."('".$array["identifier"]."','_view_".$array["identifier"]."')\"> ";
		$t_msg .= "<input type='button' class='btn2' value='取消' onclick=\"phpjs_clear_".$array["input"]."('".$array["identifier"]."','_view_".$array["identifier"]."');\"> ";
		$t_msg .= "<span id='_view_".$array["identifier"]."'></span>";
		$msg.= "<td>".$t_msg."</td>";
		$msg .= $this->_RIGHT_($array);
		return $msg;
	}

	//可视化编辑器
	function _EDIT($array)
	{
		if(!$array["if_html"])
		{
			return $this->_TEXTAREA($array);
		}
		$msg = $this->_LEFT_($array);
		//$toolbar = (intval($array["height"])>0 && intval($array["height"])<300) ? "Basic" : "Default";
		$show_html = $array["show_html"] ? true : false;
		$height = $array["height"] ? $array["height"] : "370px";
		$width = $array["width"] ? $array["width"] : "690px";
		$array["default_val"] = $this->format_edit_msg($array["default_val"]);
		$fckeditor = $this->_FckEditor_($array["identifier"],$array["default_val"],$height,$width,$show_html);
		$msg.= "<td>".$fckeditor."</td>";
		$msg .= $this->_RIGHT_($array);
		return $msg;
	}

	function format_edit_msg($msg)
	{
		if(!$msg)
		{
			return false;
		}
		if(function_exists("base_url"))
		{
			$url = base_url();
		}
		else
		{
			$app = sys_init();
			if($app->config["siteurl"])
			{
				$url = $app->config["siteurl"];
			}
			else
			{
				return false;
			}
		}
		$imgArray = array();
		preg_match_all("/src=[\"|'| ]((.*)\.(gif|jpg|jpeg|bmp|png|swf))/isU",$msg,$imgArray);
		$imgArray = array_unique($imgArray[1]);
		$count = count($imgArray);
		if($count < 1)
		{
			return $msg;
		}
		foreach($imgArray AS $key=>$value)
		{
			$value = trim($value);
			if(strpos($value,"http://") === false && $value)
			{
				$msg = str_replace($value,$url.$value,$msg);
			}
		}
		return $msg;
	}

	function _OPT($array)
	{
		if(!$array["link_id"])
		{
			return false;
		}
		$msg = $this->_LEFT_($array);
		$msg.= "<input type='hidden' name='".$array["identifier"]."' id='".$array["identifier"]."' value='".$array["default_val"]."'>";
		$msg.= "<td id='_opt_parent_".$array["identifier"]."'></td><td id='_opt_son_".$array["identifier"]."' style='padding-left:3px;'></td>";
		$msg .= $this->_RIGHT_($array);
		return $msg;
	}

	function InputType()
	{
		$fields["text"] = "文本框";
		$fields["radio"] = "单选框";
		$fields["checkbox"] = "复选框";
		$fields["textarea"] = "文本区域";
		$fields["edit"] = "可视化编辑器";
		$fields["select"] = "下拉菜单";
		$fields["img"] = "图片选择器";
		$fields["video"] = "影音选择器";
		$fields["download"] = "下载框选择器";
		$fields["opt"] = "联动选择";
		$fields["simg"] = "图片选择器（单选）";
		$fields["module"] = "内联模块";
		$fields["time"] = "时间戳";
		return $fields;
	}

	function _LEFT_($array)
	{
		$msg = "<div class='table'>";
		$msg.= "<div class='left'>";
		$array["sub_left"] = $array["sub_left"] ? str_replace("：","",$array["sub_left"]) : $array["title"];
		$array["sub_left"] = str_replace(":","",$array["sub_left"]);
		if($array["input"] == "img")
		{
			$msg .= "<div style='padding-bottom:3px;'>";
			if($array["if_must"])
			{
				$msg .= "<span class='red'>*</span> ";
			}
			$msg .= $array["sub_left"]."：";
			$msg .= "</div>";
			$msg .= "<div style='padding-bottom:3px;'><input type='button' class='btn2' value='选择' onclick=\"phpjs_".$array["input"]."('".$array["identifier"]."','_view_".$array["identifier"]."')\"> &nbsp;</div>";
			$msg .= "<div><input type='button' class='btn2' value='取消' onclick=\"phpjs_clear_".$array["input"]."('".$array["identifier"]."','_view_".$array["identifier"]."');\"> &nbsp;</div>";
		}
		elseif($array["input"] == "edit")
		{
			$msg .= "<div style='padding-bottom:3px;'>";
			if($array["if_must"])
			{
				$msg .= "<span class='red'>*</span> ";
			}
			$msg .= $array["sub_left"]."：";
			$msg .= "</div>";
		}
		else
		{
			if($array["if_must"])
			{
				$msg .= "<span class='red'>*</span> ";
			}
			$msg .= $array["sub_left"]."：";
		}
		$msg.= "</div><div class='right'>";
		$msg.= "<div><table cellpadding='0' cellspacing='0'><tr>";
		return $msg;
	}

	function _RIGHT_($array)
	{
		if($array["input"] == "edit" && $array["if_html"])
		{
			$msg .= "</tr></table></div>";
			if($array["sub_note"])
			{
				$msg.= "<div style='width:".$array["width"].";line-height:23px;height:23px;' class='clue_on'>".$array["sub_note"]."</div>";
			}
		}
		elseif($array["input"] == "textarea" || $array["input"] == "simg" || $array["input"] == "img" ||($array["input"] == "edit" && !$array["if_html"]))
		{
			$msg .= "</tr></table></div>";
			if($array["sub_note"])
			{
				$msg.= "<div class='clue_on' style='padding-top:3px;'>".$array["sub_note"]."</div>";
			}
		}
		else
		{
			if($array["sub_note"])
			{
				$msg.= "<td class='clue_on'>&nbsp;".$array["sub_note"]."</td>";
			}
			$msg .= "</tr></table></div>";
		}
		$msg.= "</div>";
		$msg.= "<div class='clear'></div>";
		$msg.= "</div>";
		return $msg;
	}

	function _FckEditor_($var="",$defaultvalue="",$toolbar="Default",$height="370px",$width="690px")
	{
		return sys_fckeditor($var,$defaultvalue,$toolbar,$height,$width);
	}

}

?>