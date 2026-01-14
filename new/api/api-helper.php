<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/bootstrap.php";
require __DIR__ . "/../includes/csrf.php";

header('Content-Type: application/json');

/* Helper */
function respond($status, $message, $extra = [])
{
  echo json_encode(array_merge([
    'status' => $status,
    'message' => $message
  ], $extra));
  exit;
}

// Function to convert DD/MM/YYYY to MySQL date format
function convertToMySQLDate($ddmmyyyy) {
    if (empty($ddmmyyyy)) {
        return null;
    }
    $date = DateTime::createFromFormat('d/m/Y', $ddmmyyyy);
    return $date ? $date->format('Y-m-d') : null;
}

function generateDatabaseId()
{
  // 1. Current Time (millisecond precision) - ensures order
  $time = (int)(microtime(true) * 1000);

  // 2. High-entropy Randomness (last 22 bits) - ensures uniqueness
  // random_int is cryptographically secure compared to mt_rand
  $random = random_int(0, 0x3FFFFF);

  // 3. Bit-shift to combine: [42 bits Time][22 bits Random]
  // Result is a 64-bit unsigned integer
  $id = ($time << 22) | $random;

  return (string)$id;
}

/* 1️⃣ Enforce POST method */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode([
    'status' => 'error',
    'message' => 'Invalid request'
  ]);
  exit;
}

/* 2️⃣ CSRF protection */
if (!csrf_validate($_POST['csrf_token'] ?? '')) {
  echo json_encode([
    "status" => "error",
    "message" => "Invalid CSRF token: ".$_POST['csrf_token']
  ]);
  exit;
}
