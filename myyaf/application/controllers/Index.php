<?php
class IndexController extends Yaf_Controller_Abstract {
    //http://myyaf.com/index/index
    //http://myyaf.com
    public function init() {//访问的所有方法都会先访问这个方法
        $yaf_session = Yaf_Session::getInstance();
        if(empty($yaf_session->get('login_info')['is_login']) || $yaf_session->get('login_info')['is_login'] !=1){
              $this->forward("login","loginout"); //入验证未通过转发至登出
        }
    }
    
    public function indexAction() {//默认Action
        $yaf_session = Yaf_Session::getInstance();
        //var_dump($yaf_session->get('login_info'));
        $this->getView()->assign("username",$yaf_session->get('login_info')['username']);
    }
    
    public function userinforAction() {//默认Action
        $yaf_session = Yaf_Session::getInstance();
        $login_info  = $yaf_session->get('login_info');
        $qrcode_url = "otpauth://totp/".$login_info['username']."?secret=".$login_info['google_pwd'];
        $this->getView()->assign("verifyCode", $login_info['google_pwd']);
        $this->getView()->assign("open_verify", $login_info['google_check']);
        $this->getView()->assign("qrcode_url", $qrcode_url);
    }
    
    public function changePasswdAction() {//默认Action
        $request     = $this->getRequest();
        $oldpassword = $request->getPost('oldpassword');
        $newpassword = $request->getPost('newpassword');
        $newpassword2= $request->getPost('newpassword2');
        
        $yaf_session = Yaf_Session::getInstance();
        $login_info  = $yaf_session->get('login_info');
        $qrcode_url = "otpauth://totp/".$login_info['username']."?secret=".$login_info['google_pwd'];
        $this->getView()->assign("verifyCode", $login_info['google_pwd']);
        $this->getView()->assign("open_verify", $login_info['google_check']);
        $this->getView()->assign("qrcode_url", $qrcode_url);
        
        if($newpassword != $newpassword2){
            $this->getView()->assign("msg2",'2次输入密码不一致！！！');
            $this->getView()->display('index/userinfor.phtml');die;
        }elseif($newpassword == ''){
            $this->getView()->assign("msg2",'密码不能为空！！！');
            $this->getView()->display('index/userinfor.phtml');die;
        }
        if(md5(md5($oldpassword)) != $login_info['passwd']){
            $this->getView()->assign("msg",'密码错误！！！');
            $this->getView()->display('index/userinfor.phtml');die;
        }
        
        $model = new Model('private');
        $map   = array('passwd'=>md5(md5($newpassword)));
        $res   = $model->table('admin_user')
                    ->where('id = ? and username = ?',array($login_info['id'],$login_info['username']))
                    ->update($map);
        
        if($res){
            $this->getView()->assign("msg3",'密码修改成功,下次登入请使用新密码登入！！！');
            $this->getView()->display('index/userinfor.phtml');die;
        }else{
            $this->getView()->assign("msg4",'修改密码失败！！！');
            $this->getView()->display('index/userinfor.phtml');die;
        }
        
    }
    
    public function changeVerifyCodeAction() {//默认Action
        
        $yaf_session = Yaf_Session::getInstance();
        $login_info  = $yaf_session->get('login_info');
        $qrcode_url = "otpauth://totp/".$login_info['username']."?secret=".$login_info['google_pwd'];
        $this->getView()->assign("verifyCode", $login_info['google_pwd']);
        $this->getView()->assign("open_verify", $login_info['google_check']);
        $this->getView()->assign("qrcode_url", $qrcode_url);
        
        $request     = $this->getRequest();
        $newverifycode = $request->getPost('newverifycode');
        $open_verify = $request->getPost('open_verify') == 1? 1:0;
        if(strlen($newverifycode) != 16){
            $this->getView()->assign("msg5",'字符长度必须为16位！！！');
            $this->getView()->display('index/userinfor.phtml');die;
        }
        
        $regex = '/[^ABCDEFGHIJKLMNOPQRSTUVWXYZ234567]/';
        $str = $newverifycode;
        $matches = array();
        if(preg_match($regex, $str, $matches)){
            $this->getView()->assign("msg6",'字符"'.$matches[0].'"不是有效字符！！！');
            $this->getView()->display('index/userinfor.phtml');die;
        }
        
        $model = new Model('private');
        $map   = array('google_pwd'=>$newverifycode,'google_check'=>$open_verify);
        $res   = $model->table('admin_user')
                    ->where('id = ? and username = ?',array($login_info['id'],$login_info['username']))
                    ->update($map);
        
        if($res){
            $login_info['google_pwd'] = $newverifycode;
            $login_info['google_check'] = $open_verify;
            $yaf_session->set('login_info',$login_info);
            $this->getView()->assign("msg3",'Google验证秘钥修改成功，请输入或扫码跟新密钥！！！');
            $this->getView()->display('index/userinfor.phtml');die;
        }else{
            $this->getView()->assign("msg4",'修改秘钥失败！！！');
            $this->getView()->display('index/userinfor.phtml');die;
        }
    }
    
    
    public function allAction() {//默认Action
        //$this->getView()->assign("content", "WTFx");
        //$this->getView()->display('index/all.phtml');  
    }
   
   
}
?>