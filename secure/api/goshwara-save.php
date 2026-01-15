<?php
require_once __DIR__ . "/api-helper.php";


/* 3️⃣ Authorization check */
if (!in_array($_SESSION['role'], ['admin'], true)) {
  http_response_code(403);
  respond('error', 'Unauthorized');
}

/* 4️⃣ Required fields check */
$required = [
  'designation_hash',
  'designation_name',
  'data',
];

foreach ($required as $field) {
  if (empty($_POST[$field])) {
    respond('error', "Missing field: {$field}");
  }
}

$designationName = trim($_POST['designation_name'] ?? '');
$data     = $_POST['data'] ?? [];
$remark   = trim($_POST['remark'] ?? '');

if ($designationName === '' || mb_strlen($designationName) > 100) {
    respond('error', 'Invalid Designation name');
}

if (!is_array($data) || empty($data)) {
    respond('error', 'Invalid data payload');
}

if (mb_strlen($remark) > GOSHWARA_REMARK_LENGTH) {
    respond('error', 'Remark too long');
}

/* 5️⃣ Session & org validation */
$orgId = (int) $_SESSION['user_id'];
if ($orgId <= 0) {
  respond('error', 'Invalid session');
}

/* 6️⃣ Decode and validate designation */
$hash = trim($_POST['designation_hash']);
$decoded = $hashids->decode($hash);

$designationId = (int)$decoded[0];

/* ===============================
3. Transaction Start
================================ */

$mysqli->begin_transaction();

try {

    /* ===============================
    4. Soft Delete (Prepared)
    ================================ */
    $delStmt = $mysqli->prepare(
        "UPDATE goshwara SET is_deleted = 1 WHERE organization_id = ? AND designation_id = ?"
    );
    $delStmt->bind_param("is", $orgId, $designationId);
    $delStmt->execute();
    $delStmt->close();

    $sql = "SELECT * FROM goshwara_categories";
    $stmt = $mysqli->prepare($sql);
    $stmt->execute();
    $res = $stmt->get_result();
    $gcat = [];
    while ($row = $res->fetch_assoc()):
        // echo $row['id']."-".$row['category_name'];
        $gcat[$row['id']] = $row['category_name'];
    endwhile;

    /* ===============================
    5. Secure Insert
    ================================ */
    $insertStmt = $mysqli->prepare("
        INSERT INTO goshwara (
            organization_id, designation_id, designation_name, g_category_id, g_category,
            col0, col1, col2, col3, col4, col5,
            col6, col7, col8, col9, col10, total
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");

    foreach ($data as $categoryId => $cols) {

        if (!is_array($cols)) {
            throw new Exception('Invalid column structure');
        }

        // Normalize numeric inputs safely
        $values = [];
        for ($i = 0; $i <= 10; $i++) {
            $values[$i] = isset($cols["col{$i}"]) ? (int)$cols["col{$i}"] : 0;
        }

        $total = isset($cols['total']) ? (int)$cols['total'] : 0;

        $insertStmt->bind_param(
            "iisisiiiiiiiiiiii",
            $orgId,
            $designationId,
            $designationName,
            $categoryId,
            $gcat[$categoryId], // category name
            $values[0],
            $values[1],
            $values[2],
            $values[3],
            $values[4],
            $values[5],
            $values[6],
            $values[7],
            $values[8],
            $values[9],
            $values[10],
            $total
        );

        $insertStmt->execute();
    }

    $insertStmt->close();

    /* ===============================
    6. Commit Transaction
    ================================ */
    $mysqli->commit();

    respond('success', 'Goshwara saved successfully.');
} catch (Throwable $e) {

    $mysqli->rollback();
    error_log($e->getMessage());

    respond('error', 'Failed to save Goshwara');
}
