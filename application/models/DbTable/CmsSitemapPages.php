<?php
class Application_Model_DbTable_CmsSitemapPages extends Zend_Db_Table_Abstract
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    
    protected $_name = 'cms_sitemap_pages';
    
    public function getSitemapPageById ($id) {
        
        $select = $this->select();
        $select->where('id = ?', $id);
        
        $row = $this->fetchRow($select);
        
        if ($row instanceof Zend_Db_Table_Row) {
            
            return $row->toArray();
        } else {
            // row is not found
            return null;
        }
    }
    
    /**
     * @param int $id
     * @param array $sitemapPage Associative array with keys as column names and values as column new values
     */
    public function updateSitemapPage ($id, $sitemapPage) {
        
        if (isset($sitemapPage['id'])) {
            // Forbid changing of user id
            unset($sitemapPage['id']);
        }
        
        $this->update($sitemapPage, 'id = ' . $id);
    }
    
    /**
     * @param array $sitemapPage Associative array with keys as column names and values as column new values
     * @return int The ID of new sitemapPage (autoincrement)
     */
    public function insertSitemapPage ($sitemapPage) {
        // fetch order number for new sitemapPage
        
        $select = $this->select();
        
        // sort rows by order_number DESCENDING and fetch one row from the top
        // with biggest order_number
        $select->where('parent_id = ?', $sitemapPage['parent_id'])
                ->order('order_number DESC');
        
        $sitemapPageWithBiggerstOrderNumber = $this->fetchRow($select);
        
        if ($sitemapPageWithBiggerstOrderNumber instanceof Zend_Db_table_Row) {
            
            $sitemapPage['order_number'] = $sitemapPageWithBiggerstOrderNumber['order_number'] + 1;
            
        } else {
            // table was empty, we are inserting first sitemapPage
            $sitemapPage['order_number'] = 1;
        }
        
        $id = $this->insert($sitemapPage);
        
        return $id;
    }
    
    /**
     * @param int $id ID of sitemapPage to delete
     */
    public function deleteSitemapPage ($id) {
        
        // sitemapPage who is going to be deleted
        $sitemapPage = $this->getSitemapPageById($id);
        
        $this->update(array(
           'order_number' => new Zend_Db_Expr('order_number - 1') 
        ),
        'order_number > ' . $sitemapPage['order_number'] . ' AND parent_id = ' . $sitemapPage['parent_id']);
        
        $this->delete('id = ' . $id);
    }
    
    /**
     * @param int $id ID of sitemapPage to disable
     */
    public function disableSitemapPage ($id) {
        
        $this->update(array(
            'status' => self::STATUS_DISABLED
        ), 'id = ' . $id);
    }
    
    /**
     * @param int $id ID of sitemapPage to enable
     */
    public function enableSitemapPage ($id) {
        
        $this->update(array(
            'status' => self::STATUS_ENABLED
        ), 'id = ' . $id);
    }
    
    public function updateOrderOfSitemapPages ($sortedIds) {
        foreach ($sortedIds as $orderNumber => $id) {
            
            $this->update(array(
            'order_number' => $orderNumber + 1 // +1 because order_number starts from 1, not from 0
        ), 'id = ' . $id);
            
        }
    }
}