# 配置管理类

配置分情况统一管理的封装类。设计目的是解决两点：

1. 简化和统一获取配置的方式（比如获取时如果尚未加载就自动加载配置）
2. 支持多种配置格式，php的,ini的，单一大文件或拆开的多个小文件

配置一般是多级的，比如数据库配置

        {
            DB: {
                mysqlAtServer1:{
                    server: "1.2.3.4",
                    user: "root"
                    pass: "123456"
                }
            },
            Session: {
                ....
            }
        }

这种情况下，可以通过 ini::getInstance()->getIni('DB.mysqlAtServer1.server')得到"1.2.3.4"

也可以通过 ini::getInstance()->getIni('DB.mysqlAtServer1')得到 {server:"1.2.3.4",user:"root",pass:"123456"}

配置支持几种写法，参看：[Ini文件格式](Ini.md)

## 基本使用

1） 初始化构建ini实例(以目录模式为例)：

`\Sooh\Ini::getInstance()->initLoader(new \Sooh\IniClasses\Files("/root/SingleService/_config"));`

如果是swoole这种，两个请求之间不会彻底释放的，需要在处理controller的action之前，ini->runtime->free();

2） 基本使用

        \Sooh\Ini::getInstance()->getIni("Email.server");
        \Sooh\Ini::getInstance()->getRuntime("some.runtime.var");
        \Sooh\Ini::getInstance()->setRuntime("some.runtime.var",mixed);


## 关于配置的说明

配置按存储位置分为本地和远程；按类型基本可以分为模块配置和资源配置；按作用域可以分为静态配置、运行时的动态配置以及外部配置（可跨越进程的动态配置）

Ini提供了三个public的属性应对上述情况：

 * statics  静态配置，这里主要存储系统初始化参数，一般不应该在运行时更改（并发覆盖啥的要自行考虑清除）
 * runtime  运行时，主要是当前进程处理中用的，每个请求之初应该清空，当框架不会自动释放时(比如swoole里处理任务的函数之前生成的实例)，请在收到请求之初执行 ->runtime->free(); (ini->onNewRequest()执行的)
 * permanent 永久的（比如redis），可跨进程间共享的，不是必须的，下一版本准备提供一个redis的

针对statics，分别提供了\Sooh\IniClasses\Files 和 \Sooh\IniClasses\Url 两个获取配置的驱动
permanent 暂未开发

## 详细使用和限制

**注意：由于在定位配置的时候使用了“.”，所以配置的键值部分不能有“.”!!!**

详细用法参看 [Ini设计和使用](docs/Design.md)

另外，当凑数也罢，这里增加了shutdown管理的相关方法，使用时要自行配套使用：

function registerShutdown($func,$identifier)

注册一个shutdown方法，当onShutdown的时候执行。（$identifier 是标识，
如果执行时抛出异常了，error_log的时候会给出这个identifier）

public function onShutdown()

系统执行结束后的清理，需根据运行环境框架自行选择调用位置触发执行

您也可以使用自己的Ini类替换使用：

通过 \Sooh\Ini::getInstance(其他实例) 设置一下，之后 \Sooh\Ini::getInstance()获
得的就是这个新的“其他实例”了