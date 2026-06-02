<?php $user = current_user(); $role = $_SESSION['role'] ?? 'guest'; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= page_title($title ?? 'Dashboard') ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/app.css">
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
<div class="flex min-h-screen">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="min-w-0 flex-1">
    <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur">
      <div class="flex items-center gap-3 px-4 py-3 md:px-6">
        <div class="min-w-0 flex-1">
          <h2 class="truncate text-lg font-semibold text-slate-900"><?= e($title ?? 'Research Hub') ?></h2>
          <p class="text-xs text-slate-500 hidden sm:block">University Research Project Management</p>
        </div>
        <form action="search.php" method="get" class="relative hidden w-full max-w-md md:block">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">⌕</span>
          <input name="q" class="input pl-9" placeholder="Search projects, publications, users">
        </form>
        <a href="notifications.php" class="btn btn-outline px-3">🔔</a>
        <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm">
          <div class="flex h-8 w-8 items-center justify-center rounded-full bg-sky-100 text-sm font-bold text-sky-700"><?= e(strtoupper(substr($_SESSION['full_name'] ?? 'RH', 0, 2))) ?></div>
          <div class="hidden text-left md:block">
            <p class="text-sm font-medium text-slate-800"><?= e($_SESSION['full_name'] ?? 'Research User') ?></p>
            <p class="text-xs capitalize text-slate-500"><?= e($role) ?></p>
          </div>
        </div>
      </div>
    </header>
    <main class="p-4 md:p-6">
      <?php foreach (flash() ?? [] as $item): ?>
        <div class="mb-4 rounded-2xl border <?= $item['type'] === 'error' ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700' ?> p-4 font-medium">
          <?= e($item['message']) ?>
        </div>
      <?php endforeach; ?>
