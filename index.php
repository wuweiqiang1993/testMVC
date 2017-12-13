<?php 
    declare(strict_types=1); //开启强类型检查，必须写在文件开头
    // 应用目录为当前目录
    define('APP_PATH', __DIR__ . '/');
    // 开启调试模式
    define('APP_DEBUG', true);
    require(APP_PATH . 'Lib/base.php');
    // 实例化框架类
    Base::run();