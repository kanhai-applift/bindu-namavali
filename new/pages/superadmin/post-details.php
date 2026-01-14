<?php

$postHash = (int)trim($segments[1]); // non-hashed it integer

// ✅ Fetch organisations post info
$ret = "SELECT op.*, u.office_name, u.head_name, u.email 
      FROM organisations_post op
      JOIN users u ON u.id = op.organization_id
      WHERE op.post_hash=?";
$stmt = $mysqli->prepare($ret);
$stmt->bind_param('i', $postHash);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_object();


// ✅ Fetch data from goshwara table
$stmt2 = $mysqli->prepare("SELECT * FROM goshwara WHERE is_deleted=0 AND organization_id=? AND designation_id=? ORDER BY g_category_id ASC");
$stmt2->bind_param('ii', $row->organization_id, $row->designation_id);
$stmt2->execute();
$posts = $stmt2->get_result();


// Store all rows in an array for processing
$post_data = [];
while ($p = $posts->fetch_assoc()) {
  $post_data[] = $p;
}

// Separate the extra row (अतिरिक्त_पदे) from other rows
$main_rows = [];
$extra_row = null;

foreach ($post_data as $p) {
  if ($p['g_category'] === 'अतिरिक्त_पदे') {
    $extra_row = $p;
  } else {
    $main_rows[] = $p;
  }
}
?>

<div class="container-fluid">
  <div class="row" id="print">
    <div class="col-md-12">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="page-title" style="margin-top:3%">प्रकरण क्रमांक #<?php echo $row->post_hash; ?> Details</h3>

        <div>
          <button class="btn btn-secondary" onclick="window.history.back()">
            <i class="bi bi-chevron-left"></i>
            मागे जा (Go Back)
          </button>
        </div>
      </div>

      <!-- ✅ User Info -->
      <div class="card card-primary">
        <div class="card-header">User Information</div>
        <div class="card-body">
          <p><strong>Name:</strong> <?php echo trim($row->head_name); ?></p>
          <p><strong>Email:</strong> <?php echo $row->email; ?></p>
          <p><strong>प्रकरण क्रमांक:</strong> <?php echo $row->post_hash; ?></p>
          <p><strong>प्रकरण प्रकार:</strong> <?php echo $row->designation_name; ?></p>
          <p><strong>Status:</strong> <span class="badge text-bg-<?= $row->approved ? "success" : "info"; ?>"><?= $row->approved ? "Approved" : "New"; ?></span></p>
          <p><strong>Registration Date:</strong> <?php echo $row->created_at; ?></p>
          <p><strong>प्रकरण माहिती:</strong> <?php echo $row->remarks; ?></p>
          <p><strong>File:</strong>
          <ol>
            <?php
            $files = [
              'सेवा प्रवेश नियम' => $row->service_rules_pdf ?? null,
              'आकृतीबंध' => $row->layout_pdf ?? null,
              'गोषवारा' => $row->goshwara_pdf ?? null
            ];
            foreach ($files as $file_label => $file_name):
              echo '<li>';
              if ($file_name) {
                echo "<strong> $file_label :</strong> <a href='" . baseUrl($file_name) . "' class='file-link' target='_blank'>View</a> ";
              } else {
                echo "<strong> $file_label :</strong> NA";
              }
              echo '</li>';
            endforeach;
            ?>
          </ol>
          </p>
        </div>
      </div>

      <!-- ✅ User Post Table -->
      <?php if (count($main_rows) > 0): ?>
        <div class="card card-primary goshwara-form my-5">
          <div class="card-header">User Post Table Data</div>
          <div class="card-body">
            <table class="table table-bordered">
              <tr>
                <th>प्रकार / Category</th>
                <th>अनुसूचित जाती</th>
                <th>अनुसूचित जमाती</th>
                <th>विमुक्त जमाती (अ)</th>
                <th>भटक्या जमाती (ब)</th>
                <th>भटक्या जमाती (क)</th>
                <th>भटक्या जमाती (ड)</th>
                <th>विशेष मागास प्रवर्ग</th>
                <th>इतर मागास प्रवर्ग</th>
                <th>सामाजिक आणि शैक्षणिक मागास वर्ग</th>
                <th>आर्थिक दृष्ट्या दुर्बल घटक</th>
                <th>अराखीव</th>
                <th>Total</th>
              </tr>

              <!-- ✅ Fixed Percentage Row -->
              <tr>
                <td>प्रतिशत (%)</td>
                <td>13%</td>
                <td>7%</td>
                <td>3%</td>
                <td>2.5%</td>
                <td>3.5%</td>
                <td>2%</td>
                <td>2%</td>
                <td>19%</td>
                <td>10%</td>
                <td>10%</td>
                <td>28%</td>
                <td>100%</td>
              </tr>

              <!-- ✅ Dynamic Rows (excluding extra row) -->
              <?php
              $show_extra_after = false;
              foreach ($main_rows as $p):
                // Check if this is the row after which we should show the extra row
                if ($p['g_category'] === 'एकूण_भरायची_पदे') {
                  $show_extra_after = true;
                }
              ?>
                <tr>
                  <td><?php echo $p['g_category']; ?></td>
                  <td><?php echo $p['col0']; ?></td>
                  <td><?php echo $p['col1']; ?></td>
                  <td><?php echo $p['col2']; ?></td>
                  <td><?php echo $p['col3']; ?></td>
                  <td><?php echo $p['col4']; ?></td>
                  <td><?php echo $p['col5']; ?></td>
                  <td><?php echo $p['col6']; ?></td>
                  <td><?php echo $p['col7']; ?></td>
                  <td><?php echo $p['col8']; ?></td>
                  <td><?php echo $p['col9']; ?></td>
                  <td><?php echo $p['col10']; ?></td>
                  <td><?php echo $p['total']; ?></td>
                </tr>

                <!-- ✅ Show extra row after एकूण_भरायची_पदे -->
                <?php if ($show_extra_after && $extra_row): ?>
                  <tr class="extra-row">
                    <td><?php echo $extra_row['category']; ?></td>
                    <td><?php echo $extra_row['col0']; ?></td>
                    <td><?php echo $extra_row['col1']; ?></td>
                    <td><?php echo $extra_row['col2']; ?></td>
                    <td><?php echo $extra_row['col3']; ?></td>
                    <td><?php echo $extra_row['col4']; ?></td>
                    <td><?php echo $extra_row['col5']; ?></td>
                    <td><?php echo $extra_row['col6']; ?></td>
                    <td><?php echo $extra_row['col7']; ?></td>
                    <td><?php echo $extra_row['col8']; ?></td>
                    <td><?php echo $extra_row['col9']; ?></td>
                    <td><?php echo $extra_row['col10']; ?></td>
                    <td><?php echo $extra_row['total']; ?></td>
                  </tr>
              <?php
                  $show_extra_after = false; // Reset flag after showing
                endif;
              endforeach;
              ?>
            </table>

            <?php if ($extra_row && !$show_extra_after): ?>
              <div class="alert alert-info">
                <strong>Note:</strong>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- ✅ Action Buttons -->
      <div class="text-center" style="margin:20px 0;">
        <?php if ($row->approved === 0): ?>
          <!-- Button trigger modal -->
          <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#postApprovalModal">
            Approve
          </button>
        <?php endif; ?>
        <button class="btn btn-secondary" onclick="window.history.back()">मागे जा (Go Back)</button>
      </div>
    </div>
  </div>

</div>


<!-- ✅ Action Modal -->
<div class="modal fade" id="postApprovalModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Approve Post?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form id="approvePostForm">
        <?= csrfField() ?>
        <div class="modal-body">

        <div>
          <p><strong>प्रकरण क्रमांक:</strong> <?php echo $row->post_hash; ?></p>
          <p><strong>प्रकरण प्रकार:</strong> <?php echo $row->designation_name; ?></p>
          <p><strong>Status:</strong> <span class="status-approved"><?php echo $row->approved ? "Approved" : "New"; ?></span></p>
          <p><strong>Registration Date:</strong> <?php echo $row->created_at; ?></p>
        </div>

          <input type="hidden" name="post_hash" value="<?= $row->post_hash ?>" id="approvePostHash">

          <div class="mb-3">
            <label class="form-label">Remarks</label>
            <textarea
              class="form-control"
              name="remarks"
              rows="3"
              required></textarea>
          </div>

          <div id="approveAlert"></div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Cancel
          </button>
          <button type="submit" class="btn btn-success">
            Approve
          </button>
        </div>
      </form>

    </div>
  </div>
</div>


<?php 
$page_scripts = [
  'https://code.jquery.com/jquery-3.7.1.min.js',
];

$inline_scripts = <<<JS
$(function () {

 // Open modal
  $(document).on('click', '.approvePostBtn', function () {
    $('#approvePostHash').val($(this).data('hash'));
    $('#approvePostForm')[0].reset();
    $('#approveAlert').html('');
    modal.show();
  });

 // Submit approval
  $('#approvePostForm').on('submit', function (e) {
    e.preventDefault();

    $.ajax({
      url: baseUrl('api/approve-post'),
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',

      success: function (res) {
        if (res.status === 'success') {
          $('#approveAlert').html(
            '<div class="alert alert-success">'+res.message+'</div>'
          );

          setTimeout(() => {
            modal.hide();
            $('#organisationPostTable').DataTable().ajax.reload(null, false);
          }, 800);

        } else {
          $('#approveAlert').html(
            '<div class="alert alert-danger">'+res.message+'</div>'
          );
        }
      },

      error: function () {
        $('#approveAlert').html(
          `<div class="alert alert-danger">Server error occurred</div>`
        );
      }
    });
  });
});
JS;
?>