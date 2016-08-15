<?php

class Admin_IndexslidesController extends Zend_Controller_Action {

    public function indexAction() {
        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );
        //prikaz svih indexSlidea
        $cmsIndexSlidesDbTable = new Application_Model_DbTable_CmsIndexSlides();

        $indexSlides = $cmsIndexSlidesDbTable->search(array(
            //'filters' => array(//filtriram tabelu po
            //'status'=>Application_Model_DbTable_CmsIndexSlides::STATUS_DISABLED,
            //'work_title' =>  	'PHP Developer',
           // 'first_name' => array('Aleksandar', 'Aleksandra', 'Bojan')
            
            //),
            'orders' => array(//sortiram tabelu po
                'order_number'=>'ASC'
            ),
            //'limit' => 4,
            //'page' => 2
        ));


        $this->view->indexSlides = $indexSlides; //prosledjivanje rezultata
        $this->view->systemMessages = $systemMessages;
    }

    public function addAction() {

        $request = $this->getRequest(); //objekat koji cuva inputdata podatke unete preko forme to je getter za post podatke
        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(//niz za poruke o uspesno ili neuspesno unetim podacima u formu
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );

        $form = new Application_Form_Admin_IndexSlideAdd();

        //default form data
        $form->populate(array(
        ));



        if ($request->isPost() && $request->getPost('task') === 'save') {//ovo znaci ukoliko je forma pokrenuta da li je form zahtev POST i da li je yahtev pokrenut na formi, asocijativni niz ciji su kljucevi atributi iz polja forme a vrednosti unos korisnika u formu
            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {//validacija forme ukoliko nisu validni/dobri podaci iz forme bacamo exception i idemo na catch
                    throw new Application_Model_Exception_InvalidInput('Invalid form data was sent for new indexSlide');
                }

                //get form data
                $formData = $form->getValues(); //ovo treba da se upise u bazu(podaci iz forme)
                
                
                //remove key index_slide_photo form because there is no column index_slide_photo in cms_indexSlide
                unset($formData['index_slide_photo']);
                //die(print_r($formData, true));
                $cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexSlides();
                
                
                
                //insert indexSlide returns ID of the new indexSlide
                $indexSlideId =  $cmsIndexSlidesTable->insertIndexSlide($formData);

                
                if($form->getElement('index_slide_photo')->isUploaded()) {
                //photo is uploaded
                    $fileInfos = $form->getElement('index_slide_photo')->getFileInfo('index_slide_photo');
                    $fileInfo=$fileInfos['index_slide_photo'];
                    
                    try{
                      //open uploaded photo in temporary directory
                     $indexSlidePhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
                     //dimenzionise sliku
                     $indexSlidePhoto->fit(600, 400);
                     
                     $indexSlidePhoto->save(PUBLIC_PATH . '/uploads/index-slides/' . $indexSlideId . '.jpg');
                     
                    } catch (Exception $ex) {
                        $flashMessenger->addMessage('IndexSlide has been saved, but error occured during image processing', 'errors'); //u sesiju upisujemo poruku indexSlide has been saved
                //redirect to same or another page
                        $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
                        $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _indexSlides
                            ->gotoRoute(array(
                                'controller' => 'admin_indexslides',
                                'action' => 'edit',
                                'id' => $indexSlideId
                                    ), 'default', true);  
                    }
                    
//                    print_r($fileInfo);
//                    die();
                    
                    //isto kao $fileInfo=$_FILES['index_slide_photo'];
                }
                
                
                // do actual task
                //save to database etc
                //set system message
                $flashMessenger->addMessage('IndexSlide has been saved', 'success'); //u sesiju upisujemo poruku indexSlide has been saved
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
                $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _indexSlides
                        ->gotoRoute(array(
                            'controller' => 'admin_indexslides',
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
        $request = $this->getRequest(); //dohvatamo request objekat
        $id = (int) $request->getParam('id'); //iscitavamo parametar id filtriramo ga da bude int

        if ($id <= 0) {
            //prekida se izvrsavanje programa i prikazuje se "Page not found"
            throw new Zend_Controller_Router_Exception('Invalid indexSlide id: ' . $id, 404);
        }

        $cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexSlides();

        $indexSlide = $cmsIndexSlidesTable->getIndexSlideById($id);

        if (empty($indexSlide)) {

            throw new Zend_Controller_Router_Exception('No indexSlide is found with id: ' . $id, 404);
        }
        //$this->view->indexSlide = $indexSlide;//prosledjujemo $indexSlidea prezentacionoj logici
        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(//niz za poruke o uspesno ili neuspesno unetim podacima u formu
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );

        $form = new Application_Form_Admin_IndexSlideAdd();

        //default form data
        $form->populate($indexSlide);
        


        if ($request->isPost() && $request->getPost('task') === 'update') {//ovo znaci ukoliko je forma pokrenuta da li je form zahtev POST i da li je yahtev pokrenut na formi, asocijativni niz ciji su kljucevi atributi iz polja forme a vrednosti unos korisnika u formu
            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {//validacija forme ukoliko nisu validni/dobri podaci iz forme bacamo exception i idemo na catch
                    throw new Application_Model_Exception_InvalidInput('Invalid form data was sent for indexSlide');
                }

                //get form data
                $formData = $form->getValues(); //ovo treba da se upise u bazu(podaci iz forme)
                //die(print_r($formData, true));
                //$cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexSlides();
                //$cmsIndexSlidesTable->insert($formData);
                
                unset($formData['index_slide_photo']);

                if($form->getElement('index_slide_photo')->isUploaded()) {
                //photo is uploaded
                    $fileInfos = $form->getElement('index_slide_photo')->getFileInfo('index_slide_photo');
                    $fileInfo=$fileInfos['index_slide_photo'];
                    
                    try{
                      //open uploaded photo in temporary directory
                     $indexSlidePhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
                     //dimenzionise sliku
                     $indexSlidePhoto->fit(600, 400);
                     
                     $indexSlidePhoto->save(PUBLIC_PATH . '/uploads/index-slides/' . $indexSlide['id'] . '.jpg');
                     
                    } catch (Exception $ex) {
                        
                        throw new Application_Model_Exception_InvalidInput('Error occured during image processing');
                        
                    }
                }
                //Radimo update postojeceg zapisa u tabeli
               
                $cmsIndexSlidesTable->updateIndexSlide($indexSlide['id'], $formData);

                // do actual task
                //save to database etc
                //set system message
                $flashMessenger->addMessage('IndexSlide has been updated', 'success'); //u sesiju upisujemo poruku indexSlide has been saved
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
                $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _indexSlides
                        ->gotoRoute(array(
                            'controller' => 'admin_indexslides',
                            'action' => 'index'
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
        $this->view->indexSlide = $indexSlide;
    }
    public function deleteAction(){
        $request = $this->getRequest(); //dohvatamo request objekat
        
        if(!$request->isPost() || $request->getPost('task') != 'delete'){
            //request is not post
            //or task is not delete
            //redirect to index page
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _indexSlides
                    ->gotoRoute(array(
                        'controller' => 'admin_indexslides',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try  {
            // read $_POST['id']
            $id = (int) $request->getPost('id'); //iscitavamo parametar id filtriramo ga da bude int

            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput('Invalid indexSlide id: ' . $id);
                
            }

            $cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexSlides();

            $indexSlide = $cmsIndexSlidesTable->getIndexSlideById($id);

            if (empty($indexSlide)) {
                throw new Application_Model_Exception_InvalidInput('No indexSlide is found with id: ' . $id);
            }

            $cmsIndexSlidesTable->deleteIndexSlide($id);

            $flashMessenger->addMessage('IndexSlide : ' . $indexSlide['title']  . ' has been deleted', 'success');
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _indexSlides
                    ->gotoRoute(array(
                        'controller' => 'admin_indexslides',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _indexSlides
                    ->gotoRoute(array(
                        'controller' => 'admin_indexslides',
                        'action' => 'index'
                            ), 'default', true);
        } 
    }
    public function disableAction(){
        $request = $this->getRequest(); //dohvatamo request objekat
        
        if(!$request->isPost() || $request->getPost('task') != 'disable'){
            //request is not post
            //or task is not delete
            //redirect to index page
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _indexSlides
                    ->gotoRoute(array(
                        'controller' => 'admin_indexslides',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try  {
            // read $_POST['id']
            $id = (int) $request->getPost('id'); //iscitavamo parametar id filtriramo ga da bude int

            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput('Invalid indexSlide id: ' . $id);
                
            }

            $cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexSlides();

            $indexSlide = $cmsIndexSlidesTable->getIndexSlideById($id);

            if (empty($indexSlide)) {
                throw new Application_Model_Exception_InvalidInput('No indexSlide is found with id: ' . $id);
            }

            $cmsIndexSlidesTable->disableIndexSlide($id);

            $flashMessenger->addMessage('IndexSlide : ' . $indexSlide['title']  . ' has been disabled', 'success');
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _indexSlides
                    ->gotoRoute(array(
                        'controller' => 'admin_indexslides',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _indexSlides
                    ->gotoRoute(array(
                        'controller' => 'admin_indexslides',
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
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _indexSlides
                    ->gotoRoute(array(
                        'controller' => 'admin_indexslides',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try  {
            // read $_POST['id']
            $id = (int) $request->getPost('id'); //iscitavamo parametar id filtriramo ga da bude int

            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput('Invalid indexSlide id: ' . $id);
                
            }

            $cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexSlides();

            $indexSlide = $cmsIndexSlidesTable->getIndexSlideById($id);

            if (empty($indexSlide)) {
                throw new Application_Model_Exception_InvalidInput('No indexSlide is found with id: ' . $id);
            }

            $cmsIndexSlidesTable->enableIndexSlide($id);

            $flashMessenger->addMessage('IndexSlide : ' . $indexSlide['title']  . ' has been enabled', 'success');
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _indexSlides
                    ->gotoRoute(array(
                        'controller' => 'admin_indexslides',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _indexSlides
                    ->gotoRoute(array(
                        'controller' => 'admin_indexslides',
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
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _indexSlides
                    ->gotoRoute(array(
                        'controller' => 'admin_indexslides',
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
            
            $cmsIndexSlidesTable = new Application_Model_DbTable_CmsIndexSlides();
            
            $cmsIndexSlidesTable->updateOrderOfIndexSlides($sortedIds);
            
            $flashMessenger->addMessage('Order is successfully saved', 'success');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _indexSlides
                    ->gotoRoute(array(
                        'controller' => 'admin_indexslides',
                        'action' => 'index'
                            ), 'default', true);
            
            
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _indexSlides
                    ->gotoRoute(array(
                        'controller' => 'admin_indexslides',
                        'action' => 'index'
                            ), 'default', true);
        }
    }
    public function dashboardAction() {
        
        $cmsIndexSlidesDbTable = new Application_Model_DbTable_CmsIndexSlides();
        //$select = $cmsIndexSlidesDbTable->select();
        //$indexSlides = $cmsIndexSlidesDbTable->fetchAll($select);
        
        //$enabled = $cmsIndexSlidesDbTable->enabledIndexSlides($indexSlides);
        $enabled = $cmsIndexSlidesDbTable->count(array(
        'status'=>Application_Model_DbTable_CmsIndexSlides::STATUS_ENABLED));
        //$allIndexSlides =$cmsIndexSlidesDbTable->allIndexSlides($indexSlides);
        $allIndexSlides =$cmsIndexSlidesDbTable->count();
        
        
        $this->view->enabledIndexSlides = $enabled;
        $this->view->allIndexSlides = $allIndexSlides;
    }
}
