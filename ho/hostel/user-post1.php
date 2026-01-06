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

// Save data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_name = $_POST['post_name'];
    $user_id = 1; // Replace with $_SESSION['user_id'] after login integration

    foreach ($_POST['data'] as $category => $values) {
        $sql = "INSERT INTO posts_data 
            (user_id, post_name, category, 
            anugami_jati, anugami_jamati, vimukt_jamati_a, 
            bhatkya_jamati_b, bhatkya_jamati_c, bhatkya_jamati_d, 
            vishesh_magav_pravarg, itar_magav_pravarg, 
            samajik_magas, arthik_durbal, arakhiv, total)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "issiiiiiiiiiiii",
            $user_id,
            $post_name,
            $category,
            $values['col0'],
            $values['col1'],
            $values['col2'],
            $values['col3'],
            $values['col4'],
            $values['col5'],
            $values['col6'],
            $values['col7'],
            $values['col8'],
            $values['col9'],
            $values['col10'],
            $values['total']
        );
        $stmt->execute();
    }
    echo "<p style='color:green'>✅ Data saved successfully!</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post Entry</title>
    <style>
        table, th, td { border: 1px solid black; border-collapse: collapse; padding: 5px; text-align: center; }
        th { background: #f2a65a; }
        td:first-child { font-weight: bold; background: #f9e7c4; }
        input { width: 60px; text-align: right; }
        input[readonly] { background: #eee; font-weight: bold; }
        .percent-guide { font-weight: bold; font-size: 0.9em; color: #000; }
    </style>
</head>
<body>
    <h2>पदांची माहिती नोंदवा</h2>
    <form method="POST">
        <label>पदाचे नाव (Post Name): </label>
        <input type="text" name="post_name" required><br><br>

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
            <!-- Reference percentages row (guide) -->
            <tr>
                <td>प्रतिशत (%)</td>
                <?php
                $percentages = [13, 7, 3, 2.5, 3.5, 2, 2, 19, 10, 10, 28];
                foreach ($percentages as $p) {
                    echo "<td class='percent-guide'>{$p}%</td>";
                }
                echo "<td class='percent-guide'>100%</td>"; // Total column
                ?>
            </tr>

            <?php
            $categories = [
                "मंजूर",
                "कार्यारत",
                "दिनांक",
                "कालावधितील_संभव_भरवयाची_पदे",
                "एकूण_भरायची_पदे", // auto sum row
                "अतिरिक्त_पदे"       // auto only if negative
            ];

            foreach ($categories as $index => $cat) {
                echo "<tr>";
                echo "<td>$cat</td>";
                for ($i = 0; $i < 11; $i++) {
                    $readonly = ($index >= 4) ? "readonly" : ""; // Auto sum & extra rows readonly
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
    function calculateTotals() {
        let table = document.getElementById("postTable");
        let rows = table.rows.length;

        // Row totals
        for (let r = 2; r < rows; r++) { // skip header + percentage row
            let row = table.rows[r];
            let sum = 0;
            for (let c = 1; c <= 11; c++) {
                let val = parseFloat(row.cells[c].children[0].value) || 0;
                sum += val;
            }
            row.cells[12].children[0].value = sum;
        }

        // Auto-fill "एकूण_भरायची_पदे" (row 5)
        let totalRow = 5;
        for (let c = 1; c <= 12; c++) {
            let colSum = 0;
            for (let r = 2; r < 5; r++) { // first 3 data rows
                colSum += parseFloat(table.rows[r].cells[c].children[0].value) || 0;
            }
            table.rows[totalRow].cells[c].children[0].value = colSum;
        }

        // Auto-fill "अतिरिक्त_पदे" (row 6)
        let extraRow = 6;
        for (let c = 1; c <= 12; c++) {
            let val = parseFloat(table.rows[totalRow].cells[c].children[0].value) || 0;
            table.rows[extraRow].cells[c].children[0].value = (val < 0) ? Math.abs(val) : 0;
        }
    }
    </script>
</body>
</html>
