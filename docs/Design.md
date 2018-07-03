# Ini 设计和使用说明

配置按存储位置分为本地和远程；按类型基本可以分为模块配置和资源配置；按作用域可以
分为静态配置、运行时的动态配置以及外部配置（可跨越进程的动态配置）

Ini提供了三个public的属性应对上述情况：

**statics  静态配置**

这里主要存储系统初始化参数，一般不应该在运行时更改（并发覆盖啥的要自行考虑清除）

**runtime  运行时配置**

主要是当前进程处理中用的，每个请求之初应该清空，当框架不会自动释放时，
请在收到请求之初执行 ->runtime->free(); (ini->onNewRequest()执行的)

**permanent 永久的**

（比如redis），可跨进程间共享的，不是必须的，下一版本准备提供一个redis的

**注意：由于在定位配置的时候使用了“.”，所以配置的键值部分不能有“.”!!!**

**注意：由于在定位配置的时候使用了“.”，所以配置的键值部分不能有“.”!!!**

**注意：由于在定位配置的时候使用了“.”，所以配置的键值部分不能有“.”!!!**

另外，整个的设计是基于：
不论controller执行action之前是否共享共用，以及事后是否覆盖全局值，
只要一旦到了要执行action了，类的static成员就变成独享不公用了。

## 设计

配置管理类，如果发现配置找不到，通过error_log输出IniMissing 的错误日志。

### \Sooh\Ini 是总的管理包装类

负责：

* 生成获取实例
* 管理前面提到的三种属性类
* 提供几个常用快捷函数方法

**生成获取实例**

getInstance() 获取唯一实例

注意，不同环境下该实例的生存周期不一致，常见的通过php-fpm执行，是每个请求都会清
空的，而swoole下，配置一般一开始就加载了，这个实例会永久保存了，对于statics和
permanent的还好，runtime就需要在controller准备执行action之前重置，否则会不确定带
入之前不确定哪个请求的时候的值

getInstance()生成唯一实例后，还要根据情况通过->initLoader()设置静态配置读取类
（本地文件或外部url），后面会详细说明

**常用快捷函数方法**

getIni(key) 获取statics里的配置（等价于 ->statics->gets(key)）

getRuntime(key) 获取runtime里的配置（等价于 ->runtime->gets(key)）

setRuntime(key, value) 设置runtime里的配置（等价于 ->runtime->sets(key)）

onNewRequest() 如果使用环境下系统没自动释放，在要处理新的请求之前调用，清除runtime里的值

### Sooh\IniClasses\Vars 

单纯基于数组的基本读取设置封装，可直接用于runtime

主要方法：

* gets(key) 读取
* sets(key, value) 设置
* reload() 重新加载（这里没有用到，后面基于文件或url的有实际效果）
* free() 清空数组
* dump() 导出全部

### Sooh\IniClasses\Files

基于本地文件的

构造函数 __construct($baseDir,$mainModule,$fieldNameNeedsMore='NeedsMoreIni')

baseDir 是配置文件存放的路径，只能支持一级子目录，相关说明参看：[Ini文件格式](Ini.md)
mainModule 需要加载的模块；
fieldNameNeedsMore指定的那个字段里罗列了该模块需要的其他配置，会一并加载。


### Sooh\IniClasses\Url

通过http get 方式获取配置的封装

构造函数 __construct($url, $mainModule,$fieldRoot='SoohIni',$fieldNameNeedsMore='NeedsMoreIni')

url ： 获取配置的地址，要求http get方法，url的最后可以直接拼上逗号分割的配置名;
fieldRoot: 返回的json格式数据里，ini是放在哪个节点下的,默认SoohIni
fieldNameNeedsMore指定的那个字段里罗列了该模块需要的其他配置，会一并加载。
下面以作者用到的ini集中管理服务为例：

        //session微服务启动时初始化
        $url = 'http://ServiceProxy/ini/broker/getini?name=';
        $moduleName = "Session,DB";
        ini::getInstance()->initLoader(new \Sooh\IniClasses\Url($url,$moduleName));


        //session处理业务的时候
        controller {
            action(){
                $dbIni = ini::getInstance()->getIni("DB.ForSession");

            }
        }

关于NeedsMoreIni，同上，用于找出需要加载的相关配置