# tpAdmin

### 描述
基于thinkphp6+layui开发的后台管理框架

### 基础功能
- `权限管理` 后台权限管理
    * `管理员管理`
    * `角色管理`
    * `菜单管理`
- `操作日志`    


### 安装使用

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
4、根目录执行 composer install 安装必要的Composer包，包括ThinkPHP6框架和ThinkPHP6的其他代码！
```
composer install
```
5、根目录执行ThinkPHP6的数据库迁移命令，这里会导入Laytp框架需要的数据库文件
```
php think migrate:run
```

### 技术栈
* topthink/think-migration 数据库迁移扩展


### 说明

**运行**

进入命令行下面，执行下面指令
```
php think run -p 80
```
在浏览器中输入地址：http://localhost:80/


**路由规则**：http://127.0.0.1:8080/admin.test/hello


