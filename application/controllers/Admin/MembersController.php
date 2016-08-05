<?php

class Admin_MembersController extends Zend_Controller_Action {

    public function indexAction() {
        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );
        //prikaz svih membera
        $cmsMembersDbTable = new Application_Model_DbTable_CmsMembers();

        $members = $cmsMembersDbTable->search(array(
            //'filters' => array(//filtriram tabelu po
            //'status'=>Application_Model_DbTable_CmsMembers::STATUS_DISABLED,
            //'work_title' =>  	'PHP Developer',
           // 'first_name' => array('Aleksandar', 'Aleksandra', 'Bojan')
            
            //),
            //'orders' => array(//sortiram tabelu po
            //    'order_number'=>'ASC'
            //),
            //'limit' => 4,
            //'page' => 2
        ));


        $this->view->members = $members; //prosledjivanje rezultata
        $this->view->systemMessages = $systemMessages;
    }

    public function addAction() {

        $request = $this->getRequest(); //objekat koji cuva inputdata podatke unete preko forme to je getter za post podatke
        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(//niz za poruke o uspesno ili neuspesno unetim podacima u formu
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );

        $form = new Application_Form_Admin_MemberAdd();

        //default form data
        $form->populate(array(
        ));



        if ($request->isPost() && $request->getPost('task') === 'save') {//ovo znaci ukoliko je forma pokrenuta da li je form zahtev POST i da li je yahtev pokrenut na formi, asocijativni niz ciji su kljucevi atributi iz polja forme a vrednosti unos korisnika u formu
            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {//validacija forme ukoliko nisu validni/dobri podaci iz forme bacamo exception i idemo na catch
                    throw new Application_Model_Exception_InvalidInput('Invalid form data was sent for new member');
                }

                //get form data
                $formData = $form->getValues(); //ovo treba da se upise u bazu(podaci iz forme)
                
                
                //remove key member_photo form because there is no column member_photo in cms_member
                unset($formData['member_photo']);
                //die(print_r($formData, true));
                $cmsMembersTable = new Application_Model_DbTable_CmsMembers();
                
                
                
                //insert member returns ID of the new member
                $memberId =  $cmsMembersTable->insertMember($formData);

                
                if($form->getElement('member_photo')->isUploaded()) {
                //photo is uploaded
                    $fileInfos = $form->getElement('member_photo')->getFileInfo('member_photo');
                    $fileInfo=$fileInfos['member_photo'];
                    
                    try{
                      //open uploaded photo in temporary directory
                     $memberPhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
                     //dimenzionise sliku
                     $memberPhoto->fit(150, 150);
                     
                     $memberPhoto->save(PUBLIC_PATH . '/uploads/members/' . $memberId . '.jpg');
                     
                    } catch (Exception $ex) {
                        $flashMessenger->addMessage('Member has been saved, but error occured during image processing', 'errors'); //u sesiju upisujemo poruku member has been saved
                //redirect to same or another page
                        $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
                        $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _members
                            ->gotoRoute(array(
                                'controller' => 'admin_members',
                                'action' => 'edit',
                                'id' => $memberId
                                    ), 'default', true);  
                    }
                    
//                    print_r($fileInfo);
//                    die();
                    
                    //isto kao $fileInfo=$_FILES['member_photo'];
                }
                
                
                // do actual task
                //save to database etc
                //set system message
                $flashMessenger->addMessage('Member has been saved', 'success'); //u sesiju upisujemo poruku member has been saved
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
                $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _members
                        ->gotoRoute(array(
                            'controller' => 'admin_members',
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
            throw new Zend_Controller_Router_Exception('Invalid member id: ' . $id, 404);
        }

        $cmsMembersTable = new Application_Model_DbTable_CmsMembers();

        $member = $cmsMembersTable->getMemberById($id);

        if (empty($member)) {

            throw new Zend_Controller_Router_Exception('No member is found with id: ' . $id, 404);
        }
        //$this->view->member = $member;//prosledjujemo $membera prezentacionoj logici
        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(//niz za poruke o uspesno ili neuspesno unetim podacima u formu
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );

        $form = new Application_Form_Admin_MemberAdd();

        //default form data
        $form->populate($member);
        


        if ($request->isPost() && $request->getPost('task') === 'update') {//ovo znaci ukoliko je forma pokrenuta da li je form zahtev POST i da li je yahtev pokrenut na formi, asocijativni niz ciji su kljucevi atributi iz polja forme a vrednosti unos korisnika u formu
            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {//validacija forme ukoliko nisu validni/dobri podaci iz forme bacamo exception i idemo na catch
                    throw new Application_Model_Exception_InvalidInput('Invalid form data was sent for member');
                }

                //get form data
                $formData = $form->getValues(); //ovo treba da se upise u bazu(podaci iz forme)
                //die(print_r($formData, true));
                //$cmsMembersTable = new Application_Model_DbTable_CmsMembers();
                //$cmsMembersTable->insert($formData);
                
                unset($formData['member_photo']);

                if($form->getElement('member_photo')->isUploaded()) {
                //photo is uploaded
                    $fileInfos = $form->getElement('member_photo')->getFileInfo('member_photo');
                    $fileInfo=$fileInfos['member_photo'];
                    
                    try{
                      //open uploaded photo in temporary directory
                     $memberPhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
                     //dimenzionise sliku
                     $memberPhoto->fit(150, 150);
                     
                     $memberPhoto->save(PUBLIC_PATH . '/uploads/members/' . $member['id'] . '.jpg');
                     
                    } catch (Exception $ex) {
                        
                        throw new Application_Model_Exception_InvalidInput('Error occured during image processing');
                        
                    }
                }
                //Radimo update postojeceg zapisa u tabeli
               
                $cmsMembersTable->updateMember($member['id'], $formData);

                // do actual task
                //save to database etc
                //set system message
                $flashMessenger->addMessage('Member has been updated', 'success'); //u sesiju upisujemo poruku member has been saved
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
                $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _members
                        ->gotoRoute(array(
                            'controller' => 'admin_members',
                            'action' => 'index'
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
        $this->view->member = $member;
    }
    public function deleteAction(){
        $request = $this->getRequest(); //dohvatamo request objekat
        
        if(!$request->isPost() || $request->getPost('task') != 'delete'){
            //request is not post
            //or task is not delete
            //redirect to index page
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _members
                    ->gotoRoute(array(
                        'controller' => 'admin_members',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try  {
            // read $_POST['id']
            $id = (int) $request->getPost('id'); //iscitavamo parametar id filtriramo ga da bude int

            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput('Invalid member id: ' . $id);
                
            }

            $cmsMembersTable = new Application_Model_DbTable_CmsMembers();

            $member = $cmsMembersTable->getMemberById($id);

            if (empty($member)) {
                throw new Application_Model_Exception_InvalidInput('No member is found with id: ' . $id);
            }

            $cmsMembersTable->deleteMember($id);

            $flashMessenger->addMessage('Member : ' . $member['first_name'] . ' ' . $member['last_name'] . ' has been deleted', 'success');
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _members
                    ->gotoRoute(array(
                        'controller' => 'admin_members',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _members
                    ->gotoRoute(array(
                        'controller' => 'admin_members',
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
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _members
                    ->gotoRoute(array(
                        'controller' => 'admin_members',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try  {
            // read $_POST['id']
            $id = (int) $request->getPost('id'); //iscitavamo parametar id filtriramo ga da bude int

            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput('Invalid member id: ' . $id);
                
            }

            $cmsMembersTable = new Application_Model_DbTable_CmsMembers();

            $member = $cmsMembersTable->getMemberById($id);

            if (empty($member)) {
                throw new Application_Model_Exception_InvalidInput('No member is found with id: ' . $id);
            }

            $cmsMembersTable->disableMember($id);

            $flashMessenger->addMessage('Member : ' . $member['first_name'] . ' ' . $member['last_name'] . ' has been disabled', 'success');
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _members
                    ->gotoRoute(array(
                        'controller' => 'admin_members',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _members
                    ->gotoRoute(array(
                        'controller' => 'admin_members',
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
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _members
                    ->gotoRoute(array(
                        'controller' => 'admin_members',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try  {
            // read $_POST['id']
            $id = (int) $request->getPost('id'); //iscitavamo parametar id filtriramo ga da bude int

            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput('Invalid member id: ' . $id);
                
            }

            $cmsMembersTable = new Application_Model_DbTable_CmsMembers();

            $member = $cmsMembersTable->getMemberById($id);

            if (empty($member)) {
                throw new Application_Model_Exception_InvalidInput('No member is found with id: ' . $id);
            }

            $cmsMembersTable->enableMember($id);

            $flashMessenger->addMessage('Member : ' . $member['first_name'] . ' ' . $member['last_name'] . ' has been enabled', 'success');
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _members
                    ->gotoRoute(array(
                        'controller' => 'admin_members',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _members
                    ->gotoRoute(array(
                        'controller' => 'admin_members',
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
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _members
                    ->gotoRoute(array(
                        'controller' => 'admin_members',
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
            
            $cmsMembersTable = new Application_Model_DbTable_CmsMembers();
            
            $cmsMembersTable->updateOrderOfMembers($sortedIds);
            
            $flashMessenger->addMessage('Order is successfully saved', 'success');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _members
                    ->gotoRoute(array(
                        'controller' => 'admin_members',
                        'action' => 'index'
                            ), 'default', true);
            
            
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _members
                    ->gotoRoute(array(
                        'controller' => 'admin_members',
                        'action' => 'index'
                            ), 'default', true);
        }
    }
    public function dashboardAction() {
        
        $cmsMembersDbTable = new Application_Model_DbTable_CmsMembers();
        //$select = $cmsMembersDbTable->select();
        //$members = $cmsMembersDbTable->fetchAll($select);
        
        //$enabled = $cmsMembersDbTable->enabledMembers($members);
        $enabled = $cmsMembersDbTable->count(array(
        'status'=>Application_Model_DbTable_CmsMembers::STATUS_ENABLED));
        //$allMembers =$cmsMembersDbTable->allMembers($members);
        $allMembers =$cmsMembersDbTable->count();
        
        
        $this->view->enabledMembers = $enabled;
        $this->view->allMembers = $allMembers;
    }
}
