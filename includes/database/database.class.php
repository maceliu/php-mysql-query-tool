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

	

	/*数据库连接*/
	abstract protected function connect(); 
	

}
?>