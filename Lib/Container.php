<?php
/**
 * 通过反射，控制反转解决类依赖，使上层控制底层的依赖
 */
class Container
{
    //构建类的对象
    static public function make($className)
    {
        $reflectionClass = new ReflectionClass($className);
        $constructor = $reflectionClass->getConstructor();
        if(!is_null($constructor)){
            $parameters = $constructor->getParameters();
            $dependencies = self::getDependencies($parameters);
            return $reflectionClass->newInstanceArgs($dependencies);
        }else{
            return $reflectionClass->newInstance();
        }
    }

    //依赖解析
    static public function getDependencies($parameters)
    {
        $dependencies = [];
        foreach ($parameters as $parameter) {
            
            $dependency = $parameter->getClass();
            
            if (is_null($dependency)) {
                
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    //不是可选参数的为了简单直接赋值为字符串0
                    //针对构造方法的必须参数这个情况
                    $dependencies[] = '0';
                }
            } else {
                //递归解析出依赖类的对象
                $dependencies[] = self::make($parameter->getClass()->name);
            }
        }
        return $dependencies;
    }
}