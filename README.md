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

数据库文件在myyaf.sql 里面

还在持续优化中，下面是待优化事项

1.yaf完成一个分页类,完善ORM虽然已经完成查询跟数据库连接，但是觉得还是有必要添加一个数据处理类

2.博客添加一个在线聊天，支持图片文件传送
```
