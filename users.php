<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/ui.php';
require_once __DIR__ . '/includes/data_helpers.php';
require_login();
require_role('admin');
$title = 'Manage Users';
$valid = ['students', 'supervisors', 'reviewers'];
$entity = in_array(getq('entity', 'students'), $valid, true) ? getq('entity', 'students') : 'students';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $entity = in_array(post('entity', 'students'), $valid, true) ? post('entity', 'students') : 'students';
  try {
    $action = post('action');
    if ($entity === 'students') {
      if ($action === 'save') {
        $params = ['id' => post('student_id'), 'name' => post('student_name'), 'phone' => post('phone'), 'dob' => post('dob'), 'email' => post('email'), 'department_id' => post('department_id'), 'old_id' => post('old_id')];
        if ($params['id'] === '' || $params['name'] === '') throw new Exception('Student ID and name are required.');
        if ($params['old_id']) db_execute("UPDATE students SET student_id=:id, student_name=:name, phone=:phone, dob=TO_DATE(:dob,'YYYY-MM-DD'), email=:email, department_id=:department_id WHERE student_id=:old_id", $params);
        else db_execute("INSERT INTO students (student_id, student_name, phone, dob, email, department_id) VALUES (:id, :name, :phone, TO_DATE(:dob,'YYYY-MM-DD'), :email, :department_id)", $params);
      } elseif ($action === 'delete') db_execute('DELETE FROM students WHERE student_id=:id', ['id' => post('id')]);
    }
    if ($entity === 'supervisors') {
      if ($action === 'save') {
        $params = ['id' => post('supervisor_id'), 'name' => post('supervisor_name'), 'email' => post('email'), 'room' => post('room_no'), 'designation' => post('designation'), 'department_id' => post('department_id'), 'old_id' => post('old_id')];
        if ($params['id'] === '' || $params['name'] === '') throw new Exception('Supervisor ID and name are required.');
        if ($params['old_id']) db_execute('UPDATE supervisors SET supervisor_id=:id, supervisor_name=:name, email=:email, room_no=:room, designation=:designation, department_id=:department_id WHERE supervisor_id=:old_id', $params);
        else db_execute('INSERT INTO supervisors (supervisor_id, supervisor_name, email, room_no, designation, department_id) VALUES (:id, :name, :email, :room, :designation, :department_id)', $params);
      } elseif ($action === 'delete') db_execute('DELETE FROM supervisors WHERE supervisor_id=:id', ['id' => post('id')]);
    }
    if ($entity === 'reviewers') {
      if ($action === 'save') {
        $params = ['id' => post('reviewer_id'), 'name' => post('reviewer_name'), 'designation' => post('designation'), 'email' => post('email'), 'phone' => post('phone'), 'old_id' => post('old_id')];
        if ($params['id'] === '' || $params['name'] === '') throw new Exception('Reviewer ID and name are required.');
        if ($params['old_id']) db_execute('UPDATE reviewers SET reviewer_id=:id, reviewer_name=:name, designation=:designation, email=:email, phone=:phone WHERE reviewer_id=:old_id', $params);
        else db_execute('INSERT INTO reviewers (reviewer_id, reviewer_name, designation, email, phone) VALUES (:id, :name, :designation, :email, :phone)', $params);
      } elseif ($action === 'delete') db_execute('DELETE FROM reviewers WHERE reviewer_id=:id', ['id' => post('id')]);
    }
    flash(($action === 'delete' ? 'Deleted' : 'Saved') . ' ' . rtrim($entity, 's') . ' successfully.');
  } catch (Exception $ex) {
    flash($ex->getMessage(), 'error');
  }
  redirect('users.php?entity=' . urlencode($entity));
}

$q = strtolower(getq('q'));
$departments = department_options();
$edit = null;
if (getq('edit')) {
  if ($entity === 'students') $edit = db_fetch_one("SELECT student_id, student_name, phone, TO_CHAR(dob,'YYYY-MM-DD') dob, email, department_id FROM students WHERE student_id=:id", ['id' => getq('edit')]);
  if ($entity === 'supervisors') $edit = db_fetch_one('SELECT * FROM supervisors WHERE supervisor_id=:id', ['id' => getq('edit')]);
  if ($entity === 'reviewers') $edit = db_fetch_one('SELECT * FROM reviewers WHERE reviewer_id=:id', ['id' => getq('edit')]);
}
$showForm = getq('mode') === 'add' || $edit;

$params = [];
if ($entity === 'students') {
  $sql = "SELECT s.student_id, s.student_name, s.phone, TO_CHAR(s.dob,'YYYY-MM-DD') dob, s.email, d.department_name FROM students s LEFT JOIN departments d ON d.department_id=s.department_id";
  if ($q !== '') {
    $sql .= " WHERE LOWER(s.student_id || ' ' || s.student_name || ' ' || s.phone || ' ' || s.email || ' ' || d.department_name) LIKE :q";
    $params['q'] = '%' . $q . '%';
  }
  $sql .= ' ORDER BY s.student_name';
}
if ($entity === 'supervisors') {
  $sql = "SELECT s.supervisor_id, s.supervisor_name, s.email, s.room_no, s.designation, d.department_name FROM supervisors s LEFT JOIN departments d ON d.department_id=s.department_id";
  if ($q !== '') {
    $sql .= " WHERE LOWER(s.supervisor_id || ' ' || s.supervisor_name || ' ' || s.email || ' ' || s.designation || ' ' || d.department_name) LIKE :q";
    $params['q'] = '%' . $q . '%';
  }
  $sql .= ' ORDER BY s.supervisor_name';
}
if ($entity === 'reviewers') {
  $sql = "SELECT * FROM reviewers";
  if ($q !== '') {
    $sql .= " WHERE LOWER(reviewer_id || ' ' || reviewer_name || ' ' || designation || ' ' || email || ' ' || phone) LIKE :q";
    $params['q'] = '%' . $q . '%';
  }
  $sql .= ' ORDER BY reviewer_name';
}
$rows = db_fetch_all($sql, $params);
include __DIR__ . '/includes/header.php';
?>
<div class="mb-4 flex flex-wrap items-center gap-2 text-sm text-slate-500"><span>Admin</span><span>/</span><span class="font-medium text-slate-800">Users</span></div>
<?php section_header('Manage Users', 'Administer students, supervisors, and reviewers through searchable tabs', '<a href="users.php?entity=' . e($entity) . '&mode=add" class="btn btn-primary">＋ Add Record</a>'); ?>

<div class="mb-5 grid grid-cols-3 rounded-xl bg-slate-100 p-1 text-sm font-medium">
  <?php foreach ($valid as $tab): ?><a class="rounded-lg px-4 py-2 text-center <?= $entity === $tab ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500' ?>" href="users.php?entity=<?= e($tab) ?>"><?= e(ucfirst($tab)) ?></a><?php endforeach; ?>
</div>

<?php if ($showForm): ?>
  <div class="card mb-6 p-5">
    <h3 class="text-lg font-semibold"><?= $edit ? 'Update' : 'Add' ?> <?= e(rtrim($entity, 's')) ?></h3>
    <form method="post" class="mt-5 grid gap-4 md:grid-cols-2">
      <input type="hidden" name="entity" value="<?= e($entity) ?>"><input type="hidden" name="action" value="save">
      <?php if ($entity === 'students'): ?>
        <input type="hidden" name="old_id" value="<?= e($edit['student_id'] ?? '') ?>">
        <?php input_field('Student ID', 'student_id', $edit['student_id'] ?? '');
        input_field('Name', 'student_name', $edit['student_name'] ?? '');
        input_field('Phone Number', 'phone', $edit['phone'] ?? '');
        input_field('Date of Birth', 'dob', $edit['dob'] ?? '', 'date');
        input_field('Email Address', 'email', $edit['email'] ?? '', 'email'); ?>
        <label><span class="form-label">Department</span><select class="input" name="department_id"><?php foreach ($departments as $d): ?><option value="<?= e($d['department_id']) ?>" <?= selected($edit['department_id'] ?? '', $d['department_id']) ?>><?= e($d['department_name']) ?></option><?php endforeach; ?></select></label>
      <?php elseif ($entity === 'supervisors'): ?>
        <input type="hidden" name="old_id" value="<?= e($edit['supervisor_id'] ?? '') ?>">
        <?php input_field('Supervisor ID', 'supervisor_id', $edit['supervisor_id'] ?? '');
        input_field('Name', 'supervisor_name', $edit['supervisor_name'] ?? '');
        input_field('Email Address', 'email', $edit['email'] ?? '', 'email');
        input_field('Room No', 'room_no', $edit['room_no'] ?? '');
        input_field('Designation', 'designation', $edit['designation'] ?? ''); ?>
        <label><span class="form-label">Department</span><select class="input" name="department_id"><?php foreach ($departments as $d): ?><option value="<?= e($d['department_id']) ?>" <?= selected($edit['department_id'] ?? '', $d['department_id']) ?>><?= e($d['department_name']) ?></option><?php endforeach; ?></select></label>
      <?php else: ?>
        <input type="hidden" name="old_id" value="<?= e($edit['reviewer_id'] ?? '') ?>">
        <?php input_field('Reviewer ID', 'reviewer_id', $edit['reviewer_id'] ?? '');
        input_field('Name', 'reviewer_name', $edit['reviewer_name'] ?? '');
        input_field('Designation', 'designation', $edit['designation'] ?? '');
        input_field('Email Address', 'email', $edit['email'] ?? '', 'email');
        input_field('Phone Number', 'phone', $edit['phone'] ?? ''); ?>
      <?php endif; ?>
      <div class="flex gap-2 md:col-span-2"><button class="btn btn-primary" type="submit">Save Changes</button><a class="btn btn-outline" href="users.php?entity=<?= e($entity) ?>">Cancel</a></div>
    </form>
  </div>
<?php endif; ?>

<div class="card p-5">
  <?php search_bar('Search by name, ID, email, or department', '<input type="hidden" name="entity" value="' . e($entity) . '">'); ?>
  <div class="table-scroll rounded-2xl border border-slate-200">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
      <thead class="bg-slate-50 text-left text-slate-600">
        <tr>
          <?php if ($entity === 'students'): ?><th class="px-4 py-3">Student ID</th>
            <th class="px-4 py-3">Name</th>
            <th class="px-4 py-3">Phone</th>
            <th class="px-4 py-3">DOB</th>
            <th class="px-4 py-3">Email</th>
            <th class="px-4 py-3">Department</th><?php endif; ?>
          <?php if ($entity === 'supervisors'): ?><th class="px-4 py-3">Supervisor ID</th>
            <th class="px-4 py-3">Name</th>
            <th class="px-4 py-3">Email</th>
            <th class="px-4 py-3">Room</th>
            <th class="px-4 py-3">Designation</th>
            <th class="px-4 py-3">Department</th><?php endif; ?>
          <?php if ($entity === 'reviewers'): ?><th class="px-4 py-3">Reviewer ID</th>
            <th class="px-4 py-3">Name</th>
            <th class="px-4 py-3">Designation</th>
            <th class="px-4 py-3">Email</th>
            <th class="px-4 py-3">Phone</th><?php endif; ?>
          <th class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 bg-white">
        <?php foreach ($rows as $r): $id = $r[$entity === 'students' ? 'student_id' : ($entity === 'supervisors' ? 'supervisor_id' : 'reviewer_id')]; ?>
          <tr>
            <?php foreach ($r as $key => $val): ?><td class="px-4 py-3 <?= strpos($key, 'name') !== false ? 'font-medium' : '' ?>"><?= e($val) ?></td><?php endforeach; ?>
            <td class="px-4 py-3">
              <div class="flex justify-end gap-2"><a class="btn btn-outline px-3 py-2" href="users.php?entity=<?= e($entity) ?>&edit=<?= urlencode($id) ?>">✎</a>
                <form method="post" onsubmit="return confirmDelete('Delete this record?')"><input type="hidden" name="entity" value="<?= e($entity) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e($id) ?>"><button class="btn btn-danger px-3 py-2">🗑</button></form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>