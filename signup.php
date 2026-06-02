<?php
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$departments = db_fetch_all("
    SELECT department_id, department_name 
    FROM departments 
    ORDER BY department_name
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = post('full_name');
    $email = post('email');
    $phone = post('phone');
    $dob = post('dob');
    $department_id = post('department_id');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    try {
        if ($full_name === '' || $email === '' || $password === '' || $department_id === '') {
            flash('Please fill all required fields.', 'error');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('Please enter a valid email address.', 'error');
        } elseif (strlen($password) < 6) {
            flash('Password must be at least 6 characters.', 'error');
        } elseif ($password !== $confirm_password) {
            flash('Password and confirm password do not match.', 'error');
        } else {
            $existing = db_fetch_one("
                SELECT email 
                FROM app_users 
                WHERE LOWER(email) = LOWER(:email)
            ", [
                'email' => $email
            ]);

            if ($existing) {
                flash('This email is already registered. Please login.', 'error');
            } else {
                $dept = db_fetch_one("
                    SELECT department_id, department_name 
                    FROM departments 
                    WHERE department_id = :department_id
                ", [
                    'department_id' => $department_id
                ]);

                if (!$dept) {
                    flash('Selected department was not found.', 'error');
                } else {
                    $next_user_id = db_scalar("
                        SELECT NVL(MAX(TO_NUMBER(SUBSTR(user_id, 4))), 0) + 1 
                        FROM app_users
                        WHERE user_id LIKE 'USR%'
                    ");

                    $next_student_id = db_scalar("
                        SELECT NVL(MAX(TO_NUMBER(SUBSTR(student_id, 5))), 0) + 1 
                        FROM students
                        WHERE student_id LIKE 'STU%'
                    ");

                    $user_id = 'USR' . str_pad($next_user_id, 3, '0', STR_PAD_LEFT);
                    $student_id = 'STU ' . str_pad($next_student_id, 4, '0', STR_PAD_LEFT);
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);

                    db_execute("
                        INSERT INTO app_users (
                            user_id,
                            full_name,
                            email,
                            password_hash,
                            role,
                            department,
                            designation,
                            phone,
                            is_active
                        ) VALUES (
                            :user_id,
                            :full_name,
                            :email,
                            :password_hash,
                            'user',
                            :department,
                            'Student Researcher',
                            :phone,
                            1
                        )
                    ", [
                        'user_id' => $user_id,
                        'full_name' => $full_name,
                        'email' => $email,
                        'password_hash' => $password_hash,
                        'department' => $dept['department_name'],
                        'phone' => $phone
                    ], false);

                    if ($dob !== '') {
                        db_execute("
                            INSERT INTO students (
                                student_id,
                                student_name,
                                phone,
                                dob,
                                email,
                                department_id
                            ) VALUES (
                                :student_id,
                                :student_name,
                                :phone,
                                TO_DATE(:dob, 'YYYY-MM-DD'),
                                :email,
                                :department_id
                            )
                        ", [
                            'student_id' => $student_id,
                            'student_name' => $full_name,
                            'phone' => $phone,
                            'dob' => $dob,
                            'email' => $email,
                            'department_id' => $department_id
                        ], false);
                    } else {
                        db_execute("
                            INSERT INTO students (
                                student_id,
                                student_name,
                                phone,
                                dob,
                                email,
                                department_id
                            ) VALUES (
                                :student_id,
                                :student_name,
                                :phone,
                                NULL,
                                :email,
                                :department_id
                            )
                        ", [
                            'student_id' => $student_id,
                            'student_name' => $full_name,
                            'phone' => $phone,
                            'email' => $email,
                            'department_id' => $department_id
                        ], false);
                    }

                    oci_commit(db_connect());

                    flash('Signup successful. Please login with your email and password.', 'success');
                    redirect('index.php');
                }
            }
        }
    } catch (Exception $ex) {
        oci_rollback(db_connect());
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
    <title>Student Signup | Research Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/app.css">
</head>

<body class="min-h-screen bg-gradient-to-br from-sky-50 via-white to-teal-50 p-4 text-slate-900 flex items-center justify-center">
    <div class="mx-auto w-full max-w-md overflow-hidden rounded-[2rem] border border-white/60 bg-white shadow-2xl">


                <?php foreach (
                    [
                        'Student project submission',
                        'Publication and review tracking',
                        'Supervisor and reviewer workflow',
                        'Oracle 10g connected account system'
                    ] as $item
                ): ?>
                    <div class="rounded-2xl border border-white/15 bg-white/10 p-4 text-sm text-sky-50 backdrop-blur">
                        <?= e($item) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div> -->

        <div class="flex items-center justify-center p-6 md:p-10">
            <div class="w-full rounded-3xl border border-slate-200 bg-white p-6">

                <?php foreach ($messages as $item): ?>
                    <div class="mb-4 rounded-2xl border <?= $item['type'] === 'error' ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700' ?> p-4 font-medium">
                        <?= e($item['message']) ?>
                    </div>
                <?php endforeach; ?>

                <div class="space-y-3 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-50 text-2xl text-sky-700">✦</div>
                    <h1 class="text-2xl font-semibold">Student Signup</h1>
                    <p class="text-sm text-slate-500">Create a student account for Research Hub</p>
                </div>

                <form method="post" class="mt-6 space-y-4">
                    <input class="input" type="text" name="full_name" placeholder="Full name" required>

                    <input class="input" type="email" name="email" placeholder="Email address" required>

                    <input class="input" type="text" name="phone" placeholder="Phone number">

                    <input class="input" type="date" name="dob" placeholder="Date of birth">

                    <select class="input" name="department_id" required>
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $department): ?>
                            <option value="<?= e($department['department_id']) ?>">
                                <?= e($department['department_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <input class="input" type="password" name="password" placeholder="Password" required>

                    <input class="input" type="password" name="confirm_password" placeholder="Confirm password" required>

                    <button class="btn btn-primary w-full" type="submit">Create Student Account</button>

                    <p class="rounded-xl bg-slate-50 p-3 text-center text-sm text-slate-500">
                        Already have an account?
                        <a href="index.php" class="font-medium text-sky-700">Sign in</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</body>

</html>