<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/ho/hostel/includes/config.php');
include('includes/checklogin.php');
check_login();

// Get the post name from URL parameter
$post_name = isset($_GET['post_name']) ? trim($_GET['post_name']) : "";

// ✅ Save Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_name'])) {
    $user_id   = $_SESSION['id'];   // user_id from login session
    $post_name = $_POST['post_name'];
    $data      = $_POST['data'];

    // overwrite: delete old records first
    $conn->query("DELETE FROM user_posts WHERE user_id='$user_id' AND post_name='$post_name'");

    foreach ($data as $category => $cols) {
        $stmt = $conn->prepare("INSERT INTO user_posts 
            (user_id, post_name, category, col0, col1, col2, col3, col4, col5, col6, col7, col8, col9, col10, total) 
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

        $stmt->bind_param(
            "issiiiiiiiiiiii",
            $user_id, $post_name, $category,
            $cols['col0'], $cols['col1'], $cols['col2'], $cols['col3'], $cols['col4'],
            $cols['col5'], $cols['col6'], $cols['col7'], $cols['col8'], $cols['col9'], $cols['col10'],
            $cols['total']
        );
        $stmt->execute();
    }

    echo "<script>alert('✅ डेटा सेव्ह झाला!');</script>";
}
?>

<!DOCTYPE html>
<html lang="mr">
<head>
    <meta charset="UTF-8">
    <title>Post Entry</title>
    <style>
        table, th, td { 
            border: 1px solid black; 
            border-collapse: collapse; 
            padding: 5px; 
            text-align: center; 
        }
        th { background: #f2a65a; }
        td:first-child { font-weight: bold; background: #f9e7c4; }

        input { 
            width: 70px; 
            text-align: right; 
            font-size: 18px;   /* ✅ नंबर मोठे */
            font-weight: bold; /* ✅ नंबर bold */
        }
        input[readonly] { 
            background: #eee; 
            font-weight: bold; 
            font-size: 18px; 
        }
        .percent-guide { 
            font-weight: bold; 
            font-size: 1.3em; 
            color: #000; 
        }
        .btn { 
            padding: 5px 12px; 
            background: #0077cc; 
            color: #fff; 
            border: none; 
            cursor: pointer; 
        }
        .post-name-info {
            background: #e7f3ff;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #0077cc;
        }
        .post-name-info strong {
            color: #0077cc;
        }
        .date-box {
            width: 180px;
            padding: 5px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <h2>पदांची माहिती नोंदवा</h2>
    
    <?php if (!empty($post_name)): ?>
    <div class="post-name-info">
        <strong>पदाचे नाव:</strong> <?= htmlspecialchars($post_name) ?>
        <br><small>हा पदावरील माहिती भरण्यासाठी तयार आहात.</small>
    </div>
    <?php endif; ?>
    
    <form method="POST">
        <label>पदाचे नाव (Post Name): </label>
        <input type="text" id="post_name" name="post_name" required 
               value="<?= htmlspecialchars($post_name) ?>"
               <?= !empty($post_name) ? 'readonly' : '' ?>>
        <button type="button" class="btn" onclick="loadKaryarat()">कार्यरत भरा</button>
        <br><br>

        <!-- मंजूर पदे Textbox + Button -->
        <label>मंजूर पदे: </label>
        <input type="number" id="bharvayachi_pade" value="0">
        <button type="button" class="btn" onclick="distributeSanctioned()">Run</button>
        <br><br>

        <table id="postTable">
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

            <!-- Percentages row -->
            <tr>
                <td>प्रतिशत (%)</td>
                <?php
                $percentages = [12, 14, 3, 2.5, 3.5, 2, 2, 17, 8, 8, 28];
                foreach ($percentages as $p) {
                    echo "<td class='percent-guide'>{$p}%</td>";
                }
                echo "<td class='percent-guide'>100%</td>";
                ?>
            </tr>

            <?php
            $categories = [
                "मंजूर",                          // row 2
                "कार्यारत",                       // row 3
                "दिनांक",                         // row 4 → मंजूर - कार्यारत
                "कालावधितील_संभव_भरवयाची_पदे",   // row 5 → manual input
                "एकूण_भरायची_पदे",               // row 6 → दिनांक + कालावधितील
                "अतिरिक्त_पदे"                    // row 7
            ];

            foreach ($categories as $index => $cat) {
                echo "<tr>";
                echo "<td>$cat</td>";
                for ($i = 0; $i < 11; $i++) {
                    $readonly = ($index == 2 || $index == 4 || $index == 5) ? "readonly" : "";
                    echo "<td><input type='number' name='data[$cat][col$i]' value='0' oninput='calculateTotals()' $readonly></td>";
                }
                echo "<td><input type='number' name='data[$cat][total]' value='0' readonly></td>";
                echo "</tr>";
            }
            ?>
        </table>
        <br>
        <button type="submit" name="submit">Save Data</button>
    </form>

    <h3>एसईबीसी भारती करिता गणना :</h3>
    <table id="sebcTable" border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width:100%;">
        <tr>
            <th>पाहिल्या भरती वर्षात भरवयाची पदे</th>
            <th>एसईबीसी भारती करीता पाहिल्या भरती वर्षात एकुन भरवयाच्या पदांच्या १०% नुसार येणारी पदे</th>
            <th>भरती वर्षात एसईबीसी प्रवर्गाकरिता उपलब्ध पदे</th>
        </tr>
        <tr>
            <td><input type="number" id="first_year_posts" oninput="calculateSebc()" value="0" ></td>
            <td><input type="text" id="sebc_10percent" value="0" readonly></td>
            <td><input type="number" id="sebc_available" value="0" readonly></td>
        </tr>
    </table>

    <!-- 🔹 नवीन टेबल स्वतंत्र -->
    <h3>आर्थिक दृष्ट्या दुर्बल घटक आरक्षण करिता गणना :</h3>
    <table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width:100%; text-align:center;">
        <tr>
            <th>🗓️ रोजी रिक्त असलेली पदे (From - To Date)</th>
            <th colspan="3">मागील वर्ष + चालू वर्ष → एकूण पदे</th>
            <th>आर्थिक दृष्ट्या दुर्बल घटक आरक्षण करिता गणना १०% नुसार येणारी पदे</th>
            <th>चालू वर्षात एसईबीसी प्रवर्गाकरिता उपलब्ध पदे</th>
        </tr>
        <tr>
            <td>
                <input type="date" id="from_date" class="date-box"> ते 
                <input type="date" id="to_date" class="date-box"><br>
                <input type="number" id="vacant_posts" value="0" style="width:150px;">
            </td>
            <td colspan="3" style="border-left:none; border-right:none;">
                <input type="number" id="prev_posts" oninput="calculateEws()" value="0" style="width:100px;"> +
                <input type="number" id="curr_posts" oninput="calculateEws()" value="0" style="width:100px;"> =
                <input type="text" id="total_posts" value="0" readonly style="width:100px;">
            </td>
            <td>
                <input type="text" id="sebc_10percent_new" value="0" readonly style="width:120px;">
            </td>
            <td>
                <input type="text" id="sebc_available_new" value="0" readonly style="width:120px;">
            </td>
        </tr>
    </table>

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

        // ✅ मंजूर - कार्यारत = दिनांक (negative allow)
        let approvedRow = table.rows[2];
        let activeRow   = table.rows[3];
        let dateRow     = table.rows[4];

        let dateTotal = 0;
        for (let c = 1; c <= 11; c++) {
            let approved = parseFloat(approvedRow.cells[c].children[0].value) || 0;
            let active   = parseFloat(activeRow.cells[c].children[0].value) || 0;
            let diff     = approved - active;
            dateRow.cells[c].children[0].value = diff; 
            dateTotal += diff;
        }
        dateRow.cells[12].children[0].value = dateTotal;

        // ✅ एकूण_भरायची_पदे = दिनांक + कालावधितील
        let totalRow = table.rows[6];
        let kalavRow = table.rows[5];

        let totalSum = 0;
        for (let c = 1; c <= 11; c++) {
            let val1 = parseFloat(dateRow.cells[c].children[0].value) || 0;
            let val2 = parseFloat(kalavRow.cells[c].children[0].value) || 0;
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
    }

    // Auto-distribute मंजूर row
    function distributeSanctioned() {
        let bhar = parseFloat(document.getElementById("bharvayachi_pade").value) || 0;
        let percentages = [12, 14, 3, 2.5, 3.5, 2, 2, 17, 8, 8, 28];
        let table = document.getElementById("postTable");
        let row = table.rows[2];

        // Special case: if bhar = 2, then set अनुसूचित जाती = 1 and विमुक्त जमाती (अ) = 1
        if (bhar === 2) {
            // Clear all values first
            for (let i = 0; i < percentages.length; i++) {
                row.cells[i+1].children[0].value = 0;
            }
            
            // Set अनुसूचित जमाती (column 2) = 1
            row.cells[2].children[0].value = 1;
            
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
                row.cells[i+1].children[0].value = 0;
            }
           // Set अनुसूचित जमाती (column 2) = 1
            row.cells[2].children[0].value = 1;
              // Set विमुक्त जमाती (अ) (column 3) = 1
            row.cells[3].children[0].value = 1;
             // Set अराखीव  (column 11) = 1
            row.cells[11].children[0].value = 1;
             // Update total
            row.cells[12].children[0].value = 3;
              calculateTotals();
            return;
        } 
if (bhar === 4) {
            // Clear all values first
            for (let i = 0; i < percentages.length; i++) {
                row.cells[i+1].children[0].value = 0;
            }
           // Set अनुसूचित जमाती (column 2) = 1
            row.cells[2].children[0].value = 1;
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
  if (bhar === 5) {
            // Clear all values first
            for (let i = 0; i < percentages.length; i++) {
                row.cells[i+1].children[0].value = 0;
            }
           // Set अनुसूचित जमाती (column 2) = 1
            row.cells[2].children[0].value = 1;
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
if (bhar === 6) {
            // Clear all values first
            for (let i = 0; i < percentages.length; i++) {
                row.cells[i+1].children[0].value = 0;
            }
           // Set अनुसूचित जमाती (column 2) = 1
            row.cells[2].children[0].value = 1;
              // Set विमुक्त जमाती (अ) (column 3) = 1
            row.cells[3].children[0].value = 1;
	    // Set इतर मागास प्रवर्ग (column 8) = 1
            row.cells[8].children[0].value = 1;
		// Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 1
            row.cells[9].children[0].value = 1;
		// Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 0
            row.cells[10].children[0].value = 0;
             // Set अराखीव  (column 11) = 1
            row.cells[11].children[0].value = 2;
             // Update total
            row.cells[12].children[0].value = 6;
              calculateTotals();
            return;
        }

if (bhar === 7) {
            // Clear all values first
            for (let i = 0; i < percentages.length; i++) {
                row.cells[i+1].children[0].value = 0;
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
		// Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 0
            row.cells[10].children[0].value = 0;
             // Set अराखीव  (column 11) = 2
            row.cells[11].children[0].value = 2;
             // Update total
            row.cells[12].children[0].value = 7;
              calculateTotals();
            return;
        }

if (bhar === 8) {
            // Clear all values first
            for (let i = 0; i < percentages.length; i++) {
                row.cells[i+1].children[0].value = 0;
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

if (bhar === 9) {
            // Clear all values first
            for (let i = 0; i < percentages.length; i++) {
                row.cells[i+1].children[0].value = 0;
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

if (bhar === 10) {
            // Clear all values first
            for (let i = 0; i < percentages.length; i++) {
                row.cells[i+1].children[0].value = 0;
            }
           // Set अनुसूचित जाती (column 1) = 1
            row.cells[1].children[0].value = 1;
           // Set अनुसूचित जमाती (column 2) = 1
            row.cells[2].children[0].value = 1;
              // Set विमुक्त जमाती (अ) (column 3) = 1
            row.cells[3].children[0].value = 1;
	// Set भटक्या जमाती (ब) (column 7) = 1
            row.cells[7].children[0].value = 1;
	    // Set इतर मागास प्रवर्ग (column 8) = 1
            row.cells[8].children[0].value = 1;
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

if (bhar === 11) {
            // Clear all values first
            for (let i = 0; i < percentages.length; i++) {
                row.cells[i+1].children[0].value = 0;
            }
           // Set अनुसूचित जाती (column 1) = 1
            row.cells[1].children[0].value = 1;
           // Set अनुसूचित जमाती (column 2) = 1
            row.cells[2].children[0].value = 1;
              // Set विमुक्त जमाती (अ) (column 3) = 1
            row.cells[3].children[0].value = 1;
	// Set भटक्या जमाती (ब) (column 7) = 1
            row.cells[7].children[0].value = 1;
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

if (bhar === 12) {
            // Clear all values first
            for (let i = 0; i < percentages.length; i++) {
                row.cells[i+1].children[0].value = 0;
            }
           // Set अनुसूचित जाती (column 1) = 2
            row.cells[1].children[0].value = 2;
           // Set अनुसूचित जमाती (column 2) = 1
            row.cells[2].children[0].value = 1;
              // Set विमुक्त जमाती (अ) (column 3) = 1
            row.cells[3].children[0].value = 1;
	// Set भटक्या जमाती (ब) (column 7) = 1
            row.cells[7].children[0].value = 1;
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
if (bhar === 13) {
            // Clear all values first
            for (let i = 0; i < percentages.length; i++) {
                row.cells[i+1].children[0].value = 0;
            }
           // Set अनुसूचित जाती (column 1) = 2
            row.cells[1].children[0].value = 2;
           // Set अनुसूचित जमाती (column 2) = 1
            row.cells[2].children[0].value = 1;
              // Set विमुक्त जमाती (अ) (column 3) = 1
            row.cells[3].children[0].value = 1;
	// Set भटक्या जमाती (ब) (column 7) = 1
            row.cells[7].children[0].value = 1;
	    // Set इतर मागास प्रवर्ग (column 8) = 2
            row.cells[8].children[0].value = 2;
		// Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 1
            row.cells[9].children[0].value = 1;
		// Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 1
            row.cells[10].children[0].value = 1;
             // Set अराखीव  (column 11) = 3
            row.cells[11].children[0].value = 4;
             // Update total
            row.cells[12].children[0].value = 13;
              calculateTotals();
            return;
        }
if (bhar === 14) {
            // Clear all values first
            for (let i = 0; i < percentages.length; i++) {
                row.cells[i+1].children[0].value = 0;
            }
           // Set अनुसूचित जाती (column 1) = 2
            row.cells[1].children[0].value = 2;
           // Set अनुसूचित जमाती (column 2) = 2
            row.cells[2].children[0].value = 2;
              // Set विमुक्त जमाती (अ) (column 3) = 1
            row.cells[3].children[0].value = 1;
	   // Set भटक्या जमाती (ब) (column 7) = 1
            row.cells[7].children[0].value = 1;
	    // Set इतर मागास प्रवर्ग (column 8) = 2
            row.cells[8].children[0].value = 2;
		// Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 1
            row.cells[9].children[0].value = 1;
		// Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 1
            row.cells[10].children[0].value = 1;
             // Set अराखीव  (column 11) = 3
            row.cells[11].children[0].value = 4;
             // Update total
            row.cells[12].children[0].value = 14;
              calculateTotals();
            return;
        }

if (bhar === 15) {
            // Clear all values first
            for (let i = 0; i < percentages.length; i++) {
                row.cells[i+1].children[0].value = 0;
            }
           // Set अनुसूचित जाती (column 1) = 2
            row.cells[1].children[0].value = 2;
           // Set अनुसूचित जमाती (column 2) = 2
            row.cells[2].children[0].value = 2;
              // Set विमुक्त जमाती (अ) (column 3) = 1
            row.cells[3].children[0].value = 1;
	   // Set भटक्या जमाती (ब) (column 7) = 1
            row.cells[7].children[0].value = 1;
	    // Set इतर मागास प्रवर्ग (column 8) = 3
            row.cells[8].children[0].value = 3;
		// Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 1
            row.cells[9].children[0].value = 1;
		// Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 1
            row.cells[10].children[0].value = 1;
             // Set अराखीव  (column 11) = 3
            row.cells[11].children[0].value = 4;
             // Update total
            row.cells[12].children[0].value = 15;
              calculateTotals();
            return;
        }

if (bhar === 16) {
            // Clear all values first
            for (let i = 0; i < percentages.length; i++) {
                row.cells[i+1].children[0].value = 0;
            }
           // Set अनुसूचित जाती (column 1) = 2
            row.cells[1].children[0].value = 2;
           // Set अनुसूचित जमाती (column 2) = 2
            row.cells[2].children[0].value = 2;
              // Set विमुक्त जमाती (अ) (column 3) = 1
            row.cells[3].children[0].value = 1;
	   // Set भटक्या जमाती (ब) (column 4) = 1
 	    row.cells[4].children[0].value = 1;
	   // Set विशेष मागास प्रवर्ग(column 7) = 1
            row.cells[7].children[0].value = 1;
	    // Set इतर मागास प्रवर्ग (column 8) = 3
            row.cells[8].children[0].value = 3;
		// Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 1
            row.cells[9].children[0].value = 1;
		// Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 1
            row.cells[10].children[0].value = 1;
             // Set अराखीव  (column 11) = 3
            row.cells[11].children[0].value = 4;
             // Update total
            row.cells[12].children[0].value = 16;
              calculateTotals();
            return;
        }

if (bhar === 17) {
            // Clear all values first
            for (let i = 0; i < percentages.length; i++) {
                row.cells[i+1].children[0].value = 0;
            }
           // Set अनुसूचित जाती (column 1) = 2
            row.cells[1].children[0].value = 2;
           // Set अनुसूचित जमाती (column 2) = 2
            row.cells[2].children[0].value = 2;
              // Set विमुक्त जमाती (अ) (column 3) = 1
            row.cells[3].children[0].value = 1;
           // Set भटक्या जमाती (ब) (column 4) = 1
 	    row.cells[4].children[0].value = 1;
	   // Set भटक्या जमाती (क) (column 5) = 1
 	    row.cells[5].children[0].value = 1;
	   // Set विशेष मागास प्रवर्ग(column 7) = 1
            row.cells[7].children[0].value = 1;
	    // Set इतर मागास प्रवर्ग (column 8) = 3
            row.cells[8].children[0].value = 3;
		// Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 1
            row.cells[9].children[0].value = 1;
		// Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 1
            row.cells[10].children[0].value = 1;
             // Set अराखीव  (column 11) = 5
            row.cells[11].children[0].value = 5;
             // Update total
            row.cells[12].children[0].value = 17;
              calculateTotals();
            return;
        }
if (bhar === 18) {
            // Clear all values first
            for (let i = 0; i < percentages.length; i++) {
                row.cells[i+1].children[0].value = 0;
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
	   // Set विशेष मागास प्रवर्ग(column 7) = 0
            row.cells[7].children[0].value = 0;
	    // Set इतर मागास प्रवर्ग (column 8) = 3
            row.cells[8].children[0].value = 3;
		// Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 1
            row.cells[9].children[0].value = 1;
		// Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 1
            row.cells[10].children[0].value = 1;
             // Set अराखीव  (column 11) = 5
            row.cells[11].children[0].value = 5;
             // Update total
            row.cells[12].children[0].value = 18;
              calculateTotals();
            return;
        }
if (bhar === 19) {
            // Clear all values first
            for (let i = 0; i < percentages.length; i++) {
                row.cells[i+1].children[0].value = 0;
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
	   // Set विशेष मागास प्रवर्ग(column 7) = 0
            row.cells[7].children[0].value = 0;
	    // Set इतर मागास प्रवर्ग (column 8) = 3
            row.cells[8].children[0].value = 3;
		// Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 2
            row.cells[9].children[0].value = 2;
		// Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 1
            row.cells[10].children[0].value = 1;
             // Set अराखीव  (column 11) = 5
            row.cells[11].children[0].value = 5;
             // Update total
            row.cells[12].children[0].value = 19;
              calculateTotals();
            return;
        }
if (bhar === 20) {
            // Clear all values first
            for (let i = 0; i < percentages.length; i++) {
                row.cells[i+1].children[0].value = 0;
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
	   // Set विशेष मागास प्रवर्ग(column 7) = 0
            row.cells[7].children[0].value = 0;
	    // Set इतर मागास प्रवर्ग (column 8) = 3
            row.cells[8].children[0].value = 3;
		// Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 2
            row.cells[9].children[0].value = 1;
		// Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 1
            row.cells[10].children[0].value = 2;
             // Set अराखीव  (column 11) = 6
            row.cells[11].children[0].value = 6;
             // Update total
            row.cells[12].children[0].value = 20;
              calculateTotals();
            return;
        }
if (bhar === 21) {
            // Clear all values first
            for (let i = 0; i < percentages.length; i++) {
                row.cells[i+1].children[0].value = 0;
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
	   // Set विशेष मागास प्रवर्ग(column 7) = 0
            row.cells[7].children[0].value = 0;
	    // Set इतर मागास प्रवर्ग (column 8) = 3
            row.cells[8].children[0].value = 3;
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
if (bhar === 22) {
            // Clear all values first
            for (let i = 0; i < percentages.length; i++) {
                row.cells[i+1].children[0].value = 0;
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
	   // Set विशेष मागास प्रवर्ग(column 7) = 0
            row.cells[7].children[0].value = 0;
	    // Set इतर मागास प्रवर्ग (column 8) = 4
            row.cells[8].children[0].value = 4;
		// Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 2
            row.cells[9].children[0].value = 2;
		// Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 2
            row.cells[10].children[0].value = 2;
             // Set अराखीव  (column 11) = 6
            row.cells[11].children[0].value = 6;
             // Update total
            row.cells[12].children[0].value = 22;
              calculateTotals();
            return;
        }
if (bhar === 23) {
            // Clear all values first
            for (let i = 0; i < percentages.length; i++) {
                row.cells[i+1].children[0].value = 0;
            }
           // Set अनुसूचित जाती (column 1) = 3
            row.cells[1].children[0].value = 3;
           // Set अनुसूचित जमाती (column 2) = 3
            row.cells[2].children[0].value = 3;
              // Set विमुक्त जमाती (अ) (column 3) = 1
            row.cells[3].children[0].value = 1;
           // Set भटक्या जमाती (ब) (column 4) = 1
 	    row.cells[4].children[0].value = 1;
	   // Set भटक्या जमाती (क) (column 5) = 1
 	    row.cells[5].children[0].value = 1;
	   // Set विशेष मागास प्रवर्ग(column 7) = 0
            row.cells[7].children[0].value = 0;
	    // Set इतर मागास प्रवर्ग (column 8) = 4
            row.cells[8].children[0].value = 4;
		// Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 2
            row.cells[9].children[0].value = 2;
		// Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 2
            row.cells[10].children[0].value = 2;
             // Set अराखीव  (column 11) = 6
            row.cells[11].children[0].value = 6;
             // Update total
            row.cells[12].children[0].value = 23;
              calculateTotals();
            return;
        }
if (bhar === 24) {
            // Clear all values first
            for (let i = 0; i < percentages.length; i++) {
                row.cells[i+1].children[0].value = 0;
            }
           // Set अनुसूचित जाती (column 1) = 3
            row.cells[1].children[0].value = 3;
           // Set अनुसूचित जमाती (column 2) = 3
            row.cells[2].children[0].value = 3;
              // Set विमुक्त जमाती (अ) (column 3) = 1
            row.cells[3].children[0].value = 1;
           // Set भटक्या जमाती (ब) (column 4) = 1
 	    row.cells[4].children[0].value = 1;
	   // Set भटक्या जमाती (क) (column 5) = 1
 	    row.cells[5].children[0].value = 1;
	   // Set विशेष मागास प्रवर्ग(column 7) = 0
            row.cells[7].children[0].value = 0;
	    // Set इतर मागास प्रवर्ग (column 8) = 4
            row.cells[8].children[0].value = 4;
		// Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 2
            row.cells[9].children[0].value = 2;
		// Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 2
            row.cells[10].children[0].value = 2;
             // Set अराखीव  (column 11) = 8
            row.cells[11].children[0].value = 7;
             // Update total
            row.cells[12].children[0].value = 24;
              calculateTotals();
            return;
        }
if (bhar === 25) {
            // Clear all values first
            for (let i = 0; i < percentages.length; i++) {
                row.cells[i+1].children[0].value = 0;
            }
           // Set अनुसूचित जाती (column 1) = 3
            row.cells[1].children[0].value = 3;
           // Set अनुसूचित जमाती (column 2) = 3
            row.cells[2].children[0].value = 3;
              // Set विमुक्त जमाती (अ) (column 3) = 1
            row.cells[3].children[0].value = 1;
           // Set भटक्या जमाती (ब) (column 4) = 1
 	    row.cells[4].children[0].value = 1;
	   // Set भटक्या जमाती (क) (column 5) = 1
 	    row.cells[5].children[0].value = 1;
	   // Set भटक्या जमाती (ड) (column 6) = 1
 	    row.cells[6].children[0].value = 1;
	   // Set विशेष मागास प्रवर्ग(column 7) = 0
            row.cells[7].children[0].value = 0;
	    // Set इतर मागास प्रवर्ग (column 8) = 4
            row.cells[8].children[0].value = 4;
		// Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 2
            row.cells[9].children[0].value = 2;
		// Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 2
            row.cells[10].children[0].value = 2;
             // Set अराखीव  (column 11) = 7
            row.cells[11].children[0].value = 7;
             // Update total
            row.cells[12].children[0].value = 25;
              calculateTotals();
            return;
        }

if (bhar === 26) {
            // Clear all values first
            for (let i = 0; i < percentages.length; i++) {
                row.cells[i+1].children[0].value = 0;
            }
           // Set अनुसूचित जाती (column 1) = 4
            row.cells[1].children[0].value = 4;
           // Set अनुसूचित जमाती (column 2) = 3
            row.cells[2].children[0].value = 3;
              // Set विमुक्त जमाती (अ) (column 3) = 1
            row.cells[3].children[0].value = 1;
           // Set भटक्या जमाती (ब) (column 4) = 1
 	    row.cells[4].children[0].value = 1;
	   // Set भटक्या जमाती (क) (column 5) = 1
 	    row.cells[5].children[0].value = 1;
	   // Set भटक्या जमाती (ड) (column 6) = 1
 	    row.cells[6].children[0].value = 1;
	   // Set विशेष मागास प्रवर्ग(column 7) = 0
            row.cells[7].children[0].value = 0;
	    // Set इतर मागास प्रवर्ग (column 8) = 4
            row.cells[8].children[0].value = 4;
		// Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 2
            row.cells[9].children[0].value = 2;
		// Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 2
            row.cells[10].children[0].value = 2;
             // Set अराखीव  (column 11) = 7
            row.cells[11].children[0].value = 7;
             // Update total
            row.cells[12].children[0].value = 26;
              calculateTotals();
            return;
        }
if (bhar === 27) {
            // Clear all values first
            for (let i = 0; i < percentages.length; i++) {
                row.cells[i+1].children[0].value = 0;
            }
           // Set अनुसूचित जाती (column 1) = 4
            row.cells[1].children[0].value = 4;
           // Set अनुसूचित जमाती (column 2) = 3
            row.cells[2].children[0].value = 3;
              // Set विमुक्त जमाती (अ) (column 3) = 1
            row.cells[3].children[0].value = 1;
           // Set भटक्या जमाती (ब) (column 4) = 1
 	    row.cells[4].children[0].value = 1;
	   // Set भटक्या जमाती (क) (column 5) = 1
 	    row.cells[5].children[0].value = 1;
	   // Set भटक्या जमाती (ड) (column 6) = 1
 	    row.cells[6].children[0].value = 1;
	   // Set विशेष मागास प्रवर्ग(column 7) = 0
            row.cells[7].children[0].value = 0;
	    // Set इतर मागास प्रवर्ग (column 8) = 4
            row.cells[8].children[0].value = 4;
		// Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 2
            row.cells[9].children[0].value = 2;
		// Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 2
            row.cells[10].children[0].value = 2;
             // Set अराखीव  (column 11) = 7
            row.cells[11].children[0].value = 8;
             // Update total
            row.cells[12].children[0].value = 27;
              calculateTotals();
            return;
        }

if (bhar === 28) {
            // Clear all values first
            for (let i = 0; i < percentages.length; i++) {
                row.cells[i+1].children[0].value = 0;
            }
           // Set अनुसूचित जाती (column 1) = 4
            row.cells[1].children[0].value = 4;
           // Set अनुसूचित जमाती (column 2) = 3
            row.cells[2].children[0].value = 3;
              // Set विमुक्त जमाती (अ) (column 3) = 1
            row.cells[3].children[0].value = 1;
           // Set भटक्या जमाती (ब) (column 4) = 1
 	    row.cells[4].children[0].value = 1;
	   // Set भटक्या जमाती (क) (column 5) = 1
 	    row.cells[5].children[0].value = 1;
	   // Set भटक्या जमाती (ड) (column 6) = 1
 	    row.cells[6].children[0].value = 1;
	   // Set विशेष मागास प्रवर्ग(column 7) = 0
            row.cells[7].children[0].value = 0;
	    // Set इतर मागास प्रवर्ग (column 8) = 5
            row.cells[8].children[0].value = 5;
		// Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 2
            row.cells[9].children[0].value = 2;
		// Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 2
            row.cells[10].children[0].value = 2;
             // Set अराखीव  (column 11) = 7
            row.cells[11].children[0].value = 8;
             // Update total
            row.cells[12].children[0].value = 28;
              calculateTotals();
            return;
        }


if (bhar === 28) {
            // Clear all values first
            for (let i = 0; i < percentages.length; i++) {
                row.cells[i+1].children[0].value = 0;
            }
           // Set अनुसूचित जाती (column 1) = 4
            row.cells[1].children[0].value = 4;
           // Set अनुसूचित जमाती (column 2) = 3
            row.cells[2].children[0].value = 3;
              // Set विमुक्त जमाती (अ) (column 3) = 1
            row.cells[3].children[0].value = 1;
           // Set भटक्या जमाती (ब) (column 4) = 1
 	    row.cells[4].children[0].value = 1;
	   // Set भटक्या जमाती (क) (column 5) = 1
 	    row.cells[5].children[0].value = 1;
	   // Set भटक्या जमाती (ड) (column 6) = 1
 	    row.cells[6].children[0].value = 1;
	   // Set विशेष मागास प्रवर्ग(column 7) = 1
            row.cells[7].children[0].value = 1;
	    // Set इतर मागास प्रवर्ग (column 8) = 5
            row.cells[8].children[0].value = 5;
		// Set सामाजिक आणि शैक्षणिक मागास वर्ग (column 9) = 2
            row.cells[9].children[0].value = 2;
		// Set आर्थिक दृष्ट्या दुर्बल घटक (column 10) = 2
            row.cells[10].children[0].value = 2;
             // Set अराखीव  (column 11) = 7
            row.cells[11].children[0].value = 8;
             // Update total
            row.cells[12].children[0].value = 28;
              calculateTotals();
            return;
        }









        // [All other special cases remain exactly the same...]
        // Special cases from 4 to 32 continue here...
        // ... [The rest of the distributeSanctioned function remains unchanged]

        // Normal distribution for other values
        let distributed = [];
        let sum = 0;

         for (let i = 0; i < percentages.length; i++) {
        let exactVal = bhar * percentages[i] / 100;
        let decimalPart = exactVal - Math.floor(exactVal);
        let val;

        if (decimalPart >= 0.5) {
            val = Math.ceil(exactVal);   // 0.5 किंवा जास्त → round up
        } else {
            val = Math.floor(exactVal);  // 0.5 पेक्षा कमी → floor
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
            row.cells[i+1].children[0].value = distributed[i];
            total += distributed[i];
        }
        row.cells[12].children[0].value = total;
        

        calculateTotals();
    }

    // Load कार्यारत row
    function loadKaryarat() {
        let postName = document.getElementById("post_name").value;
        if(postName.trim() === "") {
            alert("कृपया Post Name द्या!");
            return;
        }
        fetch("load_karyarat.php?post_name=" + encodeURIComponent(postName))
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                let table = document.getElementById("postTable");
                let row = table.rows[3];
                let total = 0;
                for (let i=0; i<11; i++) {
                    row.cells[i+1].children[0].value = data.values[i];
                    total += parseFloat(data.values[i]) || 0;
                }
                row.cells[12].children[0].value = total;
		
                calculateTotals();
            } else {
                alert("माहिती मिळाली नाही!");
            }
        });
    }

    function calculateSebc(){
        let first = parseFloat(document.getElementById("first_year_posts").value) || 0;

        // calculate 10% with decimal
        let percent = first * 0.10;
        document.getElementById("sebc_10percent").value = percent.toFixed(2);

        // rounding rule for available seats
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

        // ✅ एकूण पदे
        document.getElementById("total_posts").value = total;

        // ✅ 10% calculation (with decimal)
        let percent = total * 0.10;
        document.getElementById("sebc_10percent_new").value = percent.toFixed(2);

        // ✅ चालू वर्षातील उपलब्ध पदे (decimal rounding formula)
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
    });
    </script>
</body>
</html>