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
        
        if ($id != 0) {
            
            $sitemapPage = $cmsSitemapPagesDbTable->getSitemapPageById($id);

            if (!$sitemapPage) {
                throw new Zend_Controller_Router_Exception('No sitemap page is found', 404);
            }
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
        
        $this->view->currentSitemapPageId = $id;
        $this->view->childSitemapPages = $childSitemapPages;
        $this->view->systemMessages = $systemMessages;
        $this->view->sitemapPageBreadcrumbs = $sitemapPageBredcrumbs;
    }
        public function dashboardAction() {
        
        $cmsSitemapPagesDbTable = new Application_Model_DbTable_CmsSitemapPages();

        $enabled = $cmsSitemapPagesDbTable->count(array(
        'status'=>Application_Model_DbTable_CmsSitemapPages::STATUS_ENABLED
        ));
        
        $allPages =$cmsSitemapPagesDbTable->count();
        
         
   
        $this->view->enabledSitemapPages = $enabled;
        $this->view->allSitemapPages = $allPages;
    }
    
    public function addAction() {
     $request = $this->getRequest(); //objekat koji cuva inputdata podatke unete preko forme to je getter za post podatke
     
     $parentId = (int) $request->getParam('parent_id', 0);
     
     if($parentId < 0){
            throw new Zend_Controller_Router_Exception('Invalid id for sitemap pages', 404);//kad ovako bacamo exception korisniku se direktno pojavljuje sta pise ovde
        }
     
     $parentType = '';
     
        
     $cmsSitemapPagesDbTable = new Application_Model_DbTable_CmsSitemapPages();
     
     if($parentId !=0){
         
         $parentSitemapPage = $cmsSitemapPagesDbTable->getSitemapPageById($parentId);//ovde utvrdjujemo da li strana postoji na osnovu toga da li postoji parent_id
         
         if(!$parentSitemapPage){
             throw new Zend_Controller_Router_Exception('No sitemap page is found for id: '. $parentId, 404);
         }
         $parentType=$parentSitemapPage['type'];
         
     }
     
     
     $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(//niz za poruke o uspesno ili neuspesno unetim podacima u formu
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );

        $form = new Application_Form_Admin_SitemapPageAdd($parentId, $parentType);

        //default form data
        $form->populate(array(
        ));

        if ($request->isPost() && $request->getPost('task') === 'save') {//ovo znaci ukoliko je forma pokrenuta da li je form zahtev POST i da li je yahtev pokrenut na formi, asocijativni niz ciji su kljucevi atributi iz polja forme a vrednosti unos korisnika u formu
            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {//validacija forme ukoliko nisu validni/dobri podaci iz forme bacamo exception i idemo na catch
                    throw new Application_Model_Exception_InvalidInput('Invalid form data was sent for new sitemapPage');
                }

                //get form data
                $formData = $form->getValues(); //ovo treba da se upise u bazu(podaci iz forme)
                
                //set parent_id for new page
                $formData['parent_id'] = $parentId;
                
                
                //remove key sitemap_page_photo form because there is no column sitemap_page_photo in cms_sitemapPage
                //unset($formData['sitemap_page_photo']);
                //die(print_r($formData, true));
                

                //insert sitemapPage returns ID of the new sitemapPage
                $sitemapPageId =  $cmsSitemapPagesDbTable->insertsitemapPage($formData);

                
//                if($form->getElement('sitemap_page_photo')->isUploaded()) {
//                //photo is uploaded
//                    $fileInfos = $form->getElement('sitemap_page_photo')->getFileInfo('sitemap_page_photo');
//                    $fileInfo=$fileInfos['sitemap_page_photo'];
//                    
//                    try{
//                      //open uploaded photo in temporary directory
//                     $sitemapPagePhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
//                     //dimenzionise sliku
//                     $sitemapPagePhoto->fit(150, 150);
//                     
//                     $sitemapPagePhoto->save(PUBLIC_PATH . '/uploads/sitemapPage/' . $sitemapPageId . '.jpg');
//                     
//                    } catch (Exception $ex) {
//                        $flashMessenger->addMessage('sitemapPage has been saved, but error occured during image processing', 'errors'); //u sesiju upisujemo poruku sitemapPage has been saved
//                //redirect to same or another page
//                        $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
//                        $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _sitemapPage
//                            ->gotoRoute(array(
//                                'controller' => 'admin_sitemapPage',
//                                'action' => 'edit',
//                                'id' => $sitemapPageId
//                                    ), 'default', true);  
//                    }
//                    
////                    print_r($fileInfo);
////                    die();
//                    
//                    //isto kao $fileInfo=$_FILES['sitemap_page_photo'];
//                }
                
                
                // do actual task
                //save to database etc
                //set system message
                $flashMessenger->addMessage('sitemapPage has been saved', 'success'); //u sesiju upisujemo poruku sitemapPage has been saved
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
                $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _sitemapPage
                        ->gotoRoute(array(
                            'controller' => 'admin_sitemap',
                            'action' => 'index',
                            'id' => $parentId
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }
        $sitemapPageBredcrumbs = $cmsSitemapPagesDbTable->getSitemapPageBredcrumbs($parentId);
        
        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form; 
        $this->view->parentId = $parentId;
        $this->view->sitemapPageBredcrumbs = $sitemapPageBredcrumbs;
    }
    
    public function editAction() {
        $request = $this->getRequest(); //dohvatamo request objekat
        
        $id = (int) $request->getParam('id'); //iscitavamo parametar id filtriramo ga da bude int

        if ($id <= 0) {
            //prekida se izvrsavanje programa i prikazuje se "Page not found"
            throw new Zend_Controller_Router_Exception('Invalid Sitemap Page id: ' . $id, 404);
        }
        
        $cmsSitemapPagesTable = new Application_Model_DbTable_CmsSitemapPages();

        $sitemapPage = $cmsSitemapPagesTable->getSitemapPageById($id);

        if (empty($sitemapPage)) {

            throw new Zend_Controller_Router_Exception('No sitemapPage is found with id: ' . $id, 404);
        }
        
        
        $parentType='';
        if($sitemapPage['parent_id'] != 0 ){
            $parentSitemapPage = $cmsSitemapPagesTable->getSitemapPageById($sitemapPage['parent_id']);
            
            $parentType=$parentSitemapPage['type'];
        }
        
        //$this->view->sitemapPage = $sitemapPage;//prosledjujemo $sitemapPagea prezentacionoj logici
        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(//niz za poruke o uspesno ili neuspesno unetim podacima u formu
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );

        $form = new Application_Form_Admin_SitemapPageEdit($sitemapPage['id'], $sitemapPage['parent_id'], $parentType);

        //default form data
        $form->populate($sitemapPage);
        
        if ($request->isPost() && $request->getPost('task') === 'update') {//ovo znaci ukoliko je forma pokrenuta da li je form zahtev POST i da li je yahtev pokrenut na formi, asocijativni niz ciji su kljucevi atributi iz polja forme a vrednosti unos korisnika u formu
            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {//validacija forme ukoliko nisu validni/dobri podaci iz forme bacamo exception i idemo na catch
                    throw new Application_Model_Exception_InvalidInput('Invalid form data was sent for sitemapPage');
                }

                //get form data
                $formData = $form->getValues(); //ovo treba da se upise u bazu(podaci iz forme)
                //die(print_r($formData, true));
                //$cmsSitemapPagesTable = new Application_Model_DbTable_CmsSitemapPages();
                //$cmsSitemapPagesTable->insert($formData);
                
//                unset($formData['sitemapPage_photo']);
//
//                if($form->getElement('sitemapPage_photo')->isUploaded()) {
//                //photo is uploaded
//                    $fileInfos = $form->getElement('sitemapPage_photo')->getFileInfo('sitemapPage_photo');
//                    $fileInfo=$fileInfos['sitemapPage_photo'];
//                    
//                    try{
//                      //open uploaded photo in temporary directory
//                     $sitemapPagePhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
//                     //dimenzionise sliku
//                     $sitemapPagePhoto->fit(150, 150);
//                     
//                     $sitemapPagePhoto->save(PUBLIC_PATH . '/uploads/sitemapPages/' . $sitemapPage['id'] . '.jpg');
//                     
//                    } catch (Exception $ex) {
//                        
//                        throw new Application_Model_Exception_InvalidInput('Error occured during image processing');
//                        
//                    }
//                }
                //Radimo update postojeceg zapisa u tabeli
               
                $cmsSitemapPagesTable->updateSitemapPage($sitemapPage['id'], $formData);

                // do actual task
                //save to database etc
                //set system message
                $flashMessenger->addMessage('Sitemap Page has been updated', 'success'); //u sesiju upisujemo poruku sitemapPage has been saved
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
                $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _sitemapPages
                        ->gotoRoute(array(
                            'controller' => 'admin_sitemap',
                            'action' => 'index',
                            'id' => $sitemapPage['parent_id']
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }
        $sitemapPageBredcrumbs = $cmsSitemapPagesTable->getSitemapPageBredcrumbs($sitemapPage['parent_id']);
        
        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
        $this->view->sitemapPage = $sitemapPage;
        $this->view->sitemapPageBredcrumbs = $sitemapPageBredcrumbs;
    }
    
        public function disableAction(){
        $request = $this->getRequest(); //dohvatamo request objekat
        
        if(!$request->isPost() || $request->getPost('task') != 'disable'){
            //request is not post
            //or task is not delete
            //redirect to index page
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _sitemapPages
                    ->gotoRoute(array(
                        'controller' => 'admin_sitemap',
                        'action' => 'index',
                        'id' => $sitemapPage['parent_id']
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try  {
            // read $_POST['id']
            $id = (int) $request->getPost('id'); //iscitavamo parametar id filtriramo ga da bude int

            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput('Invalid Sitemap Page id: ' . $id);
                
            }

            $cmsSitemapPagesTable = new Application_Model_DbTable_CmsSitemapPages();

            $sitemapPage = $cmsSitemapPagesTable->getSitemapPageById($id);

            if (empty($sitemapPage)) {
                throw new Application_Model_Exception_InvalidInput('No Sitemap Page is found with id: ' . $id);
            }

            $cmsSitemapPagesTable->disableSitemapPage($id);

            $flashMessenger->addMessage('Sitemap Page type : ' . $sitemapPage['type'] . ' with title ' . $sitemapPage['title'] . ' has been disabled.', 'success');
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _sitemapPages
                    ->gotoRoute(array(
                        'controller' => 'admin_sitemap',
                        'action' => 'index',
                        'id' => $sitemapPage['parent_id']
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _sitemapPages
                    ->gotoRoute(array(
                        'controller' => 'admin_sitemap',
                        'action' => 'index',
                        'id' => $sitemapPage['parent_id']
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
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _sitemapPages
                    ->gotoRoute(array(
                        'controller' => 'admin_sitemap',
                        'action' => 'index',
                        'id' => $sitemapPage['parent_id']
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try  {
            // read $_POST['id']
            $id = (int) $request->getPost('id'); //iscitavamo parametar id filtriramo ga da bude int

            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput('Invalid Sitemap Page id: ' . $id);
                
            }

            $cmsSitemapPagesTable = new Application_Model_DbTable_CmsSitemapPages();

            $sitemapPage = $cmsSitemapPagesTable->getSitemapPageById($id);

            if (empty($sitemapPage)) {
                throw new Application_Model_Exception_InvalidInput('No Sitemap Page is found with id: ' . $id);
            }

            $cmsSitemapPagesTable->enableSitemapPage($id);

            $flashMessenger->addMessage('SitemapPage : ' . $sitemapPage['type'] . ' with title ' . $sitemapPage['title'] . ' has been enabled.', 'success');
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _sitemapPages
                    ->gotoRoute(array(
                        'controller' => 'admin_sitemap',
                        'action' => 'index',
                        'id' => $sitemapPage['parent_id']
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _sitemapPages
                    ->gotoRoute(array(
                        'controller' => 'admin_sitemap',
                        'action' => 'index',
                        'id' => $sitemapPage['parent_id']
                            ), 'default', true);
        } 
    }
    public function deleteAction(){
        $request = $this->getRequest(); //dohvatamo request objekat
        
        if(!$request->isPost() || $request->getPost('task') != 'delete'){
            //request is not post
            //or task is not delete
            //redirect to index page
            
            $redirector = $this->getHelper('Redirector'); 
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_sitemap',
                        'action' => 'index',
                        'id' => $sitemapPage['parent_id']
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try  {
            
            $id = (int) $request->getPost('id'); 

            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput('Invalid Sitemap Page id: ' . $id);
                
            }

            $cmsSitemapPagesTable = new Application_Model_DbTable_CmsSitemapPages();

            $sitemapPage = $cmsSitemapPagesTable->getSitemapPageById($id);

            if (empty($sitemapPage)) {
                throw new Application_Model_Exception_InvalidInput('No Sitemap Page is found with id: ' . $id);
            }

            $cmsSitemapPagesTable->deleteSitemapPage($id);

            $flashMessenger->addMessage('Sitemap Page : ' . $sitemapPage['title'] . ' has been deleted', 'success');
            $redirector = $this->getHelper('Redirector'); 
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_sitemap',
                        'action' => 'index',
                        'id' => $sitemapPage['parent_id']
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); 
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_sitemap',
                        'action' => 'index',
                        'id' => $sitemapPage['parent_id']
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
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _sitemapPages
                    ->gotoRoute(array(
                        'controller' => 'admin_sitemap',
                        'action' => 'index',
                        'id' => $sitemapPage['parent_id']
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
            
            $cmsSitemapPagesTable = new Application_Model_DbTable_CmsSitemapPages();
            
            $cmsSitemapPagesTable->updateOrderOfSitemapPages($sortedIds);
            
            $flashMessenger->addMessage('Order is successfully saved', 'success');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _sitemapPages
                    ->gotoRoute(array(
                        'controller' => 'admin_sitemap',
                        'action' => 'index',
                        'id' => $sitemapPage['parent_id']
                            ), 'default', true);
            
            
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _sitemapPages
                    ->gotoRoute(array(
                        'controller' => 'admin_sitemapPages',
                        'action' => 'index',
                        'id' => $sitemapPage['parent_id']
                            ), 'default', true);
        }
    }
}

