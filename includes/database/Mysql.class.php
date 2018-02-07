<?php
/**
 *  Mysql操作MYSQL数据库类
 *  @author liubo  2017-06-20
 */
require_once dirname(__FILE__).'/MysqlDriver.class.php';

class MysqlMysqlDriver extends MysqlDriver
{

	public $conn; //数据库连接方式;

	/*构造函数*/
	public function __construct($db_host,$db_port, $db_user, $db_pwd, $db_database, $coding='utf8', $conn='pconn')
	{
		$this->db_host = $db_host;
		$this->db_user = $db_user;
		$this->db_pwd = $db_pwd;
		$this->db_database = $db_database;
		$this->conn = $conn;
		$this->coding = $coding;
		$this->_connect();
	}

	/*数据库连接*/
	protected function _connect() 
	{
		if ($this->conn == "pconn") {
			//长链接
			$this->DB = mysql_pconnect($this->db_host, $this->db_user, $this->db_pwd);
		} else {
			//短链接
			$this->DB = mysql_connect($this->db_host, $this->db_user, $this->db_pwd);
		}

		if (!mysql_select_db($this->db_database, $this->DB)) {
			$this->error_info = mysql_errno() . mysql_error();
			return false;
		}
		mysql_query("SET NAMES $this->coding");
		return true;
	}

	/*数据库执行语句，可执行查询添加修改删除等任何sql语句*/
	protected function _query($sql) 
	{
		if (empty($sql)) 
		{
			$this->error_info = "SQL statement error : empty SQL statement";
			return false;
		}

		$this->sql = $sql;

		$this->query = mysql_query($this->sql, $this->DB);

		if (!$this->query) 
		{
			$this->error_info = "MySQL query error : " . mysql_errno() . mysql_error();
			return false;
		} 

		return true;
	}

	//获取关联数组,使用$row['字段名']
	protected function _fetch($type='one') 
	{
		$result = array();
        switch ($type)
        {
            case 'one':
                $result = mysql_fetch_assoc($this->query);
                break;
            case 'all':
                while ($result_temp = mysql_fetch_assoc($this->query)) 
                {
                    $result[] = $result_temp;
                }
                break;
            default:
            	$this->error_info = '获取fetch错误！';
                return false;
        }
        return $result;
	}

	//获取关联数组,使用$row['字段名']
	protected function _close() 
	{
		mysql_close($this->DB);
	}

	/**
     * 返回上一次执行的SQL影响的数据行数
     * @return int 影响行数
     */
    public function getRowCount() 
    {
        return mysql_affected_rows();
    }


	//析构函数，自动关闭数据库,垃圾回收机制
	public function __destruct() 
	{
		$this->_close();
	}



}
?>
