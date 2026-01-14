<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');
check_login();

// Get the post name from URL parameter
$post_name = isset($_GET['post_name']) ? trim($_GET['post_name']) : "";

// ✅ Save Logic - FIXED VERSION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['post_name']) && isset($_POST['submit'])) {
        $user_id   = $_SESSION['id'];   // user_id from login session
        $post_name = trim($_POST['post_name']);
        $data      = isset($_POST['data']) ? $_POST['data'] : [];
        $remark    = isset($_POST['remark']) ? trim($_POST['remark']) : "";

        if (empty($post_name)) {
            echo "<script>alert('❌ कृपया पदाचे नाव द्या!');</script>";
        } else {
            // overwrite: delete old records first
            $delete_query = "DELETE FROM user_posts WHERE user_id=? AND post_name=?";
            $delete_stmt = $conn->prepare($delete_query);
            $delete_stmt->bind_param("is", $user_id, $post_name);
            $delete_stmt->execute();
            $delete_stmt->close();

            $success = true;
            $insert_count = 0;

            foreach ($data as $category => $cols) {
                // Prepare values
                $col0 = isset($cols['col0']) ? (int)$cols['col0'] : 0;
                $col1 = isset($cols['col1']) ? (int)$cols['col1'] : 0;
                $col2 = isset($cols['col2']) ? (int)$cols['col2'] : 0;
                $col3 = isset($cols['col3']) ? (int)$cols['col3'] : 0;
                $col4 = isset($cols['col4']) ? (int)$cols['col4'] : 0;
                $col5 = isset($cols['col5']) ? (int)$cols['col5'] : 0;
                $col6 = isset($cols['col6']) ? (int)$cols['col6'] : 0;
                $col7 = isset($cols['col7']) ? (int)$cols['col7'] : 0;
                $col8 = isset($cols['col8']) ? (int)$cols['col8'] : 0;
                $col9 = isset($cols['col9']) ? (int)$cols['col9'] : 0;
                $col10 = isset($cols['col10']) ? (int)$cols['col10'] : 0;
                $total = isset($cols['total']) ? (int)$cols['total'] : 0;

                $stmt = $conn->prepare("INSERT INTO user_posts 
                    (user_id, post_name, category, col0, col1, col2, col3, col4, col5, col6, col7, col8, col9, col10, total, remark) 
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

                if ($stmt) {
                    $stmt->bind_param(
                        "issiiiiiiiiiiiiis",
                        $user_id, $post_name, $category,
                        $col0, $col1, $col2, $col3, $col4,
                        $col5, $col6, $col7, $col8, $col9, $col10,
                        $total,
                        $remark
                    );
                    
                    if ($stmt->execute()) {
                        $insert_count++;
                    } else {
                        $success = false;
                        echo "<script>console.error('Error inserting category $category: " . $stmt->error . "');</script>";
                    }
                    $stmt->close();
                } else {
                    $success = false;
                    echo "<script>console.error('Prepare failed for category $category: " . $conn->error . "');</script>";
                }
            }

            if ($success && $insert_count > 0) {
                echo "<script>
                    alert('✅ डेटा यशस्वीरित्या सेव्ह झाला!');
                    setTimeout(function() {
                        window.location.href = 'my_posts.php?saved=true';
                    }, 1000);
                </script>";
            } else {
                echo "<script>alert('❌ डेटा सेव्ह करताना त्रुटी!');</script>";
            }
        }
    }
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
        .save-btn {
            padding: 10px 20px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
        }
        .save-btn:hover {
            background: #218838;
        }
        .save-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <h2>पदांची माहिती नोंदवा</h2>
    
    <!-- Home Button -->
    <button type="button" class="home-btn" onclick="goToDashboard()">🏠 Home</button>
    <button type="button" class="home-btn" onclick="goToMyPosts()" style="background: #6c757d;">📋 माझ्या पोस्ट्स</button>
    
    <?php if (!empty($post_name)): ?>
    <div class="post-name-info">
        <strong>पदाचे नाव:</strong> <?= htmlspecialchars($post_name) ?>
        <br><small>हा पदावरील माहिती भरण्यासाठी तयार आहात.</small>
    </div>
    <?php endif; ?>

    <form method="POST" id="postForm" onsubmit="return validateForm()">
        <label>पदाचे नाव (Post Name): </label>
        <input type="text" id="post_name_input" name="post_name" required 
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
                "दिनांक",                         // row 4 → मंजूर - कार्यारत
                "संभाव्य_भरवयाची_पदे",   // row 5 → manual input
                "एकूण_भरायची_पदे",               // row 6 → दिनांक + कालावधितील
                "अतिरिक्त_पदे"                    // row 7
            ];

            foreach ($categories as $index => $cat) {
                echo "<tr>";
                echo "<td>$cat</td>";
                for ($i = 0; $i < 11; $i++) {
                    $readonly = ($index == 2 || $index == 4 || $index == 5) ? "readonly" : "";
                    $value = 0;
                    echo "<td><input type='number' name='data[$cat][col$i]' value='$value' oninput='calculateTotals()' $readonly></td>";
                }
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
        <button type="submit" name="submit" id="saveButton" class="save-btn">💾 Save Data</button>
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

    function goToDashboard() {
        window.location.href = 'dashboard.php';
    }
    
    function goToMyPosts() {
        window.location.href = 'my_posts.php';
    }

    // Auto-distribute मंजूर row
    function distributeSanctioned() {
        let bhar = parseFloat(document.getElementById("bharvayachi_pade").value) || 0;
        let percentages = [13, 7, 3, 2.5, 3.5, 2, 2, 19, 10, 10, 28];
        let table = document.getElementById("postTable");
        let row = table.rows[2];

        // For bhar <= 50, use special distribution
        if (bhar <= 50) {
            // Clear all values first
            for (let i = 0; i < percentages.length; i++) {
                row.cells[i+1].children[0].value = 0;
            }
            
            // Calculate based on percentages
            let totalAssigned = 0;
            for (let i = 0; i < percentages.length; i++) {
                let calculated = Math.round(bhar * percentages[i] / 100);
                row.cells[i+1].children[0].value = calculated;
                totalAssigned += calculated;
            }
            
            // Adjust for rounding differences
            let difference = bhar - totalAssigned;
            if (difference !== 0) {
                // Add/subtract from the largest percentage column (अराखीव - column 11)
                let currentValue = parseInt(row.cells[11].children[0].value) || 0;
                row.cells[11].children[0].value = currentValue + difference;
            }
            
            // Update total
            row.cells[12].children[0].value = bhar;
            calculateTotals();
            return;
        }

        // For larger values, use simple percentage calculation
        let distributed = [];
        let sum = 0;

        for (let i = 0; i < percentages.length; i++) {
            let exactVal = bhar * percentages[i] / 100;
            let decimalPart = exactVal - Math.floor(exactVal);
            let val;

            if (decimalPart >= 0.5) {
                val = Math.ceil(exactVal);
            } else {
                val = Math.floor(exactVal);
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
        let postName = document.getElementById("post_name_input").value;
        if(postName.trim() === "") {
            alert("कृपया Post Name द्या!");
            return;
        }
        
        // Show loading
        let button = event.target;
        let originalText = button.innerHTML;
        button.innerHTML = 'लोड होत आहे...';
        button.disabled = true;
        
        fetch("load_karyarat.php?post_name=" + encodeURIComponent(postName))
        .then(res => {
            if (!res.ok) {
                throw new Error('Network response was not ok');
            }
            return res.json();
        })
        .then(data => {
            if(data.success) {
                let table = document.getElementById("postTable");
                let row = table.rows[3];
                let total = 0;
                for (let i=0; i<11; i++) {
                    let value = data.values[i] || 0;
                    row.cells[i+1].children[0].value = value;
                    total += parseFloat(value) || 0;
                }
                row.cells[12].children[0].value = total;
                
                calculateTotals();
                alert('✅ कार्यरत डेटा यशस्वीरित्या लोड झाला!');
            } else {
                alert("माहिती मिळाली नाही!");
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("त्रुटी आली: " + error.message);
        })
        .finally(() => {
            // Restore button
            button.innerHTML = originalText;
            button.disabled = false;
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

    // Form validation
    function validateForm() {
        let postName = document.getElementById('post_name_input').value;
        if (!postName.trim()) {
            alert('कृपया पदाचे नाव द्या!');
            document.getElementById('post_name_input').focus();
            return false;
        }
        
        // Check if मंजूर_पदे has data
        let table = document.getElementById("postTable");
        let approvedRow = table.rows[2];
        let approvedTotal = parseFloat(approvedRow.cells[12].children[0].value) || 0;
        
        if (approvedTotal <= 0) {
            if (!confirm("मंजूर पदांची संख्या 0 आहे. तुम्हाला अजूनही सेव्ह करायचे आहे का?")) {
                return false;
            }
        }
        
        // Show loading on save button
        let saveButton = document.getElementById('saveButton');
        saveButton.innerHTML = 'सेव्ह होत आहे...';
        saveButton.disabled = true;
        
        return true;
    }

    // Auto-focus on मंजूर पदे input when page loads with pre-filled post name
    window.addEventListener('load', function() {
        <?php if (!empty($post_name)): ?>
            document.getElementById('bharvayachi_pade').focus();
        <?php endif; ?>
        calculateTotals(); // Initialize calculations
    });

    // Enable form after load
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('postForm').style.display = 'block';
    });
    </script>
</body>
</html>