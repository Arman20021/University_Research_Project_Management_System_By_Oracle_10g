<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/ui.php';
require_login();
$title = 'Search';
$q = getq('q');
$results = [];
if ($q !== '') {
    $like = '%' . strtolower($q) . '%';
    $projects = db_fetch_all("SELECT project_id id, title label, status meta FROM projects WHERE LOWER(project_id || ' ' || title || ' ' || status) LIKE :q ORDER BY created_at DESC", ['q'=>$like]);
    foreach ($projects as $r) $results[] = ['type'=>'Project','url'=>'projects.php?view='.urlencode($r['id']),'label'=>$r['label'],'meta'=>$r['meta']];
    $students = db_fetch_all("SELECT student_id id, student_name label, email meta FROM students WHERE LOWER(student_id || ' ' || student_name || ' ' || email) LIKE :q ORDER BY student_name", ['q'=>$like]);
    foreach ($students as $r) $results[] = ['type'=>'Student','url'=>'users.php?entity=students&edit='.urlencode($r['id']),'label'=>$r['label'],'meta'=>$r['meta']];
    $pubs = db_fetch_all("SELECT publication_id id, doi_number label, review_status meta FROM publications WHERE LOWER(publication_id || ' ' || doi_number || ' ' || publisher) LIKE :q ORDER BY publication_date DESC", ['q'=>$like]);
    foreach ($pubs as $r) $results[] = ['type'=>'Publication','url'=>'publications_reviews.php?edit='.urlencode($r['id']),'label'=>$r['label'],'meta'=>$r['meta']];
}
include __DIR__ . '/includes/header.php';
?>
<?php section_header('Search', 'Search projects, students, and publications from one place'); ?>
<div class="card p-5">
  <form method="get" class="mb-5 flex gap-3"><input class="input" name="q" value="<?= e($q) ?>" placeholder="Search anything"><button class="btn btn-primary">Search</button></form>
  <div class="space-y-3">
    <?php foreach($results as $r): ?><a class="block rounded-2xl border border-slate-200 p-4 transition hover:bg-sky-50" href="<?= e($r['url']) ?>"><div class="flex items-center justify-between"><p class="font-semibold text-slate-900"><?= e($r['label']) ?></p><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-600"><?= e($r['type']) ?></span></div><p class="mt-1 text-sm text-slate-500"><?= e($r['meta']) ?></p></a><?php endforeach; ?>
    <?php if($q !== '' && !$results): ?><p class="rounded-xl bg-slate-50 p-4 text-slate-500">No result found.</p><?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
