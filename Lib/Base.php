<?php
/**
 * Base框架核心
 */
    class Base
    {
        static $_config = [];
        /**
         * 初始化配置
         */
        static function _init()
        {
            // 加载配置文件
            $config = require(APP_PATH . 'config/config.php');
            self::$_config = $config;
        }

        /**
         * 运行程序
         */
        static function run():void
        {
            self::_init();
            spl_autoload_register(array('Base','loadClass'));
            self::setReporting();
            self::removeMagicQuotes();
            self::unregisterGlobals();
            self::setDbConfig();
            self::route();
        }

        // 路由处理
        static function route():void
        {
            $controllerName = self::$_config['defaultController'];
            $actionName = self::$_config['defaultAction'];
            $param = array();

            $url = $_SERVER['REQUEST_URI'];
            // 清除?之后的内容
            $position = strpos($url, '?');
            $url = $position === false ? $url : substr($url, 0, $position);
            // 删除前后的“/”
            $url = trim($url, '/');

            if ($url) {
                // 使用“/”分割字符串，并保存在数组中
                $urlArray = explode('/', $url);
                // 删除空的数组元素
                $urlArray = array_filter($urlArray);
                
                // 获取控制器名
                $controllerName = ucfirst($urlArray[0]);
                
                // 获取动作名
                array_shift($urlArray);
                $actionName = $urlArray ? $urlArray[0] : $actionName;
                
                // 获取URL参数
                array_shift($urlArray);
                $param = $urlArray ? $urlArray : array();
            }

            // 判断控制器和操作是否存在
            $controller = $controllerName . 'Controller';
            if (!class_exists($controller)) {
                exit($controller . '控制器不存在');
            }
            if (!method_exists($controller, $actionName)) {
                exit($actionName . '方法不存在');
            }

            // 如果控制器和操作名存在，则实例化控制器，因为控制器对象里面
            // 还会用到控制器名和操作名，所以实例化的时候把他们俩的名称也
            // 传进去。结合Controller基类一起看
            $dispatch = new $controller($controllerName, $actionName);

            // $dispatch保存控制器实例化后的对象，我们就可以调用它的方法，
            // 也可以像方法中传入参数，以下等同于：$dispatch->$actionName($param)
            call_user_func_array(array($dispatch, $actionName), $param);
        }
        // 检测开发环境
        static function setReporting():void
        {
            if (APP_DEBUG === true) {
                error_reporting(E_ALL);
                ini_set('display_errors','On');
            } else {
                error_reporting(E_ALL & ~E_NOTICE);
                ini_set('display_errors','Off');
                ini_set('log_errors', 'On');
            }
        }

        // 删除敏感字符
        public function stripSlashesDeep(array $value):array
        {
            $value = is_array($value) ? array_map(array('Base', 'stripSlashesDeep'), $value) : stripslashes($value);
            return $value;
        }
        // 检测敏感字符并删除
        static function removeMagicQuotes():void
        {
            if (get_magic_quotes_gpc()) {
                $_GET = isset($_GET) ? self::stripSlashesDeep($_GET ) : '';
                $_POST = isset($_POST) ? self::stripSlashesDeep($_POST ) : '';
                $_COOKIE = isset($_COOKIE) ? self::stripSlashesDeep($_COOKIE) : '';
                $_SESSION = isset($_SESSION) ? self::stripSlashesDeep($_SESSION) : '';
            }
        }

        // 检测自定义全局变量并移除。因为 register_globals 已经弃用，如果
        // 已经弃用的 register_globals 指令被设置为 on，那么局部变量也将
        // 在脚本的全局作用域中可用。 例如， $_POST['foo'] 也将以 $foo 的
        // 形式存在，这样写是不好的实现，会影响代码中的其他变量。 相关信息，
        // 参考: http://php.net/manual/zh/faq.using.php#faq.register-globals
        static function unregisterGlobals():void
        {
            if (ini_get('register_globals')) {
                $array = array('_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
                foreach ($array as $value) {
                    foreach ($GLOBALS[$value] as $key => $var) {
                        if ($var === $GLOBALS[$key]) {
                            unset($GLOBALS[$key]);
                        }
                    }
                }
            }
        }

        // 配置数据库信息
        static function setDbConfig():void
        {
            if (self::$_config['db']) {
                Model::$dbConfig = self::$_config['db'];
            }
        }

        // 自动加载控制器和模型类 
        public static function loadClass($class):void
        {
            $frameworks = __DIR__ . '/' . $class . '.php';
            $controllers = APP_PATH . 'app/controller/' . $class . '.php';
            $models = APP_PATH . 'app/model/' . $class . '.php';

            if (file_exists($frameworks)) {
                // 加载框架核心类
                include $frameworks;
            } elseif (file_exists($controllers)) {
                // 加载应用控制器类
                include $controllers;
            } elseif (file_exists($models)) {
                //加载应用模型类
                include $models;
            } else {
            }
        }
    }
    