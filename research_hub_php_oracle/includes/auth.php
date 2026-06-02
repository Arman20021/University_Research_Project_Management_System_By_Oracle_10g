<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

function current_user() {
    if (empty($_SESSION['user_id'])) return null;
    return db_fetch_one('SELECT * FROM app_users WHERE user_id = :id', ['id' => $_SESSION['user_id']]);
}

function require_login() {
    if (empty($_SESSION['user_id'])) {
        redirect('index.php');
    }
}

function require_role($role) {
    require_login();
    if (($_SESSION['role'] ?? '') !== $role) {
        flash('You do not have permission to open that page.', 'error');
        redirect('dashboard.php');
    }
}

function login_user($email, $password, $role) {
    $user = db_fetch_one('SELECT * FROM app_users WHERE LOWER(email) = LOWER(:email) AND role = :role AND is_active = 1', [
        'email' => $email,
        'role' => $role
    ]);
    if (!$user) return false;
    if (!password_verify($password, $user['password_hash'])) return false;
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['full_name'] = $user['full_name'];
    return true;
}
