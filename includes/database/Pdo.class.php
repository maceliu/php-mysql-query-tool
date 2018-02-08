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
    public function __construct($db_host,$db_port,$db_user,$db_pwd,$db_database,$charset='utf8') 
    {
        echo 'pdo';
        parent::__construct($db_host,$db_port,$db_user,$db_pwd,$db_database,$charset);
        //连接数据库
        $this->_connect();
    }
    
    /**
     * 创建数据库连接
     * @return bool 连接是否成功  true=>成功   false=>失败
     */
    protected function _connect() 
    {
        try 
        {
            $this->DB = new PDO(self::$db_type . ':host=' . $this->db_host . ';port=' . $this->db_port . ';dbname=' . $this->db_database, $this->db_user, $this->db_pwd, array (PDO::ATTR_PERSISTENT => self::$connect));
            if ($this->DB) 
            {
                $this->DB->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
                $this->DB->setAttribute(PDO::ATTR_EMULATE_PREPARES,true);
                $sql = 'SET NAMES ' . $this->charset;
                self::_query($sql);
                return true;
            }     
        }
        catch(PDOException $e)
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
        $this->sql = $sql;
        $this->query = $this->DB->query($this->sql);
        if($this->DB->errorCode() != '00000') 
        {
            $info = ($this->query) ? $this->query->errorInfo() : $this->DB->errorInfo();
            $this->error_info = 'MySQL query error : '.$info[1].' '.$info[2];
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
        return $this->query->rowCount();
    }

    /**
     * 关闭数据连接
     */
    protected function _close() 
    {
        $this->DB = null;
    }

    /**
     * 析构函数
     */
    public function __destruct() 
    {
        self::_close();
    }

}
