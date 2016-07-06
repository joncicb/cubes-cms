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

        // $select je objekat klase Zend_Db_Select
        $select = $cmsMembersDbTable->select();

        $select->order('order_number');

        //debug za db select - vraca se sql upit
        //die($select->assemble());

        $members = $cmsMembersDbTable->fetchAll($select);

        $this->view->members = $members; //prosledjivanje rezultata
        $this->view->systemMessages = $systemMessages;
    }

    public function addAction() {

        $request = $this->getRequest();//objekat koji cuva inputdata podatke unete preko forme to je getter za post podatke
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
                $formData = $form->getValues();//ovo treba da se upise u bazu(podaci iz forme)
                //die(print_r($formData, true));
                $cmsMembersTable = new Application_Model_DbTable_CmsMembers();
                $cmsMembersTable->insert($formData);
                
                
                
                
                
                
                
                // do actual task
                //save to database etc
                //set system message
                $flashMessenger->addMessage('Member has been saved', 'success');//u sesiju upisujemo poruku member has been saved

                
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');//redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
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
         $request = $this->getRequest();//dohvatamo request objekat
         $id = (int) $request->getParam('id');//iscitavamo parametar id filtriramo ga da bude int
         
         if($id<=0){
             //prekida se izvrsavanje programa i prikazuje se "Page not found"
             throw new Zend_Controller_Router_Exception('Invalid member id: ' . $id , 404);
         }
         
         $cmsMembersTable = new Application_Model_DbTable_CmsMembers();
         
         $member = $cmsMembersTable->getMemberById($id);
         
         if(empty($member)){
             
             throw new Zend_Controller_Router_Exception('No member is found with id: ' . $id , 404);
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
                $formData = $form->getValues();//ovo treba da se upise u bazu(podaci iz forme)
                //die(print_r($formData, true));
                //$cmsMembersTable = new Application_Model_DbTable_CmsMembers();
                //$cmsMembersTable->insert($formData);
                
                //Radimo update postojeceg zapisa u tabeli
                
                $cmsMembersTable->update($formData, 'id = ' . $member['id']);
          
                // do actual task
                //save to database etc
                //set system message
                $flashMessenger->addMessage('Member has been updated', 'success');//u sesiju upisujemo poruku member has been saved

                
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');//redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
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
    
    
    
}
