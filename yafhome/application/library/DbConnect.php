<?php
class DbConnect{
    private static $Instace = [];           ///对象
    
    private function __construct(){ //不允许实利化
    }

    public static function getInstace($params){
        if(!isset(static::$Instace[$params])){
            static::$Instace[$params] = static::connectDb($params);
        }
        return static::$Instace[$params];
    }
    
    public static function connectDb($params){
        $config = Yaf_Registry::get('config');
        
        $dbms   = $config->database->$params->type;
        $host   = $config->database->$params->host;
        $port   = $config->database->$params->port;
        $dbName = $config->database->$params->name;
        $user   = $config->database->$params->user;
        $pass   = $config->database->$params->pwd;
        
        $dsn    = "{$dbms}:host={$host};port={$port};dbname={$dbName}"; //;charset=utf8设置数据库编码可提高安全性
        
        try {
            $dbh = new PDO($dsn, $user, $pass); //初始化一个PDO对象
            $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); //禁止PHP模拟预编译
            return $dbh;
        } catch (PDOException $e) {
            die ("Error!: " . $e->getMessage() . "<br/>");
        }
    }
}
