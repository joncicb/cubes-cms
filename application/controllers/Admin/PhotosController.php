<?php

class Admin_PhotosController extends Zend_Controller_Action {


    public function addAction() {

        $request = $this->getRequest(); //objekat koji cuva inputdata podatke unete preko forme to je getter za post podatke
        
        $photoGalleryId = (int) $request->getParam('photo_gallery_id'); //iscitavamo parametar id filtriramo ga da bude int

        if ($photoGalleryId <= 0) {
            //prekida se izvrsavanje programa i prikazuje se "Page not found"
            throw new Zend_Controller_Router_Exception('Invalid photo gallery id: ' . $photoGalleryId, 404);
        }

        $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();

        $photoGallery = $cmsPhotoGalleriesTable->getPhotoGalleryById($photoGalleryId);

        if (empty($photoGallery)) {

            throw new Zend_Controller_Router_Exception('No photo gallery is found with id: ' . $photoGalleryId, 404);
        }
        
        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(//niz za poruke o uspesno ili neuspesno unetim podacima u formu
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );

        $form = new Application_Form_Admin_PhotoAdd();

        //default form data
        $form->populate(array(
        ));



        if ($request->isPost() && $request->getPost('task') === 'save') {//ovo znaci ukoliko je forma pokrenuta da li je form zahtev POST i da li je yahtev pokrenut na formi, asocijativni niz ciji su kljucevi atributi iz polja forme a vrednosti unos korisnika u formu
            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {//validacija forme ukoliko nisu validni/dobri podaci iz forme bacamo exception i idemo na catch
                    throw new Application_Model_Exception_InvalidInput('Invalid form data was sent for new photo');
                }

                //get form data
                $formData = $form->getValues(); //ovo treba da se upise u bazu(podaci iz forme)
                
                
                //remove key photo_upload form because there is no column photo_upload in cms_photo
                unset($formData['photo_upload']);
                
                $formData['photo_gallery_id'] = $photoGallery['id'];
                
                
                //die(print_r($formData, true));
                $cmsPhotosTable = new Application_Model_DbTable_CmsPhotos();
                
                
                
                //insert photo returns ID of the new photo
                $photoId =  $cmsPhotosTable->insertPhoto($formData);

                
                if($form->getElement('photo_upload')->isUploaded()) {
                //photo is uploaded
                    $fileInfos = $form->getElement('photo_upload')->getFileInfo('photo_upload');
                    $fileInfo=$fileInfos['photo_upload'];
                    
                    try{
                      //open uploaded photo in temporary directory
                     $photoPhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
                     //dimenzionise sliku
                     $photoPhoto->fit(660, 495);
                     
                     $photoPhoto->save(PUBLIC_PATH . '/uploads/photo-galleries/photos/' . $photoId . '.jpg');
                     
                    } catch (Exception $ex) {
                        $flashMessenger->addMessage('Photo has been saved, but error occured during image processing', 'errors'); //u sesiju upisujemo poruku photo has been saved
                //redirect to same or another page
                        $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
                        $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photos
                            ->gotoRoute(array(
                                'controller' => 'admin_photogalleries',
                                'action' => 'edit',
                                'id' => $photoGallery['id']
                                    ), 'default', true);  
                    }
                    
//                    print_r($fileInfo);
//                    die();
                    
                    //isto kao $fileInfo=$_FILES['photo_upload'];
                }
                
                
                // do actual task
                //save to database etc
                //set system message
                $flashMessenger->addMessage('Photo has been saved', 'success'); //u sesiju upisujemo poruku photo has been saved
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
                $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photos
                        ->gotoRoute(array(
                            'controller' => 'admin_photogalleries',
                            'action' => 'edit',
                            'id'=> $photoGallery['id']
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $flashMessenger->addMessage($ex->getMessage(), 'errors'); //u sesiju upisujemo poruku photo has been saved
                        //redirect to same or another page
                        $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
                        $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photos
                            ->gotoRoute(array(
                                'controller' => 'admin_photogalleries',
                                'action' => 'edit',
                                'id' => $photoGallery['id']
                                    ), 'default', true);  
            }
        }
        
                        $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
                        $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photos
                            ->gotoRoute(array(
                                'controller' => 'admin_photogalleries',
                                'action' => 'edit',
                                'id' => $photoGallery['id']
                                    ), 'default', true);  
    }

    public function editAction() {
        $request = $this->getRequest(); //dohvatamo request objekat
        $id = (int) $request->getParam('id'); //iscitavamo parametar id filtriramo ga da bude int

        if ($id <= 0) {
            //prekida se izvrsavanje programa i prikazuje se "Page not found"
            throw new Zend_Controller_Router_Exception('Invalid photo id: ' . $id, 404);
        }

        $cmsPhotosTable = new Application_Model_DbTable_CmsPhotos();

        $photo = $cmsPhotosTable->getPhotoById($id);

        if (empty($photo)) {

            throw new Zend_Controller_Router_Exception('No photo is found with id: ' . $id, 404);
        }
        //$this->view->photo = $photo;//prosledjujemo $photoa prezentacionoj logici
        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(//niz za poruke o uspesno ili neuspesno unetim podacima u formu
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );

        $form = new Application_Form_Admin_PhotoEdit();

        //default form data
        $form->populate($photo);
        if ($request->isPost() && $request->getPost('task') === 'update') {//ovo znaci ukoliko je forma pokrenuta da li je form zahtev POST i da li je yahtev pokrenut na formi, asocijativni niz ciji su kljucevi atributi iz polja forme a vrednosti unos korisnika u formu
            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {//validacija forme ukoliko nisu validni/dobri podaci iz forme bacamo exception i idemo na catch
                    throw new Application_Model_Exception_InvalidInput('Invalid form data was sent for photo');
                }

                //get form data
                $formData = $form->getValues(); //ovo treba da se upise u bazu(podaci iz forme)
                //die(print_r($formData, true));
                
                //Radimo update postojeceg zapisa u tabeli
               
                $cmsPhotosTable->updatePhoto($photo['id'], $formData);

                // do actual task
                //save to database etc
                //set system message
                $flashMessenger->addMessage('Photo has been updated', 'success'); //u sesiju upisujemo poruku photo has been saved
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
                $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photos
                        ->gotoRoute(array(
                            'controller' => 'admin_photogalleries',
                            'action' => 'edit',
                            'id' => $photo['photo_gallery_id']
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $flashMessenger->addMessage($ex->getMessage(), 'errors'); //u sesiju upisujemo poruku photo has been saved
                        //redirect to same or another page
                        $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
                        $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photos
                            ->gotoRoute(array(
                                'controller' => 'admin_photogalleries',
                                'action' => 'edit',
                                'id' => $photo['photo_gallery_id']
                                    ), 'default', true); 
            }
        }

                        $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
                        $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photos
                            ->gotoRoute(array(
                                'controller' => 'admin_photogalleries',
                                'action' => 'edit',
                                'id' => $photo['photo_gallery_id']
                                    ), 'default', true); 
    }
    
    public function deleteAction(){
        $request = $this->getRequest(); //dohvatamo request objekat
        
        if(!$request->isPost() || $request->getPost('task') != 'delete'){
            //request is not post
            //or task is not delete
            //redirect to index page
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photos
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
                throw new Application_Model_Exception_InvalidInput('Invalid photo id: ' . $id);
                
            }

            $cmsPhotosTable = new Application_Model_DbTable_CmsPhotos();

            $photo = $cmsPhotosTable->getPhotoById($id);

            if (empty($photo)) {
                throw new Application_Model_Exception_InvalidInput('No photo is found with id: ' . $id);
            }

            $cmsPhotosTable->deletePhoto($id);

            $flashMessenger->addMessage('Photo has been deleted', 'success');
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photos
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'edit',
                        'id' => $photo['photo_gallery_id']
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
                        $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
                        $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photos
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
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photos
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
                throw new Application_Model_Exception_InvalidInput('Invalid photo id: ' . $id);
                
            }

            $cmsPhotosTable = new Application_Model_DbTable_CmsPhotos();

            $photo = $cmsPhotosTable->getPhotoById($id);

            if (empty($photo)) {
                throw new Application_Model_Exception_InvalidInput('No photo is found with id: ' . $id);
            }

            $cmsPhotosTable->disablePhoto($id);

            $flashMessenger->addMessage('Photo has been disabled', 'success');
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photos
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'edit',
                        'id' => $photo['photo_gallery_id']
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photos
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
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photos
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
                throw new Application_Model_Exception_InvalidInput('Invalid photo id: ' . $id);
                
            }

            $cmsPhotosTable = new Application_Model_DbTable_CmsPhotos();

            $photo = $cmsPhotosTable->getPhotoById($id);

            if (empty($photo)) {
                throw new Application_Model_Exception_InvalidInput('No photo is found with id: ' . $id);
            }

            $cmsPhotosTable->enablePhoto($id);

            $flashMessenger->addMessage('Photo has been enabled', 'success');
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photos
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'edit',
                        'id' => $photo['photo_gallery_id']
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photos
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'index'
                            ), 'default', true);
        } 
    }
    public function updateorderAction(){
       $request = $this->getRequest(); //dohvatamo request objekat
       
       
       $photoGalleryId = (int) $request->getParam('photo_gallery_id'); //iscitavamo parametar id filtriramo ga da bude int

        if ($photoGalleryId <= 0) {
            //prekida se izvrsavanje programa i prikazuje se "Page not found"
            throw new Zend_Controller_Router_Exception('Invalid photo gallery id: ' . $photoGalleryId, 404);
        }

        $cmsPhotoGalleriesTable = new Application_Model_DbTable_CmsPhotoGalleries();

        $photoGallery = $cmsPhotoGalleriesTable->getPhotoGalleryById($photoGalleryId);

        if (empty($photoGallery)) {

            throw new Zend_Controller_Router_Exception('No photo gallery is found with id: ' . $photoGalleryId, 404);
        }
        
        if(!$request->isPost() || $request->getPost('task') != 'saveOrder'){
            //request is not post
            //or task is not saveOrder
            //redirect to index page
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photos
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
            
            $cmsPhotosTable = new Application_Model_DbTable_CmsPhotos();
            
            $cmsPhotosTable->updateOrderOfPhotos($sortedIds);
            
            $flashMessenger->addMessage('Order is successfully saved', 'success');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photos
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'edit',
                        'id' => $photoGallery['id']
                            ), 'default', true);
            
            
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _photos
                    ->gotoRoute(array(
                        'controller' => 'admin_photogalleries',
                        'action' => 'index'
                            ), 'default', true);
        }
    }
    
}
