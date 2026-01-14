<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');
check_login();

// Get the post name from URL parameter
$post_name = isset($_GET['post_name']) ? trim($_GET['post_name']) : "";

// ✅ Save Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_name'])) {
    $user_id   = $_SESSION['id'];   // user_id from login session
    $post_name = $_POST['post_name'];
    $data      = $_POST['data'];
    $remark    = isset($_POST['remark']) ? $_POST['remark'] : "";

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
        .home-btn {
            padding: 8px 15px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 15px;
        }
        .remark-container {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        .remark-container label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #007bff;
        }
        .remark-textbox {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            min-height: 80px;
            font-family: Arial, sans-serif;
        }
        .remark-textbox:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }
        .print-btn {
            padding: 8px 20px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-left: 10px;
        }
        .print-btn:hover {
            background: #5a6268;
        }
        .save-pdf-btn {
            padding: 8px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .save-pdf-btn:hover {
            background: #0056b3;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            table, th, td {
                border: 1px solid #000;
            }
            th {
                background: #f2a65a !important;
                -webkit-print-color-adjust: exact;
            }
            td:first-child {
                background: #f9e7c4 !important;
                -webkit-print-color-adjust: exact;
            }
        }
        .category-date-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .category-date-label {
            font-weight: bold;
        }
        .category-date-input {
            width: 120px;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            padding: 4px;
        }
    </style>
</head>
<body>
    <h2>पदांची माहिती नोंदवा</h2>
    
    <!-- Home Button -->
    <button type="button" class="home-btn no-print" onclick="goToDashboard()">🏠 Home</button>
    
    <?php if (!empty($post_name)): ?>
    <div class="post-name-info">
        <strong>पदाचे नाव:</strong> <?= htmlspecialchars($post_name) ?>
        <br><small>हा पदावरील माहिती भरण्यासाठी तयार आहात.</small>
    </div>
    <?php endif; ?>

    <form method="POST" id="postForm">
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
                $percentages = [13, 7, 3, 2.5, 3.5, 2, 2, 19, 10, 10, 28];
                foreach ($percentages as $p) {
                    echo "<td class='percent-guide'>{$p}%</td>";
                }
                echo "<td class='percent-guide'>100%</td>";
                ?>
            </tr>

            <?php
            $categories = [
                "मंजूर_पदे",                          // row 2
                "कार्यारत_पदे",                       // row 3
                "संभाव्य_भरवयाची_पदे",                // row 4 → मंजूर - कार्यारत (WITH DATE PICKER)
                "दिनांक",                             // row 5 → regular text
                "एकूण_भरायची_पदे",                   // row 6 → दिनांक + कालावधितील
                "अतिरिक्त_पदे"                       // row 7
            ];

            foreach ($categories as $index => $cat) {
                echo "<tr>";
                
                // First column (Category column)
                if ($cat === "संभाव्य_भरवयाची_पदे") {
                    // Show "संभाव्य_भरवयाची_पदे" text with date picker in first column
                    echo '<td>';
                    echo '<div class="category-date-wrapper">';
                    echo '<span class="category-date-label">संभाव्य_भरवयाची_पदे</span>';
                    echo '<input type="date" name="data['.$cat.'][category_date]" class="category-date-input" value="' . date('Y-m-d') . '">';
                    echo '</div>';
                    echo '</td>';
                } else {
                    echo "<td>$cat</td>";
                }
                
                // Data columns (columns 1-11)
                for ($i = 0; $i < 11; $i++) {
                    $readonly = ($index == 2 || $index == 4 || $index == 5) ? "readonly" : "";
                    echo "<td><input type='number' name='data[$cat][col$i]' value='0' oninput='calculateTotals()' $readonly></td>";
                }
                
                // Total column (column 12)
                echo "<td><input type='number' name='data[$cat][total]' value='0' readonly></td>";
                echo "</tr>";
            }
            ?>
        </table>
        
        <!-- Remark Textbox -->
        <div class="remark-container">
            <label for="remark">शेरा / Remark:</label>
            <textarea id="remark" name="remark" class="remark-textbox" 
                      placeholder="येथे तुमचा शेरा / टिप्पणी टाका..."></textarea>
        </div>
        
        <br>
        <div class="no-print">
            <button type="submit" class="save-pdf-btn">💾 Save & Upload PDF</button>
            <button type="button" class="print-btn" onclick="printPage()">🖨️ Print</button>
        </div>
       
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

        // ✅ मंजूर - कार्यारत = संभाव्य_भरवयाची_पदे
        let approvedRow = table.rows[2];
        let activeRow   = table.rows[3];
        let possibleRow = table.rows[4];

        let possibleTotal = 0;
        for (let c = 1; c <= 11; c++) {
            let approved = parseFloat(approvedRow.cells[c].children[0].value) || 0;
            let active   = parseFloat(activeRow.cells[c].children[0].value) || 0;
            let diff     = approved - active;
            possibleRow.cells[c].children[0].value = diff; 
            possibleTotal += diff;
        }
        possibleRow.cells[12].children[0].value = possibleTotal;

        // ✅ एकूण_भरायची_पदे = संभाव्य_भरवयाची_पदे (since दिनांक is just date input row)
        let totalRow = table.rows[6];

        let totalSum = 0;
        for (let c = 1; c <= 11; c++) {
            let val = parseFloat(possibleRow.cells[c].children[0].value) || 0;
            totalRow.cells[c].children[0].value = val;
            totalSum += val;
        }
        totalRow.cells[12].children[0].value = totalSum;

        // ✅ अतिरिक्त_पदे = फक्त negative value असल्यास positive करून
        let extraRow = table.rows[7];
        for (let c = 1; c <= 12; c++) {
            let val = parseFloat(totalRow.cells[c].children[0].value) || 0;
            extraRow.cells[c].children[0].value = (val < 0) ? Math.abs(val) : 0;
        }
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
    });
    </script>
</body>
</html>