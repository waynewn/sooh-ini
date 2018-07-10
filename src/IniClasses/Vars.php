<?php
namespace Sooh\IniClasses;
class Vars implements \Sooh\IniClasses\DriverInterface{
    protected $_vars=array();
    /**
     * 获取预定义配置 
     *      array(a=>[b=>[c=>1]]) ，可以通过 a.b 获得 [c=>1]
     * @param string $k 逗号分割的路径
     * @throws \ErrorException
     * @return mixed
     */
    public function gets($k)
    {
        $r = explode('.', $k);
        $f = array_shift($r);
        if(!isset($this->_vars[$f])){
            return null;
        }
        $tmp = $this->_vars[$f];
        foreach($r as $i){
            if(isset($tmp[$i])){
                $tmp = $tmp[$i];
            }else{
                return null;
            }
        }
        return $tmp;
    }
    /**
     * 清空重置
     * @return Vars
     */
    public function free(){
        $this->_vars = array();
        return $this;
    }
    
    /**
     * 用于设置变量
     * @param type $k
     * @param type $v
     */
    public function sets($k,$v){
        $arrKeys = explode('.', $k);
        $this->_vars= $this->loopSets($this->_vars, $arrKeys, $v);
    }
    
    /**
     * 循环到正确未知设置变量
     * @param type $r 数组引用
     * @param type $arrKeys
     * @param type $v
     */
    protected function loopSets($r, $arrKeys,$v){
        $k = array_shift($arrKeys);
        if(sizeof($arrKeys)==0){
            $r[$k] = $v;
        }else{
            $r[$k] = $this->loopSets($r[$k], $arrKeys, $v);
        }
        return $r;
    }
    public function onNewRequest() {
        
    }
    public function reload(){}
    
    public function dump()
    {
        return $this->_vars;
    }
}