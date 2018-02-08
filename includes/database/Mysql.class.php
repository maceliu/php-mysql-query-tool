<?php
/**
 *  Mysql操作MYSQL数据库类
 *  @author liubo  2017-06-20
 */
require_once dirname(__FILE__).'/MysqlDriver.class.php';

class MysqlMysqlDriver extends MysqlDriver
{

	public static $db_host_port = '';
	public static $conn = 'pconn'; //数据库连接方式;

	/**
     * 构造函数
     */
	public function __construct($db_host,$db_port,$db_user,$db_pwd,$db_database,$charset='utf8')
	{
		echo 'mysql';
		parent::__construct($db_host,$db_port,$db_user,$db_pwd,$db_database,$charset);
		self::$db_host_port = empty($db_port) ? $this->db_host : $this->db_port.':'.$db_port;
		//连接数据库
		$this->_connect();
	}

	/**
     * 创建数据库连接
     * @return bool 连接是否成功  true=>成功   false=>失败
     */
	protected function _connect() 
	{
		if (self::$conn == "pconn") 
		{
			//长链接
			$this->DB = mysql_pconnect(self::$db_host_port, $this->db_user, $this->db_pwd);
		} 
		else 
		{
			//短链接
			$this->DB = mysql_connect(self::$db_host_port, $this->db_user, $this->db_pwd);
		}

		if (!mysql_select_db($this->db_database, $this->DB)) 
		{
			$this->error_info = mysql_errno() . mysql_error();
			return false;
		}
		mysql_query("SET NAMES $this->charset");
		return true;
	}

	/**
     * 执行一条SQL语句
     * @param $sql str 要执行的SQL语句
     * @return array=>执行成功   bool(false)=>执行失败
     */
	protected function _query($sql) 
	{
		$this->sql = $sql;

		$this->query = mysql_query($this->sql, $this->DB);

		if (mysql_errno()) 
		{
			$this->error_info = "MySQL query error : " . mysql_errno() .' '. mysql_error();
			return false;
		}

		return true;
	}

	/**
     * 返回结果的数据
     * @param $type str 操作类型  one获取1条  all获取全部
     * @return array=>正常查询结果   bool(false)=>查询失败
     */
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
            	$this->error_info = 'Fetch type error!';
                return false;
        }
        return $result;
	}

	

	/**
     * 返回上一次执行的SQL影响的数据行数
     * @return int 影响行数
     */
    public function getRowCount() 
    {
        return mysql_affected_rows();
    }

    /**
     * 关闭数据连接
     */
	protected function _close() 
	{
		mysql_close($this->DB);
	}
	
	/**
     * 析构函数
     */
	public function __destruct() 
	{
		$this->_close();
	}



}
?>
