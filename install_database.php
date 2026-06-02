<?php
require_once __DIR__ . '/config/db.php';

$statements = [
    ['Drop notifications', <<<'SQL'
BEGIN EXECUTE IMMEDIATE 'DROP TABLE notifications CASCADE CONSTRAINTS'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;
SQL],
    ['Drop publications', <<<'SQL'
BEGIN EXECUTE IMMEDIATE 'DROP TABLE publications CASCADE CONSTRAINTS'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;
SQL],
    ['Drop project_students', <<<'SQL'
BEGIN EXECUTE IMMEDIATE 'DROP TABLE project_students CASCADE CONSTRAINTS'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;
SQL],
    ['Drop projects', <<<'SQL'
BEGIN EXECUTE IMMEDIATE 'DROP TABLE projects CASCADE CONSTRAINTS'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;
SQL],
    ['Drop reviewers', <<<'SQL'
BEGIN EXECUTE IMMEDIATE 'DROP TABLE reviewers CASCADE CONSTRAINTS'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;
SQL],
    ['Drop supervisors', <<<'SQL'
BEGIN EXECUTE IMMEDIATE 'DROP TABLE supervisors CASCADE CONSTRAINTS'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;
SQL],
    ['Drop students', <<<'SQL'
BEGIN EXECUTE IMMEDIATE 'DROP TABLE students CASCADE CONSTRAINTS'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;
SQL],
    ['Drop departments', <<<'SQL'
BEGIN EXECUTE IMMEDIATE 'DROP TABLE departments CASCADE CONSTRAINTS'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;
SQL],
    ['Drop app_users', <<<'SQL'
BEGIN EXECUTE IMMEDIATE 'DROP TABLE app_users CASCADE CONSTRAINTS'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;
SQL],
    ['Drop notification_seq', <<<'SQL'
BEGIN EXECUTE IMMEDIATE 'DROP SEQUENCE notification_seq'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -2289 THEN RAISE; END IF; END;
SQL],
    ['Create app_users', <<<'SQL'
CREATE TABLE app_users (
  user_id VARCHAR2(20) PRIMARY KEY,
  full_name VARCHAR2(120) NOT NULL,
  email VARCHAR2(160) NOT NULL UNIQUE,
  password_hash VARCHAR2(255) NOT NULL,
  role VARCHAR2(20) NOT NULL CHECK (role IN ('admin', 'user')),
  department VARCHAR2(120),
  designation VARCHAR2(120),
  phone VARCHAR2(40),
  is_active NUMBER(1) DEFAULT 1 NOT NULL CHECK (is_active IN (0,1)),
  created_at DATE DEFAULT SYSDATE NOT NULL
)
SQL],
    ['Create departments', <<<'SQL'
CREATE TABLE departments (
  department_id VARCHAR2(20) PRIMARY KEY,
  department_name VARCHAR2(120) NOT NULL,
  office_room VARCHAR2(40),
  email VARCHAR2(160),
  university VARCHAR2(160)
)
SQL],
    ['Create students', <<<'SQL'
CREATE TABLE students (
  student_id VARCHAR2(20) PRIMARY KEY,
  student_name VARCHAR2(120) NOT NULL,
  phone VARCHAR2(40),
  dob DATE,
  email VARCHAR2(160),
  department_id VARCHAR2(20),
  CONSTRAINT fk_students_department FOREIGN KEY (department_id) REFERENCES departments(department_id)
)
SQL],
    ['Create supervisors', <<<'SQL'
CREATE TABLE supervisors (
  supervisor_id VARCHAR2(20) PRIMARY KEY,
  supervisor_name VARCHAR2(120) NOT NULL,
  email VARCHAR2(160),
  room_no VARCHAR2(40),
  designation VARCHAR2(120),
  department_id VARCHAR2(20),
  CONSTRAINT fk_supervisors_department FOREIGN KEY (department_id) REFERENCES departments(department_id)
)
SQL],
    ['Create reviewers', <<<'SQL'
CREATE TABLE reviewers (
  reviewer_id VARCHAR2(20) PRIMARY KEY,
  reviewer_name VARCHAR2(120) NOT NULL,
  designation VARCHAR2(120),
  email VARCHAR2(160),
  phone VARCHAR2(40)
)
SQL],
    ['Create projects', <<<'SQL'
CREATE TABLE projects (
  project_id VARCHAR2(20) PRIMARY KEY,
  title VARCHAR2(500) NOT NULL,
  publication_status VARCHAR2(40) DEFAULT 'Pending' NOT NULL,
  submission_date DATE,
  supervisor_id VARCHAR2(20),
  funding VARCHAR2(250),
  publication_info VARCHAR2(250),
  reviewer_id VARCHAR2(20),
  status VARCHAR2(40) DEFAULT 'Pending' NOT NULL,
  progress NUMBER(3) DEFAULT 0 CHECK (progress BETWEEN 0 AND 100),
  notes VARCHAR2(1000),
  created_by VARCHAR2(20),
  created_at DATE DEFAULT SYSDATE NOT NULL,
  CONSTRAINT fk_projects_supervisor FOREIGN KEY (supervisor_id) REFERENCES supervisors(supervisor_id),
  CONSTRAINT fk_projects_reviewer FOREIGN KEY (reviewer_id) REFERENCES reviewers(reviewer_id),
  CONSTRAINT fk_projects_created_by FOREIGN KEY (created_by) REFERENCES app_users(user_id)
)
SQL],
    ['Create project_students', <<<'SQL'
CREATE TABLE project_students (
  project_id VARCHAR2(20) NOT NULL,
  student_id VARCHAR2(20) NOT NULL,
  PRIMARY KEY (project_id, student_id),
  CONSTRAINT fk_ps_project FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE,
  CONSTRAINT fk_ps_student FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
)
SQL],
    ['Create publications', <<<'SQL'
CREATE TABLE publications (
  publication_id VARCHAR2(20) PRIMARY KEY,
  project_id VARCHAR2(20),
  publication_date DATE,
  doi_number VARCHAR2(160),
  publisher VARCHAR2(180),
  reviewer_id VARCHAR2(20),
  review_status VARCHAR2(40) DEFAULT 'Pending',
  comments VARCHAR2(1000),
  CONSTRAINT fk_publications_project FOREIGN KEY (project_id) REFERENCES projects(project_id),
  CONSTRAINT fk_publications_reviewer FOREIGN KEY (reviewer_id) REFERENCES reviewers(reviewer_id)
)
SQL],
    ['Create notification_seq', <<<'SQL'
CREATE SEQUENCE notification_seq START WITH 1001 INCREMENT BY 1 NOCACHE
SQL],
    ['Create notifications', <<<'SQL'
CREATE TABLE notifications (
  notification_id NUMBER PRIMARY KEY,
  user_id VARCHAR2(20),
  title VARCHAR2(180) NOT NULL,
  detail VARCHAR2(1000),
  type VARCHAR2(40),
  created_at DATE DEFAULT SYSDATE NOT NULL,
  CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES app_users(user_id)
)
SQL],
    ['Seed admin user', <<<'SQL'
INSERT INTO app_users (user_id, full_name, email, password_hash, role, department, designation, phone)
VALUES ('USR001', 'Premium Trendz', 'premiumtrendz290@gmail.com', '$2y$12$BSVLV4QKKmt9PQYR.XmdbenOw/lHOYoYh/UhKnMcIsCUSRK4XWIue', 'admin', 'Computer Science', 'Administrator', '+8801700000000')
SQL],
    ['Seed normal user', <<<'SQL'
INSERT INTO app_users (user_id, full_name, email, password_hash, role, department, designation, phone)
VALUES ('USR002', 'Ava Rahman', 'ava.rahman@univ.edu', '$2y$12$BSVLV4QKKmt9PQYR.XmdbenOw/lHOYoYh/UhKnMcIsCUSRK4XWIue', 'user', 'Computer Science', 'Student Researcher', '+8801711223344')
SQL],
    ['Seed departments 1', "INSERT INTO departments VALUES ('DEP101', 'Computer Science', 'A 302', 'cse@northbridge.edu', 'Northbridge University')"],
    ['Seed departments 2', "INSERT INTO departments VALUES ('DEP102', 'Electrical Engineering', 'B 210', 'eee@northbridge.edu', 'Northbridge University')"],
    ['Seed departments 3', "INSERT INTO departments VALUES ('DEP103', 'Biotechnology', 'C 115', 'biotech@greenfield.edu', 'Greenfield University')"],
    ['Seed departments 4', "INSERT INTO departments VALUES ('DEP104', 'Economics', 'D 404', 'economics@eastland.edu', 'Eastland University')"],
    ['Seed departments 5', "INSERT INTO departments VALUES ('DEP105', 'Physics', 'A 118', 'physics@greenfield.edu', 'Greenfield University')"],
    ['Seed students 1', "INSERT INTO students VALUES ('STU2101', 'Ava Rahman', '+880 1711 223344', TO_DATE('2002-03-12','YYYY-MM-DD'), 'ava.rahman@univ.edu', 'DEP101')"],
    ['Seed students 2', "INSERT INTO students VALUES ('STU2102', 'Nabil Karim', '+880 1812 554433', TO_DATE('2001-11-05','YYYY-MM-DD'), 'nabil.karim@univ.edu', 'DEP105')"],
    ['Seed students 3', "INSERT INTO students VALUES ('STU2103', 'Sadia Noor', '+880 1910 332211', TO_DATE('2003-01-18','YYYY-MM-DD'), 'sadia.noor@univ.edu', 'DEP104')"],
    ['Seed supervisors 1', "INSERT INTO supervisors VALUES ('SUP301', 'Dr. Mahin Islam', 'mahin.islam@univ.edu', 'A 503', 'Professor', 'DEP101')"],
    ['Seed supervisors 2', "INSERT INTO supervisors VALUES ('SUP302', 'Dr. Lamiya Sultana', 'lamiya.sultana@univ.edu', 'B 311', 'Associate Professor', 'DEP103')"],
    ['Seed supervisors 3', "INSERT INTO supervisors VALUES ('SUP303', 'Dr. Farhan Ali', 'farhan.ali@univ.edu', 'C 221', 'Assistant Professor', 'DEP105')"],
    ['Seed reviewers 1', "INSERT INTO reviewers VALUES ('REV401', 'Prof. Nadim Hasan', 'External Reviewer', 'nadim.hasan@review.org', '+880 1713 000111')"],
    ['Seed reviewers 2', "INSERT INTO reviewers VALUES ('REV402', 'Dr. Muna Tabassum', 'Senior Reviewer', 'muna.tabassum@review.org', '+880 1714 222333')"],
    ['Seed reviewers 3', "INSERT INTO reviewers VALUES ('REV403', 'Dr. Rafi Chowdhury', 'Peer Reviewer', 'rafi.chowdhury@review.org', '+880 1715 444555')"],
    ['Seed projects 1', "INSERT INTO projects VALUES ('PRJ9001', 'AI Driven Water Quality Monitoring for Urban Campuses', 'Pending', TO_DATE('2026-03-20','YYYY-MM-DD'), 'SUP301', 'Innovation Grant 2026', 'Not linked yet', 'REV401', 'Ongoing', 62, 'Initial implementation complete.', 'USR002', SYSDATE)"],
    ['Seed projects 2', "INSERT INTO projects VALUES ('PRJ9002', 'Blockchain Based Archive for University Research Assets', 'Published', TO_DATE('2026-02-10','YYYY-MM-DD'), 'SUP302', 'ICT Research Fund', 'DOI 10.5555 urb 2026 104', 'REV402', 'Published', 100, 'Publication accepted.', 'USR002', SYSDATE)"],
    ['Seed projects 3', "INSERT INTO projects VALUES ('PRJ9003', 'Smart Energy Prediction in Multi Building University Networks', 'Reviewed', TO_DATE('2026-04-05','YYYY-MM-DD'), 'SUP303', 'Sustainable Campus Fund', 'Under editorial review', 'REV403', 'Reviewed', 84, 'Needs final revision.', 'USR002', SYSDATE)"],
    ['Seed project_students 1', "INSERT INTO project_students VALUES ('PRJ9001', 'STU2101')"],
    ['Seed project_students 2', "INSERT INTO project_students VALUES ('PRJ9001', 'STU2102')"],
    ['Seed project_students 3', "INSERT INTO project_students VALUES ('PRJ9002', 'STU2103')"],
    ['Seed project_students 4', "INSERT INTO project_students VALUES ('PRJ9003', 'STU2101')"],
    ['Seed publications 1', "INSERT INTO publications VALUES ('PUB6001', 'PRJ9001', TO_DATE('2026-03-28','YYYY-MM-DD'), '10.2200/nbu.2026.001', 'Academic Press Asia', 'REV401', 'Approved', 'Strong methodology and clear dataset rationale.')"],
    ['Seed publications 2', "INSERT INTO publications VALUES ('PUB6002', 'PRJ9002', TO_DATE('2026-04-06','YYYY-MM-DD'), '10.2200/nbu.2026.002', 'Global University Journal', 'REV402', 'Pending', 'Awaiting final reviewer notes.')"],
    ['Seed publications 3', "INSERT INTO publications VALUES ('PUB6003', 'PRJ9003', TO_DATE('2026-04-10','YYYY-MM-DD'), '10.2200/nbu.2026.003', 'Research Nexus', 'REV403', 'Rejected', 'Needs stronger baseline comparison and clearer figures.')"],
    ['Seed notifications 1', "INSERT INTO notifications VALUES (notification_seq.NEXTVAL, NULL, 'Project deadline approaching', 'PRJ9003 final revision is due in 2 days.', 'deadline', SYSDATE)"],
    ['Seed notifications 2', "INSERT INTO notifications VALUES (notification_seq.NEXTVAL, NULL, 'Reviewer update received', 'Comments added for PUB6003.', 'review', SYSDATE)"],
    ['Seed notifications 3', "INSERT INTO notifications VALUES (notification_seq.NEXTVAL, NULL, 'Project approved', 'PRJ9002 has been marked as published.', 'approval', SYSDATE)"],
    ['Seed notifications 4', "INSERT INTO notifications VALUES (notification_seq.NEXTVAL, NULL, 'Publication record updated', 'New DOI assigned to PUB6001.', 'publication', SYSDATE)"],
];

$results = [];
$okCount = 0;
$errorCount = 0;

$conn = db_connect();
foreach ($statements as [$label, $sql]) {
    $stid = @oci_parse($conn, $sql);
    if (!$stid) {   
        $e = oci_error($conn);
        $results[] = ['label' => $label, 'ok' => false, 'message' => $e['message'] ?? 'Parse error'];
        $errorCount++;
        continue;
    }
    $ok = @oci_execute($stid, OCI_COMMIT_ON_SUCCESS);
    if ($ok) {
        $results[] = ['label' => $label, 'ok' => true, 'message' => 'OK'];
        $okCount++;
    } else {
        $e = oci_error($stid);
        $results[] = ['label' => $label, 'ok' => false, 'message' => $e['message'] ?? 'Execute error'];
        $errorCount++;
    }
    @oci_free_statement($stid);
}

$testRows = [];
try {
    $stid = oci_parse($conn, 'SELECT table_name FROM user_tables WHERE table_name IN (\'APP_USERS\',\'DEPARTMENTS\',\'STUDENTS\',\'SUPERVISORS\',\'REVIEWERS\',\'PROJECTS\',\'PUBLICATIONS\',\'NOTIFICATIONS\') ORDER BY table_name');
    oci_execute($stid);
    while (($row = oci_fetch_assoc($stid)) !== false) {
        $testRows[] = $row['TABLE_NAME'];
    }
} catch (Throwable $e) {
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Install Database - Research Hub</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8fafc;
            color: #0f172a;
            margin: 0;
            padding: 30px
        }

        .wrap {
            max-width: 1000px;
            margin: auto;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 22px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .08)
        }

        h1 {
            margin-top: 0
        }

        .ok {
            color: #047857
        }

        .err {
            color: #be123c
        }

        .box {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 14px;
            padding: 12px;
            margin: 10px 0
        }

        .btn {
            display: inline-block;
            background: #0369a1;
            color: #fff;
            padding: 10px 16px;
            border-radius: 12px;
            text-decoration: none
        }

        .small {
            color: #64748b;
            font-size: 14px
        }

        pre {
            white-space: pre-wrap;
            word-break: break-word
        }
    </style>
</head>

<body>
    <div class="wrap">
        <h1>Database installation result</h1>
        <p class="small">Connected as <strong><?= htmlspecialchars(DB_USERNAME) ?></strong> using <strong><?= htmlspecialchars(DB_CONNECTION_STRING) ?></strong>.</p>
        <p><strong class="ok"><?= $okCount ?> successful</strong> · <strong class="err"><?= $errorCount ?> errors</strong></p>

        <?php if (count($testRows) >= 8): ?>
            <div class="box ok"><strong>Success:</strong> Required tables were created. Now open <a class="btn" href="index.php">Go to Login</a></div>
            <p class="err"><strong>Important:</strong> Delete this file after successful installation: <code>install_database.php</code></p>
        <?php else: ?>
            <div class="box err"><strong>Not complete:</strong> Some tables are missing. Check the error messages below.</div>
        <?php endif; ?>

        <h2>Created tables found</h2>
        <div class="box">
            <pre><?= htmlspecialchars(implode("\n", $testRows) ?: 'No required tables found') ?></pre>
        </div>

        <h2>Execution log</h2>
        <?php foreach ($results as $r): ?>
            <div class="box">
                <strong class="<?= $r['ok'] ? 'ok' : 'err' ?>"><?= htmlspecialchars($r['ok'] ? 'OK' : 'ERROR') ?></strong>
                — <?= htmlspecialchars($r['label']) ?>
                <?php if (!$r['ok']): ?>
                    <pre><?= htmlspecialchars($r['message']) ?></pre><?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>

</html>