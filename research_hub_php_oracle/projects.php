<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/ui.php';
require_once __DIR__ . '/includes/data_helpers.php';
require_login();
if ($_SESSION['role'] !== 'admin') redirect('my_projects.php');
$title = 'Manage Research Projects';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = post('action');
        if ($action === 'save') {
            $params = [
                'id'=>post('project_id'), 'title'=>post('project_title'), 'publication_status'=>post('publication_status'),
                'submission_date'=>post('submission_date'), 'supervisor_id'=>post('supervisor_id'), 'funding'=>post('funding'),
                'publication_info'=>post('publication_info'), 'reviewer_id'=>post('reviewer_id'), 'status'=>post('status'),
                'progress'=>post('progress', '0'), 'notes'=>post('notes'), 'created_by'=>$_SESSION['user_id'], 'old_id'=>post('old_id')
            ];
            if ($params['id']==='' || $params['title']==='') throw new Exception('Project ID and title are required.');
            if ($params['old_id']) {
                db_execute("UPDATE projects SET project_id=:id, title=:title, publication_status=:publication_status, submission_date=TO_DATE(:submission_date,'YYYY-MM-DD'), supervisor_id=:supervisor_id, funding=:funding, publication_info=:publication_info, reviewer_id=:reviewer_id, status=:status, progress=:progress, notes=:notes WHERE project_id=:old_id", $params);
            } else {
                db_execute("INSERT INTO projects (project_id,title,publication_status,submission_date,supervisor_id,funding,publication_info,reviewer_id,status,progress,notes,created_by) VALUES (:id,:title,:publication_status,TO_DATE(:submission_date,'YYYY-MM-DD'),:supervisor_id,:funding,:publication_info,:reviewer_id,:status,:progress,:notes,:created_by)", $params);
            }
            replace_project_students($params['id'], $_POST['students'] ?? []);
            flash('Project saved successfully.');
        } elseif ($action === 'delete') {
            db_execute('DELETE FROM projects WHERE project_id=:id', ['id'=>post('project_id')]);
            flash('Project deleted successfully.');
        }
    } catch (Exception $ex) { flash($ex->getMessage(), 'error'); }
    redirect('projects.php');
}

$q = strtolower(getq('q'));
$params=[];
$sql = "SELECT p.project_id, p.title, p.publication_status, TO_CHAR(p.submission_date,'YYYY-MM-DD') submission_date, p.funding, p.publication_info, p.status, p.progress, p.notes, p.supervisor_id, p.reviewer_id, s.supervisor_name, r.reviewer_name FROM projects p LEFT JOIN supervisors s ON s.supervisor_id=p.supervisor_id LEFT JOIN reviewers r ON r.reviewer_id=p.reviewer_id";
if ($q !== '') { $sql .= " WHERE LOWER(p.project_id || ' ' || p.title || ' ' || p.publication_status || ' ' || p.status || ' ' || s.supervisor_name || ' ' || r.reviewer_name || ' ' || p.funding) LIKE :q"; $params['q']='%'.$q.'%'; }
$sql .= ' ORDER BY p.created_at DESC';
$projects = db_fetch_all($sql,$params);
$edit = getq('edit') ? db_fetch_one("SELECT project_id, title, publication_status, TO_CHAR(submission_date,'YYYY-MM-DD') submission_date, supervisor_id, funding, publication_info, reviewer_id, status, progress, notes FROM projects WHERE project_id=:id", ['id'=>getq('edit')]) : null;
$detail = getq('view') ? db_fetch_one("SELECT p.*, TO_CHAR(p.submission_date,'YYYY-MM-DD') submission_date_fmt, s.supervisor_name, r.reviewer_name FROM projects p LEFT JOIN supervisors s ON s.supervisor_id=p.supervisor_id LEFT JOIN reviewers r ON r.reviewer_id=p.reviewer_id WHERE p.project_id=:id", ['id'=>getq('view')]) : null;
$studentIds = $edit ? project_student_ids($edit['project_id']) : [];
$showForm = getq('mode')==='add' || $edit;
$supervisors = supervisor_options(); $reviewers = reviewer_options(); $students = student_options();
include __DIR__ . '/includes/header.php';
?>
<div class="mb-4 flex flex-wrap items-center gap-2 text-sm text-slate-500"><span>Admin</span><span>/</span><span class="font-medium text-slate-800">Research Projects</span></div>
<?php section_header('Manage Research Projects', 'Track submissions, funding, review assignments, and publication progress in one view', '<a class="btn btn-primary" href="projects.php?mode=add">＋ Add Project</a>'); ?>

<?php if ($detail): ?>
<div class="card mb-6 p-5">
  <div class="flex items-start justify-between gap-4"><div><h3 class="text-xl font-semibold"><?= e($detail['title']) ?></h3><p class="mt-1 text-sm text-slate-500">Project details, assignments, and status tracking</p></div><a class="btn btn-outline" href="projects.php">Close</a></div>
  <div class="mt-5 grid gap-4 md:grid-cols-2">
    <?php foreach ([['Project ID',$detail['project_id']],['Submission Date',$detail['submission_date_fmt']],['Supervisor',$detail['supervisor_name']],['Assigned Students',project_student_names($detail['project_id'])],['Funding Info',$detail['funding']],['Publication Info',$detail['publication_info']],['Reviewer Assignment',$detail['reviewer_name']],['Notes',$detail['notes']]] as $info): ?>
      <div class="rounded-2xl border border-slate-200 p-4"><p class="text-sm text-slate-500"><?= e($info[0]) ?></p><p class="mt-2 font-medium text-slate-800"><?= e($info[1]) ?></p></div>
    <?php endforeach; ?>
    <div class="rounded-2xl border border-slate-200 p-4"><p class="text-sm text-slate-500">Current Progress</p><div class="mt-3 h-3 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-sky-500" style="width:<?= e($detail['progress']) ?>%"></div></div><div class="mt-2 flex items-center justify-between text-sm"><span><?= e($detail['progress']) ?>% complete</span><?= status_badge($detail['status']) ?></div></div>
  </div>
</div>
<?php endif; ?>

<?php if ($showForm): ?>
<div class="card mb-6 p-5">
  <h3 class="text-lg font-semibold"><?= $edit ? 'Update' : 'Add' ?> Research Project</h3>
  <form method="post" class="mt-5 grid gap-4 md:grid-cols-2">
    <input type="hidden" name="action" value="save"><input type="hidden" name="old_id" value="<?= e($edit['project_id'] ?? '') ?>">
    <?php input_field('Project ID','project_id',$edit['project_id'] ?? ''); input_field('Project Title','project_title',$edit['title'] ?? ''); select_field('Publication Status','publication_status',$edit['publication_status'] ?? 'Pending',['Pending','Reviewed','Published']); input_field('Submission Date','submission_date',$edit['submission_date'] ?? date('Y-m-d'),'date'); ?>
    <label><span class="form-label">Supervisor</span><select class="input" name="supervisor_id"><?php foreach($supervisors as $s): ?><option value="<?= e($s['supervisor_id']) ?>" <?= selected($edit['supervisor_id'] ?? '',$s['supervisor_id']) ?>><?= e($s['supervisor_name']) ?></option><?php endforeach; ?></select></label>
    <label><span class="form-label">Reviewer</span><select class="input" name="reviewer_id"><?php foreach($reviewers as $r): ?><option value="<?= e($r['reviewer_id']) ?>" <?= selected($edit['reviewer_id'] ?? '',$r['reviewer_id']) ?>><?= e($r['reviewer_name']) ?></option><?php endforeach; ?></select></label>
    <?php input_field('Funding Info','funding',$edit['funding'] ?? ''); input_field('Publication Info','publication_info',$edit['publication_info'] ?? ''); select_field('Project Status','status',$edit['status'] ?? 'Pending',['Pending','Ongoing','Reviewed','Published']); input_field('Progress %','progress',$edit['progress'] ?? '20','number','min="0" max="100"'); ?>
    <label class="md:col-span-2"><span class="form-label">Assigned Students</span><div class="grid gap-2 rounded-xl border border-slate-200 p-3 md:grid-cols-3"><?php foreach($students as $s): ?><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="students[]" value="<?= e($s['student_id']) ?>" <?= checked($s['student_id'],$studentIds) ?>><?= e($s['student_name']) ?></label><?php endforeach; ?></div></label>
    <label class="md:col-span-2"><span class="form-label">Notes</span><textarea class="input" name="notes" rows="4"><?= e($edit['notes'] ?? '') ?></textarea></label>
    <div class="flex gap-2 md:col-span-2"><button class="btn btn-primary">Save Project</button><a class="btn btn-outline" href="projects.php">Cancel</a></div>
  </form>
</div>
<?php endif; ?>

<div class="card p-5">
  <?php search_bar('Search by title, supervisor, status, or reviewer'); ?>
  <div class="table-scroll rounded-2xl border border-slate-200"><table class="min-w-full divide-y divide-slate-200 text-sm">
    <thead class="bg-slate-50 text-left text-slate-600"><tr><th class="px-4 py-3">Project ID</th><th class="px-4 py-3">Title</th><th class="px-4 py-3">Publication Status</th><th class="px-4 py-3">Submission Date</th><th class="px-4 py-3">Supervisor</th><th class="px-4 py-3">Assigned Students</th><th class="px-4 py-3">Funding</th><th class="px-4 py-3">Reviewer</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Actions</th></tr></thead>
    <tbody class="divide-y divide-slate-100 bg-white">
    <?php foreach($projects as $p): ?>
      <tr><td class="px-4 py-3"><?= e($p['project_id']) ?></td><td class="px-4 py-3 max-w-xs font-medium"><?= e($p['title']) ?></td><td class="px-4 py-3"><?= status_badge($p['publication_status']) ?></td><td class="px-4 py-3"><?= e($p['submission_date']) ?></td><td class="px-4 py-3"><?= e($p['supervisor_name']) ?></td><td class="px-4 py-3"><?= e(project_student_names($p['project_id'])) ?></td><td class="px-4 py-3"><?= e($p['funding']) ?></td><td class="px-4 py-3"><?= e($p['reviewer_name']) ?></td><td class="px-4 py-3"><?= status_badge($p['status']) ?></td><td class="px-4 py-3"><div class="flex justify-end gap-2"><a class="btn btn-outline px-3 py-2" href="projects.php?view=<?= urlencode($p['project_id']) ?>">👁</a><a class="btn btn-outline px-3 py-2" href="projects.php?edit=<?= urlencode($p['project_id']) ?>">✎</a><form method="post" onsubmit="return confirmDelete('Delete this project?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="project_id" value="<?= e($p['project_id']) ?>"><button class="btn btn-danger px-3 py-2">🗑</button></form></div></td></tr>
    <?php endforeach; ?>
    </tbody></table></div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
