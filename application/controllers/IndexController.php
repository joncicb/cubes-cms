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
        
        $this->view->services = $services;
        $this->view->indexSlides = $indexSlides;
    }
    public function testAction()
    {
        
    }

}

