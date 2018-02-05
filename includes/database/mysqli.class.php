<?php
/**
 *  PDO操作MYSQL数据库类
 *  @author liubo  2017-06-20
 */
require_once dirname(__FILE__).'/database.class.php';


class ConnectMysqli extends database
{
    //私有的属性
    private $db_host;
    private $port;
    private $host_port;
    private $user;
    private $pass;
    private $db;
    private $charset;
    private $link;
    public $error_info = '';
    public $sql = '';

    //私有的构造方法
    public function __construct($host,$port,$user,$pass,$db,$charset='utf8')
    {
        $this->host = $host;
        $this->host_port =  empty($port) ? $this->host : $this->host.':'.$port;
        $this->user = $user;
        $this->pass = $pass;
        $this->db = $db;
        $this->charset= $charset;
        //连接数据库
        if ($this->connect()) 
        {
            //设置字符集
            $this->_query("set names {$this->charset}");
            //选择数据库
            $this->_query("use {$this->db}");
        }
    }

    //连接数据库
    private function _connect()
    {
        mysqli_report(MYSQLI_REPORT_STRICT);
        try
        {
            $this->link = new mysqli($this->host_port,$this->user,$this->pass,$this->db);
            return true;
        }
        catch(Exception $e)
        {
            $this->error_info = "Connect Error Infomation:" . $e->getMessage();
            return false;
        }
    }

    //执行sql语句的方法
    public function _query($sql)
    {
        $res=mysqli_query($this->link,$sql);
        if(!$res)
        {
            echo "sql语句执行失败<br>";
            echo "错误编码是".mysqli_errno($this->link)."<br>";
            echo "错误信息是".mysqli_error($this->link)."<br>";
        }
        return $res;
    }

    //获得最后一条记录id
    public function getInsertId()
    {
        return mysqli_insert_id($this->link);
    }
    
    /**
     * 查询某个字段
     * @param
     * @return string or int
     */
    public function getOne($sql)
    {
        $query=$this->query($sql);
        return mysqli_free_result($query);
    }

    //获取一行记录,return array 一维数组
    public function getRow($sql,$type="assoc")
    {
        $query=$this->query($sql);
        if(!in_array($type,array("assoc",'array',"row")))
        {
            die("mysqli_query error");
        }
        $funcname="mysqli_fetch_".$type;
        return $funcname($query);
    }
    
    //获取一条记录,前置条件通过资源获取一条记录
    public function getFormSource($query,$type="assoc")
    {
        if(!in_array($type,array("assoc","array","row")))
        {
            die("mysqli_query error");
        }
        $funcname="mysqli_fetch_".$type;
        return $funcname($query);
    }

    //获取多条数据，二维数组
    public function getAll($sql)
    {
        $query = $this->query($sql);
        $list=array();
        while ($r=$this->getFormSource($query)) 
        {
            $list[]=$r;
        }
        return $list;
    }
    /**
    * 定义添加数据的方法
    * @param string $table 表名
    * @param string orarray $data [数据]
    * @return int 最新添加的id
    */
    public function insert($table,$data)
    {
        //遍历数组，得到每一个字段和字段的值
        $key_str='';
        $v_str='';
        foreach($data as $key=>$v)
        {
            if(empty($v))
            {
                die("error");
            }
            //$key的值是每一个字段s一个字段所对应的值
            $key_str.=$key.',';
            $v_str.="'$v',";
        }
        $key_str=trim($key_str,',');
        $v_str=trim($v_str,',');
        //判断数据是否为空
        $sql="insert into $table ($key_str) values ($v_str)";
        $this->query($sql);
        //返回上一次增加操做产生ID值
        return $this->getInsertId();
     }

    /*
     * 删除一条数据方法
     * @param1 $table, $where=array('id'=>'1') 表名 条件
     * @return 受影响的行数
     */
    public function deleteOne($table, $where)
    {
        if(is_array($where))
        {
            foreach ($where as $key => $val) 
            {
                $condition = $key.'='.$val;
            }
        } 
        else 
        {
            $condition = $where;
        }
        $sql = "delete from $table where $condition";
        $this->query($sql);
        //返回受影响的行数
        return mysqli_affected_rows($this->link);
    }

    /*
     * 删除多条数据方法
     * @param1 $table, $where 表名 条件
     * @return 受影响的行数
     */
    public function deleteAll($table, $where)
    {
        if(is_array($where))
        {
            foreach ($where as $key => $val) 
            {
                if(is_array($val))
                {
                    $condition = $key.' in ('.implode(',', $val) .')';
                } 
                else 
                {
                    $condition = $key. '=' .$val;
                }
            }
        } 
        else 
        {
            $condition = $where;
        }
        $sql = "delete from $table where $condition";
        $this->query($sql);
        //返回受影响的行数
        return mysqli_affected_rows($this->link);
    }

    /**
     * [修改操作description]
     * @param [type] $table [表名]
     * @param [type] $data [数据]
     * @param [type] $where [条件]
     * @return [type]
     */
    public function update($table,$data,$where)
    {
        //遍历数组，得到每一个字段和字段的值
        $str='';
        foreach($data as $key=>$v)
        {
            $str.="$key='$v',";
        }
        $str=rtrim($str,',');
        //修改SQL语句
        $sql="update $table set $str where $where";
        $this->query($sql);
        //返回受影响的行数
        return mysqli_affected_rows($this->link);
    }


    /**
     * 返回上一次执行的SQL影响的数据行数
     * @return int 影响行数
     */
    public function getRowCount() 
    {
        return mysqli_affected_rows($this->link);
    }



}

//使用示例

// $db = new ConnectMysqli($config_arr['host'],$config_arr['port'],$config_arr['user'],$config_arr['pass'],$config_arr['db'],$config_arr['charset']);

// $sql = "SELECT  * from test_table";

// $list = $db->getAll($sql);

// $db->p($list);



?>