<?php

class AboutusController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $cmsMembersDbTable = new Application_Model_DbTable_CmsMembers();
        
        // $select je objekat klase Zend_Db_Select
        $select = $cmsMembersDbTable->select();
        
        $select->where('status = ?', Application_Model_DbTable_CmsMembers::STATUS_ENABLED)
                ->order('order_number');
                //->order('last_name')
               // ->order('first_name');
                //->limitPage(2, 3);//druga stranica, troje po stranici
               //->where('id = ?', 5);
        
        
        //debug za db select - vraca se sql upit
       //die($select->assemble());
        
        
       $request= $this->getRequest();//saljemo zahtev
        $sitemapPageId= (int) $request->getParam('sitemap_page_id');//dohvatamo parametar
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
                //proverava da li nije ulogovan user
                && !Zend_Auth::getInstance()->hasIdentity()
        ){
            throw new Zend_Controller_Router_Exception('No Sitemap Page is disabled: ' . $sitemapPageId, 404);
        }
            
        
        
        
        
        
        $members = $cmsMembersDbTable->fetchAll($select);
        $this->view->sitemapPage = $sitemapPage;
        $this->view->members = $members;//prosledjivanje rezultata
    }
    public function memberAction()
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
        $select->where('id = ?', $id)
                ->where('status = ?', Application_Model_DbTable_CmsMembers::STATUS_ENABLED);
        
        $foundMembers = $cmsMembersDbTable->fetchAll($select);//find vraca niz objekata tj vise redova, ne samo jedan
        
        if(count($foundMembers) <= 0){
            
           throw new Zend_Controller_Router_Exception('No member is found for id: ' . $id, 404) ;//framework ce znati da vrati status ovog koda
        }
        
        $member = $foundMembers[0];
//        $memberSlug = $request->getParam('member_slug');
//        
//        if(empty($memberSlug)) {
//            $redirector = $this->getHelper('Redirector');
//                        $redirector->setExit(true)
//                                ->gotoRoute(array(
//                                    'id' => $member['id'],
//                                    'member_slug' => $member['first_name'] . '-' . $member['last_name']
//                                ), 'member-route', true);
//        }
        //Fetching all other members
        $select = $cmsMembersDbTable->select();
        
        $select->where('status = ?', Application_Model_DbTable_CmsMembers::STATUS_ENABLED)
                ->where('id !=?', $id)
                ->order('order_number');
        
        
        $members = $cmsMembersDbTable->fetchAll($select);
        
        $this->view->members = $members;
        
        $this->view->member = $member;
        
    }

}

