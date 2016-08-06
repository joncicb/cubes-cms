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

