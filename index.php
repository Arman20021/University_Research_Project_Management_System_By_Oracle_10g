<?php
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['user_id'])) {
  redirect('dashboard.php');
}

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

<body class="min-h-screen bg-gradient-to-br from-sky-50 via-white to-teal-50 p-4 text-slate-900 flex items-center justify-center">
  <div class="mx-auto w-full max-w-md overflow-hidden rounded-[2rem] border border-white/60 bg-white shadow-2xl">



    <div class="flex items-center justify-center p-6 md:p-10">
      <div class="w-full rounded-3xl border border-slate-200 bg-white p-6">

        <?php foreach ($messages as $item): ?>
          <div class="mb-4 rounded-2xl border <?= $item['type'] === 'error' ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700' ?> p-4 font-medium">
            <?= e($item['message']) ?>
          </div>
        <?php endforeach; ?>

        <div class="space-y-3 text-center">
          <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-50 text-2xl text-sky-700">▣</div>
          <h1 class="text-2xl font-semibold">Sign in</h1>
          <p class="text-sm text-slate-500">Access the university research workspace</p>
        </div>

        <form method="post" class="mt-6 space-y-4">
          <input type="hidden" name="role" id="roleInput" value="admin">

          <div class="grid grid-cols-2 gap-2 rounded-xl bg-slate-100 p-1">
            <button
              type="button"
              id="adminBtn"
              class="cursor-pointer rounded-lg bg-white px-4 py-2 text-center text-sm font-medium text-slate-900 shadow-sm">
              Admin
            </button>

            <button
              type="button"
              id="userBtn"
              class="cursor-pointer rounded-lg px-4 py-2 text-center text-sm font-medium text-slate-500">
              User
            </button>
          </div>

          <input
            id="emailInput"
            class="input"
            type="email"
            name="email"
            
            placeholder="Email address"
            required>

          <input
            id="passwordInput"
            class="input"
            type="password"
            name="password"
            
            placeholder="Password"
            required>

          <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2 text-slate-600">
              <input type="checkbox"> Remember me
            </label>
            <span class="font-medium text-sky-700">Forgot password</span>
          </div>

          <button class="btn btn-primary w-full" type="submit">Sign in</button>


          <p class="text-center text-sm text-slate-500">
            New student?
            <a href="signup.php" class="font-medium text-sky-700">Create an account</a>
          </p>
        </form>
      </div>
    </div>
  </div>

  <script>
    const roleInput = document.getElementById('roleInput');
    const emailInput = document.getElementById('emailInput');
    const passwordInput = document.getElementById('passwordInput');
    const adminBtn = document.getElementById('adminBtn');
    const userBtn = document.getElementById('userBtn');

    const activeClass = 'cursor-pointer rounded-lg bg-white px-4 py-2 text-center text-sm font-medium text-slate-900 shadow-sm';
    const inactiveClass = 'cursor-pointer rounded-lg px-4 py-2 text-center text-sm font-medium text-slate-500';

    function setRole(role) {
      roleInput.value = role;

      if (role === 'admin') {
        adminBtn.className = activeClass;
        userBtn.className = inactiveClass;
      } else {
        userBtn.className = activeClass;
        adminBtn.className = inactiveClass;
      }
    }

    adminBtn.addEventListener('click', function() {
      setRole('admin');
    });

    userBtn.addEventListener('click', function() {
      setRole('user');
    });
  </script>
</body>

</html>