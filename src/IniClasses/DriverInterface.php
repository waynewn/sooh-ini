<?php
namespace Sooh\IniClasses;

interface DriverInterface {
    public function reload();
    public function gets($k);
    public function sets($k,$v);
    public function onNewRequest();
}
