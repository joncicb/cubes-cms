<?php

class Application_Model_DbTable_CmsServices extends Zend_Db_Table_Abstract {

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    protected $_name = 'cms_services';

    /**
     * @param iny $id
     * return null|array Associative array with keys as cms_services table columns or NULL if not found
     */
    public function getServiceById($id) {

        $select = $this->select();
        $select->where('id = ?', $id);

        $row = $this->fetchRow($select); 

        if ($row instanceof Zend_Db_Table_Row) {

            return $row->toArray();
        } else {
            //row is not found
            return NULL;
        }
    }

    /**
     * @param type  int $id
     * @param array $service Associative array with keys at column names and values as column new values
     */
    public function updateService($id, $service) {
        if (isset($service['id'])) {
            //forbid changing of service id
            unset($service['id']);
        }


        $this->update($service, 'id = ' . $id);
    }

    /**
     * 
     * @param array $service Associative array with keys at column names and values as column new values
     * @return int The ID of new service (autoincrement)
     */
    public function insertService($service) {
       //fetch order number of new service        
        
        $id = $this->insert($service);
        
        return $id;
    }

}
