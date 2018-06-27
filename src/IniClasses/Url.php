<?php
namespace Sooh\IniClasses;

class Url extends Vars{
    protected $_url;
    protected $_retNodeName;
    public function __construct($url,$pathAtReturn) {
        $this->_url = $url;
        $this->_retNodeName = $pathAtReturn;
    }
    public function reload(){
        $tmp = self::getConfigFromUrl($dirOrUrl,$name);
        $this->loaded[$name]=$tmp[$name];
        if(!empty($this->loaded[$name]['NeedsMoreIni'])){
            $tmp = self::getConfigFromUrl($dirOrUrl,$this->loaded[$name]['NeedsMoreIni']);
            foreach ($tmp as $k=>$v) {
                $this->loaded[$k]=$v;
            }
        }
    }    
}