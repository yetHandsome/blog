<?php
class ArticleModel extends Model{
    public function __construct($params) {
       parent::__construct($params);
    }
    
    public function getArticleClass() {
        return $this->table('article_class')->select();
    }
    
    

}
