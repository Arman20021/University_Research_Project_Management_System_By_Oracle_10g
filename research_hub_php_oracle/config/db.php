<?php
// Oracle 10g database connection settings.
// Update these values after creating the Oracle user/schema from database/oracle10g_schema.sql.
// define('DB_USERNAME', 'RESEARCH_HUB');
// define('DB_PASSWORD', 'research123');
// // Oracle 10g Easy Connect format: //host:port/service_name or host/service_name.
// // For Oracle 10g XE on the same machine, localhost/XE usually works.
// define('DB_CONNECTION_STRING', 'localhost/XE');



define('DB_USERNAME', 'scott');
define('DB_PASSWORD', 'tiger');
define('DB_CONNECTION_STRING', 'localhost/XE');
define('DB_CHARSET', 'AL32UTF8');

function db_connect() {
    static $conn = null;
    if ($conn) {
        return $conn;
    }
    $conn = @oci_connect(DB_USERNAME, DB_PASSWORD, DB_CONNECTION_STRING, DB_CHARSET);
    if (!$conn) {
        $e = oci_error();
        http_response_code(500);
        echo '<div style="font-family:Arial;padding:24px;color:#0f172a">';
        echo '<h2>Database connection failed</h2>';
        echo '<p>Check config/db.php credentials, Oracle listener, service name, and OCI8 extension.</p>';
        echo '<pre style="background:#f8fafc;border:1px solid #e2e8f0;padding:12px;border-radius:12px">' . htmlspecialchars($e['message'] ?? 'Unknown OCI error', ENT_QUOTES, 'UTF-8') . '</pre>';
        echo '</div>';
        exit;
    }
    return $conn;
}

function db_parse($sql) {
    $stid = oci_parse(db_connect(), $sql);
    if (!$stid) {
        $e = oci_error(db_connect());
        throw new Exception($e['message']);
    }
    return $stid;
}

function db_bind($stid, $params) {
    $bound = [];
    foreach ($params as $key => $value) {
        $name = ':' . ltrim($key, ':');
        $bound[$name] = $value;
        oci_bind_by_name($stid, $name, $bound[$name], -1);
    }
    return $bound;
}

function db_execute($sql, $params = [], $commit = true) {
    $stid = db_parse($sql);
    $bound = db_bind($stid, $params);
    $mode = $commit ? OCI_COMMIT_ON_SUCCESS : OCI_NO_AUTO_COMMIT;
    $ok = @oci_execute($stid, $mode);
    if (!$ok) {
        $e = oci_error($stid);
        throw new Exception($e['message']);
    }
    return $stid;
}

function db_fetch_all($sql, $params = []) {
    $stid = db_execute($sql, $params, false);
    $rows = [];
    while (($row = oci_fetch_assoc($stid)) !== false) {
        $rows[] = array_change_key_case($row, CASE_LOWER);
    }
    oci_free_statement($stid);
    return $rows;
}

function db_fetch_one($sql, $params = []) {
    $rows = db_fetch_all($sql, $params);
    return $rows ? $rows[0] : null;
}

function db_scalar($sql, $params = []) {
    $row = db_fetch_one($sql, $params);
    if (!$row) return 0;
    $first = array_values($row)[0];
    return $first === null ? 0 : $first;
}
