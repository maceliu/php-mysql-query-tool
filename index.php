<?php
error_reporting(E_ALL);

require_once dirname(__FILE__).'/includes/function.inc.php';
require_once dirname(__FILE__).'/includes/database/pdo.class.php';
require_once dirname(__FILE__).'/config.php';
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
		$db = new PdoMysql($database_use['host'],$database_use['port'],$database_use['user'],$database_use['pass'],$database_use['db'],$database_use['charset']);
		if ($db->error_info) 
		{
			$notice = $db->error_info;
		}
		else
		{
			//标记开始执行时间
			$_callStartTime = microtime(true);
			//执行查询
			$item_list = $db->getAll($sql);
			//获取执行结束时间
			$_callEndTime = microtime(true);
			//计算执行耗时
			$_callTime = $_callEndTime - $_callStartTime;
			$times_count =  sprintf('%.4f',$_callTime);
			//如果查询结果不是数组说明执行报错，尝试获取报错信息
			if ($item_list === false)
			{
				$notice = $db->getError();
			}
			else
			{	
				//将执行结果数组转化成HTML格式字符串
				$table_html = data_to_chart($item_list);
				//获取本次执行影响数据行数
				$rows_count = $db->getRowCount();
				//生成执行基本信息列
				$head_html = '<div>影响行数：'.$rows_count.'行    用时：'.$times_count.'秒</div>';
			}
		}
	}
}



//加载模板
require_once 'sql.html';
?>