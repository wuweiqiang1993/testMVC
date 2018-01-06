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
    public static function _init()
    {
        // 加载配置文件
        $config = require ROOT_PATH . 'config/config.php';
        self::$_config = $config;
    }

    /**
     * 运行程序
     */
    public static function run()
    {
        self::_init();
        spl_autoload_register(array('Base', 'loadClass'));
        self::setReporting();
        self::removeMagicQuotes();
        self::unregisterGlobals();
        self::setDbConfig();
        self::route();
    }

    // 路由处理
    public static function route()
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
            if (!method_exists('EmptyController', '_empty')) {
                exit($actionName . '方法不存在');
            }
            $empty_dispatch = new EmptyController('EmptyController', '_empty');
            call_user_func_array(array($empty_dispatch, '_empty'), $param);
        }else if (!method_exists($controller, $actionName)) {
            if (!method_exists($controller, '_empty')) {
                exit($actionName . '方法不存在');
            }
            $empty_dispatch = new $controller($controllerName, '_empty');
            call_user_func_array(array($empty_dispatch, '_empty'), $param);
        } else {
            // 如果控制器和操作名存在，则实例化控制器，因为控制器对象里面
            // 还会用到控制器名和操作名，所以实例化的时候把他们俩的名称也
            // 传进去。结合Controller基类一起看
            $dispatch = new $controller($controllerName, $actionName);

            //前置操作
            $before_action = '_before_' . $actionName;
            if (method_exists($controller, $before_action)) {
                $before_dispatch = new $controller($controllerName, $before_action);
                call_user_func_array(array($before_dispatch, $before_action), $param);
            }
            // $dispatch保存控制器实例化后的对象，我们就可以调用它的方法，
            // 也可以像方法中传入参数，以下等同于：$dispatch->$actionName($param)
            call_user_func_array(array($dispatch, $actionName), $param);
            //后置操作
            $after_action = '_after_' . $actionName;
            if (method_exists($controller, $after_action)) {
                $after_dispatch = new $controller($controllerName, $after_action);
                call_user_func_array(array($after_dispatch, $after_action), $param);
            }
        }

    }
    // 检测开发环境
    public static function setReporting()
    {
        if (APP_DEBUG === true) {
            error_reporting(E_ALL);
            ini_set('display_errors', 'On');
        } else {
            error_reporting(E_ALL & ~E_NOTICE);
            ini_set('display_errors', 'Off');
            ini_set('log_errors', 'On');
        }
    }

    // 删除敏感字符
    public function stripSlashesDeep(array $value)
    {
        $value = is_array($value) ? array_map(array('Base', 'stripSlashesDeep'), $value) : stripslashes($value);
        return $value;
    }
    // 检测敏感字符并删除
    public static function removeMagicQuotes()
    {
        if (get_magic_quotes_gpc()) {
            $_GET = isset($_GET) ? self::stripSlashesDeep($_GET) : '';
            $_POST = isset($_POST) ? self::stripSlashesDeep($_POST) : '';
            $_COOKIE = isset($_COOKIE) ? self::stripSlashesDeep($_COOKIE) : '';
            $_SESSION = isset($_SESSION) ? self::stripSlashesDeep($_SESSION) : '';
        }
    }

    // 检测自定义全局变量并移除。因为 register_globals 已经弃用，如果
    // 已经弃用的 register_globals 指令被设置为 on，那么局部变量也将
    // 在脚本的全局作用域中可用。 例如， $_POST['foo'] 也将以 $foo 的
    // 形式存在，这样写是不好的实现，会影响代码中的其他变量。 相关信息，
    // 参考: http://php.net/manual/zh/faq.using.php#faq.register-globals
    public static function unregisterGlobals()
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
    public static function setDbConfig()
    {
        if (self::$_config['db']) {
            Model::$dbConfig = self::$_config['db'];
        }
    }

    // 自动加载控制器和模型类
    public static function loadClass($class)
    {
        $frameworks = __DIR__ . '/' . $class . '.php';
        $controllers = APP_PATH . '/controller/' . $class . '.php';
        $models = APP_PATH . '/model/' . $class . '.php';

        if (file_exists($controllers)) {
            // 加载应用控制器类
            include $controllers;
        } else if (file_exists($models)) {
            //加载应用模型类
            include $models;
        } else if (file_exists($frameworks)) {
            // 加载框架核心类
            include $frameworks;
        } else {
        }
    }
}
