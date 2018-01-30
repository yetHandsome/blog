<?php
class UserPlugin extends Yaf_Plugin_Abstract {
    
    public function chenckLogin($request) {
        
    }
    
    public function chenckRole($request) {
        //p('chenckRole');
        //p($request->module);
        //p($request->controller);
        //p($request->action);
    }
    
    public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        if($request->isXmlHttpRequest()){
            Yaf_Dispatcher::getInstance()->disableView();    //如果只是提供数据接口，则禁止模板输出
        }
    }

    public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        $this->chenckLogin($request);
        //ob_start();
    }
    
    //可以做视图缓存
    public function dispatchLoopShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        //p('dispatchLoopShutdown');
        //$output = ob_get_contents();
        //p($output);
    }
}