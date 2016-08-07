<?php
class Application_Model_DbTable_CmsSitemapPages extends Zend_Db_Table_Abstract
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    
    protected $_name = 'cms_sitemap_pages';
 
    /**
     * @param iny $id
     * return null|array Associative array with keys as cms_sitemap table columns or NULL if not found
     */    
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
        //1. pronaci sve child elemente u tree strukturi
        //2. pronaci sve parent id u odnosu child-parent
        //3. pronaci i ispitati sve pronadjene child vrednosti
        //4. ukoliko postoje obrisati ih
        //5.pobrinuti se za order_number zbog tabele
        
        $children= $this->search(array(
           'filters' => array(//filtriram tabelu po
           'parent_id'=>$id
            //'status'=>Application_Model_DbTable_CmsClients::STATUS_ENABLED,
            //'description_search' => 'farm'
            
            ), 
        ));
        if(count($children)!=0){
            foreach ($children as $key => $value) {
                $this->deleteSitemapPage($value['id']);
            }
        }
        
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
    /**
         * Array $parameters is keeping search parameters.
         * Array $parameters must be in following format:
         *      array(
         *       'filters'=>array1(
         *          'status'=>1,
         *          'id' =>array(3,8,11)
         *              ) 
         *       'orders'=>array(
         *          'username'=>ASC,//key is column, if value is ASC then order by asc
         *          'first_name' =>DESC,//key is column, if value is DESC then order by desc 
         *          ),
         *        'limit'=>50, //limit result set to 50 rows
         *        'page' =>3 //start from page 3. If no limit is set, page is ignored            
         *      )
         * @param array $parameters Asociative array with keys filters, orders, limit and page
         */        
    public function search($parameters=array()){
            $select = $this->select();
            
            if(isset($parameters['filters'])){
                $filters = $parameters['filters'];
                $this->processFilters($filters, $select);
                
                
            }
            if(isset($parameters['orders'])){
                $orders = $parameters['orders'];
                foreach ($orders as $field => $orderDirection){
                    switch($field){
                    case 'id':
                    case 'short_title':    
                    case 'url_slug':
                    case 'title':
                    case 'parent_id':
                    case 'type':
                    case 'order_number':
                    case 'status':
                        if($orderDirection === 'DESC'){
                            $select->order($field . ' DESC');
                        }else{
                            $select->order($field);
                        }
                        break;
                    }
                }
            }
            if(isset($parameters['limit'])){
                if(isset($parameters['page'])){
                    //page is set do limit by page
                    $select->limitPage($parameters['page'], $parameters['limit']);
                }else{
                    //page is not set, just do regular limit
                   $select->limit($parameters['limit']); 
                }
            }
            //debug da vidimo koji se querie izvrsava
            //die($select->assemble());
            
            return $this->fetchAll($select)->toArray();
        }    
        /**
         * 
         * @param array $filters See function search $parameters['filters']
         * return int Count of rows that match $filters
         */
    public function count (array $filters = array()) {
        $select = $this->select();
        
        $this->processFilters($filters, $select);
        
        $select->reset('columns');
        
        $select->from($this->_name ,'COUNT(*) as total');
        
        $row = $this->fetchRow($select);
        
        return $row['total'];
    }
    /**
     * Fill $select object with WHERE conditions
     * @param array $filters
     * @param Zend_Db_Select $select
     */
    protected function processFilters(array $filters, Zend_Db_Select $select) {
        
        //$select object will be modified outside this function
        //object are always passed by reference
        
        foreach ($filters as $field => $value){
                   switch ($field){
                    case 'id':
                    case 'short_title':    
                    case 'url_slug':
                    case 'title':
                    case 'parent_id':
                    case 'type':
                    case 'order_number':
                    case 'status':
                        
                        if(is_array($value)){
                            $select->where($field . ' IN (?)', $value);
                        }else{$select->where($field . ' =?', $value);
                            
                        }
                        break;
                    
                    case 'short_title_search':
                        $select->where('short_title LIKE ?', '%' . $value . '%' );
                         break;
                    case 'title_search':
                        $select->where('title LIKE ?', '%' . $value . '%' );
                         break;
                    case 'description_search':
                        $select->where('description LIKE ?', '%' . $value . '%' );
                         break;
                    case 'body_search':
                        $select->where('body LIKE ?', '%' . $value . '%' );
                         break;
                    case 'id_exclude':
                        if(is_array($value)){
                            $select->where('id NOT IN (?)', $value);
                        }else{
                            $select->where('id !=?', $value);
                        }
                        
                        break;
                       
                } 
                }
    }
    /**
     * 
     * @param int $id the id of sitemap page
     * @return array Sitemap page rows in path
     */
    public function getSitemapPageBredcrumbs($id) {
        
        $sitemapPagesBreadcrumbs = array();
        
        
        
        while ($id > 0){
            
            $sitemapPageInPath = $this->getSitemapPageById($id);
            
            if($sitemapPageInPath){
                
                $id = $sitemapPageInPath['parent_id'];
                
                //add current page at the beggining of breadcrumbs array
                array_unshift($sitemapPagesBreadcrumbs, $sitemapPageInPath);
                
            }else{
                
                $parentId = 0;
            }  
        }
        
        return $sitemapPagesBreadcrumbs;    
        
    }
}