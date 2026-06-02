<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/ui.php';
require_once __DIR__ . '/includes/data_helpers.php';

require_login();

$title = 'My Research Projects';
$user = current_user();

$student = db_fetch_one(
    "SELECT student_id
     FROM students
     WHERE LOWER(email) = LOWER(:p_email)",
    [
        'p_email' => $user['email']
    ]
);

if ($student) {
    $projects = db_fetch_all(
        "SELECT
            p.project_id,
            p.title,
            p.publication_status,
            p.status AS project_status,
            TO_CHAR(p.submission_date, 'YYYY-MM-DD') AS submission_date,
            p.funding,
            p.publication_info,
            p.progress,
            s.supervisor_name,
            r.reviewer_name
         FROM projects p
         JOIN project_students ps ON ps.project_id = p.project_id
         LEFT JOIN supervisors s ON s.supervisor_id = p.supervisor_id
         LEFT JOIN reviewers r ON r.reviewer_id = p.reviewer_id
         WHERE ps.student_id = :p_student_id
         ORDER BY p.created_at DESC",
        [
            'p_student_id' => $student['student_id']
        ]
    );
} else {
    $projects = [];
}

$selected = null;

if (getq('view') && $student) {
    $selected = db_fetch_one(
        "SELECT
            p.project_id,
            p.title,
            p.publication_status,
            p.status AS project_status,
            TO_CHAR(p.submission_date, 'YYYY-MM-DD') AS submission_date_fmt,
            p.funding,
            p.publication_info,
            p.progress,
            p.notes,
            s.supervisor_name,
            r.reviewer_name
         FROM projects p
         JOIN project_students ps ON ps.project_id = p.project_id
         LEFT JOIN supervisors s ON s.supervisor_id = p.supervisor_id
         LEFT JOIN reviewers r ON r.reviewer_id = p.reviewer_id
         WHERE p.project_id = :p_project_id
           AND ps.student_id = :p_student_id",
        [
            'p_project_id' => getq('view'),
            'p_student_id' => $student['student_id']
        ]
    );
} else {
    $selected = $projects[0] ?? null;
}

include __DIR__ . '/includes/header.php';
?>

<div class="mb-4 flex flex-wrap items-center gap-2 text-sm text-slate-500">
  <span>User</span>
  <span>/</span>
  <span class="font-medium text-slate-800">My Research Projects</span>
</div>

<?php
section_header(
    'My Research Projects',
    'Review your active and completed submissions with a detailed project view',
    '<a class="btn btn-primary" href="submit_project.php">Submit Project</a>'
);
?>

<div class="grid gap-6 xl:grid-cols-3">
  <div class="space-y-4 xl:col-span-2">
    <?php foreach($projects as $p): ?>
      <div class="card p-5">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <p class="font-semibold text-slate-900"><?= e($p['title']) ?></p>

              <span class="text-xs text-slate-500">Publication:</span>
              <?= status_badge($p['publication_status']) ?>

              <span class="text-xs text-slate-500">Project:</span>
              <?= status_badge($p['project_status']) ?>
            </div>

            <div class="mt-2 grid gap-2 text-sm text-slate-500 md:grid-cols-3">
              <span>Project ID: <?= e($p['project_id']) ?></span>
              <span>Supervisor: <?= e($p['supervisor_name'] ?? '') ?></span>
              <span>Submitted: <?= e($p['submission_date']) ?></span>
              <span>Project Status: <?= e($p['project_status']) ?></span>
              <span>Publication Status: <?= e($p['publication_status']) ?></span>
              <span>Reviewer: <?= e($p['reviewer_name'] ?? '') ?></span>
              <span>Funding: <?= e($p['funding'] ?? '') ?></span>
            </div>
          </div>

          <a class="btn btn-outline" href="my_projects.php?view=<?= urlencode($p['project_id']) ?>">
            View Details
          </a>
        </div>
      </div>
    <?php endforeach; ?>

    <?php if(empty($projects)): ?>
      <div class="card p-5 text-center text-slate-500">
        No projects found for your account.
      </div>
    <?php endif; ?>
  </div>

  <div class="card p-5">
    <h3 class="text-lg font-semibold">Project Details</h3>
    <p class="mt-1 text-sm text-slate-500">Expanded research information for the selected item</p>

    <?php if($selected): ?>
      <div class="mt-4 space-y-4">
        <?php foreach([
            ['Full Title', $selected['title']],
            ['Project Status', $selected['project_status']],
            ['Publication Status', $selected['publication_status']],
            ['Supervisor Details', $selected['supervisor_name'] ?? ''],
            ['Assigned Reviewer', $selected['reviewer_name'] ?? ''],
            ['Funding Information', $selected['funding'] ?? ''],
            ['Linked Publication', $selected['publication_info'] ?? '']
        ] as $info): ?>
          <div class="rounded-2xl border border-slate-200 p-4">
            <p class="text-sm text-slate-500"><?= e($info[0]) ?></p>
            <p class="mt-2 font-medium text-slate-800"><?= e($info[1]) ?></p>
          </div>
        <?php endforeach; ?>

        <div class="rounded-2xl border border-slate-200 p-4">
          <p class="text-sm text-slate-500">Current Progress</p>

          <div class="mt-3 h-3 overflow-hidden rounded-full bg-slate-100">
            <div
              class="h-full rounded-full bg-sky-500"
              style="width:<?= e($selected['progress']) ?>%"
            ></div>
          </div>

          <div class="mt-2 flex items-center justify-between text-sm">
            <span><?= e($selected['progress']) ?>% complete</span>
            <?= status_badge($selected['project_status']) ?>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="mt-4 rounded-2xl border border-slate-200 p-4 text-sm text-slate-500">
        Select a project to view details.
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>