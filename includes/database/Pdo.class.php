<?php
/**
 *  PDO操作MYSQL数据库类
 *  @author liubo  2017-06-20
 */
require_once dirname(__FILE__).'/MysqlDriver.class.php';

class PdoMysqlDriver extends MysqlDriver
{
    
    public static $db_type = 'mysql';
    public static $connect = true; // 是否長连接
    
    /**
     * 构造函数
     */
    public function __construct($db_host,$db_port,$db_user,$db_pass,$db_database,$charset='utf8') 
    {
        $this->db_host = $db_host;
        $this->db_port = $db_port;
        $this->db_user = $db_user;
        $this->db_pass = $db_pass;
        $this->db_name = $db_database;
        $this->charset = $charset;
        if(self::_connect())
        {
            $this->DB->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
            $this->DB->setAttribute(PDO::ATTR_EMULATE_PREPARES,true);
            $sql = 'SET NAMES ' . $this->charset;
            self::_query($sql);
        }
    }
    
    /**
     * 创建连接数据库
     * @return bool 连接是否成功  true=>成功   false=>失败
     */
    protected function _connect() 
    {
        try 
        {
            $this->DB = new PDO(self::$db_type . ':host=' . $this->db_host . ';port=' . $this->db_port . ';dbname=' . $this->db_name, $this->db_user, $this->db_pass, array (PDO::ATTR_PERSISTENT => self::$connect));
            return true;
        }
        catch(PDOException $e)
        {
            $this->error_info = "Connect Error Infomation:" . $e->getMessage();
            return false;
        }
    }
     
    /**
     * 关闭数据连接
     */
    protected function _close() 
    {
        $this->DB = null;
    }

    /**
     * 执行一条SQL语句，并返回结果的数据
     * @param $type int 操作类型  0获取1条  1获取全部
     * @return array=>正常查询结果   bool(false)=>查询失败
     */
    protected function _query($sql)
    {
        $this->sql = $sql;
        $this->query = $this->DB->query($this->sql);
        if(self::isError()) return false;
        return true;
    }

    /**
     * 执行一条SQL语句，并返回结果的数据
     * @param $type int 操作类型  0获取1条  1获取全部
     * @return array=>正常查询结果   bool(false)=>查询失败
     */
    protected function _fetch($type='one')
    {
        $result = array();
        $this->query->setFetchMode(PDO::FETCH_ASSOC);
        switch ($type)
        {
            case 'one':
                $result = $this->query->fetch();
                break;
            case 'all':
                $result = $this->query->fetchAll();
                break;
            default:
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
        return $this->query->rowCount();
    }
     
    /**
     * 捕获PDO错误信息
     * 返回:出错信息
     * 类型:字串
     */
    private function isError() 
    {
        if ($this->DB->errorCode () != '00000') 
        {
            $info = ($this->query) ? $this->query->errorInfo () : $this->DB->errorInfo ();
            $this->error_info = 'MySQL Query Error : '.$info [2];
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 析构函数
     */
    public function __destruct() 
    {
        self::_close();
    }

}
