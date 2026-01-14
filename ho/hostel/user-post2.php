<?php
session_start();
$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname     = "hostel";
$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
            font-size: 0.9em; 
            color: #000; 
        }
        .btn { 
            padding: 5px 12px; 
            background: #0077cc; 
            color: #fff; 
            border: none; 
            cursor: pointer; 
        }
    </style>
</head>
<body>
    <h2>पदांची माहिती नोंदवा</h2>
    <form method="POST">
        <label>पदाचे नाव (Post Name): </label>
        <input type="text" id="post_name" name="post_name" required>
        <button type="button" class="btn" onclick="loadKaryarat()">कार्यरत भरा</button>
        <br><br>

        <!-- भरवयाची पदे Textbox + Button -->
        <label>भरवयाची पदे: </label>
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
        <button type="submit">Save Data</button>
    </form>

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
        let percentages = [13, 7, 3, 2.5, 3.5, 2, 2, 19, 10, 10, 28];
        let table = document.getElementById("postTable");
        let row = table.rows[2];

        let distributed = [];
        let sum = 0;

        for (let i = 0; i < percentages.length; i++) {
            let val = Math.floor(bhar * percentages[i] / 100);
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
    </script>
</body>
</html>
