<?php

	function shorten_currency($number) {
	    $suffix = ["", "K", "M", "B"];
	    $precision = 1;
	    for($i = 0; $i < count($suffix); $i++){
	        $divide = $number / pow(1000, $i);
	        if($divide < 1000){
	            return round($divide, $precision).$suffix[$i];
	            break;
	        }
	    }
	}