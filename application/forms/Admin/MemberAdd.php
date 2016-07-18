<?php
class Application_Form_Admin_MemberAdd extends Zend_Form
{
    public function init() {
        $firstName = new Zend_Form_Element_Text('first_name');
        //$firstName->addFilter(new Zend_Filter_StringTrim());
        //$firstName->addValidator(new Zend_Validate_StringLength(array('min'=>3, 'max'=>255)));
        $firstName->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min'=>3, 'max'=>255))
                ->setRequired(true);
        $this->addElement($firstName);
        
        $lastName = new Zend_Form_Element_Text('last_name');
        $lastName->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min'=>3, 'max'=>255))
                ->setRequired(true);
        $this->addElement($lastName);
        
        $workTitle = new Zend_Form_Element_Text('work_title');
        $workTitle->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min'=>3, 'max'=>255))
                ->setRequired(false);
        $this->addElement($workTitle);
        
        $email = new Zend_Form_Element_Text('email');
        $email->addFilter('StringTrim')
                ->addValidator('EmailAddress', false, array('domain'=>false))
                ->setRequired(true);
        $this->addElement($email);
        
        $resume = new Zend_Form_Element_Textarea('resume');
        $resume->addFilter('StringTrim')
               ->setRequired(true);
        $this->addElement($resume);
        
        $memberPhoto = new Zend_Form_Element_File('member_photo');
        $memberPhoto->addValidator('Count', true, 1)//true se stavlja da ako ne prodje validator prekida se izvrsavanje validacije na nivou elementa forme a ne cele forme
                ->addValidator('MimeType', true, array('image/jpeg', 'image/gif', 'image/png'))
                ->addValidator('ImageSize', false, array(
                    'minwidth' => 150,
                    'minheight' => 150,
                    'maxwidth' => 2000,
                    'maxheight' => 2000
                ))
                ->addValidator('Size', false, array(
                    'max' => '10MB'
                ))
                //->setDestination('')stavlja u destination folder koji preciziramo
                ->setValueDisabled(true)//sa ovim uvek stavja file u default direktorijum disable move file to destination when calling method getValues()
                ->setRequired(false);
                
                $this->addElement($memberPhoto);
                
        
        
    }

}

