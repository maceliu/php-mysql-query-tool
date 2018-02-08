<?php

//将二维数组形式的查询结果生成HTML表格格式
function data_to_chart($data)
{
	if (empty($data) || empty($data[0]))
	{
		return false;
	}

	$count = count($data);
	$html = '<table border="1" cellspacing="1" cellSpacing="1" align="left" style="margin-left:10px;">';
	$html .= '<tr>';			
	$table_title_arr = array_keys($data[0]);
	foreach ($table_title_arr as $key => $value) 
	{
		$html .= '<th  style="font-weight:700;padding:5px;background-color:#669;color:#F9F6F6;text-align:center;">'.$value.'</th>';
	}
	$html .= '</tr>';
	foreach ($data as $key => $value) 
	{
		$html .= '<tr>';
		foreach ($value as $key2 => $value2) 
		{
			$html .= '<td>'.$value2.'</td>';
		}
		$html .= '</tr>';
	}
	return $html;
}




/**
 * 获取页面传参，并进行过滤，防止SQL注入
 * @param string $value_name 要获取的参数名
 * @param string $def  若无法获取到值，默认值
 * @param string $method get post request
 * @return string $value 参数值
 */
function _getRequest($value_name,$def='',$method='request')
{
	$value	=	"";
	switch ($method)
	{
		case "get":
			if (!empty($_GET[$value_name])) 
			{
				$value	=	$_GET[$value_name];
			}
			break;
		case "post":
			if (!empty($_POST[$value_name])) 
			{
				$value	=	$_POST[$value_name];
			}
			break;
		default:
			if (!empty($_REQUEST[$value_name])) 
			{
				$value	=	$_REQUEST[$value_name];
			}
			break;
	}

	if(is_array($value))
	{
		while(list($key,$val)	=	each($value))
		{
			$val	=	ContentFilter($val);
			$val	=	addslashes($val);
			$val	=	trim($val);
			$value[$key]	=	$val;
		}

		return $value;
	}

	$value	=	ContentFilter($value);
	$value	=	addslashes($value);
	//$value	=	htmlentities($value);
	$value	=	trim($value);
	if($value	==	'')
	{
		return $def;
	}
	else
	{
		return $value;
	}
}


function ContentFilter($str)
{
	$str = trim($str);
	$str = preg_replace( '/[\a\f\e\0\t\x0B]/is', "", $str );
	$str = htmlspecialchars($str, ENT_QUOTES);
	$str = _TagFilter($str);
	$str = _CommonFilter($str);
	$str = _LineFilter($str);
	return $str;
}

function _TagFilter($str)
{
	$str = preg_replace( "/javascript/i" , "j&#097;v&#097;script", $str );
	$str = preg_replace( "/alert/i"      , "&#097;lert"          , $str );
	$str = preg_replace( "/about:/i"     , "&#097;bout:"         , $str );
	$str = preg_replace( "/onmouseover/i", "&#111;nmouseover"    , $str );
	$str = preg_replace( "/onclick/i"    , "&#111;nclick"        , $str );
	$str = preg_replace( "/onload/i"     , "&#111;nload"         , $str );
	$str = preg_replace( "/onsubmit/i"   , "&#111;nsubmit"       , $str );
	$str = preg_replace( "/<script/i"	 , "&#60;script"		 , $str );
	$str = preg_replace( "/document\./i" , "&#100;ocument."      , $str );
	return $str;
}

function _CommonFilter($str)
{
	$str = str_replace( "&#032;"			, " "			, $str );
	$str = preg_replace( "/\\\$/"			, "&#036;"		, $str );
	return $str;
}

function _LineFilter($str)
{
	return strtr($str, array("\r" => "", "\n" => "<br />"));
}




?>