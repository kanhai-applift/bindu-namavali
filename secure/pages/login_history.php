<?php
require_once(__DIR__ . '/../includes/auth.php');
require_login();

require_once(__DIR__ . '/../config/db.php');

$stmt = $mysqli->prepare("
    SELECT user_id, email, ip_address, city, state, login_time
    FROM user_access_logs
    WHERE user_id=?
    ORDER BY login_time DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$count = 1;
?>

<div class="container-fluid mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">User Access Logs</h4>

        <div>
            <a href="<?= baseUrl('dashboard/') ?>" class="btn btn-secondary">
                <i class="bi bi-chevron-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <table id="logsTable" class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Sr.No.</th>
                <th>User ID</th>
                <th>Email</th>
                <th>IP Address</th>
                <th>City</th>
                <th>State</th>
                <th>Login Time</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= e($count++); ?></td>
                    <td><?= e($row['user_id']) ?></td>
                    <td><?= e($row['email']) ?></td>
                    <td><?= e($row['ip_address']) ?></td>
                    <td><?= e($row['city']) ?></td>
                    <td><?= e($row['state']) ?></td>
                    <td><?= e($row['login_time']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>



<?php
$page_scripts = [
    'https://code.jquery.com/jquery-3.7.1.min.js',
    'https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.3.6/b-3.2.6/datatables.min.js',
];

$inline_scripts = <<<JS
  $(document).ready(function () {
      $('#logsTable').DataTable({
          pageLength: 50,
          order: [[0, 'desc']],
          lengthMenu: [10, 25, 50, 100],
          responsive: true
      });
  });
JS;
?>

<link href="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.3.6/b-3.2.6/datatables.min.css" rel="stylesheet" integrity="sha384-/s06xzAoMAWg73g08IlNMMxARiinD/HpBvcIRiGN7lMvv2JzijtXYansTh6zkuaY" crossorigin="anonymous">
<?php include "includes/footer.php"; ?>