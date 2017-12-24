<?php
    class View {
        //创建一个存放数组的字段
        private $_vars = array();
        private $_config = array();
        protected $_controller;
        protected $_action;
        //创建一个构造方法
        public function __construct($controller, $action)
        {
            $this->_controller = strtolower($controller);
            $this->_action = strtolower($action);
            !is_dir(TPL_DIR)&&mkdir(TPL_DIR);
            !is_dir(TPL_C_DIR)&&mkdir(TPL_C_DIR);
            if(!is_dir(TPL_DIR) || !is_dir(TPL_C_DIR) ){
                exit('ERROR：模板文件夹或者编译文件夹没有创建！');
            }
        }
       
         
        //创建变量注入方法
        /**
         * assign()变量注入方法
         * @param  $_var 要注入的变量名，对应.tpl文件中的需要替换的变量
         * @param  $_values 要注入的变量值
         */
        public function assign($_var,$_values)
        {
            if(isset($_var) && !empty($_var)){
                $this->_vars[$_var] = $_values;
            }else{
                exit('ERROR:请设置变量名！');
            }
        }
         
         
        //创建一个显示方法，用来显示编译后的文件
        public function display($_file)
        {
            //设置模板文件的路径
            $_tplFile = TPL_DIR.$_file;
            //判断模板文件是否存在
            if(!file_exists($_tplFile)){
                exit('ERROR：模板文件不存在');
            }
            //拆分注入的变量
            extract($this->_vars);
            //执行文件名
            $_selfFile =  substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'],'/')+1);
            //设置编译文件名
            $_parFile  = TPL_C_DIR.md5($_file).$_file.'.php';

            $_parser = new Parser($this->_controller,$this->_action,$_tplFile);//模板文件
            //判断编译文件是否存在，模板文件是否修改过，源文件是否修改过
            
            $_parser->compile($_parFile);//编译后文件
                 //载入编译文件
            include $_parFile;
            return;
        }
    }