# tpAdmin

### 描述
基于thinkphp6开发的后台管理基础框架

### 基础功能
- `权限管理` 
    * `管理员管理`
    * `角色管理`
    * `菜单管理`
- `操作日志`    


### 安装使用
1、将项目clone到本地
```
git clone git@github.com:will-be-fine/tpAdmin.git
```
2、创建一个数据库
```
数据库字符集 utf8mb4 -- UTF-8 Unicode
排序规则 utf8mb4_general_ci
```
3、复制根目录下的.example.env文件成.env文件，修改.env文件的数据库连接部分
```
[DATABASE]
TYPE = mysql
HOSTNAME = 127.0.0.1
DATABASE = 创建的数据库名称
USERNAME = 数据库用户名
PASSWORD = 数据库密码
HOSTPORT = 3306
CHARSET = utf8
DEBUG = true
PREFIX = 数据库前缀
```
4、根目录执行 composer install 安装必要的Composer包
```
composer install
```
5、根目录执行ThinkPHP6的数据库迁移命令，这里会导入Laytp框架需要的数据库文件
```
php think migrate:run
```
6、puculic目录下 apache配置.htaccess
```
<IfModule mod_rewrite.c>
  Options +FollowSymlinks -Multiviews
  RewriteEngine On

  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ index.php [L,E=PATH_INFO:$1]
</IfModule>
```

7、运行
进入命令行下面，执行下面指令
```
php think run -p 80
```
在浏览器中输入地址：http://localhost:80/


### 技术栈
* topthink/think-migration 数据库迁移扩展

###  中间件
* 日志：记录请求和响应以便审核和调试。
* 异常捕捉：捕获和处理错误
* 认证中间件(JWT)：验证用户身份
* 授权中间件：检查用户是否具有执行某个操作所需的权限。


### 说明

* **路由规则**：http://127.0.0.1:8080/admin.test/hello


