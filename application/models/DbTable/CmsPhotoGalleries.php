<?php

class Application_Model_DbTable_CmsPhotoGalleries extends Zend_Db_Table_Abstract {

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    protected $_name = 'cms_photo_galleries';

    /**
     * @param iny $id
     * return null|array Associative array with keys as cms_photoGalleries table columns or NULL if not found
     */
    public function getPhotoGalleryById($id) {


        $select = $this->select();
        $select->where('id = ?', $id);

        $row = $this->fetchRow($select); //find vraca niz objekata tj vise redova, ne samo jedan

        if ($row instanceof Zend_Db_Table_Row) {

            return $row->toArray();
        } else {
            //row is not found
            return NULL;
        }
    }

    /**
     * @param type  int $id
     * @param array $photoGallery Associative array with keys at column names and values as column new values
     */
    public function updatePhotoGallery($id, $photoGallery) {
        if (isset($photoGallery['id'])) {
            //forbid changing of user id
            unset($photoGallery['id']);
        }


        $this->update($photoGallery, 'id = ' . $id);
    }

    /**
     * 
     * @param array $photoGallery Associative array with keys at column names and values as column new values
     * @return int The ID of new photoGallery (autoincrement)
     */
    public function insertPhotoGallery($photoGallery) {
       //fetch order number of new photoGallery
        
        
        $select = $this->select();
        
        //Sort rows by order_number Descending and fetch one row from the top
        $select->order('order_number DESC');
        
        $this->fetchRow($select);
        
        $photoGalleryWithBiggestOrderNumber = $this->fetchRow($select);
        
        if($photoGalleryWithBiggestOrderNumber instanceof Zend_Db_Table_Row){
            
            $photoGallery['order_number'] = $photoGalleryWithBiggestOrderNumber['order_number'] + 1;
        }else {
            // table was empty, we are inserting first photoGallery
            $photoGallery['order_number'] = 1;
        }
        
        
        
        $id = $this->insert($photoGallery);
        
        return $id;
    }
    /**
     * 
     * @param int $id ID of photoGallery to delete
     */
    public function deletePhotoGallery($id){
        
        $photoGalleryPhotoFilePath = PUBLIC_PATH . '/uploads/photo-galleries/' . $id . '.jpg';
        //preneti u photoGalleries!!!!
        if(is_file($photoGalleryPhotoFilePath)){
            //delete photoGallery photo file
            unlink($photoGalleryPhotoFilePath);
        }
        
        //photoGallery who is going to be deleted
        $photoGallery = $this->getPhotoGalleryById($id);
        
        $cmsPhotosDbTable = new Application_Model_DbTable_CmsPhotos();
        $childPhotos = $cmsPhotosDbTable->search(array(
            'filters'=>array(
                'photo_gallery_id'=>$photoGallery['id']
                    )
        ));
        
        foreach($childPhotos as $childPhoto){
            $cmsPhotosDbTable->deletePhoto($childPhoto['id']);
        }
        
        $this->update(array(
            'order_number' => new Zend_Db_Expr('order_number -1')  
        ), 
            'order_number > ' . $photoGallery['order_number']);
        
        $this->delete('id = ' . $id);
        
        
    }
    /**
     * 
     * @param int $id ID of photoGallery to disable
     */
    public function disablePhotoGallery($id){
        $this->update(array(
            'status' =>  self::STATUS_DISABLED
        ),'id = ' . $id);
    }
    /**
     * 
     * @param int $id ID of photoGallery to enable
     */
    public function enablePhotoGallery($id){
        $this->update(array(
            'status' =>  self::STATUS_ENABLED
        ),'id = ' . $id);
    }
    public function updateOrderOfPhotoGalleries($sortedIds){
        foreach($sortedIds as $orderNumber => $id){
            $this->update(array(
            'order_number' =>  $orderNumber + 1 // +1 because order_number starts from 1, not from 0 
        ),'id = ' . $id);
        }
    }
//    public function enabledPhotoGalleries($photoGalleries) {
//       
//        $enabledPhotoGalleries = 0; 
//        foreach ($photoGalleries as $photoGallery) {
//            
//            
//            if ($photoGallery['status'] == self::STATUS_ENABLED) {
//                $enabledPhotoGalleries += 1;
//            }
//        
//        }return $enabledPhotoGalleries;
//    }

//    public function allPhotoGalleries($photoGalleries) {
//        $allPhotoGalleries =0;
//        
//        foreach ($photoGalleries as $photoGallery){
//            $allPhotoGalleries += 1;
//        }
//        
//        return $allPhotoGalleries ;
//    }
    
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
        public function search(array $parameters=array()){
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
                    case 'title':
                    case 'description':
                    case 'status':
                    case 'order_number':
                        
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
                    case 'title':
                    case 'description':
                    case 'status':
                        
                        if(is_array($value)){
                            $select->where($field . ' IN (?)', $value);
                        }else{$select->where($field . ' =?', $value);
                            
                        }
                        break;
                    case 'title_search':
                        $select->where('title LIKE ?', '%' . $value . '%' );
                         break;
                    case 'description_search':
                        $select->where('description LIKE ?', '%' . $value . '%' );
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
    public function deletePhotos($param) {
        
    }
}
