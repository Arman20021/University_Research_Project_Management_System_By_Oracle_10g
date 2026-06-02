<?php
require_once __DIR__ . '/includes/auth.php';
if (!empty($_SESSION['user_id'])) redirect('dashboard.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = post('email');
    $password = $_POST['password'] ?? '';
    $role = post('role', 'admin');
    try {
        if (login_user($email, $password, $role)) {
            flash('Signed in as ' . $role, 'success');
            redirect('dashboard.php');
        }
        flash('Invalid email, password, role, or inactive account.', 'error');
    } catch (Exception $ex) {
        flash($ex->getMessage(), 'error');
    }
}
$messages = flash() ?? [];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign in | Research Hub</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/app.css">
</head>
<body class="min-h-screen bg-gradient-to-br from-sky-50 via-white to-teal-50 p-4 text-slate-900">
  <div class="mx-auto grid min-h-[calc(100vh-2rem)] max-w-6xl overflow-hidden rounded-[2rem] border border-white/60 bg-white shadow-2xl lg:grid-cols-[1.1fr_0.9fr]">
    <div class="hidden bg-gradient-to-br from-sky-800 via-sky-700 to-teal-600 p-10 text-white lg:flex lg:flex-col lg:justify-between">
      <div>
        <div class="flex items-center gap-3">
          <div class="rounded-2xl bg-white/15 p-3 backdrop-blur">🎓</div>
          <div>
            <p class="text-xl font-semibold">University Research Project Management</p>
            <p class="text-sm text-sky-100">Professional research operations for modern academic institutions</p>
          </div>
        </div>
        <div class="mt-12 max-w-lg space-y-5">
          <h1 class="text-4xl font-semibold leading-tight">Clean academic workflow for projects, reviews, supervisors, and publications.</h1>
          <p class="text-sky-100">Built with PHP, HTML, Tailwind-style theme classes, and Oracle 10g database connectivity.</p>
        </div>
      </div>
      <div class="grid gap-4 sm:grid-cols-2">
        <?php foreach (['Dashboard analytics and role based access','Research tracking with publication status','Reviewer assignments and feedback flows','Responsive design for desktop and mobile'] as $item): ?>
          <div class="rounded-2xl border border-white/15 bg-white/10 p-4 text-sm text-sky-50 backdrop-blur"><?= e($item) ?></div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="flex items-center justify-center p-6 md:p-10">
      <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-6">
        <?php foreach ($messages as $item): ?>
          <div class="mb-4 rounded-2xl border <?= $item['type']==='error'?'border-rose-200 bg-rose-50 text-rose-700':'border-emerald-200 bg-emerald-50 text-emerald-700' ?> p-4 font-medium"><?= e($item['message']) ?></div>
        <?php endforeach; ?>
        <div class="space-y-3 text-center">
          <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-50 text-2xl text-sky-700">▣</div>
          <h1 class="text-2xl font-semibold">Sign in</h1>
          <p class="text-sm text-slate-500">Access the university research workspace</p>
        </div>
        <form method="post" class="mt-6 space-y-4">
          <div class="grid grid-cols-2 gap-2 rounded-xl bg-slate-100 p-1">
            <label class="cursor-pointer rounded-lg bg-white px-4 py-2 text-center text-sm font-medium text-slate-900 shadow-sm"><input type="radio" name="role" value="admin" checked class="sr-only">Admin</label>
            <label class="cursor-pointer rounded-lg px-4 py-2 text-center text-sm font-medium text-slate-500"><input type="radio" name="role" value="user" class="sr-only">User</label>
          </div>
          <input class="input" type="email" name="email" value="premiumtrendz290@gmail.com" placeholder="Email address" required>
          <input class="input" type="password" name="password" value="password" placeholder="Password" required>
          <div class="flex items-center justify-between text-sm"><label class="flex items-center gap-2 text-slate-600"><input type="checkbox">Remember me</label><span class="font-medium text-sky-700">Forgot password</span></div>
          <button class="btn btn-primary w-full" type="submit">Sign in</button>
          <p class="rounded-xl bg-slate-50 p-3 text-xs text-slate-500">Demo admin: premiumtrendz290@gmail.com / password. Demo user: ava.rahman@univ.edu / password.</p>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
