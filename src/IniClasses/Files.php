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
        $this->_vars[$this->_mainModule] = $this->loadModuleIni($this->_mainModule);
        
        if(!empty($this->loaded[$this->_mainModule][$this->_nameNeedsMore])){
            $s = $this->loaded[$this->_mainModule][$this->_nameNeedsMore];
            if($s == "*"){
                $this->loadAllIni();
            }else{
                $ks = explode(',', $s);
                foreach($ks as $k){
                    $this->_vars[$k] = $this->loadModuleIni($k);
                }
            }
        }
    }
    
    protected function loadModuleIni($name,$dir=null)
    {
        if($dir===null){
            $dir = $this->_baseDir;
        }
        if(is_dir($dir.'/'.$name)){
            return $this->loadModuleIniByDir($dir,$name);
        }else{
            return $this->loadModuleIniByFile($dir, $name);
        }
    }
    
    protected function loadModuleIniByDir($dir,$name)
    {
        $sub = scandir($dir.'/'.$name);
        $tmp = array();
        foreach($sub as $k){
            if($k[0]!='.'){
                $subname = substr($k, 0,strpos($k, '.'));
                $tmp[$subname]=$this->loadFile($k);
            }
        }
        return $tmp;
    }

    protected function loadModuleIniByFile($dir,$name)
    {
        if(is_file($dir.'/'.$name.'.ini.php')){
            return $this->loadFile($dir.'/'.$name.'.ini.php'); 
        }elseif(is_file($dir.'/'.$name.'.php')){
            return $this->loadFile($dir.'/'.$name.'.php'); 
        }elseif(is_file($dir.'/'.$name.'.ini')){
            return $this->loadFile($dir.'/'.$name.'.ini');
        }
    }

    protected function loadFile($file)
    {
        if(substr($file, -4)=='.php'){
            return include $file; 
        }else{
            return parse_ini_string(file_get_contents($file),true);
        }
    }
    
    protected function loadAllIni()
    {
        $tmp  = scandir($this->_baseDir);
        foreach($tmp as $k){
            if($k[0]=='.'){
                continue;
            }
            if(is_dir($this->_baseDir.'/'.$k)){
                $this->loadModuleIniByDir($this->_baseDir, $k);
            }else{
                $this->loadModuleIniByFile($this->_baseDir, substr($k, 0,strpos($k, '.')));
            }
        }
    }

}
