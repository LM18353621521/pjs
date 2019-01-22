ThinkBRO 1.0
===============
以客户为中心 以奋斗者为本

### 环境推荐
> php5.5+

> mysql 5.6+

> 打开rewrite


### 最低环境要求
> php5.4+

> mysql 5.5+ (mysql5.1安装时选择utf8编码，不支持表情符)

> 打开rewrite


### 自动安装
> 之前安装过的同学,请手动创建`data/install.lock`文件

1. public目录做为网站根目录,入口文件在 public/index.php
2. 配置好网站，请访问http://你的域名

### 完整版目录结构
```
bronet  根目录
├─api                   api目录(核心版不带)
├─app                   应用目录
│  ├─portal             门户应用目录
│  │  ├─config.php      应用配置文件
│  │  ├─common.php      模块函数文件
│  │  ├─controller      控制器目录
│  │  ├─model           模型目录
│  │  └─ ...            更多类库目录
│  ├─extra              微信配置目录
│  │  ├─wechat.php      微信配置文件
│  ├─ ...               更多应用
│  ├─command.php        命令行工具配置文件
│  ├─common.php         应用公共(函数)文件
│  ├─config.php         应用(公共)配置文件
│  ├─database.php       数据库配置文件
│  ├─tags.php           应用行为扩展定义文件
│  └─route.php          路由配置文件
├─data                  数据目录
│  ├─conf               动态配置目录
│  ├─runtime            应用的运行时目录（可写）
│  └─ ...               更多
├─public                WEB 部署目录（对外访问目录）
│  ├─api                api入口目录(核心版不带)
│  ├─plugins            插件目录
│  ├─static             静态资源存放目录(css,js,image)
│  ├─themes             前后台主题目录
│  │  ├─admin_simpleboot3  后台默认主题
│  │  └─simpleboot3            前台默认主题
│  ├─upload             文件上传目录
│  ├─index.php          入口文件
│  ├─robots.txt         爬虫协议文件
│  ├─router.php         快速测试文件
│  └─.htaccess          apache重写文件
├─simplewind         
│  ├─cmf                CMF核心库目录
│  ├─extend             扩展类库目录
│  ├─thinkphp           thinkphp目录
│  └─vendor             第三方类库目录（Composer）
├─composer.json         composer 定义文件
├─LICENSE.txt           授权说明文件
├─README.md             README 文件
├─think                 命令行入口文件
```

### 开发手册
http://www.kancloud.cn/thinkcmf/doc


### 贴别注意

>本环境下已经引入微信SDK(EasyWechat)

EasyWechat 使用手册：https://www.easywechat.com/docs/3.x

微信开发所需要的功能一般情况都涵盖了，以下是封装的公众号开发常用的功能

1. 微信公众号授权已经封装好，只要调用WeChatBaseController下的checkWeChatUserLogin()即可

2. 微信授权回调地址 /user/index/callback

3. 微信服务器配置接口 /Portal/Wechat/index

4. 在user/PayController下整理了微信支付，支付回调，退款，企业支付，红包，订单查询等DEMO

5. 小程序登录注册 Api目录下 /Wxapp/Public/login 需要先用code换取sessionKey和openid

### 更多内容

>   关于更多微信相关操作多多查看使用手册

>   各位同学可参考使用，更多内容还再持续更新中...

>   各位同学有什么好的想法、意见请及时反馈！

