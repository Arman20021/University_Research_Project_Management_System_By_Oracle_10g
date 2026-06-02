<?php
$role = $_SESSION['role'] ?? 'user';
$adminItems = [
  ['dashboard.php', '📊', 'Dashboard'],
  ['departments.php', '🏢', 'Manage Departments'],
  ['users.php', '👥', 'Manage Users'],
  ['projects.php', '📄', 'Research Projects'],
  ['publications_reviews.php', '📚', 'Publications & Reviews'],
  ['profile.php', '👤', 'Profile'],
  ['notifications.php', '🔔', 'Notifications'],
];
$userItems = [
  ['dashboard.php', '🏠', 'Dashboard'],
  ['my_projects.php', '📄', 'My Projects'],
  ['submit_project.php', '⬆️', 'Submit Project'],
  ['publications_reviews.php', '📚', 'Publications & Reviews'],
  ['profile.php', '👤', 'Profile'],
  ['notifications.php', '🔔', 'Notifications'],
];
$items = $role === 'admin' ? $adminItems : $userItems;
?>
<aside class="hidden w-72 shrink-0 border-r border-slate-200 bg-white md:block">
  <div class="flex h-full flex-col">
    <div class="flex items-center gap-3 px-5 py-5">
      <div class="rounded-2xl bg-sky-700 p-2 text-white shadow-sm">🎓</div>
      <div>
        <p class="font-semibold text-slate-900">Research Hub</p>
        <p class="text-xs text-slate-500">University Management</p>
      </div>
    </div>
    <div class="px-3 pb-4">
      <div class="rounded-2xl bg-gradient-to-br from-sky-700 to-teal-600 p-4 text-white shadow-sm">
        <div class="flex items-center gap-2 text-sm opacity-90">✨ Academic Workspace</div>
        <p class="mt-2 text-sm leading-6 text-sky-50">Unified project, review, funding, and publication management.</p>
      </div>
    </div>
    <nav class="flex-1 space-y-1 px-3">
      <?php foreach ($items as $item): ?>
        <a class="sidebar-link <?= nav_active($item[0]) ?>" href="<?= e($item[0]) ?>"><span><?= $item[1] ?></span><?= e($item[2]) ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="p-3"><a class="sidebar-link border border-slate-200 text-slate-600 hover:bg-slate-50" href="logout.php">🔒 Sign out</a></div>
  </div>
</aside>
