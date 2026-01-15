<?php
$categories = [
  "अनुसूचित जाती",
  "अनुसूचित जमाती",
  "विमुक्त जमाती (अ)",
  "भटक्या जमाती (ब)",
  "भटक्या जमाती (क)",
  "भटक्या जमाती (ड)",
  "विशेष मागास प्रवर्ग",
  "इतर मागास प्रवर्ग",
  "सामाजिक आणि शैक्षणिक मागास वर्ग",
  "आर्थिक दृष्ट्या दुर्बल घटक",
  "अराखीव"
];

$stmt = $mysqli->prepare("
    INSERT INTO employees (
        organization_id, designation_id, bindu_no, bindu_category,
        employee_name, employee_caste, employee_category,
        date_of_appointment, date_of_birth, date_of_retirement,
        caste_certificate_no, caste_cert_authority,
        caste_validity_certificate_no, validation_committee_name,
        working, remarks, pdf, created_at
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '', NULL, NOW())
");
$orgId = $_SESSION['user_id'];
$designationId = 8;
for ($i = 101; $i <= 200; $i++) {

    $cat = $categories[($i - 1) % count($categories)];
    $vacant = ($i % 2 === 0);

    /* ✅ Assign to variables (IMPORTANT) */
    $employeeName  = $vacant ? 'रिक्त' : "श्री. कर्मचारी $i";
    $employeeCaste = $vacant ? '' : 'कुणबी';
    $employeeCat   = $vacant ? '' : 'इमाव';

    $appointDate   = $vacant ? null : '2026-01-13';
    $birthDate     = $vacant ? null : '1990-01-01';
    $retireDate    = $vacant ? null : '2050-01-01';

    $certNo        = $vacant ? '' : "CERT-$i";
    $certAuth      = $vacant ? '' : 'एस.डी यवतमाळ';
    $validNo       = $vacant ? '' : "VAL-$i";
    $committee     = $vacant ? '' : 'अमरावती-1';

    $working       = $vacant ? 0 : 1;

    $stmt->bind_param(
        'iiisssssssssssi',
        $orgId,
        $designationId,
        $i,
        $cat,
        $employeeName,
        $employeeCaste,
        $employeeCat,
        $appointDate,
        $birthDate,
        $retireDate,
        $certNo,
        $certAuth,
        $validNo,
        $committee,
        $working
    );

    $stmt->execute();
}

$stmt->close();