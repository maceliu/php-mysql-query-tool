<?php

class DbFactory 
{
    //可用使用的数据库引擎列表，若要新增数据库引擎，则需要在此添加配置（排名分先后，若auto模式，则优先使用靠前的引擎）
    private $db_driver_list = array('Pdo','Mysqli','Mysql');
    public $db_driver_selected = '';
    public $error_info = '';

    public function createDriver($db_config_arr,$db_driver='Auto')
    {
        $db_driver = ucfirst($db_driver);
        if ($db_driver == 'Auto') 
        {
            $db_driver_selected = $this->selectDriverAuto();         
        }
        else
        {
            $db_driver_selected = $this->selectDriver($db_driver);
        }

        if (!$db_driver_selected) return false;

        $require_file_name = $db_driver_selected.'.class.php';
        $include_file_path = dirname(__FILE__).'/'.$require_file_name;
        if (file_exists($include_file_path)) 
        {
            require_once $include_file_path;
            $driver_class_name = $db_driver_selected.'MysqlDriver';
            if (class_exists($driver_class_name,false)) 
            {
                $driver_obj = new $driver_class_name($db_config_arr['host'],$db_config_arr['port'],$db_config_arr['user'],$db_config_arr['pass'],$db_config_arr['db'],$db_config_arr['charset']);
                return $driver_obj;
            }
            else
            {
                $this->error_info = 'Class'.$driver_class_name.' not exists';
                return false;
            }
        }
        else
        {
            $this->error_info = 'Can not find '.$include_file_path;
            return false;
        }


    }

    /**
     * 按db_driver_list顺序自动选择可用的数据库引擎类型
     * @return str 数据库引擎名称
     */
    public function selectDriver($db_driver)
    {
        if (in_array($db_driver,$this->db_driver_list)) 
        {
            if ($this->$db_driver()) 
            {
                $db_driver_selected = $db_driver;
                return $db_driver_selected;
            }
            else
            {
                $this->error_info = $db_driver. ' is not supported under the current environment';
                return false;
            }
        }
        else
        {
            $this->error_info = $db_driver. ' is not added to the configuration array';
            return false;
        }
    }



    /**
     * 按db_driver_list顺序自动选择可用的数据库引擎类型
     * @return str 数据库引擎名称
     */
    public function selectDriverAuto()
    {
        $is_available = false;
        foreach ($this->db_driver_list as $key => $db_driver_name) 
        {
            $is_available = $this->$db_driver_name();
            if ($is_available) 
            {
                return $db_driver_name;
            }
        } 

        $this->error_info = 'No MySQL driver is supported under the current environment';
        return false;
    }

    




    /**
     * 判断当前PHP环境是否支持Pdo引擎
     * @return bool true支持  false不支持
     */
    public function Pdo()
    {
        return class_exists('PDO',false) ? true : false;
    }

    /**
     * 判断当前PHP环境是否支持Mysqli引擎
     * @return bool true支持  false不支持
     */
    public function Mysqli()
    {
        return class_exists('mysqli',false) ? true : false;
    }


    /**
     * 判断当前PHP环境是否支持Mysql引擎
     * @return bool true支持  false不支持
     */
    public function Mysql()
    {
        return function_exists('mysql_connect') ? true : false;
    }



}


?>