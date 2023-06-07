## 运行步骤
### 项目代码
```
git clone git@github.com:meZhheng/CloudCalendar-api.git
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
#### 服务器组件（推荐）
PhpStudy
[配置参考](https://blog.csdn.net/qq_38482205/article/details/120221941)

#### PhpStorm（可选）
    从交大软件授权中心的JetBrainsToolbox中下载PhpStorm，打开项目并启动内建php服务器监听8000端口
    相应地，将前端代码/src/api/api.ts中，请求url前加入localhost:8000
    如：'/login.php'修改为'localhost:8000/login.php'
