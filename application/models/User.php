<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Resident Model
 */

class User extends CI_Model {

    public function __construct() {
        $CI =& get_instance();
        $this->db = $CI->db;
    }


    public function get_user($user_id) {
        $this->db->select('id, first_name, last_name, created_on');
        $this->db->from('users');
        $this->db->where('id', $user_id);

        $query = $this->db->get();
        $result = $query->result();

        if(empty($result)) {
            return array();
        }

        $user = current($result);


        return (object) array(
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => implode(' ', array($user->first_name, $user->last_name)),
            'properties' => $this->property->get_user_properties($user->id),
            'stamp' => strtotime($user->created_on)
        ); 
    }

}