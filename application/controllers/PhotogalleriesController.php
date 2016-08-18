<?php

class PhotogalleriesController extends Zend_Controller_Action
{
    public function indexAction() {
        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );
        //prikaz svih photoGallerya
        $cmsPhotoGalleriesDbTable = new Application_Model_DbTable_CmsPhotoGalleries();

        $photoGalleries = $cmsPhotoGalleriesDbTable->search(array(
            'filters' => array(//filtriram tabelu po
            'status'=>Application_Model_DbTable_CmsPhotoGalleries::STATUS_ENABLED,
            //'work_title' =>  	'PHP Developer',
           // 'first_name' => array('Aleksandar', 'Aleksandra', 'Bojan')
            
            ),
            'orders' => array(//sortiram tabelu po
                'order_number'=>'ASC'
            ),
            //'limit' => 4,
            //'page' => 2
        ));
        
        $request= $this->getRequest();//saljemo zahtev
        $sitemapPageId= (int)$request->getParam('sitemap_page_id');//dohvatamo parametar
        //proveravamo page id
        if($sitemapPageId<=0){
            throw new Zend_Controller_Router_Exception('Invalid Sitemap Page id: ' . $sitemapPageId, 404);
        }
        //komunikacija sa bazom
        $cmsSitemapPageDbTable=new Application_Model_DbTable_CmsSitemapPages();
        
        $sitemapPage=$cmsSitemapPageDbTable->getSitemapPageById($sitemapPageId);
        
        if(!$sitemapPage){
            throw new Zend_Controller_Router_Exception('No Sitemap Page is found for id: ' . $sitemapPageId, 404);
        }
        
        if(
                $sitemapPage['status']==Application_Model_DbTable_CmsSitemapPages::STATUS_DISABLED
                
                && !Zend_Auth::getInstance()->hasIdentity()
        ){
            throw new Zend_Controller_Router_Exception('No Sitemap Page is disabled: ' . $sitemapPageId, 404);
        }

        $this->view->sitemapPage = $sitemapPage;
        $this->view->photoGalleries = $photoGalleries; //prosledjivanje rezultata
        $this->view->systemMessages = $systemMessages;
        
        
    }
    
    public function galleryAction() {
       
        $request= $this->getRequest();//saljemo zahtev
        $sitemapPageId= (int)$request->getParam('sitemap_page_id');//dohvatamo parametar
        //proveravamo page id
        if($sitemapPageId<=0){
            throw new Zend_Controller_Router_Exception('Invalid Sitemap Page id: ' . $sitemapPageId, 404);
        }
        //komunikacija sa bazom
        $cmsSitemapPageDbTable=new Application_Model_DbTable_CmsSitemapPages();
        
        $sitemapPage=$cmsSitemapPageDbTable->getSitemapPageById($sitemapPageId);
        
        if(!$sitemapPage){
            throw new Zend_Controller_Router_Exception('No Sitemap Page is found for id: ' . $sitemapPageId, 404);
        }
        
        if(
                $sitemapPage['status']==Application_Model_DbTable_CmsSitemapPages::STATUS_DISABLED
                
                && !Zend_Auth::getInstance()->hasIdentity()
        ){
            throw new Zend_Controller_Router_Exception('No Sitemap Page is disabled: ' . $sitemapPageId, 404);
        }
        $id = (int) $request->getParam('id');
        
        $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();

        $photoGallery = $cmsPhotoGalleriesTable->getPhotoGalleryById($id);

        if (empty($photoGallery)) {

            throw new Zend_Controller_Router_Exception('No photo gallery is found with id: ' . $photoGalleryId, 404);
        }
        
        //print_r($photoGallery);
                    //die();
        $cmsPhotosDbTable = new Application_Model_DbTable_CmsPhotos();
        $photos = $cmsPhotosDbTable->search(array(
            'filters' => array(
                'photo_gallery_id' => $photoGallery['id']
            ),
            'orders' => array(
                'order_number' => 'ASC'
            ),
        ));
        
        $this->view->photos = $photos;
        $this->view->sitemapPage = $sitemapPage;
        $this->view->photoGallery = $photoGallery;
    }
}
?>