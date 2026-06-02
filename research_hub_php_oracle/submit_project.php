<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/ui.php';
require_once __DIR__ . '/includes/data_helpers.php';
require_login();
$title = 'Submit Research Project';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $projectId = post('project_id') ?: 'PRJ' . date('His');
        db_execute("INSERT INTO projects (project_id,title,publication_status,submission_date,supervisor_id,funding,publication_info,reviewer_id,status,progress,notes,created_by) VALUES (:id,:title,:publication_status,TO_DATE(:submission_date,'YYYY-MM-DD'),:supervisor_id,:funding,:publication_info,:reviewer_id,'Pending',20,:notes,:created_by)", [
            'id'=>$projectId, 'title'=>post('project_title'), 'publication_status'=>post('publication_status'), 'submission_date'=>post('submission_date'), 'supervisor_id'=>post('supervisor_id'), 'funding'=>post('funding'), 'publication_info'=>'Not linked yet', 'reviewer_id'=>post('reviewer_id'), 'notes'=>post('notes'), 'created_by'=>$_SESSION['user_id']
        ]);
        replace_project_students($projectId, $_POST['students'] ?? []);
        flash(post('action') === 'draft' ? 'Draft saved successfully.' : 'Project submitted successfully.');
        redirect('my_projects.php');
    } catch (Exception $ex) { flash($ex->getMessage(), 'error'); }
}
$supervisors = supervisor_options(); $reviewers = reviewer_options(); $students = student_options();
include __DIR__ . '/includes/header.php';
?>
<div class="mb-4 flex flex-wrap items-center gap-2 text-sm text-slate-500"><span>User</span><span>/</span><span class="font-medium text-slate-800">Submit Research Project</span></div>
<?php section_header('Submit Research Project', 'Create a polished project submission with draft saving and document upload'); ?>
<div class="card p-5"><div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
  <form method="post" class="space-y-4"><div class="grid gap-4 md:grid-cols-2">
    <?php input_field('Project ID','project_id','PRJ'.date('His')); input_field('Project Title','project_title'); select_field('Publication Status','publication_status','Pending',['Pending','Reviewed','Published']); input_field('Submission Date','submission_date',date('Y-m-d'),'date'); ?>
    <label><span class="form-label">Supervisor</span><select class="input" name="supervisor_id"><?php foreach($supervisors as $s): ?><option value="<?= e($s['supervisor_id']) ?>"><?= e($s['supervisor_name']) ?></option><?php endforeach; ?></select></label>
    <label><span class="form-label">Reviewer</span><select class="input" name="reviewer_id"><?php foreach($reviewers as $r): ?><option value="<?= e($r['reviewer_id']) ?>"><?= e($r['reviewer_name']) ?></option><?php endforeach; ?></select></label>
    <?php input_field('Funding Info','funding'); ?>
    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-center"><div class="text-xl">⬆️</div><p class="mt-2 font-medium text-slate-700">Upload Documents</p><p class="text-sm text-slate-500">Add a file upload column later if you need physical document storage.</p></div>
  </div>
  <label><span class="form-label">Assigned Students</span><div class="grid gap-2 rounded-xl border border-slate-200 p-3 md:grid-cols-3"><?php foreach($students as $s): ?><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="students[]" value="<?= e($s['student_id']) ?>"><?= e($s['student_name']) ?></label><?php endforeach; ?></div></label>
  <label><span class="form-label">Research summary or notes</span><textarea class="input" rows="6" name="notes"></textarea></label>
  <div class="flex flex-wrap gap-2"><button name="action" value="draft" class="btn btn-outline">Save Draft</button><button name="action" value="submit" class="btn btn-primary">Submit Project</button></div>
  </form>
  <div class="card bg-slate-50 p-5 shadow-none"><h3 class="text-base font-semibold">Submission Guide</h3><p class="mt-1 text-sm text-slate-500">Recommended steps for a complete and review-ready submission</p><div class="mt-4 space-y-3 text-sm text-slate-600"><div class="rounded-xl bg-white p-3">Add a precise title and check your publication status.</div><div class="rounded-xl bg-white p-3">Select the right supervisor and associated students.</div><div class="rounded-xl bg-white p-3">Upload supporting files before final submission.</div><div class="rounded-xl bg-white p-3">Use save draft if you need time before sending it for review.</div></div></div>
</div></div>
<?php include __DIR__ . '/includes/footer.php'; ?>
