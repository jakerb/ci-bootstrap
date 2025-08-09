<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Subscription
 *
 * Handles the creation of Stripe checkout sessions for recurring
 * subscriptions and records the subscription in the database upon
 * completion.  This implementation uses the Stripe server side
 * API via cURL.  For a production deployment you should also
 * implement webhooks to handle events such as failed payments or
 * cancellations.  See https://stripe.com/docs/billing/subscriptions
 */
class Subscription extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(['ion_auth']);
        $this->load->helper(['url']);
        $this->load->config('stripe');
        $this->load->library('session');
        // ensure subscriptions table exists
        $this->load->dbforge();
        if (!$this->db->table_exists('subscriptions')) {
            $fields = [
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'auto_increment' => TRUE,
                ],
                'user_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                ],
                'stripe_session_id' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ],
                'status' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'default' => 'pending',
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => TRUE,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => TRUE,
                ],
            ];
            $this->dbforge->add_field($fields);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->create_table('subscriptions', TRUE);
        }
    }

    /**
     * Require that a user is logged in.  If not, redirect to login.
     */
    private function require_login()
    {
        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
            exit;
        }
    }

    /**
     * Create a Stripe checkout session for a subscription
     *
     * This method initiates a subscription checkout by sending a
     * request to Stripe's API.  On success, the user is redirected
     * to Stripe's hosted checkout page.  The created session ID is
     * stored in the database with a pending status.  When the
     * payment completes successfully the user will be redirected
     * back to the success method.
     */
    public function create()
    {
        $this->require_login();
        $secret_key = $this->config->item('stripe_secret_key');
        $price_id   = $this->config->item('stripe_price_id');
        $success_url = base_url($this->config->item('stripe_success_path'));
        $cancel_url  = base_url($this->config->item('stripe_cancel_path'));

        $user = $this->ion_auth->user()->row();
        if (!$user) {
            show_error('Unable to retrieve user details.', 500);
            return;
        }
        // Stripe expects nested parameters for line items.  Use
        // indexed keys to represent arrays in query string.
        $payload = [
            'mode'                    => 'subscription',
            'payment_method_types[0]' => 'card',
            'customer_email'          => $user->email,
            'line_items[0][price]'    => $price_id,
            'line_items[0][quantity]' => 1,
            'success_url'             => $success_url,
            'cancel_url'              => $cancel_url,
        ];
        $post_fields = http_build_query($payload);
        $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $secret_key . ':');
        $response = curl_exec($ch);
        $curl_err = curl_error($ch);
        curl_close($ch);
        if ($response === false || $curl_err) {
            show_error('Unable to connect to Stripe: ' . $curl_err, 500);
            return;
        }
        $resp = json_decode($response, true);
        if (!isset($resp['id'])) {
            $message = isset($resp['error']['message']) ? $resp['error']['message'] : 'Unknown error creating session.';
            show_error('Stripe error: ' . $message, 500);
            return;
        }
        $session_id = $resp['id'];
        // record the pending subscription
        $this->db->insert('subscriptions', [
            'user_id'           => $user->id,
            'stripe_session_id' => $session_id,
            'status'            => 'pending',
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);
        // Redirect to Stripe's hosted checkout session
        // Use the session URL returned from Stripe if available
        if (isset($resp['url'])) {
            redirect($resp['url']);
        } else {
            // fallback to older pattern using session id
            redirect('https://checkout.stripe.com/pay/' . $session_id);
        }
    }

    /**
     * Handle successful subscription checkout
     *
     * When Stripe sends the user back to this endpoint we mark the
     * subscription as active.  This simplistic implementation does
     * not verify the session or subscription via Stripe's API – in a
     * production system you should verify the checkout session or
     * subscribe via webhooks to avoid tampering.
     */
    public function success()
    {
        $this->require_login();
        $user = $this->ion_auth->user()->row();
        if ($user) {
            // mark any pending subscriptions as active
            $this->db->where('user_id', $user->id)
                     ->where('status', 'pending')
                     ->update('subscriptions', [
                        'status'     => 'active',
                        'updated_at' => date('Y-m-d H:i:s'),
                     ]);
            $this->session->set_flashdata('message', 'Subscription successful.');
        }
        redirect('profile', 'refresh');
    }

    /**
     * Handle cancelled or failed checkout
     */
    public function cancel()
    {
        $this->require_login();
        $this->session->set_flashdata('message', 'Subscription cancelled or failed.');
        redirect('profile', 'refresh');
    }
}