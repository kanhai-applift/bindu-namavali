<?php
require_once 'api-helper.php';

/* 3️⃣ Authorization check */
if (!in_array($_SESSION['role'], ['admin', 'superadmin'], true)) {
    http_response_code(403);
    respond('error', 'Unauthorized');
}

if (!isset($_POST['id'], $_POST['designation_name'], $_POST['description'])) {
    respond('error', 'Missing fields');
}
$hash = trim($_POST['id']);
/* 2️⃣ Decode Hashid → numeric ID */
$decoded = $hashids->decode($hash);

if (empty($decoded)) {
    respond('error','Invalid designation');
}

$orgId = (int) $_SESSION['user_id'];
$id    = (int) $decoded[0];
$name  = strip_tags(trim($_POST['designation_name']));
$desc  = strip_tags(trim($_POST['description']));

if ($id <= 0 || $name === '' || mb_strlen($name) > 150) {
    respond('error', 'Invalid input');
}

try {

    $stmt = $mysqli->prepare("
        UPDATE designations
        SET designation_name = ?, description = ?
        WHERE id = ? AND organization_id = ?
    ");

    if (!$stmt) {
        throw new Exception($mysqli->error);
    }

    $stmt->bind_param(
        'ssii',   // s = string, s = string, i = int, i = int
        $name,
        $desc,
        $id,
        $orgId
    );

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    /* rowCount() equivalent in mysqli */
    if ($stmt->affected_rows === 0) {
        $stmt->close();
        respond('error', 'Update failed or unauthorized');
    }

    $stmt->close();

    respond('success', 'Designation updated');

} catch (Exception $e) {

    /* Log real error internally */
    error_log($e->getMessage());

    /* Safe client response */
    respond('error', 'Server error');
}
