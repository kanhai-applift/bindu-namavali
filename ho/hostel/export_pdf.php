<?php
session_start();
require('fpdf/fpdf.php'); // Download FPDF and place in folder
include($_SERVER['DOCUMENT_ROOT'] . '/ho/hostel/includes/config.php');
include('includes/checklogin.php');
check_login();

$user_id   = $_SESSION['id']; 
$post_name = isset($_GET['post']) ? trim($_GET['post']) : "";

if (empty($post_name)) {
    die("⚠️ Post not selected!");
}

// Table name
$table_name = "notebook_" . $user_id . "_" . preg_replace('/\s+/', '_', strtolower($post_name));

// Fetch all data
$result = $mysqli->query("SELECT * FROM `$table_name` ORDER BY id ASC");

// Create PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10, "📓 Notebook Report - $post_name",0,1,'C');
$pdf->Ln(5);

// Table Header
$pdf->SetFont('Arial','B',10);
$header = ['ID','बिंदू क्रामांक','बिंदू नामावली','कर्मचार्यांचे नाव','कर्मचारी जात','पद नियुक्त दिनांक','जन्मतारीख','सेवानिरुती दिनांक','जात प्रमाणपत्र','प्रदिकऱ्याचे पदनाव','वैधता प्रमानपत्र','वैधता समिती','कार्यरत','शेरा'];
foreach($header as $col) {
    $pdf->Cell(25,8,$col,1);
}
$pdf->Ln();

// Table Data
$pdf->SetFont('Arial','',9);
while($row = $result->fetch_assoc()) {
    $pdf->Cell(25,8,$row['id'],1);
    $pdf->Cell(25,8,$row['bindu_kramaank'],1);
    $pdf->Cell(25,8,$row['bindu_namavli'],1);
    $pdf->Cell(25,8,$row['karmachari_naam'],1);
    $pdf->Cell(25,8,$row['karmachari_jat'],1);
    $pdf->Cell(25,8,$row['pad_niyukt_dinank'],1);
    $pdf->Cell(25,8,$row['janma_tarik'],1);
    $pdf->Cell(25,8,$row['sevaniroti_dinank'],1);
    $pdf->Cell(25,8,$row['jat_pramanpatra'],1);
    $pdf->Cell(25,8,$row['jat_pramanpatra_pradikar'],1);
    $pdf->Cell(25,8,$row['jat_vaidhta_pramanpatra'],1);
    $pdf->Cell(25,8,$row['jat_vaidhta_samiti'],1);
    $pdf->Cell(25,8,($row['karyarat'] ? "✅" : "❌"),1);
    $pdf->Cell(25,8,$row['shera'],1);
    $pdf->Ln();
}

$pdf->Output("D","notebook_$post_name.pdf");
?>
