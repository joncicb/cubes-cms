<?php
class Zend_View_Helper_AskMemberUrl extends Zend_View_Helper_Abstract
{
    public function askMemberUrl($askMember) {
        
        return $this->view->url(array(
            'id' => $askMember['id'],
            'askmember_slug' => $askMember['first_name'] . '-' . $askMember['last_name']
            
        ), 'askmember-route', true);
        
    }
}