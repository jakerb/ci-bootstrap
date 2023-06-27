<?php

	class UserClass extends BaseClass {

		public function __construct($id = null, $table = 'users') {
			parent::__construct($id, $table);
		}

        public function full_name() {
            return implode(' ', array($this->first_name, $this->last_name));
        }

        public function avatar() {
            return get_user_avatar($this->email); 
        }

        public function is_admin() {
            return is_user_admin($this->id);
        }
	}