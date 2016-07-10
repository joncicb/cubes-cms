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

        $select = $cmsServicesDbTable->select();

        $select->order('order_number');

        $services = $cmsServicesDbTable->fetchAll($select);

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
    
}
