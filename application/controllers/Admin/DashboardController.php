<?php

class Admin_dashboardController extends Zend_Controller_Action
{
    public function indexAction(){
        //Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );
         $this->view->systemMessages = $systemMessages;
    }
}
