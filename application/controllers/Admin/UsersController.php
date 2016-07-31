<?php

class Admin_UsersController extends Zend_Controller_Action {

    public function indexAction() {
        $flashMessenger = $this->getHelper('FlashMessenger');

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );
        
        $this->view->users = array(); //prosledjujemo prezentacionoj logici

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
                $userId = $cmsUsersTable->insertUser($formData);

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
        if ($id == $loggedInUser['id']) {
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

    public function deleteAction() {
        $request = $this->getRequest();

        if (!$request->isPost() || $request->getPost('task') != 'delete') {

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

            $cmsUsersTable->deleteUser($id);
            
            $request instanceof Zend_Controller_Request_Http;
            
            //ispitivanje da li je request Ajax
            if($request->isXmlHttpRequest()){
                //request je Ajax
                //send response as json
                
                $responseJson=array(
                    'status'=>'ok',
                    'statusMessage'=>'User ' . $user["first_name"] . ' ' . $user["last_name"] . ' has been deleted.'
                );
                
                //send json as response
                $this->getHelper('Json')->sendJson($responseJson);
                
            }else{
                //request nije Ajax
                //send message over session
                //and do not redirect
                
            $flashMessenger->addMessage("User " . $user["first_name"] . " " . $user["last_name"] . " has been deleted.", "success");

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_users',
                        'action' => 'index'
                            ), 'default', true);
            }
            
            
        } catch (Application_Model_Exception_InvalidInput $ex) {
            if($request->isXmlHttpRequest()){
                //request is ajax
                
                $responseJson = array(
                    'status'=>'error',
                    'statusMessage'=>$ex->getMessage()
                    
                );
                //send json as response
                $this->getHelper('Json')->sendJson($responseJson);
                
            }else{
                //request is not ajax
            $flashMessenger->addMessage($ex->getMessage());

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_users',
                        'action' => 'index'
                            ), 'default', true);
            }
            
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
            
            
            $request instanceof Zend_Controller_Request_Http;
            
            //ispitivanje da li je request Ajax
            if($request->isXmlHttpRequest()){
                //request je Ajax
                //send response as json
                
                $responseJson=array(
                    'status'=>'ok',
                    'statusMessage'=>'User ' . $user["first_name"] . ' ' . $user["last_name"] . ' has been disabled.'
                );
                
                //send json as response
                $this->getHelper('Json')->sendJson($responseJson);
                
            }else{
                //request nije Ajax
                //send message over session
                //and do not redirect
                
            $flashMessenger->addMessage("User " . $user["first_name"] . " " . $user["last_name"] . " has been disabled.", "success");

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_users',
                        'action' => 'index'
                            ), 'default', true);
            }
            
            
        } catch (Application_Model_Exception_InvalidInput $ex) {
            if($request->isXmlHttpRequest()){
                //request is ajax
                
                $responseJson = array(
                    'status'=>'error',
                    'statusMessage'=>$ex->getMessage()
                    
                );
                //send json as response
                $this->getHelper('Json')->sendJson($responseJson);
                
            }else{
                //request is not ajax
            $flashMessenger->addMessage($ex->getMessage());

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_users',
                        'action' => 'index'
                            ), 'default', true);
            }
            
        }
    }

    public function enableAction() {
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
            
            $request instanceof Zend_Controller_Request_Http;
            
            //ispitivanje da li je request Ajax
            if($request->isXmlHttpRequest()){
                //request je Ajax
                //send response as json
                
                $responseJson=array(
                    'status'=>'ok',
                    'statusMessage'=>'User ' . $user["first_name"] . ' ' . $user["last_name"] . ' has been enabled.'
                );
                
                //send json as response
                $this->getHelper('Json')->sendJson($responseJson);
                
            }else{
                //request nije Ajax
                //send message over session
                //and do not redirect
                
            $flashMessenger->addMessage("User " . $user["first_name"] . " " . $user["last_name"] . " has been enabled.", "success");

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_users',
                        'action' => 'index'
                            ), 'default', true);
            }
            
            
        } catch (Application_Model_Exception_InvalidInput $ex) {
            if($request->isXmlHttpRequest()){
                //request is ajax
                
                $responseJson = array(
                    'status'=>'error',
                    'statusMessage'=>$ex->getMessage()
                    
                );
                //send json as response
                $this->getHelper('Json')->sendJson($responseJson);
                
            }else{
                //request is not ajax
            $flashMessenger->addMessage($ex->getMessage());

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_users',
                        'action' => 'index'
                            ), 'default', true);
            }
            
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
            $loggedInUser = Zend_Auth::getInstance()->getIdentity();

            if ($id == $loggedInUser['id']) {
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_profile',
                            'action' => 'changepassword'
                                ), 'default', true);
            }
            $cmsUsersTable->resetPassword($id);
            
            $request instanceof Zend_Controller_Request_Http;
            
            //ispitivanje da li je request Ajax
            if($request->isXmlHttpRequest()){
                //request je Ajax
                //send response as json
                
                $responseJson=array(
                    'status'=>'ok',
                    'statusMessage'=>'Password is successfully reseted to default value for username: ' . $user['username']
                );
                
                //send json as response
                $this->getHelper('Json')->sendJson($responseJson);
                
            }else{
                //request nije Ajax
                //send message over session
                //and do not redirect
                
            $flashMessenger->addMessage('Password is successfully reseted to default value for username: ' . $user['username'], 'success');

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_users',
                        'action' => 'index'
                            ), 'default', true);
            }
            
            
        } catch (Application_Model_Exception_InvalidInput $ex) {
            if($request->isXmlHttpRequest()){
                //request is ajax
                
                $responseJson = array(
                    'status'=>'error',
                    'statusMessage'=>$ex->getMessage()
                    
                );
                //send json as response
                $this->getHelper('Json')->sendJson($responseJson);
                
            }else{
                //request is not ajax
            $flashMessenger->addMessage($ex->getMessage());

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_users',
                        'action' => 'index'
                            ), 'default', true);
            }
            
        }

    }

    public function datatableAction() {

        $request = $this->getRequest();

        $datatableParameters = $request->getParams();

        //print_r($datatableParameters);
        //die();
        /*
          Array
          (
          [controller] => admin_users
          [action] => datatable
          [module] => default
          [draw] => 1


          [order] => Array
          (
          [0] => Array
          (
          [column] => 2
          [dir] => asc
          )

          )

          [start] => 0//page tj pocetak strane da je druga strana bila bi vrednost 5 da je str 3 vrednost bi bila 10
          [length] => 3//je limit
          [search] => Array
          (
          [value] =>
          [regex] => false
          )
          )
         */
        $cmsUsersTable = new Application_Model_DbTable_CmsUsers();

        $loggedInUser = Zend_Auth::getInstance()->getIdentity();
        $filters = array(
            'id_exclude' => $loggedInUser
        );
        $orders = array();
        $limit = 5;
        $page = 1;
        $draw = 1; //obavezan prilikom slanja

        $columns = array('status', 'username', 'first_name', 'last_name', 'email', 'actions'); //ovaj raspored mora da bude isti kao u tabeli u prezentacionoj logici
        //Process datatable parameters
        if (isset($datatableParameters['draw'])) {

            $draw = $datatableParameters['draw'];

            if (isset($datatableParameters['length'])) {

                $limit = $datatableParameters['length'];

                if ($datatableParameters['start']) {

                    $page = floor($datatableParameters['start'] / $datatableParameters['length']) + 1;
                }
            }
            
            if (
                    isset($datatableParameters['order']) && is_array($datatableParameters['order'])
            ) {
                foreach ($datatableParameters['order'] as $datatableOrder) {
                    $columnIndex = $datatableOrder['column']; //daje index iz $column niza
                    $columnDirection = strtoupper($datatableOrder['dir']);

                    if (isset($columns[$columnIndex])) {
                        $orders[$columns[$columnIndex]] = $columnDirection;
                    }
                }
            }
            if (
                    isset($datatableParameters['search']) && is_array($datatableParameters['search']) && isset($datatableParameters['search']['value'])
            ) {

                $filters['username_search'] = $datatableParameters['search']['value'];
            }
        }



        $users = $cmsUsersTable->search(array(
            'filters' => $filters,
            'orders' => $orders,
            'limit' => $limit,
            'page' => $page
        ));

        $usersFilteredCount = $cmsUsersTable->count($filters);
        $usersTotal = $cmsUsersTable->count();


        $this->view->users = $users; //prosledjivanje prezentacionoj logici
        $this->view->usersFilteredCount = $usersFilteredCount; //prosledjivanje prezentacionoj logici
        $this->view->usersTotal = $usersTotal; //prosledjivanje prezentacionoj logici
        $this->view->draw = $draw; //prosledjivanje prezentacionoj logici
        $this->view->columns = $columns;
    }
    public function dashboardAction() {
        
        $cmsUsersDbTable = new Application_Model_DbTable_CmsUsers();
        $select = $cmsUsersDbTable->select();
        $users = $cmsUsersDbTable->fetchAll($select);
        
        $enabled = $cmsUsersDbTable->enabledUsers($users);
        $allUsers =$cmsUsersDbTable->allUsers($users);
   
        $this->view->enabledUsers = $enabled;
        $this->view->allUsers = $allUsers;
    }

}
