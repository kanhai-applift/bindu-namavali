<?php
session_start();
include('includes/config.php');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

header('Content-Type: application/json');

$response = ["success" => false, "values" => []];

if (isset($_GET['post_name']) && $_GET['post_name'] != "") {
    $user_id = $_SESSION['id']; // user session id
    $post_name = $_GET['post_name'];

    // ✅ dynamic table name: notebook_userid_post
    $table = "notebook_" . $user_id . "_" . preg_replace('/\s+/', '_', strtolower($post_name));

    // ✅ Categories in correct sequence
    $categories = [
        "अनुसूचित जाती","अनुसूचित जमाती","विमुक्त जमाती (अ)",
        "भटक्या जमाती (ब)","भटक्या जमाती (क)","भटक्या जमाती (ड)",
        "विशेष मागास प्रवर्ग","इतर मागास प्रवर्ग",
        "सामाजिक आणि शैक्षणिक मागास वर्ग","आर्थिक दृष्ट्या दुर्बल घटक","अराखीव"
    ];

    $values = [];
    foreach ($categories as $cat) {
        $sql = "SELECT COUNT(*) as cnt FROM `$table` WHERE karyarat=1 AND bindu_namavli=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s",$cat);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $values[] = intval($res['cnt']);
    }

    $response = ["success" => true, "values" => $values];
}

echo json_encode($response);
