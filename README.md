# 配置管理类

配置管理类，如果发现配置找不到，通过error_log输出IniMissing 的错误日志。

## 关于配置

配置按存储位置分为本地和远程；按类型基本可以分为模块配置和资源配置；按作用域可以分为静态配置、运行时的动态配置以及外部配置（可跨越进程的动态配置）

Ini提供了三个public的属性应对上述情况：

 * statics  静态配置，这里主要存储系统初始化参数，一般不应该在运行时更改（并发覆盖啥的要自行考虑清除）
 * runtime  运行时，主要是当前进程处理中用的，每个请求之初应该清空，当框架不会自动释放时，请在收到请求之初执行 ->runtime->free();
 * permanent 永久的（比如redis），可跨进程间共享的，不是必须的，下一版本准备提供一个redis的

针对statics，分别提供了\Sooh\IniClasses\Files 和 \Sooh\IniClasses\Url 两个获取配置的驱动
permanent 暂未开发

## 基本使用

1） 初始化构建ini实例：

`\Sooh\Ini::getInstance()->initLoader(new \Sooh\IniClasses\Files("/root/SingleService/_config"));`

如果是swoole这种，两个请求之间不会彻底释放的，需要在处理controller的action之前，ini->runtime->free();

2） 基本使用

        \Sooh\Ini::getInstance()->getIni("Email.server");
        \Sooh\Ini::getInstance()->getRuntime("some.runtime.var");
        \Sooh\Ini::getInstance()->setRuntime("some.runtime.var",mixed);

详细用法参看 [Ini使用方法](docs/Codes.md)
