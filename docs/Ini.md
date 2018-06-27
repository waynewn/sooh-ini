# 配置格式说明

每个配置名对应一个专属含义，一般两种情况：

1. 对应某一类资源配置，像上面的DB就属此类；
2. 对应某以模块的模块配置，此时一般与模块名同名

## 文件格式支持php 和 ini两种

**php格式**

/Ini.php

        <?php
        return array(
            //最大异步任务数
            'SERVICE_MAX_TASK'=>0,
            //最大同时接收请求数量（同步异步都算）
            'SERVICE_MAX_REQUEST'=>10,
            //module & ctrl 名称定义（重命名的机会）  
            'SERVICE_MODULE_NAME'=>'ini',
            //需要加载所有配置
            'NeedsMoreIni'=>'*',
        );

**ini格式**

/Ini.ini

        ;最大异步任务数
        SERVICE_MAX_TASK = 0
        ;最大同时接收请求数量（同步异步都算）
        SERVICE_MAX_REQUEST = 10
        ;module & ctrl 名称定义（重命名的机会）  
        SERVICE_MODULE_NAME = "ini"
        ;需要加载所有配置
        NeedsMoreIni = *

## 组织方式支持单文件多文件两种

针对资源类一般会有多个平级配置的情况，支持引入一级目录分离，下面两种写法等价：

**单文件方案：**

/DB.php

        <?php
        return array(
            'forSession' => array(
                'server'=>'192.168.0.1',
                'type'=>'redis'
            ),
            'forPayment' => array(
                'server'=>'192.168.0.2',
                'type'=>'mysql'
            ),
        );

**多文件方案：**

/DB/forSession.ini

        server = 192.168.0.1
        type = redis

/DB/forPayment.php
        
        <?php
        return array(
            'server' => '192.168.0.1',
            'type' => 'mysql'
        );