<?php
function department_options() {
    return db_fetch_all('SELECT department_id, department_name FROM departments ORDER BY department_name');
}
function supervisor_options() {
    return db_fetch_all('SELECT supervisor_id, supervisor_name FROM supervisors ORDER BY supervisor_name');
}
function reviewer_options() {
    return db_fetch_all('SELECT reviewer_id, reviewer_name FROM reviewers ORDER BY reviewer_name');
}
function student_options() {
    return db_fetch_all('SELECT student_id, student_name FROM students ORDER BY student_name');
}
function project_options() {
    return db_fetch_all('SELECT project_id, title FROM projects ORDER BY title');
}
function project_student_ids($projectId) {
    $rows = db_fetch_all('SELECT student_id FROM project_students WHERE project_id = :id', ['id' => $projectId]);
    return array_map(function($r) { return $r['student_id']; }, $rows);
}
function project_student_names($projectId) {
    $rows = db_fetch_all('SELECT s.student_name FROM project_students ps JOIN students s ON s.student_id = ps.student_id WHERE ps.project_id = :id ORDER BY s.student_name', ['id' => $projectId]);
    return implode(', ', array_map(function($r) { return $r['student_name']; }, $rows));
}
function replace_project_students($projectId, $studentIds) {
    db_execute('DELETE FROM project_students WHERE project_id = :id', ['id' => $projectId]);
    foreach ($studentIds as $studentId) {
        db_execute('INSERT INTO project_students (project_id, student_id) VALUES (:pid, :sid)', ['pid' => $projectId, 'sid' => $studentId]);
    }
}
