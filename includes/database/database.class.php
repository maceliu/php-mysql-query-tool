<?php

abstract class database {

    public  $db_host; //数据库主机
    public $db_port; //数据库使用的端口号
    public $db_user; //数据库用户名
    public $db_pwd; //数据库用户名密码
    public $db_database; //数据库名
    public $conn; //数据库连接标识;
    public $result; //执行query命令的结果资源标识
    public $sql = ''; //sql执行语句
    public $row; //返回的条目数
    public $charset; //数据库编码，GBK,UTF8,gb2312
    public $error_reporting = true; //报错等级
    public $DB = NULL; //数据库链接句柄
    public $error_info = '';

	/*构造函数*/
	public function __construct($db_host, $db_port, $db_user, $db_pwd, $db_database,$charset) 
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


	//获取多条数据，二维数组
    public function getAll($sql)
    {
       
    }




    /**
     * 执行SQL返回第一条数据
     * @param $sql string 要执行的SQL
     * @return array 查询结果  一维数组
     */
    public function getOne($sql) 
    {

    }



    /**
    * 定义添加数据的方法
    * @param string $table 表名
    * @param string orarray $data [数据]
    * @return int 最新添加的id
    */
    public function insert($table,$data)
    {
        
    }


    /**
     * 更新数据
     * @param $table string 表名
     * @param $where sting  更新条件
     * @param $args  array  数据
     * @return int 受影响的数据
     */
    public function update($table, $args, $where) 
    {

    }


    /**
     * 删除数据
     * @param $table string 表名
     * @param $where sting  删除条件
     * @return int 受影响的数据
     */
    public function delete($table, $where) 
    {

    }




}


?>