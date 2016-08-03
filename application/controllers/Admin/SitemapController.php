<?php


class Admin_SitemapController extends Zend_Controller_Action
{
    public function indexAction(){
        
        $request= $this->getRequest();
        
        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );
        
        //if no request_id parameter, than $parameterId will be 0
        $id = (int) $request->getParam('id', 0);
        
        if($id < 0){
            throw new Zend_Controller_Router_Exception('Invalid id for sitemap pages', 404);
        }
        
        $cmsSitemapPagesDbTable = new Application_Model_DbTable_CmsSitemapPages();
        
        $sitemapPage = $cmsSitemapPagesDbTable->getSitemapPageById($id);
        
        if(!$sitemapPage && $id !=0){
            throw new Zend_Controller_Router_Exception('No sitemap page is found', 404);
        }
        
        $childSitemapPages = $cmsSitemapPagesDbTable->search(array(
            'filters' =>array(
            'parent_id' => $id
            ),// navodimo kljuceve po kojima vrsimo filtriranje
            'orders' => array(
                'order_number' => 'ASC'
            ),//to sto si filtrirao sortiraj mi po order_number-u ASC
            //'limit' => 50,//vrati mi 50 zapisa na 3 stranici - u ovom slucaju ne koristimo paginaciju
            //'page' => 3
            
        ));
        
        $sitemapPageBredcrumbs = $cmsSitemapPagesDbTable->getSitemapPageBredcrumbs($id);
        
        $this->view->childSitemapPages = $childSitemapPages;
        $this->view->systemMessages = $systemMessages;
        $this->view->sitemapPageBreadcrumbs = $sitemapPageBredcrumbs;
    }
}

