<?php
/**
 *  Mysqli操作MYSQL数据库类
 *  @author liubo  2017-06-20
 */
require_once dirname(__FILE__).'/MysqlDriver.class.php';

class MysqliMysqlDriver extends MysqlDriver
{
    
    public static $db_host_port = '';

    /**
     * 构造函数
     */
    public function __construct($db_host,$db_port,$db_user,$db_pwd,$db_database,$charset='utf8')
    {
        echo 'mysqli';
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
        mysqli_report(MYSQLI_REPORT_STRICT);
        try
        {
            $this->DB = new mysqli(self::$db_host_port,$this->db_user,$this->db_pwd,$this->db_database);
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

    /**
     * 执行一条SQL语句
     * @param $sql str 要执行的SQL语句
     * @return array=>执行成功   bool(false)=>执行失败
     */
    protected function _query($sql)
    {
        $this->query = $this->DB->query($sql);
        if($this->DB->errno)
        {
            $this->error_info = 'MySQL query error : '.$this->DB->errno.' '.$this->DB->error;
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
                $result = $this->query->fetch_assoc();
                break;
            case 'all':
                $result = $this->query->fetch_all(MYSQLI_ASSOC);
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
        return mysqli_affected_rows($this->DB);
    }

    /**
     * 关闭数据连接
     */
    protected function _close()
    {
        $this->DB->close();
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
