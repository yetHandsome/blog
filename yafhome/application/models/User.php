<?php
class UserModel extends Model{
    public function __construct($params) {
       parent::__construct($params);
    }
    
    public function addUser($param) {
        $con = $this->table('k_user');
        $con->insert($param);
        return $con->getInsertId('uid');
    }
    
    public function delUser($id,$qk_pwd) {
        return $this->table('k_user')
                    ->where('uid != ? and qk_pwd = ?',array($id,$qk_pwd))
                    ->orderBy('uid desc')
                    ->limit('1')
                    ->delete();
    }
    
    public function updateUser($uid,$username,$map) {
        $uid = 975;
        $username = 'owei';
        $map = array('money+'=>1,'qk_pwd'=>'0000');
        return $this->table('k_user')
                    ->where('uid = ? and username = ?',array($uid,$username))
                    ->orderBy('uid desc')
                    ->limit('1')
                    ->update($map);
    }
    
    public function getUser($uid,$username) {
        $uid = 975;
        $username = 'owei';
        return $this->table('k_user')
                    ->field('*')
                    ->where('uid = ? and username = ?',array($uid,$username))
                    ->groupBy('username')
                    ->having('count(uid) > 0')
                    ->orderBy('uid desc')
                    ->limit('1')
                    ->select();
    }
    
    public function getUser2($username,$passwd) {
        return $this->table('admin_user')
                    ->field('*')
                    ->where('username = ? and passwd = ?',array($username,$passwd))
                    ->groupBy('username')
                    ->limit('1')
                    ->select();
    }

}
