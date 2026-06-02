<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/ui.php';
require_login();
$title = 'Profile';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (post('action') === 'profile') {
            db_execute('UPDATE app_users SET full_name=:full_name, email=:email, department=:department, designation=:designation, phone=:phone WHERE user_id=:id', ['full_name'=>post('full_name'), 'email'=>post('email'), 'department'=>post('department'), 'designation'=>post('designation'), 'phone'=>post('phone'), 'id'=>$_SESSION['user_id']]);
            $_SESSION['full_name'] = post('full_name');
            flash('Profile updated successfully.');
        } elseif (post('action') === 'password') {
            $user = current_user();
            if (!password_verify($_POST['current_password'] ?? '', $user['password_hash'])) throw new Exception('Current password is incorrect.');
            if (($_POST['new_password'] ?? '') !== ($_POST['confirm_password'] ?? '')) throw new Exception('New password and confirmation do not match.');
            if (strlen($_POST['new_password'] ?? '') < 6) throw new Exception('New password must be at least 6 characters.');
            db_execute('UPDATE app_users SET password_hash=:hash WHERE user_id=:id', ['hash'=>password_hash($_POST['new_password'], PASSWORD_BCRYPT), 'id'=>$_SESSION['user_id']]);
            flash('Password changed successfully.');
        }
    } catch (Exception $ex) { flash($ex->getMessage(), 'error'); }
    redirect('profile.php');
}
$user = current_user();
include __DIR__ . '/includes/header.php';
?>
<div class="mb-4 flex flex-wrap items-center gap-2 text-sm text-slate-500"><span><?= e($_SESSION['role']==='admin'?'Admin':'User') ?></span><span>/</span><span class="font-medium text-slate-800">Profile</span></div>
<?php section_header('Profile', 'Manage your academic identity, contact details, and account settings'); ?>
<div class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
  <div class="card p-6 text-center"><div class="mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-sky-100 text-2xl font-semibold text-sky-700"><?= e(strtoupper(substr($user['full_name'],0,2))) ?></div><h3 class="mt-4 text-xl font-semibold"><?= e($user['full_name']) ?></h3><p class="text-slate-500"><?= e($user['designation']) ?></p><div class="mt-6 space-y-3 text-left"><div class="rounded-xl bg-slate-50 p-3">✉️ <?= e($user['email']) ?></div><div class="rounded-xl bg-slate-50 p-3">🏢 <?= e($user['department']) ?></div><div class="rounded-xl bg-slate-50 p-3">💼 <?= e($user['role']) ?></div></div></div>
  <div class="card p-5"><h3 class="text-lg font-semibold">Edit Profile</h3><form method="post" class="mt-5 space-y-4"><input type="hidden" name="action" value="profile"><div class="grid gap-4 md:grid-cols-2"><?php input_field('Full Name','full_name',$user['full_name']); input_field('Email Address','email',$user['email'],'email'); input_field('Department','department',$user['department']); input_field('Designation or Role','designation',$user['designation']); input_field('Phone','phone',$user['phone']); ?></div><button class="btn btn-primary">Save Profile</button></form><div class="mt-6 rounded-2xl border border-slate-200 p-4"><p class="mb-3 font-medium text-slate-800">Change Password</p><form method="post" class="grid gap-4 md:grid-cols-3"><input type="hidden" name="action" value="password"><input class="input" type="password" name="current_password" placeholder="Current Password" required><input class="input" type="password" name="new_password" placeholder="New Password" required><input class="input" type="password" name="confirm_password" placeholder="Confirm Password" required><div class="md:col-span-3"><button class="btn btn-outline">Change Password</button></div></form></div></div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
