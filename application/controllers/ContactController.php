<?php

class ContactController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
 {
        // action body


        $request = $this->getRequest();
        $flashMessenger = $this->getHelper('FlashMessenger');

        $form = new Application_Form_Frontend_Contact();

        $systemMessages = 'init';

        if ($request->isPost() && $request->getPost('task') === 'contact') {

            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid form data bla bla');
                }

                //get form data
                $formData = $form->getValues();

                // do actual task
                //save to database etc
                $mailHelper = new Application_Model_Library_MailHelper();
                $from_email = $formData['email'];
                $to_email = 'joncicb@gmail.com';
                $from_name = $formData['name'];
                $message = $formData['message'];
                
                $result = $mailHelper->sendmail($to_email, $from_email, $from_name, $message);
                
                if(!$result){
                   $systemMessages = 'Error';
                }else{
                   $systemMessages = 'Success';  
                }
                
                
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
    }

    public function askmemberAction()
    {
        
      $request = $this->getRequest();
      
      $id = $request->getParam('id');
      
      //rezultat ove funkcije konvertuje u integer
      //filtriranje
      $id = trim($id);
      $id = (int) $id; // $id (int_val)  

        if(empty($id)){
           throw new Zend_Controller_Router_Exception('No member id', 404) ;//framework ce znati da vrati status ovog koda
        }
        
        $cmsMembersDbTable = new Application_Model_DbTable_CmsMembers();
        
        $select = $cmsMembersDbTable->select();
        $select->where('id = ?', $id);
                
        
        $foundMembers = $cmsMembersDbTable->fetchAll($select);//find vraca niz objekata tj vise redova, ne samo jedan
        
        if(count($foundMembers) <= 0){
            
           throw new Zend_Controller_Router_Exception('No member is found for id: ' . $id, 404) ;//framework ce znati da vrati status ovog koda
        }
        
        $askMember = $foundMembers[0];

        
        $this->view->askMember = $askMember;
        
    }
}

