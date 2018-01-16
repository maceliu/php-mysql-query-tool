<?php
/**
 *  PDO操作MYSQL数据库类
 *  @author liubo  2017-06-20
 */
require_once dirname(__FILE__).'/database.class.php';

class PdoMysql extends database
{
    public static $dbtype = 'mysql';
    public static $dbhost = '';
    public static $dbport = '';
    public static $dbname = '';
    public static $dbuser = '';
    public static $dbpass = '';
    public static $charset = '';
    public static $stmt = null;
    public static $DB = null;
    public static $connect = true; // 是否長连接
    public static $debug =  1;
    private static $parms = array ();
    public $error_info = '';
    public $sql = '';
    
    /**
     * 构造函数
     */
    public function __construct($host,$port,$user,$pass,$db,$charset='utf8') 
    {
        self::$dbtype = 'mysql';
        self::$dbhost = $host;
        self::$dbport = $port;
        self::$dbname = $db;
        self::$dbuser = $user;
        self::$dbpass = $pass;
        self::$connect = true;
        self::$charset = $charset;
        if(self::_connect())
        {
            self::$DB->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
            self::$DB->setAttribute(PDO::ATTR_EMULATE_PREPARES,true);
            $sql = 'SET NAMES ' . self::$charset;
            self::_query($sql);
        }
    }

    /**
     * 析构函数
     */
    public function __destruct() 
    {
        self::_close();
    }
     
    /*********************基本方法开始********************/

    /**
     * 创建连接数据库
     * @return bool 连接是否成功  true=>成功   false=>失败
     */
    protected function _connect() 
    {
        try 
        {
            self::$DB = new PDO(self::$dbtype . ':host=' . self::$dbhost . ';port=' . self::$dbport . ';dbname=' . self::$dbname, self::$dbuser, self::$dbpass, array (PDO::ATTR_PERSISTENT => self::$connect));
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
    public function _close() 
    {
        self::$DB = null;
    }


    /**
     * 执行一条SQL语句，并返回结果的数据
     * @param $type int 操作类型  0获取1条  1获取全部
     * @return array=>正常查询结果   bool(false)=>查询失败
     */
    public function _query($sql)
    {
        $this->sql = $sql;
        self::$stmt = self::$DB->query($this->sql);
        if(self::getPDOError()) return false;
        return self::$stmt;
    }

    /**
     * 执行一条SQL语句，并返回结果的数据
     * @param $type int 操作类型  0获取1条  1获取全部
     * @return array=>正常查询结果   bool(false)=>查询失败
     */
    protected function _fetch($type='all')
    {
        $result = array();
        self::$stmt->setFetchMode(PDO::FETCH_ASSOC);
        switch ($type)
        {
            case 'one':
                $result = self::$stmt->fetch();
                break;
            case 'all':
                $result = self::$stmt->fetchAll();
                break;
            default:
                return false;
        }
        return $result;
    }




    
    /**
     * 返回最后插入行的ID或序列值
     * @return int ID或序列值
     */
    public function getInsertId() 
    {
        return self::$DB->lastInsertId();
    }
    
    /**
     * 获取要操作的数据SQL
     * @param $table string 表名
     * @param $args  array  数据
     * @return string 生成的SQL
     */
    private function getCode($table,$args) 
    {
        $code = '';
        if (is_array($args)) 
        {
            foreach ($args as $k => $v) 
            {
                // if ($v == '') {
                //     continue;
                // }
                $code .= "`$k`='$v',";
            }
        }
        $code = substr($code,0,-1);
        return $code;
    }

    /*********************Sql操作方法开始********************/

    /**
     * 向指定表写入数据
     * @param $table string 表名
     * @param $args  array  数据
     * @return int 插入的数据ID
     */
    public function add($table, $args) 
    {
        $sql = "INSERT INTO `$table` SET ";
        $code = self::getCode($table,$args);
        $sql .= $code;
        $this->sql = $sql;
        return self::_execute();
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
        $code = self::getCode($table,$args);
        $sql = "UPDATE `$table` SET ";
        $sql .= $code;
        $sql .= " Where $where ";
        $this->sql = $sql;
        return self::_execute();
    }
     
    /**
     * 删除数据
     * @param $table string 表名
     * @param $where sting  删除条件
     * @return int 受影响的数据
     */
    public function delete($table, $where) 
    {
        $sql = "DELETE FROM `$table` Where $where";
        $this->sql = $sql;
        return self::_execute();
    }

    /**
     * 执行SQL返回第一条数据
     * @param $sql string 要执行的SQL
     * @return array 查询结果  一维数组
     */
    public function getOne($sql) 
    {
        self::$stmt = $this->_query($sql);
        if (self::$stmt) 
        {
            return $this->_fetch('one');
        }
        else
        {
            return false;
        }
    }

    /**
     * 执行SQL返回所有
     * @param $sql string 要执行的SQL
     * @return array 查询结果  二维数组
     */
    public function getAll($sql) 
    {
        self::$stmt = $this->_query($sql);
        if (self::$stmt) 
        {
            return $this->_fetch();
        }
        else
        {
            return false;
        }
    }


    /**
     * 返回上一次执行的SQL影响的数据行数
     * @return int 影响行数
     */
    public function getRowCount() 
    {
        return self::$stmt->rowCount();
    }
     

    /*********************错误处理********************/

    /**
     * 设置是否为调试模式
     */
    public function setDebugMode($mode = true) 
    {
        return ($mode == true) ? self::$debug = true : self::$debug = false;
    }
     
    /**
     * 捕获PDO错误信息
     * 返回:出错信息
     * 类型:字串
     */
    private function getPDOError() 
    {
        if (self::$DB->errorCode () != '00000') 
        {
            $info = (self::$stmt) ? self::$stmt->errorInfo () : self::$DB->errorInfo ();
            // self::sqlError( 'mySQL Query Error', $info [2], $sql );
            $this->error_info = 'mySQL Query Error'.$info [2];
            return true;
        }
        else
        {
            return false;
        }
    }

    private function getSTMTError($sql) 
    {
        if (self::$stmt->errorCode () != '00000') 
        {
            $info = (self::$stmt) ? self::$stmt->errorInfo () : self::$DB->errorInfo ();
            // echo (self::sqlError ( 'mySQL Query Error', $info [2], $sql ));
            self::$error_info = 'mySQL Query Error'.$info [2].$sql;
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 作用:获取错误信息
     * 返回:错误信息详情
     * 类型:str
     */
    public function getError() 
    {
        return $this->error_info;
    }

     
    /**
     * 写入错误日志
     */
    private function errorFile($error_info) 
    {
        if (!is_dir(MYSQL_ERROR_PATH)) 
        {
            mkdir(MYSQL_ERROR_PATH);
        }
        $errorfile = MYSQL_ERROR_PATH.'mysql_error_'.date('Ymd').'.log';
        $sql = str_replace(array("\n","\r","\t","  ","  ","  "), array(" "," "," "," "," "," "),$sql);
        if (!file_exists($errorfile))
        {
            $fp = file_put_contents ( $errorfile, "\n" . date('Y-m-d H:i:s').' '.$error_info );
        } 
        else 
        {
            $fp = file_put_contents ( $errorfile, "\n" . date('Y-m-d H:i:s').' '.$error_info , FILE_APPEND );
        }
    }
     
    /**
     * 作用:运行错误信息
     * 返回:运行错误信息和SQL语句
     * 类型:字符
     */
    private function sqlError($message = '', $info = '', $sql = '') 
    { 
        $html = '';
        if ($message)
        {
            $html .=  $message;
        }
         
        if ($info)
        {
            $html .= ' SQLID: ' . $info ;
        }
        if ($sql)
        {
            $html .= ' ErrorSQL: ' . $sql;
        }
        // self::errorFile($html);
        if(self::$debug) throw new Exception($html);
    }

}