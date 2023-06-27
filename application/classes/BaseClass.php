<?php

	class BaseClass {

		public $table_name;
		public $callables = array();
		public $empty = false;

		public function __construct($id, $table_name) {
			$ci =& get_instance();
			$this->db = $ci->db;

			if($table_name) {
				$this->table_name = $table_name;
			}

			/* Create */
			if(is_array($id)) {
				$this->db->insert($this->table_name, $id);
				$insert_id = $this->db->insert_id();
				if(is_int($insert_id)) {
					$id = $insert_id;
				}
			}

			if($id) {
				$this->id = is_int($id) ? $id : intval($id);
				$this->get();
			} else {
				$this->empty = true;
			}
		}

		public function is_property($key) {
			return property_exists($this, $key);
		}

		public function exists() {
			return !$this->empty;
		}

		public function cache() {
			if(!property_exists($this, 'memcache')) {
				$this->memcache = new MemcacheClass($this->table_name);
			}

			return $this->memcache;
		}

		public function create($fields) {
			$this->db->insert($this->table_name, $fields);

			if($id = $this->db->insert_id()) {
				$this->id = $id;
				$this->get();
			}
		}

		public function delete() {

			$query = $this->db->delete($this->table_name, array(
				'id' => $this->id
			));


			return $query;

			
		}

		public function get() {
			$this->db->select('*');
			$this->db->from($this->table_name);
			$this->db->where('id', $this->id);
			$this->db->limit(1);

			$query = $this->db->get();
	        $result = $query->result();

	        if(!empty($result)) {
	        	$result = current($result);

	        	foreach($result as $key => $value) {
	        		$this->{$key} = $value;
	        		$this->callables[] = $key;
	        	}

	        }

		}

		public function __call($name, $arguments) {
			if(property_exists($this, $name)) {
				return $this->{$name};
			}
		}

		public function update($field, $value) {
			if(!property_exists($this, $field)) {
				return array('error' => 1, 'msg' => "{$field} does not exist.");
			}

			if($this->{$field} == $value) {
				return null;
			}


			$this->db->where('id', $this->id());
			$update = array();
			$update[$field] = $value;

			$update = $this->db->update($this->table_name, $update);
			$this->{$field} = $value;


			return $update;
		}


	}