<?php

class IndexController extends Zend_Controller_Action
{
  public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $cmsIndexSlidesDbTable = new Application_Model_DbTable_CmsIndexSlides();

        $indexSlides = $cmsIndexSlidesDbTable->search(array(
            'filters' => array(
                'status'=>Application_Model_DbTable_CmsIndexSlides::STATUS_ENABLED,
                //'link_type'=>array('InternalLink', 'ExternalLink'),
            //),
            //'orders' => array(//sortiram tabelu po
               // 'order_number'=>'DESC'
            ),
            //'limit' => 4,
            //'page' => 2
        ));
        $cmsSitemapPagesDbTable = new Application_Model_DbTable_CmsSitemapPages();
		
	$servicesSitemapPages = $cmsSitemapPagesDbTable->search(array(
            'filters' => array(
            'status' => Application_Model_DbTable_CmsSitemapPages::STATUS_ENABLED,
            'type' => 'ServicesPage'
			),
            'limit' => 1
		));
        
	$servicesSitemapPage = !empty($servicesSitemapPages) ? $servicesSitemapPages[0] : null;
        
        $cmsServicesDbTable = new Application_Model_DbTable_CmsServices();
        $services = $cmsServicesDbTable->search(array(
            'filters' => array(
                'status'=>  Application_Model_DbTable_CmsServices::STATUS_ENABLED,

            ),
            'orders' => array(//sortiram tabelu po
                'order_number'=>'ASC'
            ),
            'limit' => 4,
            //'page' => 2
        ));
        $photoGalleriesPages = $cmsSitemapPagesDbTable->search(array(
			'filters' => array(
				'status' => Application_Model_DbTable_CmsPhotoGalleries::STATUS_ENABLED,
				'type' => 'PhotoGalleriesPage'
			),
                        'limit'=> 1
		));
        
        $photoGalleriesPages = !empty($photoGalleriesPages) ? $photoGalleriesPages[0] : null;
        
        $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries ();
        $photoGalleries = $cmsPhotoGalleriesTable->search(array(
            'filters' => array(
                'status' => Application_Model_DbTable_CmsPhotoGalleries::STATUS_ENABLED
            ),
            'orders' => array(
                'order_number' => 'ASC',
            ),
            'limit' => 3
        ));
        
        
        $this->view->photoGalleries = $photoGalleries;
        $this->view->photoGalleriesPages = $photoGalleriesPages;
        $this->view->services = $services;
        $this->view->indexSlides = $indexSlides;
        $this->view->servicesSitemapPage = $servicesSitemapPage;
    }
    public function testAction()
    {
        
    }

}

