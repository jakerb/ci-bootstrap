<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Social_login
 *
 * Provides endpoints for authenticating users via third party
 * providers such as Google.  Upon successful authentication the
 * controller either logs the user in if an account exists or
 * registers a new account in the local database.  The new
 * account is placed into the default members group (ID 2).
 */
class Social_login extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(['ion_auth']);
        $this->load->helper(['url']);
        $this->load->model(['user']);
        // load configuration for social providers
        $this->load->config('social_login');
        // start session if not already
        $this->load->library('session');
    }

    /**
     * Redirect to Google's OAuth2 consent page
     *
     * Builds the authorisation URL using the configured client ID and
     * redirect path.  A CSRF token (state) is stored in the session
     * to protect against cross site request forgery attacks.
     */
    public function google_login()
    {
        $client_id     = $this->config->item('google_client_id');
        $redirect_uri  = base_url($this->config->item('google_redirect_path'));
        $scope         = 'openid email profile';
        // generate a random state token and store it in the session
        $state = bin2hex(random_bytes(8));
        $this->session->set_userdata('oauth_state', $state);

        $auth_url = 'https://accounts.google.com/o/oauth2/v2/auth'
            . '?response_type=code'
            . '&access_type=online'
            . '&client_id=' . urlencode($client_id)
            . '&redirect_uri=' . urlencode($redirect_uri)
            . '&scope=' . urlencode($scope)
            . '&state=' . urlencode($state);

        redirect($auth_url);
    }

    /**
     * Handle the OAuth2 callback from Google
     *
     * Exchanges the provided code for an access token, retrieves the
     * user's profile, and either logs them in or creates a new
     * account.  Any errors during the exchange or API call are
     * reported to the user.
     */
    public function google_callback()
    {
        // validate state to prevent CSRF
        $state       = $this->input->get('state');
        $saved_state = $this->session->userdata('oauth_state');
        if (!$state || !$saved_state || $state !== $saved_state) {
            show_error('Invalid state for OAuth process.', 400);
            return;
        }

        $code = $this->input->get('code');
        if (!$code) {
            // user denied permission or error occurred
            $this->session->set_flashdata('message', 'Authentication failed.');
            redirect('auth/login', 'refresh');
            return;
        }

        $client_id     = $this->config->item('google_client_id');
        $client_secret = $this->config->item('google_client_secret');
        $redirect_uri  = base_url($this->config->item('google_redirect_path'));

        // exchange authorisation code for access token
        $post_fields = http_build_query([
            'code'          => $code,
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri'  => $redirect_uri,
            'grant_type'    => 'authorization_code',
        ]);

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        $response = curl_exec($ch);
        $curl_err = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curl_err) {
            show_error('Unable to connect to Google OAuth server: ' . $curl_err, 500);
            return;
        }

        $token_data = json_decode($response, true);
        if (!isset($token_data['access_token'])) {
            show_error('Invalid response from Google OAuth server.', 500);
            return;
        }
        $access_token = $token_data['access_token'];

        // fetch the user's profile
        $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $access_token]);
        $user_response = curl_exec($ch);
        $curl_err      = curl_error($ch);
        curl_close($ch);
        if ($user_response === false || $curl_err) {
            show_error('Unable to retrieve user information: ' . $curl_err, 500);
            return;
        }

        $user_data = json_decode($user_response, true);
        $email     = isset($user_data['email']) ? $user_data['email'] : null;
        $first     = isset($user_data['given_name']) ? $user_data['given_name'] : '';
        $last      = isset($user_data['family_name']) ? $user_data['family_name'] : '';

        if (!$email) {
            show_error('Email address not returned by Google.', 500);
            return;
        }

        // determine if the user already exists in the local database
        if ($this->ion_auth->email_check($email)) {
            // existing user; fetch record and populate session
            $user = $this->ion_auth->where('email', $email)->users()->row();
            if ($user) {
                // update last login and clear login attempts for good measure
                $this->ion_auth->update_last_login($user->id);
                $this->ion_auth->clear_login_attempts($email);
                $this->ion_auth->set_session($user);
            }
        } else {
            // new user; create account with generated username and random password
            $base_username = explode('@', $email)[0];
            $unique_username = $base_username;
            $counter = 0;
            // ensure username uniqueness
            while ($this->ion_auth->username_check($unique_username)) {
                $counter++;
                $unique_username = $base_username . $counter;
            }
            // random 8 character alphanumeric password
            $password = bin2hex(random_bytes(4));
            $additional_data = [
                'first_name' => $first,
                'last_name'  => $last,
            ];
            // place new users into group 2 (members)
            $group = [2];
            $uid = $this->ion_auth->register($unique_username, $password, $email, $additional_data, $group);
            if ($uid) {
                $user = $this->ion_auth->user($uid)->row();
                $this->ion_auth->set_session($user);
            } else {
                show_error('Failed to create a local user account.', 500);
                return;
            }
        }
        // redirect to the configured success URL
        $success_path = $this->config->item('auth_success_redirect', 'app');
        redirect($success_path);
    }
}