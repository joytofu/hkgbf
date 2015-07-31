<?php
/***********************************************************
	Filename: excel_out.php
	Note	: 导出操作
	Version : 4.0
	Author  : qinggan
	Update  : 2011-12-04 09:33
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
include_once ROOT_PLUGIN."PHPExcel.php";
class excel_out_lib
{
	var $phpexcel;
	var $row;
	var $excel_type="xls";
	var $idstring = "title";
	function __construct()
	{
		@set_time_limit(0);#[设置防止超时]
		$this->phpexcel = new PHPExcel();
		$this->row = "A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z";
	}

	function excel_out_lib()
	{
		$this->__construct();
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

	function out($rslist,$filename="")
	{
		if(!$rslist)
		{
			return false;
		}
		if(!$filename) $filename = date("Ymd-His");
		if($this->excel_type == "xlsx")
		{
			$this->out_xlsx($rslist,$filename);
		}
		elseif($this->excel_type == "csv")
		{
			$this->out_csv($rslist,$filename);
		}
		else
		{
			$this->out_xls($rslist,$filename);
		}
	}

	function out_xlsx($rslist,$filename="")
	{
		$this->_out_xls_xlsx($rslist,$filename);
		$this->download_xlsx($filename);
	}



	function _out_xls_xlsx($rslist,$filename="")
	{
		if(!$rslist)
		{
			return false;
		}
		if(!$filename) $filename = date("Ymd-His");
		$idlist = explode(",",$this->idstring);
		$row_array = explode(",",$this->row);
		$width_array = array();
		$ifpic = false;
		$list = $tmplist = array();
		foreach($idlist AS $key=>$value)
		{
			$char = $row_array[$key];
			$v = $char .":";
			if($value == "id")
			{
				$v .= "8";
			}
			elseif($value == "title" || $value == "note")
			{
				$v .= "40";
			}
			elseif($value == "thumb")
			{
				$v .= "16";
				$ifpic = true;
			}
			else
			{
				$v .= "18";
			}
			$width_array[] = $v;
			$tmplist[$char."1"] = $value;
			$list[$char] = $value;
		}
		$this->set_width($width_array);//定好宽度
		$this->write_array($tmplist);
		unset($tmplist);
		//现在存储内容数据
		foreach($rslist AS $key=>$value)
		{
			$m = $key+2;
			if($ifpic) $this->set_height($m,"80");
			foreach($list AS $k=>$v)
			{
				$ispic = $v == "thumb" ? "100:100" : false;
				$isnum = $v == "price" ? true : false;
				$align = ($v == "title" || $v == "note") ? "left" : "center";
				$val = $value[$v];
				if($v == "post_date") $val = date("Y-m-d H:i:s",$val);
				$this->write($value[$v],$k."".$m,$ispic,$align,$isnum);
			}
		}
	}

	function out_xls($rslist,$filename="")
	{
		$this->_out_xls_xlsx($rslist,$filename);
		$this->download($filename);
	}

	//设置Excel的宽度
	//char，支持数组，如("A:8","B:12","C:24")
	function set_width($char)
	{
		if(is_array($char))
		{
			foreach($char AS $key=>$value)
			{
				if(trim($value))
				{
					$t = explode(":",$value);
					$c = $t[0] ? $t[0] : "A";
					$n = $t[1] ? $t[1] : "16";
					$this->phpexcel->getActiveSheet()->getColumnDimension($c)->setWidth($n);
				}
			}
		}
		else
		{
			$t = explode(":",$char);
			$c = $t[0] ? $t[0] : "A";
			$n = $t[1] ? $t[1] : "16";
			$this->phpexcel->getActiveSheet()->getColumnDimension($c)->setWidth($n);
		}
	}

	//设置行高
	function set_height($i,$height="15")
	{
		return $this->phpexcel->getActiveSheet()->getRowDimension($i)->setRowHeight($height);
	}

	//存储数组
	function write_array($list)
	{
		if(!$list || !is_array($list))
		{
			return false;
		}
		foreach($list AS $key=>$value)
		{
			$this->write($value,$key,false,"center");
		}
	}

	//设置要储存的数据，仅支持文本和图片
	//content，内容。如果是图片，则为图片的路径地址
	//char，列行号，字母A1，B2，C2等
	//ispic，是否是图片，如果为图片，可直接设置 "120:120"，这里的宽高使用像素计算
	//align，位置，默认是居中，仅支持left,center,right
	//isnum，是否是数字
	function write($content,$char="A1",$ispic=false,$align="",$isnum=false)
	{
		//如果是图片
		if($ispic && is_file($content) && file_exists($content))
		{
			$w_h = explode(":",$ispic);
			$pic_width = intval($w_h[0])>0 ? intval($w_h[0]) : 100;
			$pic_height = intval($w_h[1])>0 ? intval($w_h[1]) : 100;
			$XLS_D = new PHPExcel_Worksheet_Drawing(); //画图片
			$XLS_D->setPath($content);
			$XLS_D->setOffsetX(6);
			$XLS_D->setOffsetY(3);
			$XLS_D->setHeight($pic_width);
			$XLS_D->setWidth($pic_height);
			$XLS_D->setCoordinates($char);
			$XLS_D->getShadow()->setVisible(true);
			$XLS_D->setWorksheet($this->phpexcel->getActiveSheet());
		}
		else
		{
			//居中和居右设置
			if($align == "center")
			{
				$this->phpexcel->getActiveSheet()->getStyle($char)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			}
			elseif($align == "right")
			{
				$this->phpexcel->getActiveSheet()->getStyle($char)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			}
			//垂直居中
			$this->phpexcel->getActiveSheet()->getStyle($char)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$this->phpexcel->getActiveSheet()->getStyle($char)->getAlignment()->setWrapText(true);
			if(!$isnum)
			{
				$this->phpexcel->getActiveSheet()->setCellValueExplicit($char,$content,PHPExcel_Cell_DataType::TYPE_STRING);
			}
			else
			{
				$this->phpexcel->getActiveSheet()->setCellValue($char,$content);
			}
		}
	}

	function download($filename="excel")
	{
		if(!$filename) $filename = time();
		$this->phpexcel->createSheet();
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
		header('Cache-Control: max-age=0');
		$XLS_W = PHPExcel_IOFactory::createWriter($this->phpexcel, 'Excel5');
		$XLS_W->save('php://output');
	}

	function download_xlsx($filename="excel")
	{
		if(!$filename) $filename = time();
		$this->phpexcel->createSheet();
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
		header('Cache-Control: max-age=0');
		$XLS_W = PHPExcel_IOFactory::createWriter($this->phpexcel,'Excel2007');
		$XLS_W->save('php://output');
	}



	function out_csv($rslist,$filename="")
	{
		if(!$rslist)
		{
			return false;
		}
		if(!$filename) $filename = date("Ymd-His");
		$list = array();
		$idlist = explode(",",$this->idstring);
		foreach($idlist AS $key=>$value)
		{
			$list[0][$value] = $value;
		}
		$i=1;
		foreach($rslist AS $key=>$value)
		{
			foreach($value AS $k=>$v)
			{
				if(in_array($k,$idlist))
				{
					if($k == "post_date") $v = date("Y-m-d H:i:s",$v);
					$list[$i][$k] = $v;
				}
			}
			$i++;
		}
		unset($rslist);
		header('Content-Type: text/csv' );
		header('Content-Disposition: attachment;filename='.$filename.".csv");
		$fp = fopen('php://output', 'w');
		foreach($list AS $key=>$value)
		{
			fputcsv($fp, $value);
		}
		fclose($fp);
	}
}
?>