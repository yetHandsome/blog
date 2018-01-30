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
            $this->getView()->assign("prve_doc", $prve_doc);
            $this->getView()->assign("next_doc", $next_doc);
        }else{
            $is_doc = 0;
            $this->getView()->assign("res2", $article_list2);
        }
        
        
        $this->getView()->assign("res", $article_list2);
        $this->getView()->assign("article_class", $article_class);
        $this->getView()->assign("is_doc", $is_doc);
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