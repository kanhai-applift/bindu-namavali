<?php
session_start();
$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname     = "hostel";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) die("Connection failed: ".$conn->connect_error);

// ✅ Save Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_name'])) {
    $user_id = $_SESSION['id'] ?? 1; // temporary default 1
    $post_name = $_POST['post_name'];
    $data = $_POST['data'] ?? [];

    // Delete old records
    $conn->query("DELETE FROM user_posts WHERE user_id='$user_id' AND post_name='$post_name'");

    // Insert new records
    $stmt = $conn->prepare("INSERT INTO user_posts
        (col0,col1,col2,col3,col4,col5,col6,col7,col8,col9,col10,total,user_id,post_name,category)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

    foreach($data as $category=>$cols){
        $stmt->bind_param(
            "iiiiiiiiiiiisss",
            $cols['col0'],$cols['col1'],$cols['col2'],$cols['col3'],$cols['col4'],
            $cols['col5'],$cols['col6'],$cols['col7'],$cols['col8'],$cols['col9'],$cols['col10'],
            $cols['total'],$user_id,$post_name,$category
        );
        $stmt->execute();
    }
    $stmt->close();
    echo "<script>alert('✅ डेटा सेव्ह झाला!');</script>";
}

// ✅ Load data for a post
$loaded_data = [];
if(isset($_GET['post_name'])){
    $user_id = $_SESSION['id'] ?? 1;
    $post_name = $_GET['post_name'];
    $sql = "SELECT category,col0,col1,col2,col3,col4,col5,col6,col7,col8,col9,col10,total 
            FROM user_posts WHERE user_id='$user_id' AND post_name='$post_name'";
    $res = $conn->query($sql);
    if($res && $res->num_rows>0){
        while($row=$res->fetch_assoc()){
            $cols=[];
            for($i=0;$i<=10;$i++) $cols["col$i"]=(int)$row["col$i"];
            $cols['total']=(int)$row['total'];
            $loaded_data[$row['category']]=$cols;
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="mr">
<head>
<meta charset="UTF-8">
<title>Post Entry</title>
<style>
table, th, td { border:1px solid black; border-collapse: collapse; padding:5px; text-align:center; }
th { background:#f2a65a; }
td:first-child { font-weight:bold; background:#f9e7c4; }
input { width:70px; text-align:right; font-size:18px; font-weight:bold; }
input[readonly] { background:#eee; font-weight:bold; font-size:18px; }
.percent-guide { font-weight:bold; font-size:0.9em; color:#000; }
.btn { padding:5px 12px; background:#0077cc; color:#fff; border:none; cursor:pointer; }
</style>
</head>
<body>
<h2>पदांची माहिती नोंदवा</h2>
<form method="POST">
    <label>पदाचे नाव (Post Name): </label>
    <input type="text" id="post_name" name="post_name" value="<?php echo $_GET['post_name'] ?? ''; ?>" required>
    <button type="button" class="btn" onclick="loadPostTable()">Load Data</button>
    <br><br>

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

        <tr>
            <td>प्रतिशत (%)</td>
            <?php
            $percentages=[13,7,3,2.5,3.5,2,2,19,10,10,28];
            foreach($percentages as $p) echo "<td class='percent-guide'>{$p}%</td>";
            echo "<td class='percent-guide'>100%</td>";
            ?>
        </tr>

        <?php
        $categories=["मंजूर","कार्यारत","दिनांक","कालावधितील_संभव_भरवयाची_पदे","एकूण_भरायची_पदे","अतिरिक्त_पदे"];
        foreach($categories as $index=>$cat){
            echo "<tr>";
            echo "<td>$cat</td>";
            for($i=0;$i<11;$i++){
                $readonly = ($index==2 || $index==4 || $index==5) ? "readonly" : "";
                $value = $loaded_data[$cat]["col$i"] ?? 0;
                echo "<td><input type='number' name='data[$cat][col$i]' value='$value' oninput='calculateTotals()' $readonly></td>";
            }
            $total = $loaded_data[$cat]['total'] ?? 0;
            echo "<td><input type='number' name='data[$cat][total]' value='$total' readonly></td>";
            echo "</tr>";
        }
        ?>
    </table>
    <br>
    <button type="submit">Save Data</button>
</form>

<script>
function calculateTotals(){
    let table=document.getElementById("postTable");
    let rows=table.rows.length;

    for(let r=2;r<rows;r++){
        let sum=0;
        for(let c=1;c<=11;c++) sum+=parseFloat(table.rows[r].cells[c].children[0].value)||0;
        table.rows[r].cells[12].children[0].value=sum;
    }

    let approved=table.rows[2], active=table.rows[3], date=table.rows[4];
    for(let c=1;c<=11;c++){
        date.cells[c].children[0].value=(parseFloat(approved.cells[c].children[0].value)||0)-(parseFloat(active.cells[c].children[0].value)||0);
    }
    let tSum=0; for(let c=1;c<=11;c++) tSum+=parseFloat(date.cells[c].children[0].value)||0;
    date.cells[12].children[0].value=tSum;

    let totalRow=table.rows[6], kalavRow=table.rows[5];
    for(let c=1;c<=11;c++){
        totalRow.cells[c].children[0].value=(parseFloat(date.cells[c].children[0].value)||0)+(parseFloat(kalavRow.cells[c].children[0].value)||0);
    }
    let tSum2=0; for(let c=1;c<=11;c++) tSum2+=parseFloat(totalRow.cells[c].children[0].value)||0;
    totalRow.cells[12].children[0].value=tSum2;

    let extraRow=table.rows[7];
    for(let c=1;c<=12;c++){
        let val=parseFloat(totalRow.cells[c].children[0].value)||0;
        extraRow.cells[c].children[0].value=(val<0)?Math.abs(val):0;
    }
}

function distributeSanctioned(){
    let bhar=parseFloat(document.getElementById("bharvayachi_pade").value)||0;
    let percentages=[13,7,3,2.5,3.5,2,2,19,10,10,28];
    let row=document.getElementById("postTable").rows[2];

    let distributed=[],sum=0;
    for(let i=0;i<percentages.length;i++){
        let val=Math.floor(bhar*percentages[i]/100);
        distributed.push(val); sum+=val;
    }
    let diff=bhar-sum,i=0;
    while(diff>0){ distributed[i%distributed.length]++; diff--; i++; }

    let total=0;
    for(let i=0;i<distributed.length;i++){ row.cells[i+1].children[0].value=distributed[i]; total+=distributed[i]; }
    row.cells[12].children[0].value=total;
    calculateTotals();
}

function loadPostTable(){
    let postName=document.getElementById("post_name").value;
    if(postName.trim()===""){ alert("कृपया Post Name द्या!"); return; }

    fetch("?post_name="+encodeURIComponent(postName))
    .then(res=>res.text())
    .then(html=>{
        location.href="?post_name="+encodeURIComponent(postName);
    });
}
</script>
</body>
</html>
