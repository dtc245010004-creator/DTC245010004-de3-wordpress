<?php
declare(strict_types=1);

define('WP_INSTALLING', true);
require '/var/www/html/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';

$adminUser = getenv('DEMO_WP_ADMIN_USER') ?: 'teacher';
$adminPassword = getenv('DEMO_WP_ADMIN_PASSWORD') ?: 'teacher123';
$adminEmail = getenv('DEMO_WP_ADMIN_EMAIL') ?: 'teacher@example.test';

if (!is_blog_installed()) {
    wp_install('Website An Nhiên', $adminUser, $adminEmail, true, '', $adminPassword);
}

$user = get_user_by('login', $adminUser);
if (!$user) {
    $userId = wp_create_user($adminUser, $adminPassword, $adminEmail);
    if (is_wp_error($userId)) {
        fwrite(STDERR, $userId->get_error_message() . PHP_EOL);
        exit(1);
    }
} else {
    $userId = (int) $user->ID;
    wp_set_password($adminPassword, $userId);
}

$updated = wp_update_user([
    'ID' => $userId,
    'user_email' => $adminEmail,
    'role' => 'administrator',
]);

if (is_wp_error($updated)) {
    fwrite(STDERR, $updated->get_error_message() . PHP_EOL);
    exit(1);
}

echo "WordPress administrator ready: {$adminUser}" . PHP_EOL;
