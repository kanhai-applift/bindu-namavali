<?php
$orgId = $_SESSION['user_id'];

// Fetch organisationPosts
$sql = "SELECT *
        FROM organisations_post
        WHERE organization_id = ?
        ORDER BY id DESC";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $orgId);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container-fluid">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Registered Post</h4>
    <a href="<?= baseUrl('organisations-post-add') ?>" class="btn btn-primary">
      <i class="bi bi-plus-lg"></i> Register New Post
    </a>
  </div>

  <div id="alertBox"></div>

  <table id="organisationPostTable" class="table table-sm table-bordered table-striped">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Post ID</th>
        <th>Post Type</th>
        <th>Status</th>
        <th>Registration Date</th>
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