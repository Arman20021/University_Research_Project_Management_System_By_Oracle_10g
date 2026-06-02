<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/ui.php';
require_login();
require_role('admin');
$title = 'Manage Departments';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = post('action');
        if ($action === 'save') {
            $oldId = post('old_id');
            $params = [
                'id' => post('department_id'),
                'name' => post('department_name'),
                'room' => post('office_room'),
                'email' => post('email'),
                'university' => post('university')
            ];
            if ($params['id'] === '' || $params['name'] === '') throw new Exception('Department ID and name are required.');
            if ($oldId) {
                $params['old_id'] = $oldId;
                db_execute('UPDATE departments SET department_id=:id, department_name=:name, office_room=:room, email=:email, university=:university WHERE department_id=:old_id', $params);
                flash('Department updated successfully.');
            } else {
                db_execute('INSERT INTO departments (department_id, department_name, office_room, email, university) VALUES (:id, :name, :room, :email, :university)', $params);
                flash('Department added successfully.');
            }
        } elseif ($action === 'delete') {
            db_execute('DELETE FROM departments WHERE department_id = :id', ['id' => post('department_id')]);
            flash('Department removed.');
        }
    } catch (Exception $ex) {
        flash($ex->getMessage(), 'error');
    }
    redirect('departments.php');
}

$q = strtolower(getq('q'));
$params = [];
$sql = 'SELECT * FROM departments';
if ($q !== '') {
    $sql .= " WHERE LOWER(department_id || ' ' || department_name || ' ' || office_room || ' ' || email || ' ' || university) LIKE :q";
    $params['q'] = '%' . $q . '%';
}
$sql .= ' ORDER BY department_name';
$departments = db_fetch_all($sql, $params);
$edit = null;
if (getq('edit')) {
    $edit = db_fetch_one('SELECT * FROM departments WHERE department_id = :id', ['id' => getq('edit')]);
}
$showForm = getq('mode') === 'add' || $edit;
include __DIR__ . '/includes/header.php';
?>
<div class="mb-4 flex flex-wrap items-center gap-2 text-sm text-slate-500"><span>Admin</span><span>/</span><span class="font-medium text-slate-800">Departments</span></div>
<?php section_header('Manage Departments', 'Organize department records, offices, and academic contacts', '<a href="departments.php?mode=add" class="btn btn-primary">＋ Add Department</a>'); ?>

<?php if ($showForm): ?>
<div class="card mb-6 p-5">
  <h3 class="text-lg font-semibold"><?= $edit ? 'Update Department' : 'Add Department' ?></h3>
  <p class="mt-1 text-sm text-slate-500">Enter department details in a clean academic format.</p>
  <form method="post" class="mt-5 grid gap-4 md:grid-cols-2">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="old_id" value="<?= e($edit['department_id'] ?? '') ?>">
    <?php input_field('Department ID', 'department_id', $edit['department_id'] ?? ''); ?>
    <?php input_field('Department Name', 'department_name', $edit['department_name'] ?? ''); ?>
    <?php input_field('Office Room', 'office_room', $edit['office_room'] ?? ''); ?>
    <?php input_field('Email Address', 'email', $edit['email'] ?? '', 'email'); ?>
    <div class="md:col-span-2"><?php input_field('University', 'university', $edit['university'] ?? ''); ?></div>
    <div class="flex gap-2 md:col-span-2"><button class="btn btn-primary" type="submit">Save</button><a class="btn btn-outline" href="departments.php">Cancel</a></div>
  </form>
</div>
<?php endif; ?>

<div class="card p-5">
  <?php search_bar('Search department, university, or email'); ?>
  <div class="table-scroll rounded-2xl border border-slate-200">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
      <thead class="bg-slate-50 text-left text-slate-600"><tr><th class="px-4 py-3">Department ID</th><th class="px-4 py-3">Department Name</th><th class="px-4 py-3">Office Room</th><th class="px-4 py-3">Email Address</th><th class="px-4 py-3">University</th><th class="px-4 py-3 text-right">Actions</th></tr></thead>
      <tbody class="divide-y divide-slate-100 bg-white">
        <?php foreach ($departments as $item): ?>
        <tr>
          <td class="px-4 py-3"><?= e($item['department_id']) ?></td><td class="px-4 py-3 font-medium"><?= e($item['department_name']) ?></td><td class="px-4 py-3"><?= e($item['office_room']) ?></td><td class="px-4 py-3"><?= e($item['email']) ?></td><td class="px-4 py-3"><?= e($item['university']) ?></td>
          <td class="px-4 py-3"><div class="flex justify-end gap-2"><a class="btn btn-outline px-3 py-2" href="departments.php?edit=<?= urlencode($item['department_id']) ?>">✎</a><form method="post" onsubmit="return confirmDelete('Delete department <?= e($item['department_name']) ?>?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="department_id" value="<?= e($item['department_id']) ?>"><button class="btn btn-danger px-3 py-2">🗑</button></form></div></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
