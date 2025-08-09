<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->config->item('site_title', 'ion_auth'); ?></title>
    <!-- Tailwind CSS via CDN provides a flexible utility‑based design system -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

    <!-- Top navigation bar -->
    <nav class="bg-white shadow mb-4">
        <div class="max-w-7xl mx-auto px-4 py-2 flex justify-between items-center">
            <a href="<?php echo site_url(); ?>" class="text-xl font-bold text-gray-800">
                <?php echo $this->config->item('site_title', 'ion_auth'); ?>
            </a>
            <div class="space-x-4">
                <?php if ($this->ion_auth->logged_in()): ?>
                    <a href="<?php echo site_url('profile'); ?>" class="text-gray-600 hover:text-gray-800">Profile</a>
                    <a href="<?php echo site_url('auth/logout'); ?>" class="text-gray-600 hover:text-gray-800">Logout</a>
                <?php else: ?>
                    <a href="<?php echo site_url('auth/login'); ?>" class="text-gray-600 hover:text-gray-800">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Page container begins; closed in the footer -->
    <div class="container mx-auto px-4">