<?php
/**
 *  PDO操作MYSQL数据库类
 *  @author liubo  2017-06-20
 */
require_once dirname(__FILE__).'/database.class.php';

class PdoMysql extends database
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
    public function _close() 
    {
        $this->DB = null;
    }


    /**
     * 执行一条SQL语句，并返回结果的数据
     * @param $type int 操作类型  0获取1条  1获取全部
     * @return array=>正常查询结果   bool(false)=>查询失败
     */
    public function _query($sql)
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
     * 返回最后插入行的ID或序列值
     * @return int ID或序列值
     */
    public function getInsertId() 
    {
        return $this->DB->lastInsertId();
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
        $query_result = $this->_query($sql);
        if ($query_result && $this->query) 
        {
            return $this->_fetch('one');
        }
        else
        {
            return false;
        }
    }

    // /**
    //  * 执行SQL返回所有
    //  * @param $sql string 要执行的SQL
    //  * @return array 查询结果  二维数组
    //  */
    // public function getAll($sql) 
    // {
    //     $query_result = $this->_query($sql);
    //     if ($query_result && $this->query) 
    //     {
    //         return $this->_fetch('all');
    //     }
    //     else
    //     {
    //         return false;
    //     }
    // }


    /**
     * 返回上一次执行的SQL影响的数据行数
     * @return int 影响行数
     */
    public function getRowCount() 
    {
        return $this->query->rowCount();
    }
     

    /*********************错误处理********************/
     
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
            // self::sqlError( 'mySQL Query Error', $info [2], $sql );
            $this->error_info = 'MySQL Query Error : '.$info [2];
            return true;
        }
        else
        {
            return false;
        }
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
        if($this->error_reporting) throw new Exception($html);
    }

}