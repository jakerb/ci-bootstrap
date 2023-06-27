<?php

	class FakerClass {
        public $label = '';
        
		public function __construct($label = 'Empty') {
            $this->label = $label;
		}

        public function id() {
            return null;
        }

        public function label() {
            return $this->label;
        }

		public function __call($name, $arguments) {
			if(property_exists($this, $name)) {
				return $this->{$name};
			}

            return null;
		}

	}