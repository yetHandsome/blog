<?php
class IndexController extends Yaf_Controller_Abstract {

    private  $user_list;

    public function init() {//访问的所有方法都会先访问这个方法

    }

    public function indexAction() {//默认Action
        $request        = $this->getRequest();
        $id             = intval($request->getQuery('id',0));

        $model = new Model('private');
        $article_list   = $model->table('article_list')
                            ->field("id,title,uid,insert_time,update_time,article_class,is_show,show_time,markdown_doc_sort")
                            ->where('is_show = 1')
                            ->groupBy('id desc')
                            ->select();

        $article_class  = $model->table('article_class')->select();
        $article_list2 = $this->change_article_list($article_list,$article_class);
        //var_dump($article_list2);die;
        if($id){
            $article_doc   = $model->table('article_list')
                                ->where("id = ? and is_show = 1",array($id))
                                ->select();
            $article_doc[0]['markdown_doc_sort'] = $article_doc[0]['markdown_doc'];
            unset($article_doc[0]['markdown_doc']);
            $article_doc2 = $this->change_article_list($article_doc,$article_class);
            $this->getView()->assign("res2", $article_doc2);
            $is_doc = 1;
            list($prve_doc,$next_doc) = $this->get_prve_next_doc($id,$article_list2);
            $codeSrc = Common::getDomin().'/?id='.$id;
            $this->getView()->assign("prve_doc", $prve_doc);
            $this->getView()->assign("next_doc", $next_doc);
            $this->getView()->assign("codeSrc", $codeSrc);

        }else{
            $is_doc = 0;
            $this->getView()->assign("res2", $article_list2);
        }
        $config = Yaf_Registry::get('config');

        if($config->gitment){
          $this->getView()->assign("owner", $config->github->owner);
          $this->getView()->assign("repo", $config->github->repo);
          $this->getView()->assign("client_id", $config->github->client_id);
          $this->getView()->assign("client_secret", $config->github->client_secret);
        }
        $this->getView()->assign("gitment", $config->gitment);



        $this->getView()->assign("res", $article_list2);
        $this->getView()->assign("article_class", $article_class);
        $this->getView()->assign("is_doc", $is_doc);
    }

    public function OAuthAction() {//默认Action
        $tokenUrl =  'https://github.com/login/oauth/access_token';
        $params = json_decode(file_get_contents('php://input', 'r'),true);
    		$headers = [
    			'Accept: application/json',
    			'Content-Type: application/x-www-form-urlencoded'
    		];
	      $request = self::curl($tokenUrl,$params,$headers,true );
        header('Content-type: application/json; charset=utf-8');
        exit($request);
        //exit('{"access_token":"0dfa9ef62b48c834383354976d826c2e5badcd9b","token_type":"bearer","scope":"public_repo"}');
    }

      /**
     * @param $url 请求网址
     * @param bool $params 请求参数
     * @param int $ispost 请求方式
     * @param int $https https协议
     * @return bool|mixed
     */
    public static function curl($url, $params = false,$headers = array(), $ispost = 0, $https = 0) {
        $httpInfo = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        }
        if($headers) {
          curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if (is_array($params)){
            $params = http_build_query($params, null, '&');
        }
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }

        $response = curl_exec($ch);

        if ($response === FALSE) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }


    public function get_prve_next_doc($id,$article_list){
        $prev         = false;
        $next         = false;
        foreach ($article_list as $k => $v) {
            if($id == $v['id']){
                $prev_k = $k-1;
                $next_k = $k+1;
                break;
            }
        }
        if(isset($article_list[$prev_k])){
            $prev = $article_list[$prev_k];
        }
        if(isset($article_list[$next_k])){
            $next = $article_list[$next_k];
        }
        return array($prev,$next);
    }


    public function change_article_list($article_list,$article_class) {
        foreach ($article_list as $k => $v) {
            $article_list[$k]['username'] = $this->getUserName($v['uid']);
            $article_list[$k]['article_class2'] = $this->getArticleClass($v['article_class'],$article_class);
            $article_list[$k]['is_show2'] = $v['is_show'] == 1?'可见':'不可见';
            $article_list[$k]['show_time2'] = $v['show_time'] == ''?'':$v['show_time'];
        }
        return $article_list;
    }

    public function getUserName($uid) {
        $this->user_list;
        if(!isset($this->user_list[$uid])){
            $model = new Model('private');
            $user = $model->table('admin_user')
                    ->field('username')
                    ->where('id = ? ',array($uid))
                    ->select();
            $user_name = $user[0];
            $this->user_list[$uid] = $user_name['username'];
        }
        return $this->user_list[$uid];
    }

    public function getArticleClass($article_class_str,$article_class) {
        $article_class_arr = array_unique(array_filter(explode(',', $article_class_str)));
        $res = array();

        foreach ($article_class_arr as $key => $value) {
            foreach ($article_class as $k => $v) {
                if($value == $v['id']){
                    $res[]  = $v;
                }
            }
        }

        return $res;
    }

}
?>
