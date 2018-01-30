<?php

/**
 * 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends Yaf_Bootstrap_Abstract{

        public function _initConfig(Yaf_Dispatcher $dispatcher) {
                $config = Yaf_Application::app()->getConfig();
                Yaf_Registry::set("config", $config);
                //p($config->application->directory);
        }
        
        //注册插件
        public function _initPlugin(Yaf_Dispatcher $dispatcher) {
            $user = new UserPlugin();
            $dispatcher->registerPlugin($user);
            //p('_initPlugin');
        }

//        public function _initDefaultName(Yaf_Dispatcher $dispatcher) {
//                $dispatcher->setDefaultModule("Index")->setDefaultController("Index")->setDefaultAction("index");
//        }
        
        public function _initView(){
            
        }
}