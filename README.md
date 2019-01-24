# blog
```code

这是一个用yaf 框架搭建的博客系统

分为前台yafhome
这个是我的博客前台访问地址http://blog.yetshine.com:8080/?id=22

跟后台myyaf

myyaf/conf 跟 yafhome/conf 下的 application.ini 已经加入忽略列表，conf 这个文件夹要自己创建，添加到忽略列表了所以没有
本地跟线上请复制根目录下的 application.ini 并修改为本连接信息
application.ini  下面的 gitment
gitment = false 就不必添加其它的github信息，页面也不会有留言功能，如果要添加留言功能去下面网址申请（有github账号即可），并填写对应信息
https://github.com/settings/applications/new

gitment = true
github.owner         = yetHandsome #你的 GitHub ID ，比如我的 https://github.com/yetHandsome 填写 yetHandsome
github.repo          = blog_repo #可以是任意一个你的项目仓库名，比如我的 https://github.com/yetHandsome/blog_repo 填写 blog_repo 
github.client_id     =  xxxxxxxxx #填写你申请的Client# ID
github.client_secret = xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx 填写你申请的Client Secret


nginx 配置 假设 代码放入/home/www/github_blog/blog下

--博客 注意下面有2出 yafhome
server {
        listen 8080;
	      listen 80;
        server_name blog.yetshine.com;
        root "/home/www/github_blog/blog/yafhome/public/";
	if (!-e $request_filename) {
    		rewrite ^/(.*)  /index.php/$1 last;
  	}
	
	location /resources/{
		alias /home/www/github_blog/blog/yafhome/public/resources/;
	}
	location /{
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

--后台 注意下面有2出 myyaf
server {
        listen 8080;
	listen 80;
        server_name admin.yetshine.com;
        root "/home/www/github_blog/blog/myyaf/public/";
	if (!-e $request_filename) {
    		rewrite ^/(.*)  /index.php/$1 last;
  	}
	
	location /resources/{
		alias /home/www/github_blog/blog/myyaf/public/resources/;
	}
	location /{
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


数据库文件在myyaf.sql 里面

还在持续优化中，下面是待优化事项

1.yaf完成一个分页类,完善ORM虽然已经完成查询跟数据库连接，但是觉得还是有必要添加一个数据处理类

2.博客添加一个在线聊天，支持图片文件传送
```
