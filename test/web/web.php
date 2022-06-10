<?php

class Web
{
    public function __construct($db)
    {           
        $this->db = $db;
    }
    
    public function get_refer($transact)
    {
        return $this->db->get_result_fields($transact); 
    }
}