<?php
require_once 'api-helper.php';

/* ===============================
   2. Authentication & Authorization
================================ */
if (
    !isset($_SESSION['user_id'], $_SESSION['role']) ||
    $_SESSION['role'] !== 'superadmin'
) {
    http_response_code(403);
    respond('error', 'Unauthorized');
}

$approvedBy = (int) $_SESSION['user_id'];

/* ===============================
   3. Input Validation
================================ */

$postHash = trim($_POST['post_hash'] ?? '');
$remarks  = trim($_POST['remarks'] ?? '');

if ($postHash === '') {
    respond('error', 'Invalid post reference');
}

if (mb_strlen($remarks) > 2000) {
    respond('error', 'Remarks too long');
}

$postId = (int) $postHash;// it is numerical not hashed

/* ===============================
   5. Secure Update (Transactional)
================================ */

$mysqli->begin_transaction();

try {

    $stmt = $mysqli->prepare("
        UPDATE organisations_post
        SET
            approved = 1,
            approval_remarks = ?,
            approved_by = ?,
            approved_at = NOW()
        WHERE post_hash = ?
          AND approved = 0
    ");

    $stmt->bind_param('sii', $remarks, $approvedBy, $postId);
    $stmt->execute();

    if ($stmt->affected_rows !== 1) {
        throw new Exception('Post already approved or not found');
    }

    $stmt->close();
    $mysqli->commit();

    respond('success', 'Post approved successfully');

} catch (Throwable $e) {

    $mysqli->rollback();
    error_log($e->getMessage());

    respond('error', 'Unable to approve post');
}
