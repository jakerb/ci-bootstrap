<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| CLASSES
| -------------------------------------------------------------------
| This file loads custom classes into the application. The reason for
| this workaround is that models created under /models are automatically
| constructed when loaded which throws errors for __construct and also
| removed the ability to have a single object per model.
*/

$config['classes_dir'] = APPPATH . 'classes';

/* Base Classes */
require $config['classes_dir'] . '/BaseClass.php';
require $config['classes_dir'] . '/BaseGroupClass.php';

/* Group Classes */


/* Single Classes */
require $config['classes_dir'] . '/UserClass.php';


/* Faker Class */
require $config['classes_dir'] . '/FakerClass.php';

/* Custom Classes */
