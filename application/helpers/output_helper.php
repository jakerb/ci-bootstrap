<?php

	function output_json($array, $header = 200) {

		$ci =& get_instance();

		return $ci->output
        ->set_content_type('application/json')
        ->set_status_header($header)
        ->set_output(json_encode($array));
	}

	function output_csv($headers, $data, $title = 'csv_output', $output = true) {
		$f = fopen('php://memory', 'r+');

		fputcsv($f, $headers, ',', '"', "\\");

		foreach ($data as $item) {
	        fputcsv($f, $item, ',', '"', "\\");
	    }
	    
	    rewind($f);
	    $body = stream_get_contents($f);

		if($output) {
			header("Content-Description: File Transfer"); 
			header("Content-Disposition: attachment; filename={$title}.csv"); 
			header("Content-Type: application/csv;");
			echo $body; exit();
		}

		return $body;

	}