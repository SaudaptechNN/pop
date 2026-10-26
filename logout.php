<?php
require __DIR__ . '/includes/auth.php';

// Sign-out only happens through the POST form so other sites cannot log you out.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valid()) {
    logout_admin();
    flash('success', 'You have been signed out.');
    redirect(admin_url('login.php'));
}
redirect(admin_url('index.php'));
