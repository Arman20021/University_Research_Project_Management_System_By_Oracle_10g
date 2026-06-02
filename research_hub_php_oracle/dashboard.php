<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/ui.php';
require_login();
$title = $_SESSION['role'] === 'admin' ? 'Admin Dashboard' : 'User Dashboard';
$role = $_SESSION['role'];

try {
    $stats = [
        ['Total Universities', db_scalar('SELECT COUNT(DISTINCT university) FROM departments'), '🏢'],
        ['Total Departments', db_scalar('SELECT COUNT(*) FROM departments'), '🏛️'],
        ['Total Students', db_scalar('SELECT COUNT(*) FROM students'), '🎓'],
        ['Total Supervisors', db_scalar('SELECT COUNT(*) FROM supervisors'), '👨‍🏫'],
        ['Total Research Projects', db_scalar('SELECT COUNT(*) FROM projects'), '📄'],
        ['Total Publications', db_scalar('SELECT COUNT(*) FROM publications'), '📚'],
        ['Total Funding Records', db_scalar("SELECT COUNT(*) FROM projects WHERE funding IS NOT NULL"), '💰'],
        ['Total Reviewers', db_scalar('SELECT COUNT(*) FROM reviewers'), '✅'],
    ];
    $recentProjects = db_fetch_all("SELECT p.project_id, p.title, p.status, s.supervisor_name FROM projects p LEFT JOIN supervisors s ON s.supervisor_id = p.supervisor_id ORDER BY p.created_at DESC", []);
    $recentPubs = db_fetch_all("SELECT publication_id, publisher, review_status, TO_CHAR(publication_date,'YYYY-MM-DD') publication_date FROM publications ORDER BY publication_date DESC", []);
    $statusRows = db_fetch_all("SELECT status, COUNT(*) total FROM projects GROUP BY status ORDER BY status");
} catch (Exception $ex) {
    flash($ex->getMessage(), 'error');
    $stats = $recentProjects = $recentPubs = $statusRows = [];
}
include __DIR__ . '/includes/header.php';
?>
<div class="mb-4 flex flex-wrap items-center gap-2 text-sm text-slate-500"><span><?= e($role === 'admin' ? 'Admin' : 'User') ?></span><span>/</span><span class="font-medium text-slate-800">Dashboard</span></div>
<?php section_header($title, $role === 'admin' ? 'Overview of university research operations, submissions, publications, and academic activity' : 'Track your current research work, deadlines, and publication progress from one place', '<a class="btn btn-primary" href="projects.php">Generate Report</a>'); ?>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
  <?php foreach ($stats as $item): ?>
    <div class="card p-5">
      <div class="flex items-center justify-between">
        <div><p class="text-sm text-slate-500"><?= e($item[0]) ?></p><p class="mt-2 text-2xl font-semibold text-slate-900"><?= e($item[1]) ?></p></div>
        <div class="rounded-2xl bg-sky-50 p-3 text-xl text-sky-700"><?= $item[2] ?></div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-3">
  <div class="space-y-6 xl:col-span-2">
    <div class="grid gap-6 lg:grid-cols-2">
      <div class="card p-5">
        <h3 class="text-base font-semibold">Recent Project Submissions</h3>
        <div class="mt-4 space-y-4">
          <?php foreach (array_slice($recentProjects, 0, 4) as $row): ?>
            <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 p-3">
              <div class="min-w-0"><p class="truncate font-medium text-slate-800"><?= e($row['title']) ?></p><p class="text-sm text-slate-500"><?= e($row['supervisor_name'] ?: 'No supervisor') ?></p></div>
              <?= status_badge($row['status']) ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="card p-5">
        <h3 class="text-base font-semibold">Recent Publications</h3>
        <div class="mt-4 space-y-4">
          <?php foreach (array_slice($recentPubs, 0, 4) as $row): ?>
            <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 p-3">
              <div class="min-w-0"><p class="truncate font-medium text-slate-800"><?= e($row['publication_id']) ?></p><p class="text-sm text-slate-500"><?= e($row['publisher']) ?> · <?= e($row['publication_date']) ?></p></div>
              <?= status_badge($row['review_status']) ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <div class="card p-5">
      <h3 class="text-base font-semibold">Project Status Overview</h3>
      <p class="mt-1 text-sm text-slate-500">Current distribution of research project stages</p>
      <div class="mt-5 space-y-4">
        <?php $max = max(1, ...array_map(function($r){return (int)$r['total'];}, $statusRows ?: [['total'=>1]])); ?>
        <?php foreach ($statusRows as $row): $width = ((int)$row['total'] / $max) * 100; ?>
          <div class="space-y-2">
            <div class="flex items-center justify-between text-sm"><span class="font-medium text-slate-700"><?= e($row['status']) ?></span><span class="text-slate-500"><?= e($row['total']) ?></span></div>
            <div class="h-3 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-sky-500" style="width: <?= e($width) ?>%"></div></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="card p-5">
    <h3 class="text-base font-semibold">Quick Actions</h3>
    <p class="mt-1 text-sm text-slate-500">Shortcut tools for daily tasks</p>
    <div class="mt-4 grid gap-3">
      <?php foreach ([['Add Student','users.php?entity=students&mode=add','🎓'],['Add Supervisor','users.php?entity=supervisors&mode=add','👨‍🏫'],['Add Project','projects.php?mode=add','📄'],['Add Publication','publications_reviews.php?mode=add','📚']] as $action): ?>
        <a class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-sky-200 hover:bg-sky-50" href="<?= e($action[1]) ?>">
          <span class="rounded-xl bg-white p-2 text-sky-700 shadow-sm"><?= $action[2] ?></span><span><span class="block font-medium text-slate-800"><?= e($action[0]) ?></span><span class="text-sm text-slate-500">Open related management view</span></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
