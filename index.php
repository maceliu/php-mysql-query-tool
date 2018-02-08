<?php
require_once dirname(__FILE__).'/config.php';
require_once dirname(__FILE__).'/includes/function.inc.php';
require_once dirname(__FILE__).'/includes/database/DbFactory.class.php';

//获取传参
$host = _getRequest('host','localhost');
$database = _getRequest('database','php_test');
$sql = empty($_GET['sql']) ? '' : $_GET['sql'];
$notice = '';
//根据传入参数从配置数组中按选择选择服务器和对应数据库
$host_arr = array_keys($database_config_arr);
$database_arr = array();
foreach ($database_config_arr as $key => $value) 
{
	foreach ($value as $key2 => $value2) 
	{
		if (!in_array($key2, $database_arr)) 
		{
			$database_arr[] = $key2;
		}
	}
}

if (empty($database_config_arr[$host][$database])) 
{
	$notice = '数据库选择错误';
}
else
{
	$database_use = $database_config_arr[$host][$database];
	if (!empty($sql)) 
	{
		//连接数据库
		$DbFactory = new DbFactory();
		$db = $DbFactory->createDriver($database_use,'auto');
		if (!$db) 
		{
			echo $DbFactory->error_info;
			exit;
		}

		if ($db->error_info) 
		{
			$notice = $db->error_info;
		}
		else
		{
			//标记开始执行时间
			$query_start_time = microtime(true);
			//执行查询
			$item_list = $db->getAll($sql);
			//获取执行结束时间
			$query_end_time = microtime(true);
			//计算执行耗时
			$query_time = sprintf('%.4f',($query_end_time - $query_start_time));
			//如果查询结果不是数组说明执行报错，尝试获取报错信息
			if ($item_list === false)
			{
				$notice = $db->getErrorInfo();
			}
			else
			{	
				$show_count = count($item_list);
				//将执行结果数组转化成HTML格式字符串
				$table_html = data_to_chart($item_list);
				//获取本次执行影响数据行数
				$rows_count = $db->getRowCount();
				//生成执行基本信息列
				$head_html = '<div>影响行数：'.$rows_count.'行，查询结果行数：'.$show_count.'    用时：'.$query_time.'秒</div>';
			}
		}
	}
}



//加载模板
require_once 'sql.html';
?>