<?php
namespace Sooh;
/**
 * 配置管理类
 *     管理三种参数配置
 *         1）statics  静态配置，这里主要存储系统初始化参数，一般不应该在运行时更改（并发覆盖啥的要自行考虑清除）
 *         2）runtime  运行时，主要是当前进程处理中用的，每个请求之初应该清空，当框架不会自动释放时，请在收到请求之初执行 ->runtime->free();
 *         3）permanent 永久的（比如redis），可跨进程间共享的，默认提供的redis功能开发上不考虑高并发（毕竟是配置管理）
 *     这三种配置，每个都有三个方法
 *          1） gets (k)  获取k对应的值
 *          2） sets (k , v) 设置k对应的值
 *          3） free () 清空
 *          4） reload()
 *     关于k，这里给个例子说明使用方式：
 *          对于 array( a=> [b=> [c=>1] ] ) 
 *          可以通过 a.b 获得 [c=>1] 
 *          可以通过 a.b.c 获得 1 
 */
class Ini{
    /**
     * 这里主要存储系统初始化参数，一般不应该在运行时更改（并发覆盖啥的要自行考虑清除）
     * @var \Sooh\IniClasses\Vars 
     */
    public $statics=null;
    /**
     * 这里主要进程间永久保存的，比如通过redis保存的
     * @var \Sooh\IniClasses\Vars 
     */
    public $permanent=null;
    /**
     * 这里主要存储运行时参数
     * 作为配置管理器，因不是所有框架都会释放，所以建议在收到请求之初，执行 ->runtime->free();
     * @var \Sooh\IniClasses\Vars
     */
    public $runtime=null;
    
    /**
     * 设置三种参数存取的类
     * @param \Sooh\IniClasses\DriverInterface $forStatic
     * @param \Sooh\IniClasses\DriverInterface $forPermanent
     * @return \SoohIni\Ini
     */
    public function initLoader($forStatic, $forPermanent=null)
    {
        $this->statics = $forStatic;
        $this->statics->reload();
        $this->runtime = new \SoohIni\Vars;
        if($forPermanent!=null){
            $this->permanent = $forPermanent;
            $this->permanent->reload();
        }
    }

    protected static $_instance = null;
    /**
     * 获取当前运行中的唯一实例
     * @return \SoohIni\Ini
     */
    public static function getInstance()
    {
        if(self::$_instance==null){
            $c = get_called_class();
            self::$_instance = new $c;
        }
        return self::$_instance;
    }
}
