<?php

class Admin_ServicesController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );

       $cmsServicesDbTable = new Application_Model_DbTable_CmsServices();
       $services = $cmsServicesDbTable->search(array(
            //'filters' => array(
            //'description_search'=> 'ideja'
            
            //),
            'orders' => array(//sortiram tabelu po
                'order_number'=>'ASC'
            ),
            //'limit' => 4,
            //'page' => 2
        ));
        //$select = $cmsServicesDbTable->select();

        //$select->order('order_number');

        //$services = $cmsServicesDbTable->fetchAll($select);

        $this->view->services = $services;
        $this->view->systemMessages = $systemMessages;  
    }
    
    public function addAction() {
        $request = $this->getRequest();
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );
        
        $form = new Application_Form_Admin_ServiceAdd();

        $form->populate(array(
        ));

        if ($request->isPost() && $request->getPost('task') === 'save') {
            try {
                
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid form data was sent for new service');
                }
                
                $formData = $form->getValues(); 
                
                $cmsServicesTable = new Application_Model_DbTable_CmsServices();
                $cmsServicesTable->insertService($formData);

                $flashMessenger->addMessage('Service has been saved', 'success');
                
                $redirector = $this->getHelper('Redirector'); 
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_services',
                            'action' => 'index'
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
    }
    public function deleteAction(){
        $request = $this->getRequest(); //dohvatamo request objekat
        
        if(!$request->isPost() || $request->getPost('task') != 'delete'){
            //request is not post
            //or task is not delete
            //redirect to index page
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _members
                    ->gotoRoute(array(
                        'controller' => 'admin_services',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try  {
            // read $_POST['id']
            $id = (int) $request->getPost('id'); //iscitavamo parametar id filtriramo ga da bude int

            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput('Invalid service id: ' . $id);
                
            }

            $cmsServicesTable = new Application_Model_DbTable_CmsServices();

            $service = $cmsServicesTable->getServiceById($id);

            if (empty($service)) {
                throw new Application_Model_Exception_InvalidInput('No service is found with id: ' . $id);
            }

            $cmsServicesTable->deleteService($id);

            $flashMessenger->addMessage('Service : ' . $service['title'] . ' has been deleted', 'success');
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _members
                        ->gotoRoute(array(
                        'controller' => 'admin_services',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _members
                        ->gotoRoute(array(
                        'controller' => 'admin_services',
                        'action' => 'index'
                            ), 'default', true);
        } 
    }
    public function editAction() {
        $request = $this->getRequest();
        $id = (int) $request->getParam('id');

        if ($id <= 0) {
            throw new Zend_Controller_Router_Exception('Invalid service id: ' . $id, 404);
        }

        $cmsServicesTable = new Application_Model_DbTable_CmsServices();

        $service = $cmsServicesTable->getServiceById($id);

        if (empty($service)) {
            throw new Zend_Controller_Router_Exception('No service is found with id: ' . $id, 404);
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );

        $form = new Application_Form_Admin_ServiceAdd();

        $form->populate($service);
        
        if ($request->isPost() && $request->getPost('task') === 'update') {
            try {
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid form data was sent for service');
                }
                
                $formData = $form->getValues();

                $cmsServicesTable->updateService($service['id'], $formData);

                $flashMessenger->addMessage('Service has been updated', 'success');
                
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_services',
                            'action' => 'index'
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
        $this->view->service = $service;
    }
    public function disableAction(){
        $request = $this->getRequest(); //dohvatamo request objekat
        
        if(!$request->isPost() || $request->getPost('task') != 'disable'){
            //request is not post
            //or task is not delete
            //redirect to index page
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _members
                        ->gotoRoute(array(
                        'controller' => 'admin_services',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try  {
            // read $_POST['id']
            $id = (int) $request->getPost('id'); //iscitavamo parametar id filtriramo ga da bude int

            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput('Invalid service id: ' . $id);
                
            }

            $cmsServicesTable = new Application_Model_DbTable_CmsServices();

            $service = $cmsServicesTable->getServiceById($id);

            if (empty($service)) {
                throw new Application_Model_Exception_InvalidInput('No service is found with id: ' . $id);
            }

            $cmsServicesTable->disableService($id);

            $flashMessenger->addMessage('Service : ' . $service['title']  . ' has been disabled', 'success');
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _members
                        ->gotoRoute(array(
                        'controller' => 'admin_services',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _members
                        ->gotoRoute(array(
                        'controller' => 'admin_services',
                        'action' => 'index'
                            ), 'default', true);
        } 
    }
   
    public function enableAction(){
        $request = $this->getRequest(); //dohvatamo request objekat
        
        if(!$request->isPost() || $request->getPost('task') != 'enable'){
            //request is not post
            //or task is not delete
            //redirect to index page
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _members
                       ->gotoRoute(array(
                        'controller' => 'admin_services',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try  {
            // read $_POST['id']
            $id = (int) $request->getPost('id'); //iscitavamo parametar id filtriramo ga da bude int

            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput('Invalid service id: ' . $id);
                
            }

            $cmsServicesTable = new Application_Model_DbTable_CmsServices();

            $service = $cmsServicesTable->getServiceById($id);

            if (empty($service)) {
                throw new Application_Model_Exception_InvalidInput('No service is found with id: ' . $id);
            }

            $cmsServicesTable->enableService($id);

            $flashMessenger->addMessage('Service : ' . $service['title'] . ' has been enabled', 'success');
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _members
                       ->gotoRoute(array(
                        'controller' => 'admin_services',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _members
                       ->gotoRoute(array(
                        'controller' => 'admin_services',
                        'action' => 'index'
                            ), 'default', true);
        } 
    }
    
    public function updateorderAction(){
       $request = $this->getRequest(); //dohvatamo request objekat
        
        if(!$request->isPost() || $request->getPost('task') != 'saveOrder'){
            //request is not post
            //or task is not saveOrder
            //redirect to index page
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _members
                    ->gotoRoute(array(
                        'controller' => 'admin_services',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger'); 
        
        try{
           $sortedIds =  $request->getPost('sorted_ids'); //iscitavamo parametar id filtriramo ga da bude int

            if(empty($sortedIds)){
                
                throw new Application_Model_Exception_InvalidInput('Sorted ids are not sent');
                
            }
            $sortedIds = trim($sortedIds, ' ,');
            
            
            
            if(!preg_match('/^[0-9]+(,[0-9]+)*$/', $sortedIds)){
                throw new Application_Model_Exception_InvalidInput('Invalid sorted ids: ' . $sortedIds);
            }
            
            $sortedIds = explode(',', $sortedIds);
            
            $cmsServicesTable = new Application_Model_DbTable_CmsServices();
            
            $cmsServicesTable->updateOrderOfServices($sortedIds);
            
            $flashMessenger->addMessage('Order is successfully saved', 'success');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _members
                    ->gotoRoute(array(
                        'controller' => 'admin_services',
                        'action' => 'index'
                            ), 'default', true);
            
            
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _members
                    ->gotoRoute(array(
                        'controller' => 'admin_services',
                        'action' => 'index'
                            ), 'default', true);
        }
    }
    public function dashboardAction() {
        
        $cmsServicesDbTable = new Application_Model_DbTable_CmsServices();

        $enabled = $cmsServicesDbTable->count(array(
        'status'=>Application_Model_DbTable_CmsServices::STATUS_ENABLED,
        //'title_search'=>'seo'//se koristi kao keyword        
                ));
        
        $allServices =$cmsServicesDbTable->count();
       
       
                
        $this->view->enabledServices = $enabled;
        $this->view->allServices = $allServices;
    }
}
