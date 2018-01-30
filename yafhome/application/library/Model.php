<?php
class Model{
    private $conn;
    private $table;
    private $sql;
    private $whereBindParam=array();
    private $options;
    
    public function __construct($params) {
        $conn = DbConnect::getInstace($params);
        $this->conn = $conn;
    }
    
    public function getconn() {
        return $this->conn;
    }
    
    //在客户端感觉上Model是继承了PDO，其实是一种组合写法
    public function __call($func, $arguments){
        return call_user_func_array(array($this->conn,$func),array($arguments));
    }
    
    public function table($table){
        $this->table = $table;
        return $this;
    }
    
    public function insert($map){
        if (!$map || !is_array($map)) {
            return FALSE;
        } else {
            $fields = $values = array();

            foreach ($map as $key => $value) {
                $fields[] = '`' . $key . '`';
                $values[] = ":{$key}";
            }

            $fieldString = implode(',', $fields);
            $valueString = implode(',', $values);

            $this->sql = 'INSERT INTO ' . $this->table . " ($fieldString) VALUES ($valueString)";
            $stmt = $this->conn->prepare($this->sql);
            foreach ($map as $key => $value) {
                ${$key} = $value;
                $stmt->bindParam(":{$key}", ${$key});
                //$stmt->bindValue(":{$key}", $value);
            }
            if ($this->run($stmt)) {
                return $stmt->rowCount() ? $stmt->rowCount() : true;
            }
            return false;
        }
    }
    
    public function getInsertId($name=''){
        return $this->conn->lastInsertId($name);
    } 
    
    /**
     * Field
     */
    final public function field($field) {
        if (!$field) {
            return $this;
        }

        $str = '';
        if (is_array($field)) {
            foreach ($field as $val) {
                $str .= '`' . $val . '`, ';
            }

            $this->options['field'] = substr($str, 0, strlen($str) - 2); // 2:　Cos there is a BLANK
        } else {
            $this->options['field'] = $field;
        }

        unset($str, $field);
        return $this;
    }
    
    public function select(){
        if (isset($this->options['field'])) {
            $field = $this->options['field'];
        } else {
            $field = '*';
        }

        $this->sql = 'SELECT ' . $field . ' FROM `' . $this->table . '`';
        
        if(isset($this->options['where'])){
            $this->sql.= ' WHERE ' . $this->options['where'];
        }
        
        if (isset($this->options['group'])) {
            $this->sql .= ' GROUP BY ' . $this->options['group'];
            if (isset($this->options['having'])) {
                $this->sql .= ' HAVING ' . $this->options['having'];
            }
        }
        
        if (isset($this->options['order'])) {
            $this->sql .= ' ORDER BY ' . $this->options['order'];
        }
        
        if (isset($this->options['limit'])) {
            if(!is_numeric($this->options['limit'])){
                exit('delete 语句不支持limit '.$this->options['limit'].' 请改成类似limit x');
            }
            $this->sql .= ' LIMIT ' . $this->options['limit'];
        }
        
        $stmt = $this->conn->prepare($this->sql);
        if ($this->run($stmt,$this->whereBindParam)){
            return $result=$stmt->fetchAll(PDO::FETCH_ASSOC); 
        }
        
        return false;

    }
    
    public function update($map, $update_all = false){
        //如果是一个没有条件的跟新，那么必须指定这个是全部跟新
        if (!$this->options['where'] && $update_all) {
            return FALSE;
        }

        if (!$map) {
            return FALSE;
        } else {
            $this->sql = 'UPDATE `' . $this->table . '` SET ';
            $sets = array();
            $sets_value = array();
            
            foreach ($map as $key => $value) {
                if (strpos($key, '+') !== FALSE) {
                    list($key, $flag) = explode('+', $key);
                    $sets[] = "`$key` = `$key` + ?";
                } elseif (strpos($key, '-') !== FALSE) {
                    list($key, $flag) = explode('-', $key);
                    $sets[] = "`$key` = `$key` - ?";
                } else {
                    $sets[] = "`$key` = ?";
                }
                $sets_value[] = $value;
            }

            $this->sql .= implode(',', $sets) . ' ';

            if(isset($this->options['where'])){
                $this->sql.= ' WHERE ' . $this->options['where'];
            }

            if (isset($this->options['order'])) {
                $this->sql .= ' ORDER BY ' . $this->options['order'];
            }

            if (isset($this->options['limit'])) {
                $this->sql .= ' LIMIT ' . $this->options['limit'];
            }
            
            $stmt = $this->conn->prepare($this->sql);
            $this->whereBindParam = array_merge($sets_value,$this->whereBindParam);
            if ($this->run($stmt,$this->whereBindParam)){
                return $stmt->rowCount() ? $stmt->rowCount() : true;
            }
        
            return false;
        }
    }
    
    public function delete($delall=false){
        //如果是一个没有删除条件的删除，那么必须指定这个是全部删除
        if (!isset($this->options['where']) && $delall) {
            return FALSE;
        }
        
        $this->sql = 'DELETE FROM `' . $this->table . '`';
        
        if(isset($this->options['where'])){
            $this->sql.= ' WHERE ' . $this->options['where'];
        }
        
        if (isset($this->options['order'])) {
            $this->sql .= ' ORDER BY ' . $this->options['order'];
        }
        
        if (isset($this->options['limit'])) {
            if(!is_numeric($this->options['limit'])){
                exit('delete 语句不支持limit '.$this->options['limit'].' 请改成类似limit x');
            }
            $this->sql .= ' LIMIT ' . $this->options['limit'];
        }
        
        $stmt = $this->conn->prepare($this->sql);
        
        if ($this->run($stmt,$this->whereBindParam)){
            return $stmt->rowCount() ? $stmt->rowCount() : true;
        }
        
        return false;
    }
    
    //这里特意不用query，避免跟PDO的query同名
    public function runSql($sql,$bind_paramlist = array(),$type = 'DQL'){
        $this->sql = $sql;
        $this->whereBindParam = $bind_paramlist;
        
        $stmt = $this->conn->prepare($this->sql);
        
        //DQL  SELECT
        if($type == 'DQL'){
            $this->run($stmt,$this->whereBindParam);
            return $result=$stmt->fetchAll(PDO::FETCH_ASSOC); 
            
        //DML  INSERT UPDATE DELETE
        }elseif($type == 'DML'){
            if ($this->run($stmt,$this->whereBindParam)){
                return $stmt->rowCount() ? $stmt->rowCount() : true;
            } 
            
        //DDL CREATE TABLE/VIEW/INDEX/SYN/CLUSTER
        }elseif($type == 'DDL'){
            return $this->run($stmt,$this->whereBindParam);
            
        //DCL GRANT ROLLBACK COMMIT
        }else{
            return $this->run($stmt,$this->whereBindParam);
        }
    }
    
    public function run($stmt,$BindParam = array()){
        $this->clear();
        if(!empty($BindParam)){
            return $stmt->execute($BindParam);
        }else{
            return $stmt->execute();
        }
    }
    
    public function clear(){
        $this->table            = null;
        $this->options          = null;
        $this->whereBindParam   = array();
    }
    
    public function getSql() {
        return $this->sql;
    }
    
    /*
     * $sth = $dbh->prepare('SELECT name, colour, calories
            FROM fruit
            WHERE calories < ? AND colour = ?');
        $sth->execute($this->whereBindParam);
     */
    public function where($str,$whereBindParam_list = array()){
        $this->options['where'] = $str;
        $this->whereBindParam   = $whereBindParam_list;
        return $this;
    }
    
    public function groupBy($str){
        $this->options['group'] = $str;
        return $this;
    }
    
    public function orderBy($str){
        $this->options['order'] = $str;
        return $this;
    }
    
    
    public function having($str){
        $this->options['having'] = $str;
        return $this;
    }
    
    public function limit($str){
        $this->options['limit'] = $str;
        return $this;
    }
    
    public function beginTransaction(){
        $this->conn->beginTransaction();
    }
    
    public function commint(){
        $this->conn->commit();
    }
    
    public function rollBack(){
        $this->conn->rollBack();
    }
    
    

}
