<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initRouter () {
        $this->bootstrap('db');//resurs db daje konekciju ka bazi
        //
        
        $sitemapPageTypes = array(
            'StaticPage'=>array(
                'title'=>'Static Page',
                'subtypes'=>array(
                    //0 means unlimited number of this page type
                   'StaticPage'=>0 
                )
            ),
            'AboutUsPage'=>array(
                'title'=>'About Us Page',
                'subtypes'=>array(
                    
                )
            ),
            'ServicesPage'=>array(
                 'title'=>'Services Page',
                'subtypes'=>array(
                    
                )
            ),
            'ContactPage'=>array(
                 'title'=>'Contact Page',
                'subtypes'=>array(
                    
                )
            ),
            'PhotoGalleriesPage'=>array(
                 'title'=>'PhotoGalleries Page',
                'subtypes'=>array(
                    
                )
            )
            
        );
        
        $rootSitemapPageTypes = array(
            'StaticPage'=>0,
            'AboutUsPage'=>1,
            'ServicesPage'=>1,
            'ContactPage'=>1,
            'PhotoGalleriesPage'=>1
        );
        
        Zend_Registry::set('sitemapPageTypes', $sitemapPageTypes);//puni se samo u bootstrap fajlu-preporuka registar vrednosti koje koristimo kroz aplikaciju
        Zend_Registry::set('rootSitemapPageTypes', $rootSitemapPageTypes);
        
        // POSLEDNJA DODATA RUTA IMA NAJVECI PRIORITET!!!
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $router instanceof Zend_Controller_Router_Rewrite;//kod za dobijanje rutera
        
//        // 1. static route
//        $router->addRoute('about-us-route', new Zend_Controller_Router_Route_Static(//za rute koje nemaju parametre, moze i da se koristi za maskiranje putanje
//            'about-us',
//            array(
//                'controller' => 'aboutus',
//                'action' => 'index'
//            )
//        ));
//        
//        // 2. route, osnovna ruta za hvatanje parametara
//        $router->addRoute('member-route', new Zend_Controller_Router_Route(
//            'about-us/member/:id/:member_slug',
//            array(
//                'controller' => 'aboutus',
//                'action' => 'member',
//                //'member_slug' => '', // ovo je default za member_slug
//            )
//        ));
        
        $router->addRoute('contact-us-route', new Zend_Controller_Router_Route_Static(//za rute koje nemaju parametre, moze i da se koristi za maskiranje putanje
            'contact-us',
            array(
                'controller' => 'contact',
                'action' => 'index'
            )
        ));
        
         $router->addRoute('askmember-route', new Zend_Controller_Router_Route(
            'contact-us/ask-member/:id/:askmember_slug',
            array(
                'controller' => 'contact',
                'action' => 'askmember',
                //'member_slug' => '', // ovo je default za member_slug
            )
        ));
        
        $sitemapPagesMap = Application_Model_DbTable_CmsSitemapPages::getSitemapPagesMap();//staticki poziv funkcije
        
        foreach ($sitemapPagesMap as $sitemapPageId => $sitemapPageMap) {
            if($sitemapPageMap['type']=='StaticPage'){
            $router->addRoute('static-page-route-' . $sitemapPageId, new Zend_Controller_Router_Route_Static(//za rute koje nemaju parametre, moze i da se koristi za maskiranje putanje
            $sitemapPageMap['url'],
            array(
                'controller' => 'staticpage',
                'action' => 'index',
                'sitemap_page_id'=>$sitemapPageId
            )
        ));  
            }
             if($sitemapPageMap['type']=='AboutUsPage'){
            $router->addRoute('static-page-route-' . $sitemapPageId, new Zend_Controller_Router_Route_Static(//za rute koje nemaju parametre, moze i da se koristi za maskiranje putanje
            $sitemapPageMap['url'],
            array(
                'controller' => 'aboutus',
                'action' => 'index',
                'sitemap_page_id'=>$sitemapPageId
                 
            )
        ));
            $router->addRoute('member-route', new Zend_Controller_Router_Route(
            $sitemapPageMap['url'] . '/member/:id/:member_slug',
            array(
                'controller' => 'aboutus',
                'action' => 'member',
                'member_slug' => '', // ovo je default za member_slug
            )
        ));
            }
            if($sitemapPageMap['type']=='CounactUsPage'){
            $router->addRoute('static-page-route-' . $sitemapPageId, new Zend_Controller_Router_Route_Static(//za rute koje nemaju parametre, moze i da se koristi za maskiranje putanje
            $sitemapPageMap['url'],
            array(
                'controller' => 'contact',
                'action' => 'index',
                'sitemap_page_id'=>$sitemapPageId
            )
        ));  
            }
            if($sitemapPageMap['type']=='ServicesPage'){
            $router->addRoute('static-page-route-' . $sitemapPageId, new Zend_Controller_Router_Route_Static(//za rute koje nemaju parametre, moze i da se koristi za maskiranje putanje
            $sitemapPageMap['url'],
            array(
                'controller' => 'services',
                'action' => 'index',
                'sitemap_page_id'=>$sitemapPageId
            )
        ));  
            }
            if($sitemapPageMap['type']=='PhotoGalleriesPage'){
            $router->addRoute('static-page-route-' . $sitemapPageId, new Zend_Controller_Router_Route_Static(//za rute koje nemaju parametre, moze i da se koristi za maskiranje putanje
            $sitemapPageMap['url'],
            array(
                'controller' => 'photogalleries',
                'action' => 'index',
                'sitemap_page_id'=>$sitemapPageId
            )
        )); 
            $router->addRoute('photo-gallery-route', new Zend_Controller_Router_Route(
            $sitemapPageMap['url'] . '/:id/:photo_gallery_slug',
            array(
                'controller' => 'photogalleries',
                'action' => 'gallery',
                'sitemap_page_id'=>$sitemapPageId
            )
        ));
            }
        }
        
    }
    protected function _initCache ()
{
    $frontEndOptions = array('lifetime' => 10,//trajanje kesa 10 sekundi
                             'automatic_serialization' => true
    );

    if (!file_exists( "../cache" )) {
        mkdir( "../cache", 0777, true);
    }

    $backEndOptions = array('cache_dir' => PUBLIC_PATH . "/../cache");
    // Get a Zend_Cache_Core object
    $cache = Zend_Cache::factory('Core',
                                 'File',
                                 $frontEndOptions,
                                 $backEndOptions
    );
    Zend_Registry::set('mycache', $cache);//setovali smo kes u registar
}
    protected function _initTranslate() {
    $translate = new Zend_Translate(
                    array(
                    'adapter' => 'array',
                    'content' => APPLICATION_PATH . '/translate/language/en.php',
                    'locale' => 'en'
                    )
                );

    $translate->addTranslation(
        array(
            'adapter' => 'array',
            'content' => APPLICATION_PATH . '/translate/language/sr.php',
            'locale' => 'sr'
        )
    );

    $translate->setLocale('en');

    Zend_Registry::set('Zend_Translate', $translate);
}
}
