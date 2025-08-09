<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- Profile view -->

<?php $this->load->view('partials/header'); ?>

<div class="max-w-2xl mx-auto bg-white p-6 rounded shadow">
    <?php if (!empty($message)): ?>
        <div class="mb-4 p-3 bg-green-100 border border-green-300 text-green-800 rounded">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    <h2 class="text-2xl font-bold mb-4">Your Profile</h2>
    <form method="post" action="<?php echo site_url('profile/update'); ?>" class="space-y-4">
        <div>
            <label class="block mb-1 font-medium" for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" value="<?php echo set_value('first_name', $user->first_name); ?>" class="w-full border-gray-300 rounded p-2" required>
            <?php echo form_error('first_name', '<div class="text-red-500 text-sm">', '</div>'); ?>
        </div>
        <div>
            <label class="block mb-1 font-medium" for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" value="<?php echo set_value('last_name', $user->last_name); ?>" class="w-full border-gray-300 rounded p-2" required>
            <?php echo form_error('last_name', '<div class="text-red-500 text-sm">', '</div>'); ?>
        </div>
        <div>
            <label class="block mb-1 font-medium" for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo $user->email; ?>" class="w-full border-gray-300 rounded p-2 bg-gray-100" readonly>
        </div>
        <div>
            <label class="block mb-1 font-medium" for="phone">Phone</label>
            <input type="text" id="phone" name="phone" value="<?php echo set_value('phone', $user->phone); ?>" class="w-full border-gray-300 rounded p-2">
            <?php echo form_error('phone', '<div class="text-red-500 text-sm">', '</div>'); ?>
        </div>
        <div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Save Changes</button>
        </div>
    </form>
    <hr class="my-6">
    <h3 class="text-xl font-semibold mb-2">Subscription</h3>
    <?php if ($subscription): ?>
        <p class="mb-4 text-green-600">You have an active subscription.</p>
    <?php else: ?>
        <p class="mb-4">You are not subscribed yet. Subscribe now to access premium features.</p>
        <a href="<?php echo site_url('subscription/create'); ?>" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">Subscribe Now</a>
    <?php endif; ?>
    <hr class="my-6">
    <div>
        <h3 class="text-xl font-semibold mb-2">Social Login</h3>
        <p class="mb-2">You can also log in with your Google account:</p>
        <a href="<?php echo site_url('login/google'); ?>" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded inline-flex items-center">
            <!-- Google icon using Tailwind classes for basic styling -->
            <span class="mr-2 inline-block align-middle">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20" height="20"><path fill="#EA4335" d="M24 9.5c3.15 0 5.66 1.21 7.56 2.73l5.54-5.54C32.67 3.01 28.65 1 24 1 14.23 1 5.83 6.76 2.34 15.18l6.73 5.23C11.08 13.05 16.96 9.5 24 9.5z"/><path fill="#4285F4" d="M46.5 24.5c0-1.54-.14-3.02-.4-4.47H24v8.45h12.73c-.55 2.88-2.19 5.33-4.71 7l7.23 5.62C43.88 37.6 46.5 31.58 46.5 24.5z"/><path fill="#FBBC05" d="M9.07 28.41l-6.73-5.23C1.02 17.74 0 14.01 0 10.5 0 4.71 2.55 0.57 6.51 0.5c2.32 0 4.47.77 6.23 2.26l5.54-5.54C15.36-2.36 12.81-3 10.04-3 4.92-3 0 1.92 0 7.5c0 4.02 2.46 7.46 5.82 10.92l6.73 5.23C12.03 27.18 10.2 28.41 9.07 28.41z"/><path fill="#34A853" d="M24 48c6.63 0 12.18-2.18 16.25-5.84l-7.23-5.62C30.79 39.4 27.69 40.5 24 40.5c-7.04 0-12.92-3.55-15.93-8.91l-6.73 5.23C5.83 45.24 14.23 48 24 48z"/></svg>
            </span>
            <span>Continue with Google</span>
        </a>
    </div>
</div>

<?php $this->load->view('partials/footer'); ?>