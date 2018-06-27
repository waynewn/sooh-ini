<?php
namespace Sooh\IniClasses;
/**
 * 通过http get 一个远程地址获取配置,要求:
 *      接收参数支持 * 或 英文逗号分割的名称
 *      返回json格式  {   SoohIni: {module1Name:{ini1:1,ini2:2}, module2Name:{ini3:3,ini4:4}}}
 */
class Url extends Vars{
    protected $_url;

    protected $_mainModule;
    protected $_nameNeedsMore;
    /**
     * 
     * @param string $url 格式是 http://x.x.x.x/xxx?paramname=
     * @param string $mainModule 主模块名
     */
    public function __construct($url,$mainModule,$fieldNameNeedsMore='NeedsMoreIni') {
        $this->_url = $url;
        $this->_mainModule = $mainModule;
        $this->_nameNeedsMore = $fieldNameNeedsMore;
    }
    public function reload(){
        if(empty($this->_vars)){
            if(empty($this->_mainModule)){
                return;
            }else{
                $this->loadModuleIni($this->_mainModule);
            }
        }else{
            $ks = array_keys($this->_vars);
            foreach($ks as $k){
                $this->loadModuleIni($k, false);
            }
        }
    }
    
    protected function simpleHttpGet($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1 );
        $output = curl_exec($ch);
        $err=curl_error($ch);
        if(!empty($err)){
            error_log('IniMissing '.$url.' '.$err);
        }
        $tmp = json_decode($output,true);
        if (!is_array($tmp)){
            error_log('IniMissing '.$url.' not-json returned:'.$output);
            $tmp = null;
        }
        curl_close($ch);
        return $tmp['SoohIni'];
    }
    
    protected function loadModuleIni($name,$autoLoadMore=true)
    {
        $tmp = $this->simpleHttpGet($this->_url.$name);
        if(!is_array($tmp)){
            return;
        }
        
        foreach($tmp as $k=>$v){
            $this->_vars[$k] = $v;
            if($autoLoadMore && !empty($v[$this->_nameNeedsMore])){
                $ks = explode(",", $v[$this->_nameNeedsMore]);
                foreach($ks as $i=>$s){
                    if(isset($tmp[$s])){
                        unset($ks[$i]);
                    }
                }
                $this->loadModuleIni(implode(',', $ks));
            }
        }
    }
    
    public function gets($k)
    {
        $pos = strpos($k, ".");
        if($pos===false){
            $m = $k;
        }else{
            $m = substr($k, 0, $pos);
        }
        if(!empty($this->_vars[$m])){
            $this->loadModuleIni($m);
            if(empty($this->_vars[$m])){
                error_log("IniMissing : $m");
                return null;
            }
        }
        return parent::gets($k);
    }
}