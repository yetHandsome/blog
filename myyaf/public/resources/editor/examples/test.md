## mac搭建 yaf  


>参考网址http://www.laruence.com/manual/yaf.install.html

>一个值得看的网址
http://www.shixinke.com/php/yaf-configuration-introduction

1.查看所有的php安装包，确保是否有yaf 

    $ brew search php 
2.安装对应版本yaf扩展，我本地习惯使用7.0版本PHP

    $ brew install homebrew/php/php70-yaf
3.安装yaf需要的依赖扩展，这个可以忽略

Yaf只支持PHP5.2及以上的版本. 并支持最新的PHP5.3.3
Yaf需要SPL的支持. SPL在PHP5中是默认启用的扩展模块
Yaf需要PCRE的支持. PCRE在PHP5中是默认启用的扩展模块

目录结构
	+ public
	  |- Index.Php //入口文件
	  |- .htaccess //重写规则    
	+ conf
	  |- application.ini //配置文件   
	+ application
	  |+ controllers
		 |- Index.php //默认控制器
	  |+ views    
		 |+ index   //控制器
		 |- index.phtml //默认视图
	  |+ modules //其他模块
	  |+ library //本地类库
	  |+ models  //model目录
	  |+ plugins //插件目录


4.创建上述目录结构，我先只创建必要部分
```js
cd ~/www
mkdir myyaf;
cd myyaf;
mkdir public;
touch public/index.php;
mkdir conf;
touch conf/application.ini;
mkdir -p application/controllers;
touch application/controllers/Index.php;
mkdir -p application/views/index;
touch application/views/index/index.phtml;
```


##### 抄写手册例子即可
###### 例 3.2. 一个经典的入口文件public/index.php
    <?php
    define("APP_PATH",  realpath(dirname(__FILE__) . '/../')); /* 指向    public的上一级 */
    $app  = new Yaf_Application(APP_PATH . "/conf/application.ini");
    $app->run();

###### 例 3.7. 一个简单的配置文件application/conf/application.ini
```js
[product]
;支持直接写PHP中的已定义常量
application.directory=APP_PATH "/application/" 
```


例 3.8. 默认控制器application/controllers/Index.php

    <?php
    class IndexController extends Yaf_Controller_Abstract {
       public function indexAction() {//默认Action
           $this->getView()->assign("content", "Hello World");
       }
    }
    ?>

例 3.9. 一个默认Action的视图application/views/index/index.phtml
```html
<html>
 <head>
   <title>Hello World</title>
 </head>
 <body>
  <?php echo $content;?>
 </body>
</html>
```


5.配置站点域名及nginx

    mk_host  myyaf.com ~/www/myyaf/public 

6.修改nginx配置

    vi /usr/local/etc/nginx/servers/myyaf.com.conf

写入如下内容
```shell
server {
  listen 80;
  server_name  myyaf.com;
  root   /Users/pk002/www/myyaf/public;
  index  index.php index.html index.htm;

  if (!-e $request_filename) {
    rewrite ^/(.*)  /index.php/$1 last;
  }
}
```
7.访问
http://myyaf.com/application/index.php

呵呵哒，把index.php下载下来了

更改nginx配置

```shell
server {
  listen 80;
  server_name  myyaf.com;
  root   /Users/pk002/www/myyaf/public;
  index  index.php index.html index.htm;

  if (!-e $request_filename) {
    rewrite ^/(.*)  /index.php/$1 last;
  }
    location ~ \.php(.*)$ {
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_split_path_info  ^((?U).+\.php)(/?.+)$;
        fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
        fastcgi_param  PATH_INFO  $fastcgi_path_info;
        fastcgi_param  PATH_TRANSLATED  $document_root$fastcgi_path_info;
        include        fastcgi_params;
    }
}
```

    $ sudo nginx -s  reload


再次访问，没有变成下载但是报错 

>Fatal error: Class 'Yaf_Controller_Abstract' not found

好吧，智障了，单入口文件跟控制器搞反了

再次访问还是这样，看phpinfo();
发现没有yaf扩展
重启PHP即可

    $ php70-fpm restart


访问再次报错

妹妹的问再次智障。文件名后缀写错成.pthml 改成.phtml即可
访问http://myyaf.com
终于页面成功输出WTF