<?php

class mysql {

	private $db_host; //数据库主机
	private $db_port; //数据库使用的端口号
	private $db_user; //数据库用户名
	private $db_pwd; //数据库用户名密码
	private $db_database; //数据库名
	private $conn; //数据库连接标识;
	private $result; //执行query命令的结果资源标识
	private $sql; //sql执行语句
	private $row; //返回的条目数
	private $coding; //数据库编码，GBK,UTF8,gb2312
	private $bulletin = true; //是否开启错误记录
	private $show_error = false; //测试阶段，显示所有错误,具有安全隐患,默认关闭
	private $is_error = false; //发现错误是否立即终止,默认true,建议不启用，因为当有问题时用户什么也看不到是很苦恼的

	/*构造函数*/
	public function __construct($db_host,$db_port, $db_user, $db_pwd, $db_database, $coding='utf8', $conn='pconn')
	{
		$this->db_host = $db_host;
		$this->db_user = $db_user;
		$this->db_pwd = $db_pwd;
		$this->db_database = $db_database;
		$this->conn = $conn;
		$this->coding = $coding;
		$this->connect();
	}


	/*数据库连接*/
	public function connect() 
	{
		if ($this->conn == "pconn") {
			//永久链接
			$this->conn = mysql_pconnect($this->db_host, $this->db_user, $this->db_pwd);
		} else {
			//即使链接
			$this->conn = mysql_connect($this->db_host, $this->db_user, $this->db_pwd);
		}

		if (!mysql_select_db($this->db_database, $this->conn)) {
			if ($this->show_error) {
				$this->show_error("数据库不可用：", $this->db_database);
			}
		}
		mysql_query("SET NAMES $this->coding");
	}

	/*数据库执行语句，可执行查询添加修改删除等任何sql语句*/
	public function query($sql) 
	{
		if ($sql == "") {
			$this->show_error("SQL语句错误：", "SQL查询语句为空");
		}
		if($sql){
			$this->sql = $sql;
		}
		else
		{
			exit('SQL_str error!');
		}

		$result = mysql_query($this->sql, $this->conn);

		if (!$result) {
			//调试中使用，sql语句出错时会自动打印出来
			if ($this->show_error) {
				$this->show_error("错误SQL语句：", $this->sql);
			}
		} else {
			$this->result = $result;
		}
		return $this->result;
	}
	


	/*
	mysql_fetch_row()    array  $row[0],$row[1],$row[2]
	mysql_fetch_array()  array  $row[0] 或 $row[id]
	mysql_fetch_assoc()  array  用$row->content 字段大小写敏感
	mysql_fetch_object() object 用$row[id],$row[content] 字段大小写敏感
	*/

	//获取关联数组,使用$row['字段名']
	public function fetch_assoc() {
		return mysql_fetch_assoc($this->result);
	}





	//数据库选择
	public function select_db($db_database) 
	{
		return mysql_select_db($db_database);
	}
	

	//析构函数，自动关闭数据库,垃圾回收机制
	public function __destruct() {
		if (!empty ($this->result)) {
			$this->free();
		}
		mysql_close($this->conn);
	}




}
?>