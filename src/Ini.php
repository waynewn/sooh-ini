<?php
namespace Sooh;
/**
 * 配置管理类
 *     管理三种参数配置
 *         1）statics  静态配置，这里主要存储系统初始化参数，一般不应该在运行时更改（并发覆盖啥的要自行考虑清除）
 *         2）runtime  运行时，主要是当前进程处理中用的，每个请求之初应该清空，当框架不会自动释放时，请在收到请求之初执行 ->runtime->free();
 *         3）permanent 永久的（比如redis），可跨进程间共享的，默认提供的redis功能开发上不考虑高并发（毕竟是配置管理）
 *     这三种配置，每个都有：
 *          1） gets (k)  获取k对应的值
 *          2） sets (k , v) 设置k对应的值
 *          3） free () 清空
 *          4） reload()
 *          5） dump()
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
     * @return \Sooh\Ini
     */
    public function initLoader($forStatic, $forPermanent=null)
    {
        $this->statics = $forStatic;
        $this->statics->reload();
        $this->runtime = new \Sooh\IniClasses\Vars;
        if($forPermanent!=null){
            $this->permanent = $forPermanent;
            $this->permanent->reload();
        }
        return $this;
    }

    protected static $_instance = null;
    /**
     * 获取当前运行中的唯一实例
     * @return \Sooh\Ini
     */
    public static function getInstance($newInstance=null)
    {
        if($newInstance!==null){
            self::$_instance = $newInstance;
        }elseif(self::$_instance==null){
            $c = get_called_class();
            self::$_instance = new $c;
        }
        return self::$_instance;
    }
    
    public function getIni($k)
    {
        return $this->statics->gets($k);
    }
    
    public function getRuntime($k)
    {
        return $this->runtime->gets($k);
    }
    
    public function setRuntime($k,$v)
    {
        return $this->runtime->sets($k, $v);
    }
    
    public function onNewRequest()
    {
        $this->statics->onNewRequest();
        $this->runtime->onNewRequest();
        if($this->permanent){
            $this->permanent->onNewRequest();
        }
        $this->runtime->free();
    }
    public function setMainModule($name)
    {
        $this->runtime->sets('SoohCurServModName',$name);
    }
    public function getMainModuleConfigItem($subname){
        if($this->runtime){
            $mName = $this->runtime->gets('SoohCurServModName');
            if(!empty($mName)){
                return $this->getIni($mName.'.'.$subname);
            }
        }
        throw new \ErrorException("runtime->SoohCurServModName not Setted");
    }
    protected $_shutdown=array();
    public function registerShutdown($func,$identifier=null)
    {
        if($identifier===null){
            $identifier='NotSet';
        }
        $this->_shutdown[$identifier][]=$func;
    }
    
    public function onShutdown()
    {
        foreach ($this->_shutdown as $i=>$r){
            foreach($r as $f){
                try{
                    call_user_func($f);
                }catch(\ErrorException $ex){
                    error_log("error on shutdown ($i) : ".$ex->getMessage());
                }
            }
            unset($this->_shutdown[$i]);
        }
    }
    
    public function dump()
    {
        return array(
            'statics'=> $this->statics->dump(),
            'runtime'=>$this->runtime->dump(),
            'permanent'=>(empty($this->permanent)?null:$this->permanent->dump())
        );
    }
}
