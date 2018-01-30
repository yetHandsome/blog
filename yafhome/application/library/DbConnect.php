<?php
class DbConnect{
    private static $Instace = [];           ///对象
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
        
        $dsn    = "{$dbms}:host={$host};port={$port};dbname={$dbName}";
        
        try {
            $dbh = new PDO($dsn, $user, $pass); //初始化一个PDO对象
            return $dbh;
        } catch (PDOException $e) {
            die ("Error!: " . $e->getMessage() . "<br/>");
        }
    }
}
