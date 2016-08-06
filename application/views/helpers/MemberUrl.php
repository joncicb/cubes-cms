<?php
class Zend_View_Helper_MemberUrl extends Zend_View_Helper_Abstract
{
    public function memberUrl($member) {
        
        return $this->view->url(array(
            'id' => $member['id'],
            'member_slug' => $member['first_name'] . '-' . $member['last_name']
            
        ), 'member-route', true);
        
    }
    public function askMemberUrl($member) {
        
        return $this->view->url(array(
            'id' => $member['id'],
            'askmember_slug' => $member['first_name'] . '-' . $member['last_name']
            
        ), 'askmember-route', true);
        
    }
}