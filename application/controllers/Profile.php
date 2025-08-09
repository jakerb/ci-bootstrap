<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Profile
 *
 * Provides a simple interface for logged in users to view and edit
 * their profile information.  Users can update their first name,
 * last name and phone number.  This controller also displays
 * subscription status and exposes a link to subscribe if the user
 * does not already have an active subscription.  All forms use
 * CodeIgniter's form validation to ensure data integrity.
 */
class Profile extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation']);
        $this->load->helper(['url', 'form']);
        $this->load->library('session');
    }

    /**
     * Ensure the user is authenticated
     */
    private function require_login()
    {
        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
            exit;
        }
    }

    /**
     * Display the profile page
     */
    public function index()
    {
        $this->require_login();
        $user = $this->ion_auth->user()->row();
        // fetch active subscription if present
        $subscription = $this->db->where('user_id', $user->id)
                                 ->where('status', 'active')
                                 ->get('subscriptions')->row();
        $data = [
            'user'         => $user,
            'subscription' => $subscription,
            'message'      => $this->session->flashdata('message'),
        ];
        $this->load->view('profile/index', $data);
    }

    /**
     * Update the user's profile information
     */
    public function update()
    {
        $this->require_login();
        $user = $this->ion_auth->user()->row();
        // define validation rules
        $this->form_validation->set_rules('first_name', 'First Name', 'required|trim');
        $this->form_validation->set_rules('last_name', 'Last Name', 'required|trim');
        $this->form_validation->set_rules('phone', 'Phone', 'trim');
        if ($this->form_validation->run() === FALSE) {
            // reload form with validation errors
            return $this->index();
        }
        $data = [
            'first_name' => $this->input->post('first_name'),
            'last_name'  => $this->input->post('last_name'),
            'phone'      => $this->input->post('phone'),
        ];
        if ($this->ion_auth->update($user->id, $data)) {
            $this->session->set_flashdata('message', 'Profile updated successfully.');
        } else {
            $this->session->set_flashdata('message', 'Failed to update profile.');
        }
        redirect('profile', 'refresh');
    }
}