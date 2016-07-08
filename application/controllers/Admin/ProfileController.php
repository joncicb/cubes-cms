<?php
class Admin_ProfileController extends Zend_Controller_Action{
    public function indexAction() {
       
           //ulogovan je 
            //redirect na admin_dashboard kontroler i index akciju
            $redirector = $this->getHelper('Redirector');
            $redirector instanceof Zend_Controller_Action_Helper_Redirector;


            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_profile',
                        'action' => 'edit'
                            ), 'default', true);
        
    }
    
    public function editAction() {
        
        //dobijamo red iz tabele koji smo sacuvali u sesiju
        $user = Zend_Auth::getInstance()->getIdentity();



        $request = $this->getRequest();
        $flashMessenger = $this->getHelper('FlashMessenger');

        $form = new Application_Form_Admin_ProfileEdit();

        //default form data pomocu populate popunjava podatke u formi iz baze
        $form->populate($user);

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );

        if ($request->isPost() && $request->getPost('task') === 'save') {

            try {
                 //dobijamo podatke iz forme koja je popunjena sa getPost()
                //check form is valid
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data has been send for user profile');
                }
                 //getValues dobijamo ppodatke koji su filtrirani i validirani iz forme dobijamo sredjene podatke
                //get form data
                $formData = $form->getValues();
                
                $cmsUsersTable = new Application_Model_DbTable_CmsUsers();
                
                //update user in database table cms_members
                $cmsUsersTable->updateUser($user['id'], $formData);
                
                //fetch fresh userdata
                $user = $cmsUsersTable->getUserById($user['id']);
                
                //write fresh usewr data into session
                Zend_Auth::getInstance()->getStorage()->write($user);
                
                //set system message
                $flashMessenger->addMessage('Profile has been saved', 'success');

                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_profile',
                            'action' => 'edit'
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
    }

    public function changepasswordAction() {
                
        //dobijamo red iz tabele koji smo sacuvali u sesiju
        $user = Zend_Auth::getInstance()->getIdentity();



        $request = $this->getRequest();
        $flashMessenger = $this->getHelper('FlashMessenger');

        $form = new Application_Form_Admin_ProfileChangePassword();

        //default form data pomocu populate popunjava podatke u formi iz baze
        //$form->populate();

        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );

        if ($request->isPost() && $request->getPost('task') === 'change_password') {

            try {
                 //dobijamo podatke iz forme koja je popunjena sa getPost()
                //check form is valid
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data has been send for password change');
                }
                 //getValues dobijamo ppodatke koji su filtrirani i validirani iz forme dobijamo sredjene podatke
                //get form data
                $formData = $form->getValues();
                
                $cmsUsersTable = new Application_Model_DbTable_CmsUsers();
                
                $cmsUsersTable->changeUserPassword($user['id'], $formData['new_password']);
                
                //set system message
                $flashMessenger->addMessage('Password has been changed', 'success');

                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_profile',
                            'action' => 'changepassword'
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }

        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
      
    }
}
