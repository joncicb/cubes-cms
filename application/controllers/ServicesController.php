<?php

class ServicesController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
      $cmsServicesDbTable = new Application_Model_DbTable_CmsServices();
        
        $select = $cmsServicesDbTable->select();
        
        $select->where('status = ?', Application_Model_DbTable_CmsServices::STATUS_ENABLED)
                ->order('order_number');

        $services = $cmsServicesDbTable->fetchAll($select);
        
        $this->view->services = $services;
    }
    public function serviceAction()
    { 
      $request = $this->getRequest();
      
      $id = $request->getParam('id');

      $id = trim($id);
      $id = (int) $id; 

        if(empty($id)){
           throw new Zend_Controller_Router_Exception('No service id', 404);
        }
        $cmsServicesDbTable = new Application_Model_DbTable_CmsServices();
        
        $select = $cmsServicesDbTable->select();
        $select->where('id = ?', $id)
                ->where('status = ?', Application_Model_DbTable_CmsServices::STATUS_ENABLED);
        
        $foundServices = $cmsServicesDbTable->fetchAll($select);
        
        if(count($foundServices) <= 0){
            
           throw new Zend_Controller_Router_Exception('No service is found for id: ' . $id, 404);
        }
        
        $service = $foundServices[0];

        $select = $cmsServicesDbTable->select();
        
        $select->where('status = ?', Application_Model_DbTable_CmsServices::STATUS_ENABLED)
                ->where('id !=?', $id)
                ->order('order_number');
        
        $services = $cmsServicesDbTable->fetchAll($select);
        
        $this->view->services = $services;
        
        $this->view->service = $service;
        
    }

}


