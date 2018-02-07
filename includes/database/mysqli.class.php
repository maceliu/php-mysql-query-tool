<?php
/**
 *  Mysqli操作MYSQL数据库类
 *  @author liubo  2017-06-20
 */
require_once dirname(__FILE__).'/MysqlDriver.class.php';

class MysqliMysqlDriver extends MysqlDriver
{
    
    public static $db_host_port = '';

    //私有的构造方法
    public function __construct($db_host,$db_port,$db_user,$db_pass,$db_database,$charset='utf8')
    {
        $this->db_host = $db_host;
        self::$db_host_port = empty($db_port) ? $this->db_host : $this->db_port.':'.$db_port;
        $this->db_user = $db_user;
        $this->db_pass = $db_pass;
        $this->db_database = $db_database;
        $this->charset= $charset;
        //连接数据库
        $this->_connect();
    }

    //连接数据库
    protected function _connect()
    {
        mysqli_report(MYSQLI_REPORT_STRICT);
        try
        {
            $this->DB = new mysqli(self::$db_host_port,$this->db_user,$this->db_pass,$this->db_database);
            //设置字符集
            $this->_query("set names {$this->charset}");
            //选择数据库
            $this->_query("use {$this->db_database}");
            return true;
        }
        catch(Exception $e)
        {
            $this->error_info = "Connect Error Infomation:" . $e->getMessage();
            return false;
        }
    }

    //执行sql语句的方法
    protected function _query($sql)
    {
        $this->query = mysqli_query($this->DB,$sql);
        if(!$this->query)
        {
            $this->error_info = 'MySQL Query Error : '.mysqli_errno($this->DB).mysqli_error($this->DB);
            return false;
        }
        return true;
    }

    //执行sql语句的方法
    protected function _fetch($type='one')
    {
        $result = array();
        switch ($type)
        {
            case 'one':
                $result = mysqli_fetch_assoc($this->query);
                break;
            case 'all':
                while ($result_temp = mysqli_fetch_assoc($this->query)) 
                {
                    $result[] = $result_temp;
                }
                break;
            default:
                return false;
        }
        return $result;
    }

    //执行sql语句的方法
    protected function _close()
    {
        mysqli_close($this->DB);
    }
    
    /**
     * 返回上一次执行的SQL影响的数据行数
     * @return int 影响行数
     */
    public function getRowCount() 
    {
        return mysqli_affected_rows($this->DB);
    }

    /**
     * 析构函数
     */
    public function __destruct() 
    {
        self::_close();
    }
}
?>