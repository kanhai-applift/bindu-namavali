<?php
require_once(__DIR__ . '/../includes/auth.php');
require_login();

require_once(__DIR__ . '/../config/db.php');

if (empty($segments[1])) {
  exit('Invalid designation');
}

$hashedDesignationId = $segments[1];

// Decode designation ID
$decoded = $hashids->decode($hashedDesignationId);

if (empty($decoded)) {
  exit('Invalid designation');
}

$designationId = (int)$decoded[0];
$orgId = $_SESSION['user_id'];

// Verify designation belongs to this organization
$sql = "SELECT designation_name
        FROM designations
        WHERE id = ? AND organization_id = ?";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('ii', $designationId, $orgId);
$stmt->execute();
$res = $stmt->get_result();
$designation = $res->fetch_assoc();
$stmt->close();

if (!$designation) {
  exit('Unauthorized access');
}

// fetch goshwara categories
$sql = "SELECT * FROM goshwara_categories";
$stmt = $mysqli->prepare($sql);
$stmt->execute();
$res = $stmt->get_result();

?>

<div class="container-fluid border goshwara-form">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">पदांची माहिती नोंदवा</h2>
    <a href="<?= baseUrl('employees-add/' . $hashedDesignationId) ?>" class="btn btn-secondary">
      <i class="bi bi-chevron-left"></i> Back to <?= e($designation['designation_name']) ?> List
    </a>
  </div>


  <?php if (!empty($designation['designation_name'])): ?>
    <div class="post-name-info">
      <strong>पदाचे नाव:</strong> <?= e($designation['designation_name']) ?>
      <br><small>हा पदावरील माहिती भरण्यासाठी तयार आहात.</small>
    </div>
  <?php endif; ?>


  <form method="POST" id="goshwaraForm" class="form" novalidate>
    <?= csrfField() ?>
    <input type="hidden" id="post_name" name="designation_hash" required
      value="<?= e($hashedDesignationId) ?>">

    <label>पदाचे नाव (Post Name): </label>
    <input type="text" id="post_name" name="designation_name" required
      value="<?= e($designation['designation_name']) ?>"
      <?= !empty(e($designation['designation_name'])) ? 'readonly' : '' ?>>
    <button type="button" class="btn btn-primary" onclick="loadKaryarat()">कार्यरत भरा</button>
    <br><br>

    <!-- मंजूर पदे Textbox + Button -->
    <label>मंजूर पदे: </label>
    <input type="number" id="bharvayachi_pade" value="0">
    <button type="button" class="btn btn-primary" onclick="distributeSanctioned()">Run</button>
    <br><br>

    <table id="postTable" class="table table-bordered table-striped sfs-1">
      <tr class="bg-warning">
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

      <!-- Percentages row -->
      <tr>
        <td>प्रतिशत (%)</td>
        <?php
        $percentages = [13, 7, 3, 2.5, 3.5, 2, 2, 19, 10, 10, 28];
        foreach ($percentages as $p) {
          echo "<td class='percent-guide'>{$p}%</td>";
        }
        echo "<td class='percent-guide'>100%</td>";
        ?>
      </tr>

      <?php $index = 0;
      while ($row = $res->fetch_assoc()): ?>
        <?php
        $cat = $row['id'];
        $catName = $row['category_name'];
        echo "<tr>";

        // First column (Category column)
        if ($cat == 3) {
          // Show "दिनांक_भरावयाची_पदे" text with single date picker
          echo '<td>';
          echo '<div class="category-date-wrapper">';
          echo '<span class="category-date-label">' . $catName . '</span>';
          echo '<input type="date" name="data[' . $cat . '][category_date]" class="category-date-input" value="' . date('Y-m-d') . '">';
          echo '</div>';
          echo '</td>';
        } elseif ($cat == 4) {
          // Show "कालावधितील_संभाव्य_भरावयाची_पदे" text with From-To date pickers
          echo '<td>';
          echo '<div class="from-to-date-wrapper">';
          echo '<div class="from-to-date-row">';
          echo '<span class="category-date-label">कालावधितील_संभाव्य_भरावयाची_पदे</span>';
          echo '</div>';
          echo '<div class="from-to-date-row">';
          echo '<input type="date" name="data[' . $cat . '][from_date]" class="period-input" value="' . date('Y-m-d') . '">';
          echo '<span class="date-label">ते</span>';
          echo '<input type="date" name="data[' . $cat . '][to_date]" class="period-input" value="' . date('Y-m-d') . '">';
          echo '</div>';
          echo '</div>';
          echo '</td>';
        } else {
          echo "<td>$catName</td>";
        }

        // Data columns (columns 1-11)
        for ($i = 0; $i < 11; $i++) {
          $readonly = ($index == 2 || $index == 4 || $index == 5) ? "readonly" : "";
          echo "<td><input type='number' name='data[$cat][col$i]' value='0' oninput='calculateTotals()' $readonly></td>";
        }

        // Total column (column 12)
        echo "<td><input type='number' name='data[$cat][total]' value='0' readonly></td>";
        echo "</tr>";
        ?>
      <?php $index++;
      endwhile; ?>
    </table>

    <!-- Remark Textbox -->
    <div class="remark-container">
      <label for="remark">शेरा / Remark:</label>
      <textarea id="remark" name="remark" rows="4" class="remark-textbox form-control"
        placeholder="येथे तुमचा शेरा / टिप्पणी टाका..."></textarea>
    </div>


    <div class="no-print d-flex justify-content-between align-items-end my-3">
      <div class="m-auto">
        <div id="alertBox"></div>
      </div>
      <button type="button" class="print-btn btn btn-primary mx-3" onclick="printPage()">🖨️ Print</button>
      <button type="submit" class="save-pdf-btn btn btn-primary">💾 Save & Upload PDF</button>
    </div>

  </form>

  <?php $stmt->close(); ?>

  <h3 class="mt-5">एसईबीसी भारती करिता गणना :</h3>
  <table id="sebcTable" class="table full-input table-bordered">
    <tr>
      <th width="30%">पाहिल्या भरती वर्षात भरावयाची पदे</th>
      <th width="40%">एसईबीसी भरती करीता पाहिल्या भरती वर्षात एकुन भरावच्या पदांच्या १०% नुसार येणारी पदे</th>
      <th width="30%">भरती वर्षात एसईबीसी प्रवर्गाकरिता उपलब्ध पदे</th>
    </tr>
    <tr>
      <td><input type="number" id="first_year_posts" oninput="calculateSebc()" value="0"></td>
      <td><input type="text" id="sebc_10percent" value="0" readonly></td>
      <td><input type="number" id="sebc_available" value="0" readonly></td>
    </tr>
  </table>

  <!-- 🔹 नवीन टेबल स्वतंत्र -->
  <h3 class="mt-5">आर्थिक दृष्ट्या दुर्बल घटक आरक्षण करिता गणना :</h3>
  <table id="financialTable" class="table financial-table table-bordered">
    <tr>
      <th width="30%">🗓️ रोजी रिक्त असलेली पदे (From - To Date)</th>
      <th width="40%" colspan="3">मागील वर्ष + चालू वर्ष → एकूण पदे</th>
      <th width="20%">आर्थिक दृष्ट्या दुर्बल घटक आरक्षण करिता गणना १०% नुसार येणारी पदे</th>
      <th width="10%">चालू वर्षात एसईबीसी प्रवर्गाकरिता उपलब्ध पदे</th>
    </tr>
    <tr>
      <td class="align-center">

        <input type="date" id="from_date" class="date-box w-auto"> ते
        <input type="date" id="to_date" class="date-box w-auto"><br>
        <input type="number" id="vacant_posts" value="0" class="w-75 mt-2 ">
      </td>
      <td colspan="3" style="border-left:none; border-right:none;">
        <input type="number" id="prev_posts" oninput="calculateEws()" value="0"> +
        <input type="number" id="curr_posts" oninput="calculateEws()" value="0"> =
        <input type="text" id="total_posts" value="0" readonly>
      </td>
      <td>
        <input type="text" id="sebc_10percent_new" value="0" readonly>
      </td>
      <td>
        <input type="text" id="sebc_available_new" value="0" readonly>
      </td>
    </tr>
  </table>

  <!-- एकूण_भरावयाची_पदे Single Line Row -->
  <h3 class="mt-5">एकूण_भरावयाची_पदे</h3>
  <table class="table table-bordered table-sm">
    <tr style="background: #e3f2fd; font-weight: bold;">
      <td style="background: #bbdefb; font-weight: bold;">एकूण_भरावयाची_पदे</td>
      <td>
        <span style="display: block; font-size: 12px; margin-bottom: 2px;">अ.जा.</span>
        <input type="number" id="ekun_sc" class="ekun-input" readonly>
      </td>
      <td>
        <span style="display: block; font-size: 12px; margin-bottom: 2px;">अ.जा.त</span>
        <input type="number" id="ekun_st" class="ekun-input" readonly>
      </td>
      <td>
        <span style="display: block; font-size: 12px; margin-bottom: 2px;">वि.ज. (अ)</span>
        <input type="number" id="ekun_vj" class="ekun-input" readonly>
      </td>
      <td>
        <span style="display: block; font-size: 12px; margin-bottom: 2px;">भ.ज. (ब)</span>
        <input type="number" id="ekun_bj" class="ekun-input" readonly>
      </td>
      <td>
        <span style="display: block; font-size: 12px; margin-bottom: 2px;">भ.ज. (क)</span>
        <input type="number" id="ekun_bk" class="ekun-input" readonly>
      </td>
      <td>
        <span style="display: block; font-size: 12px; margin-bottom: 2px;">भ.ज. (ड)</span>
        <input type="number" id="ekun_bd" class="ekun-input" readonly>
      </td>
      <td>
        <span style="display: block; font-size: 12px; margin-bottom: 2px;">वि.मा.प्र.</span>
        <input type="number" id="ekun_vmp" class="ekun-input" readonly>
      </td>
      <td>
        <span style="display: block; font-size: 12px; margin-bottom: 2px;">इ.मा.प्र.</span>
        <input type="number" id="ekun_imp" class="ekun-input" readonly>
      </td>
      <td>
        <span style="display: block; font-size: 12px; margin-bottom: 2px;">सा.शै.मा.व.</span>
        <input type="number" id="ekun_smv" class="ekun-input" readonly>
      </td>
      <td>
        <span style="display: block; font-size: 12px; margin-bottom: 2px;">आ.दृ.दु.घ.</span>
        <input type="number" id="ekun_edg" class="ekun-input" readonly>
      </td>
      <td>
        <span style="display: block; font-size: 12px; margin-bottom: 2px;">अराखीव</span>
        <input type="number" id="ekun_arakhi" class="ekun-input" readonly>
      </td>
      <td>
        <span style="display: block; font-size: 12px; margin-bottom: 2px; color: #d32f2f; font-weight: bold;">Total</span>
        <input type="number" id="ekun_total" class="ekun-input" readonly style="border: 2px solid #d32f2f;">
      </td>
    </tr>
  </table>

</div>

<script>
  // Calculate row/col totals
  function calculateTotals() {
    let table = document.getElementById("postTable");
    let rows = table.rows.length;

    // Row totals
    for (let r = 2; r < rows; r++) {
      let row = table.rows[r];
      let sum = 0;
      for (let c = 1; c <= 11; c++) {
        let val = parseFloat(row.cells[c].children[0].value) || 0;
        sum += val;
      }
      row.cells[12].children[0].value = sum;
    }

    // ✅ मंजूर - कार्यारत = दिनांक_भरावयाची_पदे
    let approvedRow = table.rows[2];
    let activeRow = table.rows[3];
    let possibleRow = table.rows[4];

    let possibleTotal = 0;
    for (let c = 1; c <= 11; c++) {
      let approved = parseFloat(approvedRow.cells[c].children[0].value) || 0;
      let active = parseFloat(activeRow.cells[c].children[0].value) || 0;
      let diff = approved - active;
      possibleRow.cells[c].children[0].value = diff;
      possibleTotal += diff;
    }
    possibleRow.cells[12].children[0].value = possibleTotal;

    // ✅ एकूण_भरायची_पदे = दिनांक_भरावयाची_पदे + कालावधितील_संभाव्य_भरावयाची_पदे
    let totalRow = table.rows[6];
    let periodRow = table.rows[5];

    let totalSum = 0;
    for (let c = 1; c <= 11; c++) {
      let val1 = parseFloat(possibleRow.cells[c].children[0].value) || 0;
      let val2 = parseFloat(periodRow.cells[c].children[0].value) || 0;
      let total = val1 + val2;
      totalRow.cells[c].children[0].value = total;
      totalSum += total;
    }
    totalRow.cells[12].children[0].value = totalSum;

    // ✅ अतिरिक्त_पदे = फक्त negative value असल्यास positive करून
    let extraRow = table.rows[7];
    for (let c = 1; c <= 12; c++) {
      let val = parseFloat(totalRow.cells[c].children[0].value) || 0;
      extraRow.cells[c].children[0].value = (val < 0) ? Math.abs(val) : 0;
    }

    // ✅ UPDATE एकूण_भरावयाची_पदे ROW
    updateEkunRow();
  }

  // Function to update एकूण_भरावयाची_पदे row
  function updateEkunRow() {
    let table = document.getElementById("postTable");
    let totalRow = table.rows[6]; // एकूण_भरावयाची_पदे row

    // Map column indices to ekun table IDs
    const columnMap = [{
        col: 1,
        id: 'ekun_sc'
      }, // अनुसूचित जाती
      {
        col: 2,
        id: 'ekun_st'
      }, // अनुसूचित जमाती
      {
        col: 3,
        id: 'ekun_vj'
      }, // विमुक्त जमाती (अ)
      {
        col: 4,
        id: 'ekun_bj'
      }, // भटक्या जमाती (ब)
      {
        col: 5,
        id: 'ekun_bk'
      }, // भटक्या जमाती (क)
      {
        col: 6,
        id: 'ekun_bd'
      }, // भटक्या जमाती (ड)
      {
        col: 7,
        id: 'ekun_vmp'
      }, // विशेष मागास प्रवर्ग
      {
        col: 8,
        id: 'ekun_imp'
      }, // इतर मागास प्रवर्ग
      {
        col: 9,
        id: 'ekun_smv'
      }, // सामाजिक आणि शैक्षणिक मागास वर्ग
      {
        col: 10,
        id: 'ekun_edg'
      }, // आर्थिक दृष्ट्या दुर्बल घटक
      {
        col: 11,
        id: 'ekun_arakhi'
      } // अराखीव
    ];

    let ekunTotal = 0;

    // Update each ekun input
    columnMap.forEach(item => {
      let value = parseFloat(totalRow.cells[item.col].children[0].value) || 0;
      document.getElementById(item.id).value = value;
      ekunTotal += value;
    });

    // Update total in ekun row
    document.getElementById('ekun_total').value = ekunTotal;
  }

  // Add this function to check the total
  function checkTotalMatch() {
    let bhar = parseFloat(document.getElementById("bharvayachi_pade").value) || 0;
    let table = document.getElementById("postTable");
    let row = table.rows[2]; // मंजूर_पदे row
    let total = parseFloat(row.cells[12].children[0].value) || 0;

    if (bhar > 0 && total > 0 && bhar !== total) {
      alert(`⚠️ सूचना: मंजूर पदे (${bhar}) आणि एकूण पदे (${total}) जुळत नाहीत!`);
      return false;
    }
    return true;
  }

  function goToDashboard() {
    window.location.href = 'dashboard.php';
  }

  // Print function
  function printPage() {
    window.print();
  }

  function distributeSanctioned() {
    let bhar = parseFloat(document.getElementById("bharvayachi_pade").value) || 0;

    // First check if totals match
    if (!checkTotalMatch() && bhar > 0) {
      return; // Stop execution if totals don't match
    }

    // Your existing distributeSanctioned function code...
    // [All your existing special cases from 2 to 32 remain here]
    let percentages = [13, 7, 3, 2.5, 3.5, 2, 2, 19, 10, 10, 28];
    let table = document.getElementById("postTable");
    let row = table.rows[2];

    // Your existing special cases and distribution logic...
    // Special case: if bhar = 2, then set अनुसूचित जाती = 1 and विमुक्त जमाती (अ) = 1
    if (bhar === 2) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }

      // Set अनुसूचित जाती (column 1) = 1
      row.cells[1].children[0].value = 1;

      // Set अराखीव  (column 11) = 1
      row.cells[11].children[0].value = 1;

      // Update total
      row.cells[12].children[0].value = 2;

      calculateTotals();
      return;
    }

    // Special case: if bhar = 3, then set अनुसूचित जाती = 1, विमुक्त जमाती (अ) = 1, and इतर मागास प्रवर्ग = 1
    if (bhar === 3) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }

      // Set अनुसूचित जाती (column 1) = 1
      row.cells[1].children[0].value = 1;

      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;

      // Set अराखीव  (column 11) = 1
      row.cells[11].children[0].value = 1;

      // Update total
      row.cells[12].children[0].value = 3;

      calculateTotals();
      return;
    }

    // [All other special cases remain exactly the same...]
    // Special case: if bhar = 4, then set अनुसूचित जाती = 1, विमुक्त जमाती (अ) = 1, इतर मागास प्रवर्ग = 1, and सामाजिक आणि शैक्षणिक मागास वर्ग = 1
    if (bhar === 4) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }

      // Set अनुसूचित जाती (column 1) = 1
      row.cells[1].children[0].value = 1;

      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;

      // Set इतर मागास प्रवर्ग (column 8) = 1
      row.cells[8].children[0].value = 1;

      // Set अराखीव  (column 11) = 1
      row.cells[11].children[0].value = 1;

      // Update total
      row.cells[12].children[0].value = 4;

      calculateTotals();
      return;
    }

    // Special case: if bhar = 5, then set अनुसूचित जाती = 1, अनुसूचित जमाती = 1, विमुक्त जमाती (अ) = 1, इतर मागास प्रवर्ग = 1, and सामाजिक आणि शैक्षणिक मागास वर्ग = 1
    if (bhar === 5) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }

      // Set अनुसूचित जाती (column 1) = 1
      row.cells[1].children[0].value = 1;


      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;

      // Set इतर मागास प्रवर्ग (column 8) = 1
      row.cells[8].children[0].value = 1;

      // Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 1
      row.cells[9].children[0].value = 1;

      // Set अराखीव  (column 11) = 1
      row.cells[11].children[0].value = 1;

      // Update total
      row.cells[12].children[0].value = 5;

      calculateTotals();
      return;
    }

    // Special case: if bhar = 6, then set अनुसूचित जाती = 1, अनुसूचित जमाती = 1, विमुक्त जमाती (अ) = 1, इतर मागास प्रवर्ग = 1, सामाजिक आणि शैक्षणिक मागास वर्ग = 1, and आर्थिक दृष्ट्या दुर्बल घटक = 1
    if (bhar === 6) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }

      // Set अनुसूचित जाती (column 1) = 1
      row.cells[1].children[0].value = 1;


      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;

      // Set इतर मागास प्रवर्ग (column 8) = 1
      row.cells[8].children[0].value = 1;

      // Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 1
      row.cells[9].children[0].value = 1;

      // Set अराखीव  (column 11) = 2
      row.cells[11].children[0].value = 2;

      // Update total
      row.cells[12].children[0].value = 6;

      calculateTotals();
      return;
    }

    // Special case: if bhar = 7, then set अनुसूचित जाती = 1, अनुसूचित जमाती = 1, विमुक्त जमाती (अ) = 1, इतर मागास प्रवर्ग = 2, सामाजिक आणि शैक्षणिक मागास वर्ग = 1, and आर्थिक दृष्ट्या दुर्बल घटक = 1
    if (bhar === 7) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }

      // Set अनुसूचित जाती (column 1) = 1
      row.cells[1].children[0].value = 1;

      // Set अनुसूचित जमाती (column 2) = 1
      row.cells[2].children[0].value = 1;

      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;

      // Set इतर मागास प्रवर्ग (column 8) = 1
      row.cells[8].children[0].value = 1;

      // Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 1
      row.cells[9].children[0].value = 1;

      // Set अराखीव  (column 11) = 2
      row.cells[11].children[0].value = 2;

      // Update total
      row.cells[12].children[0].value = 7;

      calculateTotals();
      return;
    }

    // Special case: if bhar = 8, then set अनुसूचित जाती = 1, अनुसूचित जमाती = 1, विमुक्त जमाती (अ) = 1, भटक्या जमाती (ब) = 1, इतर मागास प्रवर्ग = 2, सामाजिक आणि शैक्षणिक मागास वर्ग = 1, and आर्थिक दृष्ट्या दुर्बल घटक = 1
    if (bhar === 8) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }

      // Set अनुसूचित जाती (column 1) = 1
      row.cells[1].children[0].value = 1;

      // Set अनुसूचित जमाती (column 2) = 1
      row.cells[2].children[0].value = 1;

      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;


      // Set इतर मागास प्रवर्ग (column 8) = 1
      row.cells[8].children[0].value = 1;

      // Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 1
      row.cells[9].children[0].value = 1;

      // Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 1
      row.cells[10].children[0].value = 1;
      // Set अराखीव  (column 11) = 2
      row.cells[11].children[0].value = 2;

      // Update total
      row.cells[12].children[0].value = 8;

      calculateTotals();
      return;
    }

    // Special case: if bhar = 9, then set अनुसूचित जाती = 2, अनुसूचित जमाती = 1, विमुक्त जमाती (अ) = 1, भटक्या जमाती (ब) = 1, इतर मागास प्रवर्ग = 2, सामाजिक आणि शैक्षणिक मागास वर्ग = 1, and आर्थिक दृष्ट्या दुर्बल घटक = 1
    if (bhar === 9) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }

      // Set अनुसूचित जाती (column 1) = 1
      row.cells[1].children[0].value = 1;

      // Set अनुसूचित जमाती (column 2) = 1
      row.cells[2].children[0].value = 1;

      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;


      // Set इतर मागास प्रवर्ग (column 8) = 1
      row.cells[8].children[0].value = 1;

      // Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 1
      row.cells[9].children[0].value = 1;

      // Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 1
      row.cells[10].children[0].value = 1;
      // Set अराखीव  (column 11) = 3
      row.cells[11].children[0].value = 3;

      // Update total
      row.cells[12].children[0].value = 9;

      calculateTotals();
      return;
    }

    // Special case: if bhar = 10, then set अनुसूचित जाती = 2, अनुसूचित जमाती = 1, विमुक्त जमाती (अ) = 1, भटक्या जमाती (ब) = 1, इतर मागास प्रवर्ग = 3, सामाजिक आणि शैक्षणिक मागास वर्ग = 1, and आर्थिक दृष्ट्या दुर्बल घटक = 1
    if (bhar === 10) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }

      // Set अनुसूचित जाती (column 1) = 1
      row.cells[1].children[0].value = 1;

      // Set अनुसूचित जमाती (column 2) = 1
      row.cells[2].children[0].value = 1;

      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;


      // Set इतर मागास प्रवर्ग (column 8) = 2
      row.cells[8].children[0].value = 2;

      // Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 1
      row.cells[9].children[0].value = 1;

      // Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 1
      row.cells[10].children[0].value = 1;
      // Set अराखीव  (column 11) = 3
      row.cells[11].children[0].value = 3;
      // Update total
      row.cells[12].children[0].value = 10;

      calculateTotals();
      return;
    }

    // Special case: if bhar = 11, then set अनुसूचित जाती = 2, अनुसूचित जमाती = 1, विमुक्त जमाती (अ) = 1, भटक्या जमाती (ब) = 1, इतर मागास प्रवर्ग = 3, सामाजिक आणि शैक्षणिक मागास वर्ग = 2, and आर्थिक दृष्ट्या दुर्बल घटक = 1
    if (bhar === 11) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }

      // Set अनुसूचित जाती (column 1) = 1
      row.cells[1].children[0].value = 1;

      // Set अनुसूचित जमाती (column 2) = 1
      row.cells[2].children[0].value = 1;

      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;
      // 1 पद- भ.ज.(ब) कायमस्वरुपी → 1 position for भटक्या जमाती (ब)
      row.cells[4].children[0].value = 1;


      // Set इतर मागास प्रवर्ग (column 8) = 2
      row.cells[8].children[0].value = 2;

      // Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 1
      row.cells[9].children[0].value = 1;

      // Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 1
      row.cells[10].children[0].value = 1;

      // Set अराखीव  (column 11) = 3
      row.cells[11].children[0].value = 3;

      // Update total
      row.cells[12].children[0].value = 11;

      calculateTotals();
      return;
    }

    // Special case: if bhar = 12, then set अनुसूचित जाती = 2, अनुसूचित जमाती = 1, विमुक्त जमाती (अ) = 1, भटक्या जमाती (ब) = 1, इतर मागास प्रवर्ग = 3, सामाजिक आणि शैक्षणिक मागास वर्ग = 2, and आर्थिक दृष्ट्या दुर्बल घटक = 2
    if (bhar === 12) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }

      // Set अनुसूचित जाती (column 1) = 2
      row.cells[1].children[0].value = 2;

      // Set अनुसूचित जमाती (column 2) = 1
      row.cells[2].children[0].value = 1;

      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;

      // Set भटक्या जमाती (ब) (column 4) = 1
      row.cells[4].children[0].value = 1;

      // Set इतर मागास प्रवर्ग (column 8) = 2
      row.cells[8].children[0].value = 2;

      // Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 1
      row.cells[9].children[0].value = 1;

      // Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 1
      row.cells[10].children[0].value = 1;
      // Set अराखीव  (column 11) = 3
      row.cells[11].children[0].value = 3;

      // Update total
      row.cells[12].children[0].value = 12;

      calculateTotals();
      return;
    }
    // Special case: if bhar = 13
    if (bhar === 13) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }

      // Set अनुसूचित जाती (column 1) = 2
      row.cells[1].children[0].value = 2;

      // Set अनुसूचित जमाती (column 2) = 1
      row.cells[2].children[0].value = 1;

      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;

      // Set भटक्या जमाती (ब) (column 4) = 1
      row.cells[4].children[0].value = 1;

      // Set इतर मागास प्रवर्ग (column 8) = 2
      row.cells[8].children[0].value = 2;

      // Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 1
      row.cells[9].children[0].value = 1;

      // Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 1
      row.cells[10].children[0].value = 1;
      // Set अराखीव  (column 11) = 4
      row.cells[11].children[0].value = 4;

      // Update total
      row.cells[12].children[0].value = 13;

      calculateTotals();
      return;

      calculateTotals();
      return;
    }

    // Special case: if bhar = 14
    if (bhar === 14) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }


      // Set अनुसूचित जाती (column 1) = 2
      row.cells[1].children[0].value = 2;

      // Set अनुसूचित जमाती (column 2) = 1
      row.cells[2].children[0].value = 1;

      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;

      // Set भटक्या जमाती (ब) (column 4) = 1
      row.cells[4].children[0].value = 1;

      // Set इतर मागास प्रवर्ग (column 8) = 3
      row.cells[8].children[0].value = 3;

      // Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 1
      row.cells[9].children[0].value = 1;

      // Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 1
      row.cells[10].children[0].value = 1;
      // Set अराखीव  (column 11) = 4
      row.cells[11].children[0].value = 4;

      // Update total
      row.cells[12].children[0].value = 14;

      calculateTotals();
      return;
    }

    // Special case: if bhar = 15
    if (bhar === 15) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }



      // Set अनुसूचित जाती (column 1) = 2
      row.cells[1].children[0].value = 2;

      // Set अनुसूचित जमाती (column 2) = 1
      row.cells[2].children[0].value = 1;

      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;

      // Set भटक्या जमाती (ब) (column 4) = 1
      row.cells[4].children[0].value = 1;

      // Set इतर मागास प्रवर्ग (column 8) = 3
      row.cells[8].children[0].value = 3;

      // Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 2
      row.cells[9].children[0].value = 2;

      // Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 1
      row.cells[10].children[0].value = 1;
      // Set अराखीव  (column 11) = 4
      row.cells[11].children[0].value = 4;

      // Update total
      row.cells[12].children[0].value = 15;
      calculateTotals();
      return;
    }

    // Special case: if bhar = 16
    if (bhar === 16) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }



      // Set अनुसूचित जाती (column 1) = 2
      row.cells[1].children[0].value = 2;

      // Set अनुसूचित जमाती (column 2) = 1
      row.cells[2].children[0].value = 1;

      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;

      // Set भटक्या जमाती (ब) (column 4) = 1
      row.cells[4].children[0].value = 1;

      // Set इतर मागास प्रवर्ग (column 8) = 3
      row.cells[8].children[0].value = 3;

      // Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 2
      row.cells[9].children[0].value = 2;

      // Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 2
      row.cells[10].children[0].value = 2;
      // Set अराखीव  (column 11) = 4
      row.cells[11].children[0].value = 4;

      // Update total
      row.cells[12].children[0].value = 16;

      calculateTotals();
      return;
    }

    // Special case: if bhar = 17
    if (bhar === 17) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }



      // Set अनुसूचित जाती (column 1) = 2
      row.cells[1].children[0].value = 2;

      // Set अनुसूचित जमाती (column 2) = 1
      row.cells[2].children[0].value = 1;

      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;

      // Set भटक्या जमाती (ब) (column 4) = 1
      row.cells[4].children[0].value = 1;

      // Set इतर मागास प्रवर्ग (column 8) = 3
      row.cells[8].children[0].value = 3;

      // Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 2
      row.cells[9].children[0].value = 2;

      // Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 2
      row.cells[10].children[0].value = 2;
      // Set अराखीव  (column 11) = 5
      row.cells[11].children[0].value = 5;

      // Update total
      row.cells[12].children[0].value = 17;

      calculateTotals();
      return;
    }

    // Special case: if bhar = 18
    if (bhar === 18) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }

      // Set अनुसूचित जाती (column 1) = 2
      row.cells[1].children[0].value = 2;

      // Set अनुसूचित जमाती (column 2) = 1
      row.cells[2].children[0].value = 1;

      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;

      // Set भटक्या जमाती (ब) (column 4) = 1
      row.cells[4].children[0].value = 1;

      // Set भटक्या जमाती (क) (column 5) = 1
      row.cells[5].children[0].value = 1;

      // Set इतर मागास प्रवर्ग (column 8) = 3
      row.cells[8].children[0].value = 3;

      // Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 2
      row.cells[9].children[0].value = 2;

      // Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 2
      row.cells[10].children[0].value = 2;
      // Set अराखीव  (column 11) = 5
      row.cells[11].children[0].value = 5;

      // Update total
      row.cells[12].children[0].value = 18;

      calculateTotals();
      return;
    }

    // Special case: if bhar = 19
    if (bhar === 19) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }

      // Set अनुसूचित जाती (column 1) = 2
      row.cells[1].children[0].value = 2;

      // Set अनुसूचित जमाती (column 2) = 1
      row.cells[2].children[0].value = 1;

      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;

      // Set भटक्या जमाती (ब) (column 4) = 1
      row.cells[4].children[0].value = 1;

      // Set भटक्या जमाती (क) (column 5) = 1
      row.cells[5].children[0].value = 1;

      // Set इतर मागास प्रवर्ग (column 8) = 4
      row.cells[8].children[0].value = 4;

      // Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 2
      row.cells[9].children[0].value = 2;

      // Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 2
      row.cells[10].children[0].value = 2;
      // Set अराखीव  (column 11) = 5
      row.cells[11].children[0].value = 5;

      // Update total
      row.cells[12].children[0].value = 19;
      calculateTotals();
      return;
    }

    // Special case: if bhar = 20
    if (bhar === 20) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }
      // Set अनुसूचित जाती (column 1) = 2
      row.cells[1].children[0].value = 2;

      // Set अनुसूचित जमाती (column 2) = 1
      row.cells[2].children[0].value = 1;

      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;

      // Set भटक्या जमाती (ब) (column 4) = 1
      row.cells[4].children[0].value = 1;

      // Set भटक्या जमाती (क) (column 5) = 1
      row.cells[5].children[0].value = 1;

      // Set इतर मागास प्रवर्ग (column 8) = 4
      row.cells[8].children[0].value = 4;

      // Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 2
      row.cells[9].children[0].value = 2;

      // Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 2
      row.cells[10].children[0].value = 2;
      // Set अराखीव  (column 11) = 6
      row.cells[11].children[0].value = 6;

      // Update total
      row.cells[12].children[0].value = 20;

      calculateTotals();
      return;
    }

    // Special case: if bhar = 21
    if (bhar === 21) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }

      // Set अनुसूचित जाती (column 1) = 3
      row.cells[1].children[0].value = 3;

      // Set अनुसूचित जमाती (column 2) = 1
      row.cells[2].children[0].value = 1;

      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;

      // Set भटक्या जमाती (ब) (column 4) = 1
      row.cells[4].children[0].value = 1;

      // Set भटक्या जमाती (क) (column 5) = 1
      row.cells[5].children[0].value = 1;

      // Set इतर मागास प्रवर्ग (column 8) = 4
      row.cells[8].children[0].value = 4;

      // Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 2
      row.cells[9].children[0].value = 2;

      // Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 2
      row.cells[10].children[0].value = 2;
      // Set अराखीव  (column 11) = 6
      row.cells[11].children[0].value = 6;

      // Update total
      row.cells[12].children[0].value = 21;

      calculateTotals();
      return;
    }

    // Special case: if bhar = 22
    if (bhar === 22) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }

      // Set अनुसूचित जाती (column 1) = 3
      row.cells[1].children[0].value = 3;

      // Set अनुसूचित जमाती (column 2) = 2
      row.cells[2].children[0].value = 2;

      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;

      // Set भटक्या जमाती (ब) (column 4) = 1
      row.cells[4].children[0].value = 1;

      // Set भटक्या जमाती (क) (column 5) = 1
      row.cells[5].children[0].value = 1;

      // Set इतर मागास प्रवर्ग (column 8) = 4
      row.cells[8].children[0].value = 4;

      // Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 2
      row.cells[9].children[0].value = 2;

      // Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 2
      row.cells[10].children[0].value = 2;
      // Set अराखीव  (column 11) = 5
      row.cells[11].children[0].value = 6;

      // Update total
      row.cells[12].children[0].value = 22;

      calculateTotals();
      return;
    }

    // Special case: if bhar = 23
    if (bhar === 22) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }

      // Set अनुसूचित जाती (column 1) = 3
      row.cells[1].children[0].value = 3;

      // Set अनुसूचित जमाती (column 2) = 2
      row.cells[2].children[0].value = 2;

      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;

      // Set भटक्या जमाती (ब) (column 4) = 1
      row.cells[4].children[0].value = 1;

      // Set भटक्या जमाती (क) (column 5) = 1
      row.cells[5].children[0].value = 1;

      // Set भटक्या जमाती (ड) (column 6) = 1
      row.cells[6].children[0].value = 1;

      // Set इतर मागास प्रवर्ग (column 8) = 4
      row.cells[8].children[0].value = 4;

      // Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 2
      row.cells[9].children[0].value = 2;

      // Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 2
      row.cells[10].children[0].value = 2;

      // Set अराखीव  (column 11) = 5
      row.cells[11].children[0].value = 6;

      // Update total
      row.cells[12].children[0].value = 23;

      calculateTotals();
      return;
    }


    // Special case: if bhar = 24
    if (bhar === 24) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }

      // Set अनुसूचित जाती (column 1) = 3
      row.cells[1].children[0].value = 3;

      // Set अनुसूचित जमाती (column 2) = 2
      row.cells[2].children[0].value = 2;

      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;

      // Set भटक्या जमाती (ब) (column 4) = 1
      row.cells[4].children[0].value = 1;

      // Set भटक्या जमाती (क) (column 5) = 1
      row.cells[5].children[0].value = 1;

      // Set भटक्या जमाती (ड) (column 6) = 1
      row.cells[6].children[0].value = 1;

      // Set इतर मागास प्रवर्ग (column 8) = 4
      row.cells[8].children[0].value = 4;

      // Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 2
      row.cells[9].children[0].value = 2;

      // Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 2
      row.cells[10].children[0].value = 2;

      // Set अराखीव  (column 11) = 5
      row.cells[11].children[0].value = 7;

      // Update total
      row.cells[12].children[0].value = 24;

      calculateTotals();
      return;
    }

    // Special case: if bhar = 25
    if (bhar === 25) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }

      // Set अनुसूचित जाती (column 1) = 3
      row.cells[1].children[0].value = 3;

      // Set अनुसूचित जमाती (column 2) = 2
      row.cells[2].children[0].value = 2;

      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;

      // Set भटक्या जमाती (ब) (column 4) = 1
      row.cells[4].children[0].value = 1;

      // Set भटक्या जमाती (क) (column 5) = 1
      row.cells[5].children[0].value = 1;

      // Set भटक्या जमाती (ड) (column 6) = 1
      row.cells[6].children[0].value = 1;

      // Set इतर मागास प्रवर्ग (column 8) = 5
      row.cells[8].children[0].value = 5;

      // Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 2
      row.cells[9].children[0].value = 2;

      // Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 2
      row.cells[10].children[0].value = 2;

      // Set अराखीव  (column 11) = 5
      row.cells[11].children[0].value = 7;

      // Update total
      row.cells[12].children[0].value = 25;

      calculateTotals();
      return;
    }
    // Special case: if bhar = 26
    if (bhar === 26) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }

      // Set अनुसूचित जाती (column 1) = 3
      row.cells[1].children[0].value = 3;

      // Set अनुसूचित जमाती (column 2) = 2
      row.cells[2].children[0].value = 2;

      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;

      // Set भटक्या जमाती (ब) (column 4) = 1
      row.cells[4].children[0].value = 1;

      // Set भटक्या जमाती (क) (column 5) = 1
      row.cells[5].children[0].value = 1;

      // Set भटक्या जमाती (ड) (column 6) = 1
      row.cells[6].children[0].value = 1;

      // Set इतर मागास प्रवर्ग (column 8) = 5
      row.cells[8].children[0].value = 5;

      // Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 3
      row.cells[9].children[0].value = 3;

      // Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 2
      row.cells[10].children[0].value = 2;

      // Set अराखीव  (column 11) = 5
      row.cells[11].children[0].value = 7;

      // Update total
      row.cells[12].children[0].value = 26;

      calculateTotals();
      return;
    }
    // Special case: if bhar = 27
    if (bhar === 27) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }

      // Set अनुसूचित जाती (column 1) = 3
      row.cells[1].children[0].value = 3;

      // Set अनुसूचित जमाती (column 2) = 2
      row.cells[2].children[0].value = 2;

      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;

      // Set भटक्या जमाती (ब) (column 4) = 1
      row.cells[4].children[0].value = 1;

      // Set भटक्या जमाती (क) (column 5) = 1
      row.cells[5].children[0].value = 1;

      // Set भटक्या जमाती (ड) (column 6) = 1
      row.cells[6].children[0].value = 1;

      // Set इतर मागास प्रवर्ग (column 8) = 5
      row.cells[8].children[0].value = 5;

      // Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 3
      row.cells[9].children[0].value = 3;

      // Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 2
      row.cells[10].children[0].value = 2;

      // Set अराखीव  (column 11) = 8
      row.cells[11].children[0].value = 8;

      // Update total
      row.cells[12].children[0].value = 27;

      calculateTotals();
      return;
    }

    // Special case: if bhar = 28
    if (bhar === 28) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }

      // Set अनुसूचित जाती (column 1) = 3
      row.cells[1].children[0].value = 3;

      // Set अनुसूचित जमाती (column 2) = 2
      row.cells[2].children[0].value = 2;

      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;

      // Set भटक्या जमाती (ब) (column 4) = 1
      row.cells[4].children[0].value = 1;

      // Set भटक्या जमाती (क) (column 5) = 1
      row.cells[5].children[0].value = 1;

      // Set भटक्या जमाती (ड) (column 6) = 1
      row.cells[6].children[0].value = 1;

      // Set इतर मागास प्रवर्ग (column 8) = 5
      row.cells[8].children[0].value = 5;

      // Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 3
      row.cells[9].children[0].value = 3;

      // Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 3
      row.cells[10].children[0].value = 3;

      // Set अराखीव  (column 11) = 8
      row.cells[11].children[0].value = 8;

      // Update total
      row.cells[12].children[0].value = 28;

      calculateTotals();
      return;
    }

    // Special case: if bhar = 29
    if (bhar === 29) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }

      // Set अनुसूचित जाती (column 1) = 4
      row.cells[1].children[0].value = 4;

      // Set अनुसूचित जमाती (column 2) = 2
      row.cells[2].children[0].value = 2;

      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;

      // Set भटक्या जमाती (ब) (column 4) = 1
      row.cells[4].children[0].value = 1;

      // Set भटक्या जमाती (क) (column 5) = 1
      row.cells[5].children[0].value = 1;

      // Set भटक्या जमाती (ड) (column 6) = 1
      row.cells[6].children[0].value = 1;

      // Set इतर मागास प्रवर्ग (column 8) = 5
      row.cells[8].children[0].value = 5;

      // Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 3
      row.cells[9].children[0].value = 3;

      // Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 3
      row.cells[10].children[0].value = 3;

      // Set अराखीव  (column 11) = 8
      row.cells[11].children[0].value = 8;

      // Update total
      row.cells[12].children[0].value = 29;

      calculateTotals();
      return;
    }

    // Special case: if bhar = 30
    if (bhar === 30) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }

      // Set अनुसूचित जाती (column 1) = 4
      row.cells[1].children[0].value = 4;

      // Set अनुसूचित जमाती (column 2) = 2
      row.cells[2].children[0].value = 2;

      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;

      // Set भटक्या जमाती (ब) (column 4) = 1
      row.cells[4].children[0].value = 1;

      // Set भटक्या जमाती (क) (column 5) = 1
      row.cells[5].children[0].value = 1;

      // Set भटक्या जमाती (ड) (column 6) = 1
      row.cells[6].children[0].value = 1;

      // Set इतर मागास प्रवर्ग (column 8) = 6
      row.cells[8].children[0].value = 6;

      // Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 3
      row.cells[9].children[0].value = 3;

      // Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 3
      row.cells[10].children[0].value = 3;

      // Set अराखीव  (column 11) = 8
      row.cells[11].children[0].value = 8;

      // Update total
      row.cells[12].children[0].value = 30;

      calculateTotals();
      return;
    }
    // Special case: if bhar = 31
    if (bhar === 31) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }

      // Set अनुसूचित जाती (column 1) = 4
      row.cells[1].children[0].value = 4;

      // Set अनुसूचित जमाती (column 2) = 2
      row.cells[2].children[0].value = 2;

      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;

      // Set भटक्या जमाती (ब) (column 4) = 1
      row.cells[4].children[0].value = 1;

      // Set भटक्या जमाती (क) (column 5) = 1
      row.cells[5].children[0].value = 1;

      // Set भटक्या जमाती (ड) (column 6) = 1
      row.cells[6].children[0].value = 1;

      // Set इतर मागास प्रवर्ग (column 8) = 6
      row.cells[8].children[0].value = 6;

      // Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 3
      row.cells[9].children[0].value = 3;

      // Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 3
      row.cells[10].children[0].value = 3;

      // Set अराखीव  (column 11) = 9
      row.cells[11].children[0].value = 9;

      // Update total
      row.cells[12].children[0].value = 31;

      calculateTotals();
      return;
    }

    // Special case: if bhar = 32
    if (bhar === 32) {
      // Clear all values first
      for (let i = 0; i < percentages.length; i++) {
        row.cells[i + 1].children[0].value = 0;
      }

      // Set अनुसूचित जाती (column 1) = 4
      row.cells[1].children[0].value = 4;

      // Set अनुसूचित जमाती (column 2) = 2
      row.cells[2].children[0].value = 2;

      // Set विमुक्त जमाती (अ) (column 3) = 1
      row.cells[3].children[0].value = 1;

      // Set भटक्या जमाती (ब) (column 4) = 1
      row.cells[4].children[0].value = 1;

      // Set भटक्या जमाती (क) (column 5) = 1
      row.cells[5].children[0].value = 1;

      // Set भटक्या जमाती (ड) (column 6) = 1
      row.cells[6].children[0].value = 1;

      // Set विशेष मागास प्रवर्ग (column 8) = 1
      row.cells[7].children[0].value = 1;

      // Set इतर मागास प्रवर्ग (column 8) = 6
      row.cells[8].children[0].value = 6;

      // Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 3
      row.cells[9].children[0].value = 3;

      // Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 3
      row.cells[10].children[0].value = 3;

      // Set अराखीव  (column 11) = 9
      row.cells[11].children[0].value = 9;

      // Update total
      row.cells[12].children[0].value = 32;


      calculateTotals();
      return;
    }

    // ... [The rest of the distributeSanctioned function remains unchanged]

    // Normal distribution for other values
    let distributed = [];
    let sum = 0;

    for (let i = 0; i < percentages.length; i++) {
      let exactVal = bhar * percentages[i] / 100;
      let decimalPart = exactVal - Math.floor(exactVal);
      let val;

      if (decimalPart >= 0.5) {
        val = Math.ceil(exactVal); // 0.5 किंवा जास्त → round up
      } else {
        val = Math.floor(exactVal); // 0.5 पेक्षा कमी → floor
      }

      distributed.push(val);
      sum += val;
    }

    let diff = bhar - sum;
    let i = 0;
    while (diff > 0) {
      distributed[i % distributed.length]++;
      diff--;
      i++;
    }

    let total = 0;
    for (let i = 0; i < distributed.length; i++) {
      row.cells[i + 1].children[0].value = distributed[i];
      total += distributed[i];
    }
    row.cells[12].children[0].value = total;

    calculateTotals();


  }

  // Load कार्यारत row
  function loadKaryarat() {
    let postName = document.getElementById("post_name").value;

    if (postName.trim() === "") {
      alert("कृपया Post Name द्या!");
      return;
    }

    $.ajax({
      url: baseUrl('api/load-karyarat'),
      type: 'POST',
      dataType: 'json',
      data: {
        designation_hash: "<?= $hashedDesignationId ?>",
        csrf_token: "<?= csrf_token(); ?>"
      },
      success: function(data) {
        if (data.success) {
          let table = document.getElementById("postTable");
          let row = table.rows[3];
          let total = 0;

          for (let i = 0; i < 11; i++) {
            if (row.cells[i + 1] && row.cells[i + 1].children[0]) {
              row.cells[i + 1].children[0].value = data.values[i];
              total += parseFloat(data.values[i]) || 0;
            }
          }

          if (row.cells[12] && row.cells[12].children[0]) {
            row.cells[12].children[0].value = total;
          }

          calculateTotals();
        } else {
          alert("माहिती मिळाली नाही!");
        }
      },
      error: function(xhr, status, error) {
        console.error("AJAX Error:", error);
        alert("सर्व्हरशी संपर्क होऊ शकला नाही!");
      }
    });
  }


  function calculateSebc() {
    let first = parseFloat(document.getElementById("first_year_posts").value) || 0;
    let percent = first * 0.10;
    document.getElementById("sebc_10percent").value = percent.toFixed(2);

    let decimalPart = percent - Math.floor(percent);
    let available;
    if (decimalPart < 0.5) {
      available = Math.floor(percent);
    } else {
      available = Math.floor(percent) + 1;
    }
    document.getElementById("sebc_available").value = available;
  }

  function calculateEws() {
    let prev = parseFloat(document.getElementById("prev_posts").value) || 0;
    let curr = parseFloat(document.getElementById("curr_posts").value) || 0;
    let total = prev + curr;
    document.getElementById("total_posts").value = total;

    let percent = total * 0.10;
    document.getElementById("sebc_10percent_new").value = percent.toFixed(2);

    let decimalPart = percent - Math.floor(percent);
    let available;
    if (decimalPart < 0.5) {
      available = Math.floor(percent);
    } else {
      available = Math.floor(percent) + 1;
    }
    document.getElementById("sebc_available_new").value = available;
  }

  // Auto-focus on मंजूर पदे input when page loads with pre-filled post name
  window.addEventListener('load', function() {
    <?php if (!empty($post_name)): ?>
      // If post name is pre-filled, focus on the मंजूर पदे input
      document.getElementById('bharvayachi_pade').focus();
    <?php endif; ?>

    // Initial calculation to populate ekun row
    calculateTotals();
  });
</script>

<?php
$page_scripts = [
  'https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.3.6/b-3.2.6/datatables.min.js',
  'https://code.jquery.com/ui/1.14.1/jquery-ui.js',
];
$inline_scripts = <<<JS
  $(function () {
    document.getElementById('sidebar').classList.toggle('collapsed');

    $('#goshwaraForm').on('submit', function (e) {
      e.preventDefault();

      let formData = new FormData(this);

      $.ajax({
        url: baseUrl('api/goshwara-save'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',

        success: function (res) {
          if (res.status === 'success') {
            $('#alertBox').html(
              `<div class="alert alert-success">`+res . message+`</div>`
            );
            $('#employeeForm')[0].reset();
            window.location.reload();
          } else {
            $('#alertBox').html(
              '<div class="alert alert-danger">'+res . message+'</div>'
            );
          }
        },

        error: function () {
          $('#alertBox').html(
            `<div class="alert alert-danger">Unable to process request.</div>`
          );
        }
      });

    });

  });
JS;
?>