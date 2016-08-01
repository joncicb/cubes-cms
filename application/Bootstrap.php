<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initRouter () {
        
        // POSLEDNJA DODATA RUTA IMA NAJVECI PRIORITET!!!
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $router instanceof Zend_Controller_Router_Rewrite;
        
        // 1. static route
        $router->addRoute('about-us-route', new Zend_Controller_Router_Route_Static(
            'about-us',
            array(
                'controller' => 'aboutus',
                'action' => 'index'
            )
        ));
        
        // 2. route, osnovna ruta za hvatanje parametara
        $router->addRoute('member-route', new Zend_Controller_Router_Route(
            'about-us/member/:id/:member_slug',
            array(
                'controller' => 'aboutus',
                'action' => 'member',
                //'member_slug' => '', // ovo je default za member_slug
            )
        ));
    }
}
