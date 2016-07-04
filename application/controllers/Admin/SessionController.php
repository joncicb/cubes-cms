<?php
class Admin_SessionController extends Zend_Controller_Action
{   
    public function indexAction(){
       //provera da li je korisnik ulogovan
        
        if (Zend_Auth::getInstance()->hasIdentity()){
           //ulogovan je 
            //redirect na admin_dashboard kontroler i index akciju
            $redirector = $this->getHelper('Redirector');
            $redirector instanceof Zend_Controller_Action_Helper_Redirector;


            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_dashboard',
                        'action' => 'login'
                            ), 'default', true);
        }else {
           //nije ulogovan
           
           //redirect na login stranu
           $redirector = $this->getHelper('Redirector');
            $redirector instanceof Zend_Controller_Action_Helper_Redirector;


            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_session',
                        'action' => 'login'
                            ), 'default', true);
        }
        
        
    }
    
    
    
    
    
    public function loginAction(){
        
        //disableovanje layouta
        Zend_Layout::getMvcInstance()->disableLayout();
        
        $loginForm = new Application_Form_Admin_Login();
        
        
        $request = $this->getRequest();
        $request instanceof Zend_Controller_Request_Http;
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        
        $systemMessages = array(
            
            'success' => $flashMessenger->getMessages('success'),
            'errors'  => $flashMessenger->getMessages('errors')
            
        );
        
        
        if($request->isPost() && $request->getPost('task') === 'login'){
            
            
         if($loginForm->isValid($request->getPost())){//uzima podatke iz Post-a getPost je asocijativni niz vrednosti su ono sto smo uneli u formu
             $authAdapter = new Zend_Auth_Adapter_DbTable();
             $authAdapter->setTableName('cms_users')
                     ->setIdentityColumn('username')
                     ->setCredentialColumn('password')
                     ->setCredentialTreatment('MD5(?) AND status !=0');
             
             $authAdapter->setIdentity($loginForm->getValue('username'));
             $authAdapter->setCredential($loginForm->getValue('password'));
             
             $auth = Zend_Auth::getInstance();
             $result = $auth->authenticate($authAdapter);
             
             if($result->isValid()){
                 //smestanje kompletnog reda iz tabele cms_users kao identifikator da je korisnik ulogovan
                 //po defaultu se smesta samo username, a ovako smestamo acocijativni niz tj row iz tabele
                 //asocijativni niz $user ima kljuceve koji su u stvari nazivi kolone u tabeli cms_users
                 $user = $authAdapter->getResultRowObject();
                 $auth->getStorage()->write($user);
                 
                 $redirector = $this->getHelper('Redirector');
                    $redirector instanceof Zend_Controller_Action_Helper_Redirector;


                    $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_dashboard',
                                'action' => 'index'
                                    ), 'default', true);


                    $systemMessage = "OK";
             }else{
                 $systemMessages['errors'][] = "Wrong username or password";
             }
         }else{
             $systemMessages['errors'][] = 'Username and password are required';
         } 
            
   
        }
        $this->view->systemMessages = $systemMessages;
    }
    public function logoutAction(){
        
        $auth = Zend_Auth::getInstance();
        //brise indikator da je neko ulogovan
        $auth->clearIdentity();
        
        $flashMessenger = $this->getHelper('FlashMessenger');
        $flashMessenger->addMessage('You have been logged out', 'success');
        //ovde ide redirect na login stranu
        $redirector = $this->getHelper('Redirector');
        $redirector instanceof Zend_Controller_Action_Helper_Redirector;
       
        
        $redirector->setExit(true)
                ->gotoRoute(array(
                    
                        'controller' => 'admin_session',
                        'action' => 'login'
                        
                        
                        ), 'default', true);
        
        
        //redirect ako nemamo dodatne parametre
//        $redirector->setExit(true)
//                ->gotoSimple('login', 'admin_session');
//    
        
        
    }
}
