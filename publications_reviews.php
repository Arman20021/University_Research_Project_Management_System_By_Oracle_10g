<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/ui.php';
require_once __DIR__ . '/includes/data_helpers.php';
require_login();
$title = 'Publications and Reviews';
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin) {
  try {
    $action = post('action');
    if ($action === 'save') {
      $params = ['id' => post('publication_id'), 'project_id' => post('project_id'), 'publication_date' => post('publication_date'), 'doi_number' => post('doi_number'), 'publisher' => post('publisher'), 'reviewer_id' => post('reviewer_id'), 'review_status' => post('review_status'), 'comments' => post('comments'), 'old_id' => post('old_id')];
      if ($params['id'] === '') throw new Exception('Publication ID is required.');
      if ($params['old_id']) db_execute("UPDATE publications SET publication_id=:id, project_id=:project_id, publication_date=TO_DATE(:publication_date,'YYYY-MM-DD'), doi_number=:doi_number, publisher=:publisher, reviewer_id=:reviewer_id, review_status=:review_status, comments=:comments WHERE publication_id=:old_id", $params);
      else db_execute("INSERT INTO publications (publication_id, project_id, publication_date, doi_number, publisher, reviewer_id, review_status, comments) VALUES (:id,:project_id,TO_DATE(:publication_date,'YYYY-MM-DD'),:doi_number,:publisher,:reviewer_id,:review_status,:comments)", $params);
      flash('Publication record saved successfully.');
    } elseif ($action === 'delete') {
      db_execute('DELETE FROM publications WHERE publication_id=:id', ['id' => post('publication_id')]);
      flash('Publication record deleted.');
    }
  } catch (Exception $ex) {
    flash($ex->getMessage(), 'error');
  }
  redirect('publications_reviews.php');
}

$q = strtolower(getq('q'));
$params = [];

$user = current_user();

$sql = "
    SELECT
        pub.publication_id,
        pub.project_id,
        p.title,
        TO_CHAR(pub.publication_date, 'YYYY-MM-DD') AS publication_date,
        pub.doi_number,
        pub.publisher,
        pub.reviewer_id,
        r.reviewer_name,
        pub.review_status,
        pub.comments
    FROM publications pub
    JOIN projects p ON p.project_id = pub.project_id
    LEFT JOIN reviewers r ON r.reviewer_id = pub.reviewer_id
";

$where = [];

if (!$isAdmin) {
  $student = db_fetch_one(
    "SELECT student_id
         FROM students
         WHERE LOWER(email) = LOWER(:p_email)",
    [
      'p_email' => $user['email']
    ]
  );

  if ($student) {
    $where[] = "EXISTS (
            SELECT 1
            FROM project_students ps
            WHERE ps.project_id = p.project_id
              AND ps.student_id = :p_student_id
        )";
    $params['p_student_id'] = $student['student_id'];
  } else {
    $where[] = "p.created_by = :p_created_by";
    $params['p_created_by'] = $_SESSION['user_id'];
  }
}

if ($q !== '') {
  $where[] = "LOWER(
        pub.publication_id || ' ' ||
        pub.doi_number || ' ' ||
        pub.publisher || ' ' ||
        pub.review_status || ' ' ||
        r.reviewer_name || ' ' ||
        p.title
    ) LIKE :p_search";
  $params['p_search'] = '%' . $q . '%';
}

if (!empty($where)) {
  $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY pub.publication_date DESC";

$rows = db_fetch_all($sql, $params);
$edit = null;
if ($isAdmin && getq('edit')) $edit = db_fetch_one("SELECT publication_id, project_id, TO_CHAR(publication_date,'YYYY-MM-DD') publication_date, doi_number, publisher, reviewer_id, review_status, comments FROM publications WHERE publication_id=:id", ['id' => getq('edit')]);
$showForm = $isAdmin && (getq('mode') === 'add' || $edit);
$projects = project_options();
$reviewers = reviewer_options();
include __DIR__ . '/includes/header.php';
?>
<div class="mb-4 flex flex-wrap items-center gap-2 text-sm text-slate-500"><span><?= $isAdmin ? 'Admin' : 'User' ?></span><span>/</span><span class="font-medium text-slate-800">Publications & Reviews</span></div>
<?php section_header('Publications and Reviews', 'Track publication metadata, reviewer decisions, and feedback in a clear academic layout', $isAdmin ? '<a class="btn btn-primary" href="publications_reviews.php?mode=add">＋ Add Publication</a>' : ''); ?>

<?php if ($showForm): ?>
  <div class="card mb-6 p-5">
    <h3 class="text-lg font-semibold"><?= $edit ? 'Update' : 'Add' ?> Publication</h3>
    <form method="post" class="mt-5 grid gap-4 md:grid-cols-2">
      <input type="hidden" name="action" value="save"><input type="hidden" name="old_id" value="<?= e($edit['publication_id'] ?? '') ?>">
      <?php input_field('Publication ID', 'publication_id', $edit['publication_id'] ?? ''); ?>
      <label><span class="form-label">Project</span><select class="input" name="project_id"><?php foreach ($projects as $p): ?><option value="<?= e($p['project_id']) ?>" <?= selected($edit['project_id'] ?? '', $p['project_id']) ?>><?= e($p['title']) ?></option><?php endforeach; ?></select></label>
      <?php input_field('Publication Date', 'publication_date', $edit['publication_date'] ?? date('Y-m-d'), 'date');
      input_field('DOI Number', 'doi_number', $edit['doi_number'] ?? '');
      input_field('Publisher', 'publisher', $edit['publisher'] ?? ''); ?>
      <label><span class="form-label">Reviewer</span><select class="input" name="reviewer_id"><?php foreach ($reviewers as $r): ?><option value="<?= e($r['reviewer_id']) ?>" <?= selected($edit['reviewer_id'] ?? '', $r['reviewer_id']) ?>><?= e($r['reviewer_name']) ?></option><?php endforeach; ?></select></label>
      <?php select_field('Review Status', 'review_status', $edit['review_status'] ?? 'Pending', ['Pending', 'Approved', 'Rejected', 'Reviewed']); ?>
      <label class="md:col-span-2"><span class="form-label">Reviewer Comments</span><textarea class="input" name="comments" rows="4"><?= e($edit['comments'] ?? '') ?></textarea></label>
      <div class="flex gap-2 md:col-span-2"><button class="btn btn-primary">Save Publication</button><a class="btn btn-outline" href="publications_reviews.php">Cancel</a></div>
    </form>
  </div>
<?php endif; ?>

<div class="grid gap-6 xl:grid-cols-3">
  <div class="card p-5 xl:col-span-2">
    <?php search_bar('Search publications, DOI, publisher, reviewer'); ?>
    <div class="table-scroll rounded-2xl border border-slate-200">
      <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50 text-left text-slate-600">
          <tr>
            <th class="px-4 py-3">Publication ID</th>
            <th class="px-4 py-3">Project Name</th>
            <th class="px-4 py-3">Publication Date</th>
            <th class="px-4 py-3">DOI Number</th>
            <th class="px-4 py-3">Publisher</th>
            <th class="px-4 py-3">Reviewer</th>
            <th class="px-4 py-3">Review Status</th>
            <?php if ($isAdmin): ?>
              <th class="px-4 py-3 text-right">Actions</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 bg-white">
          <?php foreach ($rows as $r): ?>
            <tr>
              <td class="px-4 py-3"><?= e($r['publication_id']) ?></td>

              <td class="px-4 py-3 font-medium text-slate-800">
                <?= e($r['title'] ?? 'No project name') ?>
              </td>

              <td class="px-4 py-3"><?= e($r['publication_date']) ?></td>
              <td class="px-4 py-3"><?= e($r['doi_number']) ?></td>
              <td class="px-4 py-3"><?= e($r['publisher']) ?></td>
              <td class="px-4 py-3"><?= e($r['reviewer_name']) ?></td>
              <td class="px-4 py-3"><?= status_badge($r['review_status']) ?></td>

              <?php if ($isAdmin): ?>
                <td class="px-4 py-3">
                  <div class="flex justify-end gap-2">
                    <a class="btn btn-outline px-3 py-2" href="publications_reviews.php?edit=<?= urlencode($r['publication_id']) ?>">✎</a>

                    <form method="post" onsubmit="return confirmDelete('Delete this publication?')">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="publication_id" value="<?= e($r['publication_id']) ?>">
                      <button class="btn btn-danger px-3 py-2">🗑</button>
                    </form>
                  </div>
                </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="space-y-4">
    <?php foreach (array_slice($rows, 0, 5) as $r): ?>
      <div class="card p-5">
        <h3 class="text-base font-semibold"><?= e($r['publication_id']) ?></h3>

        <p class="mt-1 text-sm font-medium text-slate-700">
          <?= e($r['title'] ?? 'No project name') ?>
        </p>

        <p class="mt-1 text-sm text-slate-500">
          <?= e($r['publisher']) ?>
        </p>

        <div class="mt-4 space-y-3 text-sm"></div>
      <?php endforeach; ?>
      </div>
  </div>
  <?php include __DIR__ . '/includes/footer.php'; ?>