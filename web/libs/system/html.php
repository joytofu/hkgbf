<?php
/***********************************************************
	Filename: libs/system/html.php
	Note	: 静态页生成类，优先使用 curl 函数
	Version : 3.0
	Author  : qinggan
	Update  : 2011-06-24 19:41
***********************************************************/
class html_lib
{
	var $app;
	var $purl;
	var $timeout = 10;
	var $socket;
	var $use_func = "fsockopen";
	var $is_gzip = false;
	var $is_proxy = false;
	var $proxy_service = "";
	var $proxy_user = "";
	var $proxy_pass = "";

	function __construct()
	{
        $fsock_exists = function_exists('fsockopen') && function_exists("socket_accept");
        $curl_exists = function_exists('curl_init');
		if(!$fsock_exists && !$curl_exists)
		{
			$this->use_func = "error";
			$this->use_curl = 0;
		}
		else
		{
			$this->use_func = $curl_exists ? "curl" : "fsockopen";
		}
	}

	function setting($var,$val="")
	{
		$this->$var = $val;
	}

	function html_lib()
	{
		$this->__construct();
	}

	function get_content($url)
	{
		if(!$url || $this->use_func == "error")
		{
			return false;
		}
		$url = str_replace("&amp;","&",$url);
		//return $this->_fsockopen($url);
		return $this->use_func == "curl" ? $this->_curl($url) : $this->_fsockopen($url);
	}

	function _curl($url)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_FORBID_REUSE, true); // 处理完后，关闭连接，释放资源
		curl_setopt($curl, CURLOPT_HEADER, true);//结果中包含头部信息
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);//把结果返回，而非直接输出
		curl_setopt($curl, CURLOPT_HTTPGET,true);//使用GET传输数据
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT,5);//等待时间，超时退出
		if($this->is_gzip)
		{
			curl_setopt($curl,CURLOPT_ENCODING ,'gzip');//GZIP压缩
		}
		//判断是否有启用代理
		if($this->is_proxy && $this->proxy_service)
		{
			//curl_setopt($curl,CURLOPT_HTTPPROXYTUNNEL,true);
			curl_setopt($curl,CURLOPT_PROXY,$this->proxy_service);
			if($this->proxy_user || $this->proxy_pass)
			{
				curl_setopt($curl,CURLOPT_PROXYUSERPWD,base64_encode($this->proxy_user.":".$this->proxy_pass));
			}
		}
		curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
		$this->format_url($url);
		if($this->purl["user"])
		{
			$auth = $this->purl["user"].":".$this->purl["pass"];
			curl_setopt($curl, CURLOPT_USERPWD, $auth);
			curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		}
		$header = array();
		$header[] = "Host: ".$this->purl["host"];
		$header[] = "Referer: ".$this->purl['protocol'].$this->purl["host"];
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        $content = curl_exec($curl);
        if (curl_errno($curl) != 0)
        {
            return false;
        }
        $separator = '/\r\n\r\n|\n\n|\r\r/';
        list($http_header, $http_body) = preg_split($separator, $content, 2);
        curl_close($curl);
        return $http_body;
    }

	function _fsockopen($url)
	{
		$crlf = $this->get_crlf();
		//格式化URL
		$this->format_url($url);
		if($this->is_proxy && $this->proxy_service)
		{
			$my_proxy = parse_url($this->proxy_service);
			if(!$my_proxy["port"]) $my_proxy["port"] = "80";
			$handle = fsockopen($my_proxy["host"], $my_proxy['port'], $errno, $errstr, $this->timeout);
			$out = "GET ".$url." HTTP/1.1".$crlf;
			if($this->proxy_user || $this->proxy_pass)
			{
				$out .= "Proxy-Authorization: Basic ".base64_encode ($this->proxy_user.":".$this->proxy_pass).$crlf.$crlf;
			}
		}
		else
		{
			$handle = fsockopen($this->purl["host"], $this->purl['port'], $errno, $errstr, $this->timeout);
			$out = "GET ".$urlext." HTTP/1.1".$crlf;
		}
		if(!$handle)
		{
			return false;
		}
		set_time_limit($this->timeout);
		//取得内容信息
		$urlext = $this->purl["path"];
		if($urlext != "/" && $this->purl["query"])
		{
			$urlext .= "?";
			$urlext .= $this->purl["query"];
			if($this->purl["fragment"])
			{
				$urlext .= "#".$this->purl["fragment"];
			}
		}
		$out.= "Host: ".$this->purl["host"].$crlf;
		$out.= "Referer: ".$this->purl['protocol'].$this->purl["host"].$crlf;
		$out.= "Connection: Close".$crlf.$crlf;
		if($this->is_gzip)
		{
			$out .= "Accept-Encoding: GZIP".$crlf;
		}
		if(!fwrite($handle, $out))
		{
			return false;
		}
		$content = "";
		while(!feof($handle))
		{
			$content .= fgets($handle);
		}
 		fclose($handle);
		$separator = '/\r\n\r\n|\n\n|\r\r/';
		list($http_header, $http_body) = preg_split($separator, $content, 2);
		if (strpos(strtolower($http_header), "transfer-encoding: chunked") !== FALSE)
		{
			$http_body = $this->unchunkHttp11($http_body);
		}
		if($this->is_gzip)
		{
			return $this->gzip_decode($http_body);
		}
		return $http_body;
	}

	function unchunkHttp11($data)
	{
		$fp = 0;
		$outData = "";
		while ($fp < strlen($data))
		{
			$rawnum = substr($data, $fp, strpos(substr($data, $fp), "\r\n") + 2);
			$num = hexdec(trim($rawnum));
			$fp += strlen($rawnum);
			$chunk = substr($data, $fp, $num);
			$outData .= $chunk;
			$fp += strlen($chunk);
		}
		return $outData;
	}

    function get_crlf()
    {
        $crlf = '';
        if (strtoupper(substr(PHP_OS, 0, 3) === 'WIN'))
        {
            $crlf = "\r\n";
        }
        elseif (strtoupper(substr(PHP_OS, 0, 3) === 'MAC'))
        {
            $crlf = "\r";
        }
        else
        {
            $crlf = "\n";
        }
        return $crlf;
    }


	function format_url($url)
	{
		$this->purl = parse_url($url);
		if (!isset($this->purl['host']))
		{
			if(isset($_SERVER["HTTP_HOST"]))
			{
				$this->purl['host'] = $_SERVER["HTTP_HOST"];
			}
			elseif(isset($_SERVER["SERVER_NAME"]))
			{
				$this->purl['host'] = $_SERVER["SERVER_NAME"];
			}
			else
			{
				$this->purl['host'] = "localhost";
			}
		}
		if(!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] == "off" || $_SERVER["HTTPS"] == "")
		{
			$this->purl['scheme'] = "http";
		}
		else
		{
			$this->purl['scheme'] = "https";
		}
		$this->purl['port'] = $_SERVER["SERVER_PORT"] ? $_SERVER["SERVER_PORT"] : 80;
        if(!isset($this->purl['path']))
		{
			$this->purl['path'] = "/";
		}
		elseif(($this->purl['path']{0} != '/') && ($_SERVER["PHP_SELF"]{0} == '/'))
		{
			$this->purl['path'] = substr($_SERVER["PHP_SELF"], 0, strrpos($_SERVER["PHP_SELF"], '/') + 1) . $this->purl['path'];
		}
		return $this->purl;
	}

	//解压GZIP，这个函数从网上找的
	function gzip_decode($data)
	{
        $flags = ord(substr($data, 3, 1));
        $headerlen = 10;
        $extralen = 0;
        $filenamelen = 0;
        if ($flags & 4) {
            $extralen = unpack('v' ,substr($data, 10, 2));
            $extralen = $extralen[1];
            $headerlen += 2 + $extralen;
        }
        if ($flags & 8) $headerlen = strpos($data, chr(0), $headerlen) + 1;
        if ($flags & 16) $headerlen = strpos($data, chr(0), $headerlen) + 1;
        if ($flags & 2) $headerlen += 2;
        $unpacked = @gzinflate(substr($data, $headerlen));
        if ($unpacked === FALSE) $unpacked = $data;
        return $unpacked;
    }

}
?>