## 运行步骤
### 项目代码
```
git clone git@github.com:meZhheng/CloudCalendar-api.git
```
### 目录结构
将CloudCalendar-api与CloudCalendar-frontend放置在同一目录下
```
CloudCalendar
  ├── CloudCalendar-api
  └── CloudCalendar-frontend
    ├── public
    └── src
```
### 配置环境
确认本机PHP版本
```
php -v
```
版本要求：PHP 7.4
### 安装依赖
将PHP安装目录下php.ini-development复制到./lib下，并重命名为php.ini \
打开文件 php.ini，取消以下配置的注释
```
extension_dir = "ext"
extension=gd2
extension=mysqli
extension=openssl
```
### 本地运行
#### PhpStudy（推荐）
##### react打包
    在CloudCalendar-frontend目录下，运行npm run build
    在CloudCalendar-frontend目录下，出现build文件夹即打包成功
##### nginx配置
    打开 设置->配置文件->nginx->nginx.conf 将以下两段代码取消注释并修改为：
```
server {
    listen       80;
    listen       localhost:80;
    server_name  localhost;
    return 301   https://$server_name$request_uri;
}

server {
    listen                      443 ssl;
    server_name                 localhost;
    ssl_certificate             cert.pem;
    ssl_certificate_key         cert.key;
    ssl_session_cache           shared:SSL:1m;
    ssl_session_timeout         5m;
    ssl_ciphers                 HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers   on;
    location / {
        root        yourProjectPath\CloudCalendar\CloudCalendar-frontend\build;
        try_files   $uri /index.html;
        index       index.html index.htm;
    }
    location ~* \.php$ {
        root            yourProjectPath\CloudCalendar\CloudCalendar-api;
        fastcgi_pass    localhost:9000;
        fastcgi_param   SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include         fastcgi_params;
    }
}
```
    注意将两处root后的路径改为自己的项目路径 
    cert.pem和cert.key为自签名证书，放置在nginx.conf同目录下
##### redis配置
    打开 软件管理->redis 下载redis服务端
##### 启动PhpStudy
    在 首页->套件 中启动nginx、redis即可

#### PhpStorm（可选）
    从交大软件授权中心的JetBrainsToolbox中下载PhpStorm，打开项目并启动内建php服务器监听8000端口
    相应地，将前端代码/src/api/api.ts中，请求url前加入localhost:8000
    如：'/login.php'修改为'localhost:8000/login.php'

## 注意事项
PHP的GD库不支持中文路径，请确保路径中没有中文