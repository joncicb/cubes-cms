<?php

class Admin_PhotogalleriesController extends Zend_Controller_Action {

    public function indexAction() {
        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );
        //prikaz svih photoGallerya
        $cmsPhotoGalleriesDbTable = new Application_Model_DbTable_CmsPhotoGalleries();

        $photoGalleries = $cmsPhotoGalleriesDbTable->search(array(
            //'filters' => array(//filtriram tabelu po
            //'status'=>Application_Model_DbTable_CmsPhotoGalleries::STATUS_DISABLED,
            //'work_title' =>  	'PHP Developer',
           // 'first_name' => array('Aleksandar', 'Aleksandra', 'Bojan')
            
            //),
            'orders' => array(//sortiram tabelu po
                'order_number'=>'ASC'
            ),
            //'limit' => 4,
            //'page' => 2
        ));


        $this->view->photoGalleries = $photoGalleries; //prosledjivanje rezultata
        $this->view->systemMessages = $systemMessages;
    }

    public function addAction() {

        $request = $this->getRequest(); //objekat koji cuva inputdata podatke unete preko forme to je getter za post podatke
        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(//niz za poruke o uspesno ili neuspesno unetim podacima u formu
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );

        $form = new Application_Form_Admin_PhotoGalleryAdd();

        //default form data
        $form->populate(array(
        ));



        if ($request->isPost() && $request->getPost('task') === 'save') {//ovo znaci ukoliko je forma pokrenuta da li je form zahtev POST i da li je yahtev pokrenut na formi, asocijativni niz ciji su kljucevi atributi iz polja forme a vrednosti unos korisnika u formu
            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {//validacija forme ukoliko nisu validni/dobri podaci iz forme bacamo exception i idemo na catch
                    throw new Application_Model_Exception_InvalidInput('Invalid form data was sent for new photoGallery');
                }

                //get form data
                $formData = $form->getValues(); //ovo treba da se upise u bazu(podaci iz forme)
                
                
                //remove key photo_gallery_leading_photo form because there is no column photo_gallery_leading_photo in cms_photoGallery
                unset($formData['photo_gallery_leading_photo']);
                //die(print_r($formData, true));
                $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();
                
                
                
                //insert photoGallery returns ID of the new photoGallery
                $photoGalleryId =  $cmsPhotoGalleriesTable->insertPhotoGallery($formData);

                
                if($form->getElement('photo_gallery_leading_photo')->isUploaded()) {
                //photo is uploaded
                    $fileInfos = $form->getElement('photo_gallery_leading_photo')->getFileInfo('photo_gallery_leading_photo');
                    $fileInfo=$fileInfos['photo_gallery_leading_photo'];
                    
                    try{
                      //open uploaded photo in temporary directory
                     $photoGalleryPhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
                     //dimenzionise sliku
                     $photoGalleryPhoto->fit(360,270);
                     
                     $photoGalleryPhoto->save(PUBLIC_PATH . '/uploads/photo-galleries/' . $photoGalleryId . '.jpg');
                     
                    } catch (Exception $ex) {
                        $flashMessenger->addMessage('Photo Gallery has been saved, but error occured during image processing', 'errors'); //u sesiju upisujemo poruku photoGallery has been saved
                //redirect to same or another page
                        $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
                        $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photoGalleries
                            ->gotoRoute(array(
                                'controller' => 'admin_photogalleries',
                                'action' => 'edit',
                                'id' => $photoGalleryId
                                    ), 'default', true);  
                    }
                    
//                    print_r($fileInfo);
//                    die();
                    
                    //isto kao $fileInfo=$_FILES['photo_gallery_leading_photo'];
                }
                
                
                // do actual task
                //save to database etc
                //set system message
                $flashMessenger->addMessage('Photo Gallery has been saved', 'success'); //u sesiju upisujemo poruku photoGallery has been saved
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
                $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photoGalleries
                        ->gotoRoute(array(
                            'controller' => 'admin_photogalleries',
                            'action' => 'edit',
                            'id' => $photoGalleryId
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
            throw new Zend_Controller_Router_Exception('Invalid photo gallery id: ' . $id, 404);
        }

        $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();

        $photoGallery = $cmsPhotoGalleriesTable->getPhotoGalleryById($id);

        if (empty($photoGallery)) {

            throw new Zend_Controller_Router_Exception('No photo gallery is found with id: ' . $id, 404);
        }
        //$this->view->photoGallery = $photoGallery;//prosledjujemo $photoGallerya prezentacionoj logici
        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(//niz za poruke o uspesno ili neuspesno unetim podacima u formu
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );

        $form = new Application_Form_Admin_PhotoGalleryEdit();

        //default form data
        $form->populate($photoGallery);
        


        if ($request->isPost() && $request->getPost('task') === 'update') {//ovo znaci ukoliko je forma pokrenuta da li je form zahtev POST i da li je yahtev pokrenut na formi, asocijativni niz ciji su kljucevi atributi iz polja forme a vrednosti unos korisnika u formu
            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {//validacija forme ukoliko nisu validni/dobri podaci iz forme bacamo exception i idemo na catch
                    throw new Application_Model_Exception_InvalidInput('Invalid form data was sent for photo gallery');
                }

                //get form data
                $formData = $form->getValues(); //ovo treba da se upise u bazu(podaci iz forme)
                //die(print_r($formData, true));
                //$cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();
                //$cmsPhotoGalleriesTable->insert($formData);
                
                unset($formData['photo_gallery_leading_photo']);

                if($form->getElement('photo_gallery_leading_photo')->isUploaded()) {
                //photo is uploaded
                    $fileInfos = $form->getElement('photo_gallery_leading_photo')->getFileInfo('photo_gallery_leading_photo');
                    $fileInfo=$fileInfos['photo_gallery_leading_photo'];
                    
                    try{
                      //open uploaded photo in temporary directory
                     $photoGalleryPhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
                     //dimenzionise sliku
                     $photoGalleryPhoto->fit(360, 270);
                     
                     $photoGalleryPhoto->save(PUBLIC_PATH . '/uploads/photo-galleries/' . $photoGallery['id'] . '.jpg');
                     
                    } catch (Exception $ex) {
                        
                        throw new Application_Model_Exception_InvalidInput('Error occured during image processing');
                        
                    }
                }
                //Radimo update postojeceg zapisa u tabeli
               
                $cmsPhotoGalleriesTable->updatePhotoGallery($photoGallery['id'], $formData);

                // do actual task
                //save to database etc
                //set system message
                $flashMessenger->addMessage('Photo Gallery has been updated', 'success'); //u sesiju upisujemo poruku photoGallery has been saved
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
                $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photoGalleries
                        ->gotoRoute(array(
                            'controller' => 'admin_photogalleries',
                            'action' => 'index'
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }
        
        $cmsPhotosDbTable = new Application_Model_DbTable_CmsPhotos();
        $photos = $cmsPhotosDbTable->search(array(
            'filters'=>array(
                'photo_gallery_id' =>$photoGallery['id']
            ),
            'orders'=>array(
                'order_number'=>'ASC'
            )
        ));

        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
        $this->view->photoGallery = $photoGallery;
        $this->view->photos = $photos;
    }
    public function deleteAction(){
        $request = $this->getRequest(); //dohvatamo request objekat
        
        if(!$request->isPost() || $request->getPost('task') != 'delete'){
            //request is not post
            //or task is not delete
            //redirect to index page
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photoGalleries
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try  {
            // read $_POST['id']
            $id = (int) $request->getPost('id'); //iscitavamo parametar id filtriramo ga da bude int

            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput('Invalid photo gallery id: ' . $id);
                
            }

            $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();

            $photoGallery = $cmsPhotoGalleriesTable->getPhotoGalleryById($id);

            if (empty($photoGallery)) {
                throw new Application_Model_Exception_InvalidInput('No photo gallery is found with id: ' . $id);
            }

            $cmsPhotoGalleriesTable->deletePhotoGallery($id);

            $flashMessenger->addMessage('PhotoGallery : ' . $photoGallery['first_name'] . ' ' . $photoGallery['last_name'] . ' has been deleted', 'success');
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photoGalleries
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photoGalleries
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
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
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photoGalleries
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try  {
            // read $_POST['id']
            $id = (int) $request->getPost('id'); //iscitavamo parametar id filtriramo ga da bude int

            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput('Invalid photo gallery id: ' . $id);
                
            }

            $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();

            $photoGallery = $cmsPhotoGalleriesTable->getPhotoGalleryById($id);

            if (empty($photoGallery)) {
                throw new Application_Model_Exception_InvalidInput('No photo gallery is found with id: ' . $id);
            }

            $cmsPhotoGalleriesTable->disablePhotoGallery($id);

            $flashMessenger->addMessage('Photo gallery : ' . $photoGallery['title']  . ' has been disabled', 'success');
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photoGalleries
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photoGalleries
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
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
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photoGalleries
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try  {
            // read $_POST['id']
            $id = (int) $request->getPost('id'); //iscitavamo parametar id filtriramo ga da bude int

            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput('Invalid photo gallery id: ' . $id);
                
            }

            $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();

            $photoGallery = $cmsPhotoGalleriesTable->getPhotoGalleryById($id);

            if (empty($photoGallery)) {
                throw new Application_Model_Exception_InvalidInput('No photo gallery is found with id: ' . $id);
            }

            $cmsPhotoGalleriesTable->enablePhotoGallery($id);

            $flashMessenger->addMessage('Photo gallery : ' . $photoGallery['title'] . ' has been enabled', 'success');
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photoGalleries
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photoGalleries
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
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
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photoGalleries
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
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
            
            $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();
            
            $cmsPhotoGalleriesTable->updateOrderOfPhotoGalleries($sortedIds);
            
            $flashMessenger->addMessage('Order is successfully saved', 'success');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photoGalleries
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'index'
                            ), 'default', true);
            
            
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photoGalleries
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'index'
                            ), 'default', true);
        }
    }
    public function dashboardAction() {
        
        $cmsPhotoGalleriesDbTable = new Application_Model_DbTable_CmsPhotoGalleries();
        //$select = $cmsPhotoGalleriesDbTable->select();
        //$photoGalleries = $cmsPhotoGalleriesDbTable->fetchAll($select);
        
        //$enabled = $cmsPhotoGalleriesDbTable->enabledPhotoGalleries($photoGalleries);
        $enabled = $cmsPhotoGalleriesDbTable->count(array(
        'status'=>Application_Model_DbTable_CmsPhotoGalleries::STATUS_ENABLED));
        //$allPhotoGalleries =$cmsPhotoGalleriesDbTable->allPhotoGalleries($photoGalleries);
        $allPhotoGalleries =$cmsPhotoGalleriesDbTable->count();
        
        
        $this->view->enabledPhotoGalleries = $enabled;
        $this->view->allPhotoGalleries = $allPhotoGalleries;
    }
}
