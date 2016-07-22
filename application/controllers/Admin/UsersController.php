<?php

class Admin_UsersController extends Zend_Controller_Action{
    public function indexAction() {
        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );
        $cmsUsersDbTable = new Application_Model_DbTable_CmsUsers();
        
        $users = $cmsUsersDbTable->fetchAll()->toArray();
        
        $this->view->users = $users;//prosledjujemo prezentacionoj logici
       
        $this->view->systemMessages = $systemMessages;
    }
    
    public function addAction() {
        $request = $this->getRequest(); //objekat koji cuva inputdata podatke unete preko forme to je getter za post podatke
        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(//niz za poruke o uspesno ili neuspesno unetim podacima u formu
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );

        $form = new Application_Form_Admin_UserAdd();

        //default form data
        $form->populate(array(
        ));



        if ($request->isPost() && $request->getPost('task') === 'save') {//ovo znaci ukoliko je forma pokrenuta da li je form zahtev POST i da li je yahtev pokrenut na formi, asocijativni niz ciji su kljucevi atributi iz polja forme a vrednosti unos korisnika u formu
            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {//validacija forme ukoliko nisu validni/dobri podaci iz forme bacamo exception i idemo na catch
                    throw new Application_Model_Exception_InvalidInput('Invalid form data was sent for new user');
                }

                //get form data
                $formData = $form->getValues(); //ovo treba da se upise u bazu(podaci iz forme)

                $cmsUsersTable = new Application_Model_DbTable_CmsUsers();

                //insert user returns ID of the new user
                $userId =  $cmsUsersTable->insertUser($formData);

                // do actual task
                //save to database etc
                //set system message
                $flashMessenger->addMessage('User has been saved', 'success'); //u sesiju upisujemo poruku user has been saved
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
                $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _users
                        ->gotoRoute(array(
                            'controller' => 'admin_users',
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
            throw new Zend_Controller_Router_Exception('Invalid user id: ' . $id, 404);
        }
        
        $loggedInUser = Zend_Auth::getInstance()->getIdentity();
        if($id == $loggedInUser['id']){
            //redirect user to edit profile page
             $redirector = $this->getHelper('Redirector'); 
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_profile',
                            'action' => 'edit'
                                ), 'default', true);
        }
        
        $cmsUsersTable = new Application_Model_DbTable_CmsUsers();

        $user = $cmsUsersTable->getUserById($id);

        if (empty($user)) {

            throw new Zend_Controller_Router_Exception('No user is found with id: ' . $id, 404);
        }
        //$this->view->user = $user;//prosledjujemo $usera prezentacionoj logici
        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(//niz za poruke o uspesno ili neuspesno unetim podacima u formu
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );

        $form = new Application_Form_Admin_UserEdit($user['id']);

        //default form data
        $form->populate($user);
        


        if ($request->isPost() && $request->getPost('task') === 'update') {//ovo znaci ukoliko je forma pokrenuta da li je form zahtev POST i da li je yahtev pokrenut na formi, asocijativni niz ciji su kljucevi atributi iz polja forme a vrednosti unos korisnika u formu
            try {

                //check form is valid
                if (!$form->isValid($request->getPost())) {//validacija forme ukoliko nisu validni/dobri podaci iz forme bacamo exception i idemo na catch
                    throw new Application_Model_Exception_InvalidInput('Invalid form data was sent for user');
                }

                //get form data
                $formData = $form->getValues(); //ovo treba da se upise u bazu(podaci iz forme)
                //die(print_r($formData, true));
                //$cmsUsersTable = new Application_Model_DbTable_CmsUsers();
                //$cmsUsersTable->insert($formData);

                //Radimo update postojeceg zapisa u tabeli
               
                $cmsUsersTable->updateUser($user['id'], $formData);

                // do actual task
                //save to database etc
                //set system message
                $flashMessenger->addMessage('User has been updated', 'success'); //u sesiju upisujemo poruku user has been saved
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
                $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _users
                        ->gotoRoute(array(
                            'controller' => 'admin_users',
                            'action' => 'index'
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
        $this->view->user = $user;
    }
    
    public function deleteAction(){
        $request = $this->getRequest(); 
        
        if(!$request->isPost() || $request->getPost('task') != 'delete'){
            
            $redirector = $this->getHelper('Redirector'); 
            $redirector->setExit(true)
                       ->gotoRoute(array(
                        'controller' => 'admin_users',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try  {
            
            $id = (int) $request->getPost('id'); 

            if ($id <= 0) {
                
                throw new Application_Model_Exception_InvalidInput('Invalid user id: ' . $id);
                
            }

            $cmsUsersTable = new Application_Model_DbTable_CmsUsers();

            $user = $cmsUsersTable->getUserById($id);

            if (empty($user)) {
                
                throw new Application_Model_Exception_InvalidInput('No user is found with id: ' . $id);
            }

            $cmsUsersTable->deleteUser($id);

            $flashMessenger->addMessage('User : ' . $user['first_name'] . ' ' . $user['last_name'] . ' has been deleted', 'success');
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                       ->gotoRoute(array(
                        'controller' => 'admin_users',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); 
            $redirector->setExit(true)
                       ->gotoRoute(array(
                        'controller' => 'admin_users',
                        'action' => 'index'
                            ), 'default', true);
        } 
    }
    public function disableAction() {

        $request = $this->getRequest();

        if (!$request->isPost() || $request->getPost('task') != 'disable') {

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                       ->gotoRoute(array(
                        'controller' => 'admin_users',
                        'action' => 'index'
                            ), 'default', true);
        }

        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try {
            $id = (int) $request->getPost("id");

            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput("Invalid user id: " . $id);
            }

            $cmsUsersTable = new Application_Model_DbTable_CmsUsers;

            $user = $cmsUsersTable->getUserById($id);

            if (empty($user)) {

                throw new Application_Model_Exception_InvalidInput("No user is found with id: " . $id);
            }

            $cmsUsersTable->disableUser($id);
            $flashMessenger->addMessage("User " . $user["first_name"] . " " . $user["last_name"] . " has been disabled.", "success");

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                       ->gotoRoute(array(
                        'controller' => 'admin_users',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {

            $flashMessenger->addMessage($ex->getMessage());

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                       ->gotoRoute(array(
                        'controller' => 'admin_users',
                        'action' => 'index'
                            ), 'default', true);
        }
    }

    public function enableAction()  {
        $request = $this->getRequest();

        if (!$request->isPost() || $request->getPost('task') != 'enable') {

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                       ->gotoRoute(array(
                        'controller' => 'admin_users',
                        'action' => 'index'
                            ), 'default', true);
        }

        $flashMessenger = $this->getHelper('FlashMessenger');


        try {


            $id = (int) $request->getPost("id");

            if ($id <= 0) {

                throw new Application_Model_Exception_InvalidInput("Invalid user id: " . $id);
            }

            $cmsUsersTable = new Application_Model_DbTable_CmsUsers;

            $user = $cmsUsersTable->getUserById($id);

            if (empty($user)) {

                throw new Application_Model_Exception_InvalidInput("No user is found with id: " . $id);
            }

            $cmsUsersTable->enableUser($id);
            $flashMessenger->addMessage("User " . $user["first_name"] . " " . $user["last_name"] . " has been enabled.", "success");

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                       ->gotoRoute(array(
                        'controller' => 'admin_users',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {

            $flashMessenger->addMessage($ex->getMessage());

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                       ->gotoRoute(array(
                        'controller' => 'admin_users',
                        'action' => 'index'
                            ), 'default', true);
        }
    }

    public function resetpasswordAction() {

        $request = $this->getRequest();
        if ($request->isPost() && $request->getPost('task') != 'reset') {

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                       ->gotoRoute(array(
                        'controller' => 'admin_users',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        try {

            $id = (int) $request->getPost('id');

            if ($id <= 0) {

                throw new Application_Model_Exception_InvalidInput('Invalid user id: ' . $id);
            }

            $cmsUsersTable = new Application_Model_DbTable_CmsUsers();

            $user = $cmsUsersTable->getUserById($id);

            if (empty($user)) {

                throw new Application_Model_Exception_InvalidInput('No user is found with id: ' . $id);
            }

            $cmsUsersTable->resetPassword($id);

            $flashMessenger->addMessage('Password is successfully reseted to default value for user: ' . $user['username'], 'success');

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_users',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            
            $flashMessenger->addMessage($ex->getMessage(), 'errors');

            $redirector = $this->getHelper('Redirector');
            
            $redirector->setExit(true)
                       ->gotoRoute(array(
                        'controller' => 'admin_users',
                        'action' => 'index'
                            ), 'default', true);
        }
    }

}


