<?php
class ArticleController extends Yaf_Controller_Abstract {
    private  $user_list;


    public function init() {//访问的所有方法都会先访问这个方法
        $yaf_session = Yaf_Session::getInstance();
        if(empty($yaf_session->get('login_info')['is_login']) || $yaf_session->get('login_info')['is_login'] !=1){
              $this->forward("login","loginout"); //入验证未通过转发至登出
        }
    }
    
    public function indexAction() {
        $model = new Model('private');
        $article_list   = $model->table('article_list')
                                ->field("id,title,uid,insert_time,update_time,article_class,is_show,show_time")
                                ->groupBy('id desc')
                                ->select();
        $article_class  = $model->table('article_class')->select();
        $article_list2 = $this->change_article_list($article_list,$article_class);
        $this->getView()->assign("res", $article_list2);
        $this->getView()->assign("article_class", $article_class);
    }
    
    public function DelArticleAction() {
        $request        = $this->getRequest();
        $id             = intval($request->getPost('id',0));
        $submit         = $request->getPost('submit','隐藏');
        $model = new Model('private');
        if($submit == '隐藏'){
            $param  = array('is_show'=>0);
            $res    = $model->table('article_list')
                            ->where('id = ? ',array($id))
                            ->update($param);
        }else{
            $res    = $model->table('article_list')
                            ->where('id = ? ',array($id))
                            ->delete();
            
        }
        $this->redirect("/index/article/index");
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
        $res = '';
        
        foreach ($article_class_arr as $key => $value) {
            foreach ($article_class as $k => $v) {
                if($value == $v['id']){
                    $res  .= '<font style="color:'.$v['color'].'">'.$v['name'].'</font> ';
                }
            }
        }
        
        return $res;
    }
    
    public function addArticleAction() {
        $model = new Model('private');
        $article_class_data  = $model->table('article_class')->select();
        $this->getView()->assign("article_class", $article_class_data);
    }
    
    public function getArticleAction() {
        $request        = $this->getRequest();
        $id             = intval($request->getQuery('id',1));
        $model          = new Model('private');
        $article_data   = $model->table('article_list')->where('id =? ',array($id))->select();
        $article_class  = $model->table('article_class')->select();
        $sort_num       = count(explode("\r\n",$article_data[0]['markdown_doc_sort']));
        $article_class2 = $this->ArticleClass2Check($article_class,$article_data[0]['article_class']);
        $this->getView()->assign("sort_num", $sort_num);
        $this->getView()->assign("article", $article_data[0]);
        $this->getView()->assign("article_class", $article_class2);
        $this->getView()->display('article/addarticle.phtml');die;
    }
    
     public function ArticleClass2Check($article_class_list,$article_class) {
         $article_class_checked = array_unique(array_filter(explode(',', $article_class)));
         
         foreach ($article_class_list as $k => $v) {
             if(in_array($v['id'], $article_class_checked)){
                 $article_class_list[$k]['checked'] = 1;
             }else{
                 $article_class_list[$k]['checked'] = 0;
             }
         }
         return $article_class_list;
     }
    
    public function addArticleDoAction() {
        $yaf_session = Yaf_Session::getInstance();
        $user_info   = $yaf_session->get('login_info');
        
        $request       = $this->getRequest();
        $id            = $request->getPost('id',0);
        $title         = $request->getPost('title');
        $is_show       = $request->getPost('is_show') == 0?0:1;
        
        $article_class = $request->getPost('article_class');
        $doc_num       = $request->getPost('sort_num',8);
        $markdown_doc  = $request->getPost('markdown_doc','');
        //$doc_sort      = $request->getPost('markdown_doc_sort','');
        
        $uid           = $user_info['id'];
        $insert_time   = date('Y-m-d H:i:s');
        $article_class = ','.implode(',', $article_class).',';
        $model         = new Model('private');
        
        $ex_doc        = explode("\r\n",$markdown_doc);
        $doc_sort_arr  = array_slice($ex_doc, 0,$doc_num);
        $doc_sort      = implode("\r\n", $doc_sort_arr);
        if($id){
            $param = array('title'=>$title,
                        'uid'=>$uid,
                        'update_time'=>$insert_time,
                        'article_class'=>$article_class,
                        'is_show'=>$is_show,
                        'markdown_doc_sort'=>$doc_sort,
                        'markdown_doc'=>$markdown_doc);
            $res = $model->table('article_list')
                         ->where('id = ? ',array($id))
                         ->update($param);
            $this->redirect("/index/article/index");
            if($res){
                
            }else{

            }
        }else{
            $param = array('title'=>$title,
                        'uid'=>$uid,
                        'insert_time'=>$insert_time,
                        'article_class'=>$article_class,
                        'is_show'=>$is_show,
                        'markdown_doc_sort'=>$doc_sort,
                        'markdown_doc'=>$markdown_doc);
            $con = $model->table('article_list');
            $this->redirect("/index/article/index");
            if($con->insert($param)){
                
            }else{

            }
        }
        
        
    }
    
    public function ArticleClassAction() {
        $model = new Model('private');
        $res   = $model->table('article_class')->select();
        //var_dump($res);die;
        $this->getView()->assign("res", $res);
    }
    
    public function AddArticleClassAction(){
        $request     = $this->getRequest();
        $name        = $request->getPost('name');
        $color       = $request->getPost('color');
        
        $param = array('name'=>$name,'color'=>'#'.$color);
        $model = new Model('private');
        $res   = $model->table('article_class')->select();
        $this->getView()->assign("res", $res);
        if($name == ''){
            $this->getView()->assign("msg2",'分类名称不能为空！！！');
            $this->getView()->display('article/articleclass.phtml');die;
        }
        $regex = '/[^0123456789abcdef]i/';
        $str = $color;
        $matches = array();
        if((strlen($color)!==6 &&strlen($color)!==3) || preg_match($regex, $str, $matches)){
            $this->getView()->assign("msg2",'颜色不合规，不用带#，且为3位或6位色号！！！');
            $this->getView()->display('article/articleclass.phtml');die;
        }
        
        $con = $model->table('article_class');
        //var_dump($con);die;
        $con->insert($param);
        if($con->getInsertId('id')){
            $res   = $model->table('article_class')->select();
            $this->getView()->assign("res", $res);
            $this->getView()->assign("msg",'分类添加成功！！！');
        }else{
            $this->getView()->assign("msg2",'分类添加失败！！！');
        }
        $this->getView()->display('article/articleclass.phtml');die;
        
    }
    
    public function EidtArticleClassAction() {
        $request     = $this->getRequest();
        $id          = $request->getPost('id');
        $name        = $request->getPost('name');
        $color       = $request->getPost('color');
        
        $map = array('name'=>$name,'color'=>'#'.$color);
        $model = new Model('private');
        $res   = $model
                ->table('article_class')
                ->where('id = ? ',array($id))
                ->update($map);
        $res2   = $model->table('article_class')->select();
        $this->getView()->assign("res", $res2);
        
        if($res){
            $this->getView()->assign("msg",'分类修改成功！！！');
        }else{
            $this->getView()->assign("msg2",'分类修改失败！！！');
        }
        $this->getView()->display('article/articleclass.phtml');die;
    }

    
    
   
   
}
?>