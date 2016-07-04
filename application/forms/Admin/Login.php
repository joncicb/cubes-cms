<?php

class Application_Form_admin_login extends Zend_Form
{
    public function init() {
       
        //kreiranje elementa
        $username = new Zend_Form_Element_Text('username');
        $username->addFilter('StringTrim')
                ->addFilter('StringToLower')
                ->setRequired(true);//oznacava element kao obavezan

       //dodavanje elementa u formu
       $this->addElement($username);
       
       //novi element u formi
       $password = new Zend_Form_Element_Password('password');
       
       $password->setRequired(true);
       
       $this->addElement($password);
       
       
       
       
    }

}

