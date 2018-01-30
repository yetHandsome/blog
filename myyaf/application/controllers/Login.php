<?php
class LoginController extends Yaf_Controller_Abstract {
   public function loginAction() {
        $request     = $this->getRequest();
        $username    = $request->getPost('username');
        if($username){
            $passwdInput = $request->getPost('password');
            $passwd2     = md5(md5($passwdInput));
            $model       = new UserModel('private');
            $user        = $model->getUser2($username,$passwd2);
            if(!empty($user)){
                //是否开启Google验证登入
                if($user[0]['google_check']){
                    $yaf_session = Yaf_Session::getInstance();
                    $yaf_session->set('login_info',$user[0]);
                    $this->redirect("/login/verifyCode");
                }else{
                    //未开启验证直接登入
                    $user[0]['is_login'] = 1;
                    $yaf_session = Yaf_Session::getInstance();
                    $yaf_session->set('login_info',$user[0]);
                    $this->redirect("/index/index");
                }
                
            }else{
                $this->getView()->assign("msg", "请再次检查您输入的账号或密码是否有误！");
            }
        }
   }
   
   public function loginOutAction() {
        setcookie(session_name(),'',time()-3600,'/'); //清除cookie
        session_destroy();
        $this->redirect("/login/login");
   }
   
   public function getverifyCodeUrlAction() {
        $yaf_session = Yaf_Session::getInstance();
        $user_info   = $yaf_session->get('login_info');
        $google_pass = $user_info['google_pwd'];//长度16位,只能包含如下字符串ABCDEFGHIJKLMNOPQRSTUVWXYZ234567
        $url = "otpauth://totp/".$user_info['username']."?secret=".$google_pass;
        return $url;
   }
   
   public function verifyCodeAction() {
       //echo 123;die;
        $request     = $this->getRequest();
        $verifyCode    = $request->getPost('verifyCode');
        if(!empty($verifyCode)){
            $yaf_session = Yaf_Session::getInstance();
            $user_info   = $yaf_session->get('login_info');
            if(!empty($user_info['id'])){
                $google_pass = $user_info['google_pwd'];
                ////长度16位,只能包含如下字符串ABCDEFGHIJKLMNOPQRSTUVWXYZ234567
                //                使用说明：
//                第一步：在手机应用商店搜索谷歌身份验证器,并且安装好
//                第二步：在谷歌身份验证器选手动添加账户下的输入提供的密钥, 账户名称可以为任意字符串仅标识作用, 然后输入对应的密钥【16位/数字和字母组合】, 类型选择基于时间
//                第三步：在后台口令验证设置的密钥输入框里面填入手机上绑定的密钥即可
//                动态口令只有30秒有效期, 会有5秒左右延迟, 如延迟很大请同步手机时间即可
                $checkResult = $this->loginAccessTokenAction($google_pass,$verifyCode);
                if ($checkResult) {
                    $user_info['is_login'] = 1;
                    $yaf_session->set('login_info',$user_info);
                    $u = $yaf_session->get('login_info');
                    $this->redirect("/index/index");
                    //echo '匹配! OK';
                } else {
                    $this->getView()->assign("msg", "您的输入动态秘钥有误!!!");
                    //$this->redirect("/login/verifyCode");
                    //echo '不匹配! FAILED';
                }
            }else{
                $this->redirect("/login/loginOut");
            }
        }
        
        
   }
   
   //口令验证
    public function loginAccessTokenAction($InitalizationKey, $login_token) {
        $TimeStamp = LoginAccessToken::get_timestamp();
        $secretkey = LoginAccessToken::base32_decode($InitalizationKey); // Decode it into binary
        return LoginAccessToken::verify_key($InitalizationKey, $login_token);
    }
}
?>