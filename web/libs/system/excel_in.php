<?php
/***********************************************************
	Filename: excel.php
	Note	: 操作Excel类，支持csv格式
	Version : 4.0
	Author  : qinggan
	Update  : 2011-12-04 09:33
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
include_once ROOT_PLUGIN."PHPExcel.php";
class excel_in_lib
{
	var $charset = "gbk";
	var $phpexcel;
	var $row;
	var $excel_type="xls";
	var $idlist;
	function __construct()
	{
		@set_time_limit(0);#[设置防止超时]
		$this->phpexcel = new PHPExcel();
		$this->row = "A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z";
	}

	function excel_in_lib()
	{
		$this->__construct();
	}

	//Lang
	function charset($charset="gbk")
	{
		$this->charset = $charset;
	}

	function set_type($type="xls")
	{
		if(!$type || ($type && !in_array($type,array("xls","xlsx","csv"))))
		{
			$type = "xls";
		}
		$this->excel_type = $type;
	}

	function set_idstring($idstring="title")
	{
		$this->idstring = $idstring;
	}

	function in($file)
	{
		if(!$file)
		{
			return false;
		}
		if($this->excel_type == "csv")
		{
			$filetype = "CSV";
		}
		elseif($this->excel_type == "xlsx")
		{
			$filetype = "Excel2007";
		}
		else
		{
			$filetype = "Excel5";
		}
		$objReader = PHPExcel_IOFactory::createReader($filetype);
		if($filetype == "CSV")
		{
			$objReader->setDelimiter(",");
			$objReader->setInputEncoding($this->charset);
		}
		else
		{
			$objReader->setReadDataOnly(true);
		}
		$objPHPExcel = $objReader->load($file);
		$currentSheet = $objPHPExcel->getSheet(0);
		$allColumn = $currentSheet->getHighestColumn();
		$allRow = $currentSheet->getHighestRow();
		//取得第一行字段
		$rslist = array();
		$m = 0;
		$idlist = array();
		for($i = "A";$i<=$allColumn;$i++)
		{
			$t = $i."1";
			$idlist[$t] = $currentSheet->getCell($t)->getValue();
		}
		$this->idlist = $idlist;
		for($currentRow = 2;$currentRow<=$allRow;$currentRow++)
		{
			for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++)
			{
				$address = $currentColumn.$currentRow;
				$k = $currentColumn."1";
				$key = $currentSheet->getCell($k)->getValue();
				$rslist[$m][$key] = $currentSheet->getCell($address)->getValue();
			}
			$m ++;
		}
		unset($currentSheet,$objPHPExcel,$objReader);
		return $rslist;
	}

	function idlist($file="")
	{
		if($file)
		{
			$this->in($file);
		}
		return $this->idlist;
	}
}
?>