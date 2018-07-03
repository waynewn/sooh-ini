<?php
namespace Sooh\IniClasses;

class Files extends Vars{
    protected $_baseDir;
    protected $_mainModule;
    protected $_nameNeedsMore;
    public function __construct($baseDir,$mainModule,$fieldNameNeedsMore='NeedsMoreIni') {
        $this->_baseDir = $baseDir;
        $this->_mainModule = $mainModule;
        $this->_nameNeedsMore = $fieldNameNeedsMore;
        $this->reload();
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
    
    protected function loadModuleIni($name,$autoLoadMore=true)
    {
        $dir = $this->_baseDir;
        if(is_dir($dir.'/'.$name)){
            $this->_vars[$name] = $this->loadModuleIniByDir($dir,$name);
        }else{
            $this->_vars[$name] = $this->loadModuleIniByFile($dir, $name);
        }
        if($this->_vars[$name]===null){
            unset($this->_vars[$name]);
        }
        
        if($autoLoadMore && !empty($this->_vars[$name][$this->_nameNeedsMore])){
            $s = $this->_vars[$name][$this->_nameNeedsMore];
            if($s == "*"){
                $this->loadAllIni($name);
            }else{
                $ks = explode(',', $s);
                foreach($ks as $k){
                    $this->loadModuleIni($k);
                }
            }
        }
    }
    
    protected function loadModuleIniByDir($dir,$name)
    {
        $sub = scandir($dir.'/'.$name);
        $tmp = array();
        foreach($sub as $k){
            if($k[0]!='.'){
                $subname = substr($k, 0,strpos($k, '.'));
                $last = substr($k,-4);
                if($last=='.php'){
                    $tmp[$subname] = include $dir.'/'.$name.'/'.$k;
                }elseif($last =='.ini'){
                    $tmp[$subname]=parse_ini_string(file_get_contents($dir.'/'.$name.'/'.$k),true);
                }
            }
        }
        return $tmp;
    }

    protected function loadModuleIniByFile($dir,$name)
    {
        if(is_file($dir.'/'.$name.'.ini.php')){
            return include ($dir.'/'.$name.'.ini.php'); 
        }elseif(is_file($dir.'/'.$name.'.php')){
            return include ($dir.'/'.$name.'.php'); 
        }elseif(is_file($dir.'/'.$name.'.ini')){
            return parse_ini_string(file_get_contents($dir.'/'.$name.'.ini'),true);
        }else{
            return null;
        }
    }
    
    protected function loadAllIni($except)
    {
        $tmp  = scandir($this->_baseDir);
        foreach($tmp as $k){
            if($k[0]=='.'){
                continue;
            }
            $pos = strpos($k, '.');
            if($pos!==false){
                $k = substr($k, 0, $pos);
            }
            if($k!=$except){
                $this->loadModuleIni($k);
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
                $tr = new \Exception;
                error_log("error IniMissing after try: $m, ".$tr->getTraceAsString());
                return null;
            }
        }
        return parent::gets($k);
    }
}
