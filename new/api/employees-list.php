<?php
require_once 'api-helper.php';

/* 3️⃣ Authorization check */
if (!in_array($_SESSION['role'], ['admin', 'superadmin'], true)) {
  http_response_code(403);
  respond('error', 'Unauthorized');
}

/* 4️⃣ Required fields check */
if (!isset($_POST['designation_id'])) {
  respond('error', 'Missing fields');
}

// Decode designation_id hash
$decoded = $hashids->decode($_POST['designation_id'] ?? '');

if (empty($decoded)) {
  echo json_encode([
    "status" => 'error',
    "message" => 'Invalid designation',
    "draw" => 1,
    "recordsTotal" => 0,
    "recordsFiltered" => 0,
    "data" => []
  ]);
  exit;
}

$designationId = (int)$decoded[0];
$orgId = $_SESSION['user_id'];

// DataTables parameters
$draw   = (int)$_POST['draw'];
$start  = (int)$_POST['start'];
$length = (int)$_POST['length'];
$search = $_POST['search']['value'] ?? '';

// Base WHERE
$where = " WHERE e.organization_id = ?
          AND e.designation_id = ? ";

$params = [$orgId, $designationId];
$types  = "ii";

// Search
if ($search !== '') {
  $where .= " AND (e.employee_name LIKE ?
                OR e.bindu_no LIKE ?) ";
  $params[] = "%$search%";
  $params[] = "%$search%";
  $types   .= "ss";
}

// Total records
$sqlTotal = "SELECT COUNT(*) total
            FROM employees e
            $where";

$stmt = $mysqli->prepare($sqlTotal);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Data query
$sqlData = "SELECT e.id, e.bindu_no, e.employee_name,
              e.employee_category,
              e.date_of_appointment,
              e.is_vacant
            FROM employees e
            $where
            ORDER BY e.bindu_no ASC
            LIMIT ?, ?";

$params[] = $start;
$params[] = $length;
$types   .= "ii";

$stmt = $mysqli->prepare($sqlData);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
$counter = $start + 1;

while ($row = $result->fetch_assoc()) {

  $status = $row['is_vacant']
    ? '<span class="badge bg-warning">Vacant</span>'
    : '<span class="badge bg-success">Working</span>';

  $data[] = [
    $counter++,
    e($row['bindu_no']),
    e($row['employee_name'] ?? '—'),
    e($row['employee_category']),
    $row['date_of_appointment']
      ? date('d-m-Y', strtotime($row['date_of_appointment']))
      : '—',
    $status,
    '<a href="' . baseUrl('employee-edit/' . $row['id']) . '"
            class="btn btn-sm btn-warning">Edit</a>'
  ];
}

$stmt->close();

echo json_encode([
  "draw" => $draw,
  "recordsTotal" => $total,
  "recordsFiltered" => $total,
  "data" => $data
]);
exit;