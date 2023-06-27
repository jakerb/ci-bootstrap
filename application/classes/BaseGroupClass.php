<?php

	class BaseGroupClass extends BaseClass {

		public $order = 'ASC';
		public $order_by = 'id';
		public $filter = array();
		public $page = 1;
		public $limit = 30;
		public $single_class_name = null;
		public $include_empty_child = true;
		public $get_all_query = 'id';

		public function __construct($table, $single_class_name) {
			parent::__construct(null, $table);
			$this->single_class_name = $single_class_name;
		}

		public function set_order_by($order_by) {
			$this->order_by = $order_by;
		}

		public function set_order($order) {
			$this->order = $order;
		}

		public function exclude_empty_child() {
			$this->include_empty_child = false;
		}

		public function include_empty_child() {
			$this->include_empty_child = true;
		}

		public function get($page = 1, $limit = 30) {

			$this->page = $page;
			$this->limit = $limit;
			$this->total_pages = round($this->get_count() / $this->limit);

			$items =  array_map(function($item) {
				$classname = $this->single_class_name;
				return new $classname($item->id);

			}, $this->get_all($page, $limit));

			if($this->include_empty_child) {
				$items[] = new FakerClass();
			}

			$i = 0;
			foreach($items as &$item) {
				$item->__is_last = $i == count($items)-1;
				$i++;
			}

			return $items;

		}

		public function get_count() {
			$this->db->select('id');
			$this->db->from($this->table_name);

			return $this->db->get()->num_rows();
		}

		public function get_all($page = 1, $count = 30) {
			
			$limit = max(0, ($page -1) * $count);

			$this->db->select($this->get_all_query);
			$this->db->from($this->table_name);

			if(!empty($this->filter)) {

				if(!isset($this->filter['column'])) {
					$filter = array();
					$filter_in = array();
					foreach($this->filter as $f) {

						if(is_array($f['value'])) {
							if(!empty($f['value'])) {
								$filter_in = array('column' => $f['column'], 'value' => $f['value']);
							}
						} else {
							$filter[$f['column']] = $f['value'];
						}

						
					}

					$this->db->where($filter);

					if(!empty($filter_in)) {
						$this->db->where_in($filter_in['column'], $filter_in['value']);
					}


				} else {

					$this->db->where($this->filter['column'], $this->filter['value']);

				}
			}

			$this->db->order_by($this->order_by, $this->order);
			$this->db->limit($count, $limit);

			$query = $this->db->get();
			$result = $query->result();

			return $result;
		}

	}