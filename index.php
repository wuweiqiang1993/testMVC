<?php 
    // 应用目录为当前目录
    define('ROOT_PATH', __DIR__ . '/');
    define('APP_PATH', ROOT_PATH . 'app/');
    define('TPL_DIR', APP_PATH . 'views/');
    define('TPL_C_DIR', ROOT_PATH . 'templates_c/');
    // 开启调试模式
    define('APP_DEBUG', true);
    require(ROOT_PATH . 'Lib/base.php');
    // 实例化框架类
    Base::run();