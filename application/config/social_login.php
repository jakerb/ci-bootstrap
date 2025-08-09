<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Social Login Configuration
| -------------------------------------------------------------------------
| This configuration file holds credentials and settings required for
| implementing social login providers.  In this sample we provide
| settings for Google OAuth2.  You should register your application
| with Google and obtain a client ID and secret.  These values can be
| provided via environment variables to avoid committing secrets to
| source control.  If no environment variable is found a sensible
| placeholder is used.
|
| google_client_id     Your Google OAuth client ID
| google_client_secret Your Google OAuth client secret
| google_redirect_path The path, relative to base_url(), that
|                      Google's OAuth server should redirect back to
|                      after authentication.  Note that this does not
|                      include the domain – base_url() is prepended
|                      automatically in the controller.
*/

$config['google_client_id']     = defined('GOOGLE_CLIENT_ID') ? constant('GOOGLE_CLIENT_ID') : '';
$config['google_client_secret'] = defined('GOOGLE_CLIENT_SECRET') ? constant('GOOGLE_CLIENT_SECRET') : '';
$config['google_redirect_path'] = 'login/google/callback';