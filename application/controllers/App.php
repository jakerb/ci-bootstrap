<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->library(
			['ion_auth', 'form_validation']
		);
		
		$this->load->model(
			['user']
		);
		
		$this->load->helper(
			['url', 'language']
		);
	}

	public function index() {
		if(!is_user_logged_in()) {
			return redirect('/auth', 'refresh');
		}
		
		$this->load->view('app/default');
	}

	public function auth_success() {
		if(!is_user_logged_in()) {
			return redirect('/auth', 'refresh');
		}
		
		echo 'Welcome';
	}
}
