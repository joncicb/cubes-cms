<?php

class Application_Model_DbTable_CmsClients extends Zend_Db_Table_Abstract {

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    protected $_name = 'cms_clients';

    /**
     * @param iny $id
     * return null|array Associative array with keys as cms_clients table columns or NULL if not found
     */
    public function getClientById($id) {

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
     * @param array $client Associative array with keys at column names and values as column new values
     */
    public function updateClient($id, $client) {
        if (isset($client['id'])) {
            //forbid changing of user id
            unset($client['id']);
        }
        
        $this->update($client, 'id = ' . $id);
    }
    /**
     * 
     * @param array $client Associative array with keys at column names and values as column new values
     * @return int The ID of new client (autoincrement)
     */
    public function insertClient($client) {
       //fetch order number of new client
        $select = $this->select();
        
        //Sort rows by order_number Descending and fetch one row from the top
        $select->order('order_number DESC');
        
        $this->fetchRow($select);
        
        $clientWithBiggestOrderNumber = $this->fetchRow($select);
        
        if($clientWithBiggestOrderNumber instanceof Zend_Db_Table_Row){
            
            $client['order_number'] = $clientWithBiggestOrderNumber['order_number'] + 1;
        }else {
            // table was empty, we are inserting first client
            $client['order_number'] = 1;
        }

        $id = $this->insert($client);
        
        return $id;
    }
    /**
     * 
     * @param int $id ID of client to delete
     */
    public function deleteClient($id){
        
        //client who is going to be deleted
        $client = $this->getClientById($id);
        
        $this->update(array(
            'order_number' => new Zend_Db_Expr('order_number -1')  
        ), 
            'order_number > ' . $client['order_number']);
        
        $this->delete('id = ' . $id);
    }
    /**
     * 
     * @param int $id ID of client to disable
     */
    public function disableClient($id){
        $this->update(array(
            'status' =>  self::STATUS_DISABLED
        ),'id = ' . $id);
    }
    /**
     * 
     * @param int $id ID of client to enable
     */
    public function enableClient($id){
        $this->update(array(
            'status' =>  self::STATUS_ENABLED
        ),'id = ' . $id);
    }
    public function updateOrderOfClients($sortedIds){
        foreach($sortedIds as $orderNumber => $id){
            $this->update(array(
            'order_number' =>  $orderNumber + 1 
        ),'id = ' . $id);
        }
    }
   
}
