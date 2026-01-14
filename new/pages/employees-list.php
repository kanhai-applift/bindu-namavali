<h2 class="text-center mt-5">Saved Entries</h2>

<?php

$sql = "SELECT * FROM employees WHERE designation_id = ? AND organization_id = ?";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('ii', $designationId, $orgId);
$stmt->execute();
$result = $stmt->get_result();
$count = 1;
?>

<div class="border p-2 ">

  <table id="employeesTable" class="table table-bordered table-striped sfs-3">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>बिंदू क्रमांक</th>
        <th>बिंदूचा प्रवर्ग</th>
        <th>कर्मचाऱ्याचे नाव</th>
        <th>कर्मचारी जात व प्रवर्ग</th>
        <th>नियुक्ती दिनांक</th>
        <th>जन्म दिनांक</th>
        <th>सेवा निवृत्ती दिनांक</th>
        <th>जात प्रमाणपत्र क्रमांक</th>
        <th>प्रधीकार्यांचे पदनाव</th>
        <th>जात वैधता प्रमानपत्र क्रमांक</th>
        <th>वैधता समितीचे नाव</th>
        <th>कार्यरत <br> ✅</th>
        <th>शेरा</th>
        <th>PDF</th>
        <th class="no-print">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= e($count++); ?></td>
          <td><?= e($row['bindu_no']) ?></td>
          <td><?= e($row['bindu_category']) ?></td>
          <td><?= e($row['employee_name']) ?></td>
          <td><?= e($row['employee_caste']) . ' - ' . e($row['employee_category']) ?></td>
          <td><?= formatDate(e($row['date_of_appointment'])) ?></td>
          <td><?= formatDate(e($row['date_of_birth'])) ?></td>
          <td><?= formatDate(e($row['date_of_retirement'])) ?></td>
          <td><?= e($row['caste_certificate_no']) ?></td>
          <td><?= e($row['caste_cert_authority']) ?></td>
          <td><?= e($row['caste_validity_certificate_no']) ?></td>
          <td><?= e($row['validation_committee_name']) ?></td>
          <td><?= e($row['working'] ? "✅" : "❌") ?></td>
          <td><?= e($row['remarks']) ?></td>
          <td>
            <?php if(!empty(e($row['pdf']))): ?>
            <a href="<?= baseUrl(e($row['pdf'])) ?>" target="_blank" class="btn btn-outline-primary p-1">
              <i class="bi bi-filetype-pdf"></i>
            </a>
            <?php else :?>
              <span class="btn btn-outline-light">
                <i class="bi bi-ban text-muted"></i>
              </span>
            <?php endif;?>
          </td>
          <td>
            <a href="<?= baseUrl('employees-edit/' . $hashedDesignationId . '/' . $hashids->encode($row['id'])) ?>"
              class="btn btn-sm btn-warning">
              <i class="bi bi-pencil"></i>
            </a>

          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>