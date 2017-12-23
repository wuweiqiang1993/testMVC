<?php 

/**
 * 控制器基类
 */
class Controller
{
    protected $_controller;
    protected $_action;
    protected $_view;
 
    // 构造函数，初始化属性，并实例化对应模型
    public function __construct($controller, $action)
    {
        $this->_controller = $controller;
        $this->_action = $action;
        $this->_view = new Templates();
    }

    // 分配变量
    public function assign($name, $value)
    {
        $this->_view->assign($name, $value);
    }

    // 渲染视图
    public function display($tpl_file)
    {
        $this->_view->display($tpl_file);
    }
}