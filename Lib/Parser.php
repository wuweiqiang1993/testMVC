<?php
    class Parser {
        //获取模板内容
        private $_tpl;
        //构造方法，初始化模板
        public function __construct($_tplFile){
            //判断文件是否存在
            if(!$this->_tpl = file_get_contents($_tplFile)){
                exit('ERROR：读取模板出错！');
            }
             
        }
         
        //解析普通变量
        private function parVar(){
            $_pattern = '/\{\$([\w]+)\}/';
            if (preg_match($_pattern,$this->_tpl)) {
                $this->_tpl = preg_replace($_pattern,"<?php echo \$$1 ?>",$this->_tpl);
            }
        }
        //解析IF条件语句
        private function parIf(){
            //开头if模式
            $_patternIf = '/\{if\s+\$([\w]+)\}/';
            //结尾if模式
            $_patternEnd = '/\{\/if\}/';
            //else模式
            $_patternElse = '/\{else\}/';
            //else if模式
            $_patternElseIf = '/\{else\s+if\s+\$([\w]+)\}/';
            //判断if是否存在
            if(preg_match($_patternIf, $this->_tpl)){
                //判断是否有if结尾
                if(preg_match($_patternEnd, $this->_tpl)){
                    //替换开头IF
                    $this->_tpl = preg_replace($_patternIf, "<?php if(\$$1){ ?>", $this->_tpl);
                    //替换结尾IF
                    $this->_tpl = preg_replace($_patternEnd, "<?php } ?>", $this->_tpl);
                    //判断是否有else
                    if(preg_match($_patternElse, $this->_tpl)){
                        //替换else
                        $this->_tpl = preg_replace($_patternElse, "<?php }else{ ?>", $this->_tpl);
                    }
                    if(preg_match($_patternElseIf, $this->_tpl)){
                        //替换else
                        $this->_tpl = preg_replace($_patternElseIf, "<?php }else if(\$$1){ ?>", $this->_tpl);
                    }
                }else{
                    exit('ERROR：语句没有关闭！');
                }
            }
        }
        //解析foreach
        private function parForeach(){
            $_patternForeach = '/\{foreach\s+\$(\w+)\((\w+),(\w+)\)\}/';
            $_patternEndForeach = '/\{\/foreach\}/';
            //foreach里的值
            $_patternVal = '/\{@(\w+)\}/';
            //foreach里的变量
            $_patternVar = '/\{\$(\w+)\[\'(\w+)\'\]\}/';
            //判断是否存在
            if(preg_match($_patternForeach, $this->_tpl)){
                //判断结束标志
                if(preg_match($_patternEndForeach, $this->_tpl)){
                    //替换开头
                    $this->_tpl = preg_replace($_patternForeach, "<?php foreach(\$$1 as \$$2=>\$$3){?>", $this->_tpl);
                    //替换结束
                    $this->_tpl = preg_replace($_patternEndForeach, "<?php } ?>", $this->_tpl);
                    //替换值
                    $this->_tpl = preg_replace($_patternVal, "<?php echo \$$1?>", $this->_tpl);
                    $this->_tpl = preg_replace($_patternVar, "<?php echo \$$1['$2']?>", $this->_tpl);
                }else{
                    exit('ERROR：Foreach语句没有关闭');
                }
            }
        }
        //解析include
        private function parInclude(){
            $_pattern = '/\{include\s+\"(.*)\"\}/';
            if(preg_match($_pattern, $this->_tpl,$_file)){
                //判断头文件是否存在
                if(!file_exists($_file[1]) || empty($_file[1])){
                    exit('ERROR：包含文件不存在！');
                }
                //替换内容
                $this->_tpl = preg_replace($_pattern, "<?php include '$1';?>", $this->_tpl);
            }
        }
        //解析系统变量
        private function configVar(){
            $_pattern = '/<!--\{(\w+)\}-->/';
            if(preg_match($_pattern, $this->_tpl,$_file)){
                $this->_tpl = preg_replace($_pattern,"<?php echo \$this->_config['$1'] ?>", $this->_tpl);
                 
            }
        }
         
        //解析单行PHP注释
        private function parCommon(){
            $_pattern = '/\{#\}(.*)\{#\}/';
            if(preg_match($_pattern, $this->_tpl)){
                $this->_tpl = preg_replace($_pattern, "<?php /*($1) */?>", $this->_tpl);
            }
        }
         
         
        //生成编译文件
        public function compile($_parFile){
            //解析模板变量
            $this->parVar();
            //解析IF
            $this->parIf();
            //解析注释
            $this->parCommon();
            //解析Foreach
            $this->parForeach();
            //解析include
            $this->parInclude();
            //解析系统变量
            $this->configVar();
            //生成编译文件
            if(!file_put_contents($_parFile, $this->_tpl)){
                exit('ERROR：编译文件生成失败！');
            }
        }
    }