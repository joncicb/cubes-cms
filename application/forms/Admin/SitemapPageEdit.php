<?php

class Application_Form_Admin_SitemapPageEdit extends Zend_Form
{   
    protected $sitemapPageId;
    protected $parentId;
    public function __construct($sitemapPageId, $parentId, $options = null) {
        
        $this->sitemapPageId = $sitemapPageId;
        $this->parentId = $parentId;
        
        parent::__construct($options);
    }


        public function init(){
        //type
        //url_slug
        //short_title
        //title
        //description
        //body
        
        //Zend_Form_Element_Select
        //Zend_Form_Element_Multiselect
        //Zend_Form_Element_MultiCheckbox
        
        
        $type = new Zend_Form_Element_Select('type');
        
        $type->addMultiOption('', '-- Select Sitemap Page Type --')
                ->addMultiOptions(array(
                   'StaticPage' => 'Static Page',
                   'AboutUsPage' => 'About Us Page',
                   'ContactUsPage' => 'Contact Us Page'
                    
                    
                    
                    
                ))->setRequired(true);
        $this->addElement($type);
        
        $urlSlug = new Zend_Form_Element_Text('url_slug');
        
        $urlSlug->addFilter('StringTrim')
                ->addFilter(new Application_Model_Filter_UrlSlug())
                ->addValidator('StringLength', false, array('min'=>2, 'max'=>255))
                ->addValidator(new Zend_Validate_Db_NoRecordExists(array(
                    'table'=>'cms_sitemap_pages',
                    'field'=>'url_slug',
                    'exclude' => 'parent_id = ' . $this->parentId . ' AND id != ' . $this->sitemapPageId  //ovde mozemo da pisemo izraz tj upit za tabelu jer smo je gore dohvatili, u ovom slucaju excludujemo parentId
                )))
                ->setRequired(true);
        $this->addElement($urlSlug); 
         
        $shortTitle = new Zend_Form_Element_Text('short_title');
        
        $shortTitle->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min'=>2, 'max'=>255))
                ->setRequired(true);
        $this->addElement($shortTitle);
         
        $title = new Zend_Form_Element_Text('title');
        
        $title->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min'=>2, 'max'=>500))
                ->setRequired(true);
        $this->addElement($title);
         
        $description = new Zend_Form_Element_Textarea('description');
        
        $description->addFilter('StringTrim')
                ->setRequired(false);
        $this->addElement($description);
         
        $body = new Zend_Form_Element_Textarea('body');
        
        $body->setRequired(false);
        
        $this->addElement($body);
        
    }
}

