<?php

require_superadmin();

$orgId = $_SESSION['user_id'];

if (empty($segments[1])) {
  exit('Invalid Post type');
}

if ($segments[1] === 'approved') {
  $type = 1;
} else if ($segments[1] === 'new') {
  $type = 0;
}


// Fetch organisationPosts
$sql = "SELECT *
        FROM organisations_post
        WHERE approved = ?
        ORDER BY id DESC";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $type);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container-fluid">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Approved Registered Post</h4>
    <div>
      <a href="<?= baseUrl('dashboard/') ?>" class="btn btn-secondary">
        <i class="bi bi-chevron-left"></i> Back to Dashboard
      </a>
    </div>
  </div>

  <div id="alertBox"></div>

  <table id="organisationPostTable" class="table table-sm table-bordered table-striped">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>प्रकरण क्रमांक</th>
        <th>पद प्रकार <small class="sfs-2">(Post Type)</small></th>
        <th>स्थिती <small class="sfs-2">(Status)</small></th>
        <th>नोंदणी तारीख <small class="sfs-2">(Registration Date)</small></th>
        <th>मंजुरीची तारीख <small class="sfs-2">(Approval Date)</small></th>
        <th>Remarks</th>
        <th>Action</th>
      </tr>
    </thead>

    <tbody>
      <?php $i = 1; ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= e($row['post_hash']) ?></td>
          <td><?= e($row['designation_name']) ?> </td>
          <td><?= e($row['approved']) ? 'Approved' : 'New' ?> </td>
          <td><?= date('d-m-Y H:i:s', strtotime($row['created_at'])) ?></td>
          <td><?= date('d-m-Y H:i:s', strtotime($row['approved_at'])) ?></td>
          <td><?= e($row['approval_remarks']) ?></td>
          <td>
            <a href="<?= baseUrl('organisations-post-view/' . e($row['post_hash'])) ?>"
              class="btn btn-sm btn-primary">
              <i class="bi bi-display"></i>
            </a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

</div>