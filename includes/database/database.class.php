<?php

abstract class database {

	/*构造函数*/
	public function __construct($db_host, $db_user, $db_pwd, $db_database, $conn, $coding) 
	{
		// $this->db_host = $db_host;
		// $this->db_user = $db_user;
		// $this->db_pwd = $db_pwd;
		// $this->db_database = $db_database;
		// $this->conn = $conn;
		// $this->coding = $coding;
		// $this->connect();
	}

	

	/*创建数据库连接*/
	abstract protected function _connect(); 

	/*关闭数据库连接*/
	abstract protected function _close(); 

	/*对数据库执行一次查询*/
	abstract public function _query($sql); 

	/*从结果集中获取下一行*/
	abstract protected function _fetch(); 


}
?>