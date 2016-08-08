<?php
class Zend_View_Helper_SitemapPageUrl extends Zend_View_Helper_Abstract
{
    public function sitemapPageUrl($id) {
        
        //return $this->view->url(array(), 'sitemap-page-route-' . $id, true);//jedan nacin
        $sitemapPagesMap = Application_Model_DbTable_CmsSitemapPages::getSitemapPagesMap();
        if(isset($sitemapPagesMap[$id])){
           return $this->view->baseUrl($sitemapPagesMap[$id]['url']);
        }else{
            return '';
        }
    }

}
