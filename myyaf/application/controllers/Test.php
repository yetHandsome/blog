<?php
class TestController extends Yaf_Controller_Abstract {
    public function init(){
        echo 'you are so smart!!';die;
    }
    //http://myyaf.com/index/index
    //http://myyaf.com
    public function indexAction() {//默认Action
        $config = Yaf_Registry::get("config");
        p('indexAction');
        
        $model = new UserModel('private');
        $res = $model->getUser(975,'owei');
        p($res);
        p($model->getSql());
        
        $model2 = new Model('private');
        $res2 = $model2->table('k_user')
                    ->field('uid,username')
                    ->where('uid = ? and username = ?',array(975,'owei'))
                    ->groupBy('username')
                    ->having('count(uid) > 0')
                    ->orderBy('uid desc')
                    ->limit('1')
                    ->select();
        p($res2);
        p($model2->getSql());
        
        $prepare_sql = 'SELECT uid,username,site_id FROM `k_user` WHERE uid= ? and username = ? GROUP BY username HAVING count(uid) > 0 ORDER BY uid desc LIMIT 1';
        $res3 = $model2->runSql($prepare_sql,array(975,'owei'),'DQL');
        p($res3);
        p($model2->getSql());
        
        $sql = 'SELECT uid,username,site_id FROM `k_user`  GROUP BY username HAVING count(uid) > 0 ORDER BY uid desc LIMIT 3';
        $res4 = $model2->getconn()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        p($res4);
        p($model2->getSql());//直接用query无法获取上一次的SQL，获取到的是上上次的SQL
        
        $this->getView()->assign("content", "WTFx");
    }
   
   //http://myyaf.com/index/delUser
   public function delUserAction() {
        p('delUserAction');
        $model = new UserModel('private');
        $res = $model->delUser($id=975,$qk_pwd=1234);
        p($res);
        //$this->getView()->assign("content", "WTFx");
   }
   
   //http://myyaf.com/index/updateUser
   public function updateUserAction() {
        p('updateUserAction');
        $model = new UserModel('private');
        $uid = 975;
        $username = 'owei';
        $map = array('money+'=>1,'qk_pwd'=>0000);
        $res = $model->updateUser($uid,$username,$map);
        p($res);
        //$this->getView()->assign("content", "WTFx");
   }
   
   //http://myyaf.com/index/addUser
   public function addUserAction() {
        p('addUserAction');
        $data = array('username'=>'yaf'.time(), 'password'=>md5('123456'),'qk_pwd'=>1234,'site_id'=>'t');
        $model = new UserModel('private');
        $res = $model->addUser($data);
        p($res);
   }
   
    public function testAction() {
        echo 1234;die;
        $config = Yaf_Registry::get("config");
        p('loginAction');
        $request = $this->getRequest();
        p($request->getRequestUri());     //   输出：/login/login
        p($request->getBaseUri());        //   输出：''
        p($request->getMethod());         //   输出GET
        //p($request->get());           //   输出：array()
        p($request->getPost());           //   输出：array()
        p($request->getQuery());          //   输出: array()
        p($request->getParam('some'));      //   输出：NULL
        p($request->getParams());         //   输出：array()
        p($request->getMethod());         //   输出GET
        p($request->isCli());             //false
        p($request->isGet());             //false
        p($request->isPost());            //false
        p($request->isPut());             //false
        p($request->isHead());            //false
        p($request->isOptions());         //false
        p($request->isXmlHttpRequest());  //false
        $res = $this->getResponse();
        $res -> setHeader( 'Content-Type', 'text/html; charset=utf-8' );
        $headers =array('Content-Type'=>'text/html;charset=utf-8', 'Server'=>'Yaf Server');
        $res->setAllHeaders($headers);
        $res->appendBody('after content<br>');
        $res->setBody('main content<br>');
        $res->prependBody('before content<br>');
    }
}
?>