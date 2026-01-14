<?php
require_once 'api-helper.php';

/* 3️⃣ Authorization check */
if (!in_array($_SESSION['role'], ['admin', 'superadmin'], true)) {
  http_response_code(403);
  respond('error', 'Unauthorized');
}


if (!isset($_POST['designation_hash'])) {
  respond('error', 'Missing fields');
}

$hash = trim($_POST['designation_hash']);
/* 2️⃣ Decode Hashid → numeric ID */
$decoded = $hashids->decode($hash);

$designationId = (int)$decoded[0];

if (empty($decoded)) {
  respond('error', 'Invalid designation');
}

$response = ["success" => false, "values" => []];

// ✅ Categories in correct sequence
$categories = [
  "अनुसूचित जाती",
  "अनुसूचित जमाती",
  "विमुक्त जमाती (अ)",
  "भटक्या जमाती (ब)",
  "भटक्या जमाती (क)",
  "भटक्या जमाती (ड)",
  "विशेष मागास प्रवर्ग",
  "इतर मागास प्रवर्ग",
  "सामाजिक आणि शैक्षणिक मागास वर्ग",
  "आर्थिक दृष्ट्या दुर्बल घटक",
  "अराखीव"
];

$values = [];
foreach ($categories as $cat) {
  $sql = "SELECT COUNT(*) as cnt FROM employees WHERE working=1 AND designation_id = ? AND bindu_category=?";
  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param("is", $designationId, $cat);
  $stmt->execute();
  $res = $stmt->get_result()->fetch_assoc();
  $values[] = intval($res['cnt']);
}

$response = ["success" => true, "values" => $values];

echo json_encode($response);