<?php

class Application_Model_Filter_UrlSlug implements Zend_Filter_Interface
{
    public function filter($value) {
        
        $value = preg_replace('/[^\p{L}\p{N}]/u', '-', $value);//zameni sve sto nije navedeno u [^] /u je unicode \p{L} oznaka za sva latin slova \p{N}oznaka za sve brojeve cak i japanske
        
        $value = preg_replace('/(\s+)/', '-', $value);
        
        $value = preg_replace('/(\-+)/', '-', $value);
        
        $value = trim($value, '-');
        
        return $value;
    }

}

