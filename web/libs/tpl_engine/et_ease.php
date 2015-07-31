<?php
#[模板类]
#[特别说明，这个模板类是在学习了SYSTN.COM的模板类的基础上改装过来的]
#[应用更简单]
class Ease
{
	var $thisvalue = array();
	var $imgdir = array("images");
	var $hacker = "<?php if(!defined('PHPOK_SET')){die('<h3>Error...</h3>');}?>";
	var $compile = array(
		'/(\{|<!--\s*)inc_php:([^\{\}]{1,100})(\}|\s*-->)/eisU',
		'/(\{|<!--\s*)php:([^\{\}]{1,100})(\}|\s*-->)/eisU',
		'/(\{|<!--\s*)inc:([^\{\}]{1,100})(\}|\s*-->)/eisU',
		'/(\{|<!--\s*)IF\((.+)\)(\}|\s*-->)/isU',
		'/(\{|<!--\s*)ELSEIF\((.+)\)(\}|\s*-->)/isU',
		'/(\{|<!--\s*)ELSE(\}|\s*-->)/isU',
		'/(\{|<!--\s*)END(\}|\s*-->)/isU',
		'/(\{|<!--\s*)([a-zA-Z0-9_\$\[\]\'\\\"]{2,60})\s*(AS|as)\s*(.+)(\}|\s*-->)/isU',
		'/(\{|<!--\s*)while\:(.+)(\{|<!--\s*)/isU',
		'/(\{|<!--\s*)row\:(.+)(\}|\s*-->)/eisU',
		'/(\{|<!--\s*)color\:\s*(.+)(\}|\s*-->)/eisU',
		'/(\{|<!--\s*)run\:(\}|\s*-->)\s*(.+)\s*(\{|<!--\s*)\/run(\}|\s*-->)/isU',
		'/(\{|<!--\s*)run\:(.+)(\}|\s*-->)/isU',
		'/\{:(.+)\}/isU',
	);

	var $analysis = array(
		'$this->qg_php("\\2")',
		'$this->qg_php("\\2")',
		'$this->qg_inc("\\2")',
		'<?php if(\\2){?>',
		'<?php }elseif(\\2){ ?>',
		'<?php }else{ ?>',
		'<?php } ?>',
		'<?php \$_i=0;\\2=(is_array(\\2))?\\2:array();foreach(\\2 AS \\4){\$_i++; ?>',
		'<?php \$_i=0;while(\\2){\$_i++; ?>',
		'$this->row("\\2")',
		'$this->color("\\2")',
		'<?php \\3;?>',
		'<?php \\2;?>',
		'<?php echo \\1;?>'
	);

	var $tplid = 1;
	var $tpldir = "template";
	var $cache = "data";
	var $phpdir = "";
	var $ext = "htm";
	var $autorefresh = true;#[自动刷新编译模板]
	var $autoimg = true;
	//默认
	var $default_tpldir = "template";
	var $default_autoimg = true;
	var $default_ext = "html";

	#[声明模板]
	#[$tplid：模板编译后的ID]
	#[$tpldir：模板目录]
	#[$cache：编译后的PHP文件目录]
	#[$phpdir：PHP文件目录]
	#[$autoimg：解析图片路径]
	function Ease
	(
		$set = array
		(
			"tplid"=>"1",
			"tpldir"=>"template",
			"cache"=>"data",
			"phpdir"=>"",
			"ext"=>"htm",
			"autorefresh"=>true,
			"autoimg"=>true
		)
	)
	{
		$this->tplid = $set["tplid"] ? $set["tplid"]."_" : "1_";
		$this->tpldir = $set["tpldir"] ? $set["tpldir"] : "template";
		$this->cache = $set["cache"] ? $set["cache"] : "data";
		$this->phpdir = $set["phpdir"] ? $set["phpdir"] : "";
		$this->ext = $set["ext"] ? $set["ext"] : "htm";
		$this->autoimg = $set["autoimg"];
		//默认参数
		$this->default_tpldir = "tpl/www";
		$this->default_autoimg = true;
		$this->default_ext = "html";
		#[判断是否使用常量]
		if(defined("TemplateID"))
		{
			$this->tplid = TemplateID."_";
		}
		if(defined("NewCache"))
		{
			$this->cache = NewCache;
		}
		if(defined("NewTemplate"))
		{
			$this->tpldir = NewTemplate;
		}
	}

	#[设置]
	function set($var,$value)
	{
		$this->$var = $value;
	}

	function set_var($name,$var="")
	{
		$this->thisvalue[$name] = $var;
	}

	#[输出文件]
	function p($filename,$newdir="",$tplid=0)
	{
		#[如果未设置，则使用模板文件]
		if(!$filename)
		{
			return false;
		}
		$tpldir = $newdir ? $this->tpldir."/".$newdir : $this->tpldir;
		$tplid = $tplid ? $tplid."_" : $this->tplid;
		$default_tpldir = $newdir ? $this->default_tpldir."/".$newdir : $this->default_tpldir;
		#[已编译后的文件]
		if($newdir)
		{
			$tmp_newdir = str_replace(array("../","/"),array("","-"),$newdir);
			$c_file = $this->cache."/".$tmp_newdir."/".$tplid.$filename.".php";
		}
		else
		{
			$c_file = $this->cache."/".$tplid.$filename.".php";
		}
		$r_file = $tpldir."/".$filename.".".$this->ext;
		$tpl_time = $this->_time($r_file);#[模板文件编译时间]
		$php_time = $this->_time($c_file);
		if($tpl_time < $php_time || (!$this->autorefresh && file_exists($c_file)))
		{
			@extract($this->_value());
			include($c_file);
			return true;
		}
		#[模板文件]
		$r_file = $tpldir."/".$filename.".".$this->ext;
		if(!file_exists($c_file))
		{
			if(file_exists($r_file))
			{
				$content = $this->_read($r_file);
			}
			else
			{
				$default_file = $default_tpldir."/".$filename.".".$this->default_ext;
				if(file_exists($default_file))
				{
					$content = $this->_read($default_file);
				}
				else
				{
					echo "tpl not exit:".$r_file;
					exit;
				}
			}
			$content = $this->_c($content);
			if(!$content)
			{
				return false;
			}
			$content = $this->_imgcheck($content);
			#[将内容写入]
			$this->_write($content,$c_file);
			unset($content);
			@extract($this->_value());
			include($c_file);
			return true;
		}
		#[检测文件的编译时间]
		$tpl_time = $this->_time($r_file);#[模板文件编译时间]
		$php_time = $this->_time($c_file);
		if($tpl_time > $php_time)
		{
			$content = $this->_read($r_file);
			$content = $this->_c($content);
			if(!$content)
			{
				return false;
			}
			$content = $this->_imgcheck($content);
			#[将内容写入]
			$this->_write($content,$c_file);
			unset($content);
			@extract($this->_value());
			include($c_file);
			return true;
		}
		@extract($this->_value());
		include($c_file);
		return true;
	}

	#[解析包含的PHP文件，支持网址]
	function qg_php($string)
	{
		if(strpos($string,"?") === false)
		{
			$msg = '<?php include_once("'.$this->phpdir.'/'.$string.'.php");?>';
		}
		else
		{
			if(strpos(strtolower($string),"http://") === false)
			{
				$msg = '<?php include_once("'.$this->_sysurl().'/'.$this->phpdir.'/'.$string.'");?>';
			}
			else
			{
				$msg = '<?php include_once("'.$string.'");?>';
			}
		}
		return $msg;
	}

	function qg_inc($string)
	{
		$array = explode("|",$string);
		if(!$array[0])
		{
			return false;
		}
		$tpl = trim($array[0]);
		$tpldir = $array[1] ? trim($array[1]) : "";
		$tplid = $array[2] ? trim($array[2]) : 0;
		$msg = '<?php $APP->tpl->p("'.$tpl.'","'.$tpldir.'","'.$tplid.'");?>';
		return $msg;
	}

	function row($num="")
	{
		$num = trim($num);
		if(!$num)
		{
			return false;
		}
		$nums = explode(",",$num);
		$numr = intval($nums[0]) > 0 ? intval($nums[0]) : 2;
		$input = trim($nums[1]) ? $nums[1] : "</tr><tr>";
		if(trim($nums[1]))
		{
			$Co	 = explode(":",$nums[1]);
			$outstr = "if(\$_i%$numr===0){\$row_count++;echo(\$row_count%2===0)?'</tr><tr bgcolor=\"$Co[0]\">':'</tr><tr bgcolor=\"$Co[1]\">';}";
		}
		else
		{
			$outstr = "if(\$_i%$numr===0){echo '$input';}";
		}
		$msg = "<?php ".$outstr."?>";
		return $msg;
	}

	function color($color="")
	{
		if(!$color)
		{
			return false;
		}
		$Co = explode(",",$color);
		if(count($Co)==2)
		{
			$OutStr = "echo(\$_i%2===0)?'$Co[0]':'$Co[1]';";
			return "<?php ".$OutStr."?>";
		}
	}

	function image($adds="")
	{
		$adds_ary = explode(",",$adds);
		if(is_array($adds_ary))
		{
			$this->imgdir = (is_array($this->imgdir))?@array_merge($adds_ary, $this->imgdir):$adds_ary;
		}
	}

	#[编译内容]
	function _c($content="")
	{
		if(!$content)
		{
			return false;
		}
		$content = str_replace('\\','\\\\',$content);
		$content = str_replace('"','\"',$content);
		$content = preg_replace($this->compile,$this->analysis,$content);
		return $content;
	}

	function _read($filename)
	{
		return file_get_contents($filename);
	}

	function _write($content,$filename,$mode="wb")
	{
		$content = trim($content);
		$filename = trim($filename);
		#[判断目录是否存在]
		$dir_array = explode("/",$filename);
		$dir_count = count($dir_array);
		$msg = "";
		for($i=0;$i<($dir_count-1);$i++)
		{
			$msg .= $dir_array[$i];
			if(!file_exists($msg) && ($dir_array[$i]))
			{
				@mkdir($msg,0777);
			}
			$msg .= "/";
		}
		if($content && $filename)
		{
			$content = stripslashes($content);
			$handle = fopen($filename,$mode);
			fwrite($handle,$this->hacker.$content);
			fclose($handle);
			return true;
		}
		else
		{
			return false;
		}
	}

	function _sysurl()
	{
		if($_SERVER["SERVER_NAME"])
		{
			return str_replace("http://","",$_SERVER["SERVER_NAME"]);
		}
		else
		{
			return false;
		}
	}

	//获得所有设置与公共变量
	function _value()
	{
		return (is_array($GLOBALS))?array_merge($GLOBALS,$this->thisvalue):$this->thisvalue;
	}

	#[处理图片]
	function _imgcheck($content)
	{
		if(!$this->autoimg)
		{
			return $content;
		}
		if(is_array($this->imgdir))
		{
			foreach($this->imgdir AS $rep)
			{
				$rep = trim($rep);
				if(substr($rep,-1)=='/')
				{
					$rep = substr($rep,0,strlen($rep)-1);
				}
				$content = str_replace($rep.'/',$this->tpldir."/".$rep.'/',$content);
			}
			#[修正错误]
			if(ereg($this->tpldir."/".$this->tpldir,$content))
			{
				$content = str_replace($this->tpldir."/".$this->tpldir,$this->tpldir."/",$content);
			}
		}
		return $content;
	}

	#[计算时间]
	function _time($filename)
	{
		if(!file_exists($filename))
		{
			return false;
		}
		return filemtime($filename);
	}
}
?>