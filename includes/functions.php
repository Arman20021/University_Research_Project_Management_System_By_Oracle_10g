<?php
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect($path) {
    header('Location: ' . $path);
    exit;
}

function flash($message = null, $type = 'success') {
    if ($message !== null) {
        $_SESSION['flash'][] = ['message' => $message, 'type' => $type];
        return;
    }
    $items = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $items;
}

function post($key, $default = '') {
    return trim($_POST[$key] ?? $default);
}

function getq($key, $default = '') {
    return trim($_GET[$key] ?? $default);
}

function date_for_input($value) {
    if (!$value) return '';
    $ts = strtotime($value);
    return $ts ? date('Y-m-d', $ts) : $value;
}

function status_badge($value) {
    $value = e($value ?: 'Pending');
    $map = [
        'Pending' => 'bg-amber-50 text-amber-700 border-amber-200',
        'Ongoing' => 'bg-sky-50 text-sky-700 border-sky-200',
        'Reviewed' => 'bg-violet-50 text-violet-700 border-violet-200',
        'Published' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'Approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'Rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
        'Active' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'Inactive' => 'bg-slate-100 text-slate-700 border-slate-200'
    ];
    $class = $map[$value] ?? 'bg-slate-100 text-slate-700 border-slate-200';
    return '<span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium ' . $class . '">' . $value . '</span>';
}

function page_title($title) {
    return e($title) . ' | Research Hub';
}

function selected($a, $b) { return (string)$a === (string)$b ? 'selected' : ''; }
function checked($needle, $haystack) { return in_array($needle, $haystack ?? []) ? 'checked' : ''; }

function nav_active($page) {
    $current = basename($_SERVER['PHP_SELF']);
    return $current === $page ? 'bg-sky-50 text-sky-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900';
}

function require_post_action($allowed) {
    $action = post('action');
    if (!in_array($action, $allowed, true)) {
        flash('Invalid action.', 'error');
        return false;
    }
    return $action;
}
