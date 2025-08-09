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
        // load the Stripe configuration so we can verify subscriptions
        $this->load->config('stripe');
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
        // If a subscription exists, verify with Stripe that it is still active
        if ($subscription) {
            $secret_key = $this->config->item('stripe_secret_key');
            $session_id = $subscription->stripe_session_id;
            // call Stripe checkout session to verify status
            $ch = curl_init('https://api.stripe.com/v1/checkout/sessions/' . $session_id);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $secret_key . ':');
            $resp_json = curl_exec($ch);
            $curl_err = curl_error($ch);
            curl_close($ch);
            $resp = $resp_json ? json_decode($resp_json, true) : null;
            $is_active = false;
            if (!$resp || $curl_err) {
                // If we can't verify via Stripe, assume inactive
                $is_active = false;
            } else {
                // Stripe Checkout session status can be 'complete' when paid
                $status  = isset($resp['status']) ? $resp['status'] : null;
                $paystat = isset($resp['payment_status']) ? $resp['payment_status'] : null;
                $is_active = ($status === 'complete' && $paystat === 'paid');
            }
            if (!$is_active) {
                // mark subscription as cancelled locally and clear variable
                $this->db->where('id', $subscription->id)
                         ->update('subscriptions', [
                             'status'     => 'cancelled',
                             'updated_at' => date('Y-m-d H:i:s'),
                         ]);
                $subscription = null;
            }
        }
        // define available subscription tiers
        $tiers = [
            'basic' => [
                'label'       => 'Basic',
                'description' => 'Basic plan',
                'url'         => site_url('subscription/create?plan=basic'),
            ],
            'standard' => [
                'label'       => 'Standard',
                'description' => 'Standard plan',
                'url'         => site_url('subscription/create?plan=standard'),
            ],
            'pro' => [
                'label'       => 'Pro',
                'description' => 'Pro plan',
                'url'         => site_url('subscription/create?plan=pro'),
            ],
        ];
        $data = [
            'user'         => $user,
            'subscription' => $subscription,
            'message'      => $this->session->flashdata('message'),
            'tiers'        => $tiers,
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