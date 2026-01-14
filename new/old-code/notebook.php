<?php

// Function to get category name from bindu_kramaank (supports numbers > 100)
function getCategoryName($bindu_kramaank) {
    // Convert to integer and handle numbers > 100
    $val = intval($bindu_kramaank);
    
    // If value is greater than 100, use modulo 100 to get equivalent number
    if ($val > 100) {
        $val = $val % 100;
        // If modulo result is 0, it means it's a multiple of 100, so use 100
        if ($val === 0) {
            $val = 100;
        }
    }

    // Categories mapping (1-100)
    $sc   = [1,12,21,27,37,43,51,61,67,73,81,91,97];
    $st   = [2,23,33,53,63,71,93];
    $vjA  = [3,41,83];
    $bjB  = [4,47,99];
    $bjC  = [7,31,57,99];
    $bjD  = [11,77];
    $smp  = [15,87];
    $obc  = [5,9,17,19,25,29,35,39,45,49,55,59,65,69,75,79,85,89,95];
    $ssmv = [6,13,24,36,42,54,66,74,84,96];
    $ews  = [8,16,26,38,46,56,68,76,86,98];
    $open = [10,14,18,20,22,28,30,32,34,40,44,48,50,52,58,60,62,64,70,72,78,80,82,88,90,92,94,100];

    if (in_array($val, $sc))       return "अनुसूचित जाती";
    else if (in_array($val, $st))  return "अनुसूचित जमाती";
    else if (in_array($val, $vjA)) return "विमुक्त जमाती (अ)";
    else if (in_array($val, $bjB)) return "भटक्या जमाती (ब)";
    else if (in_array($val, $bjC)) return "भटक्या जमाती (क)";
    else if (in_array($val, $bjD)) return "भटक्या जमाती (ड)";
    else if (in_array($val, $smp)) return "विशेष मागास प्रवर्ग";
    else if (in_array($val, $obc)) return "इतर मागास प्रवर्ग";
    else if (in_array($val, $ssmv)) return "सामाजिक आणि शैक्षणिक मागास वर्ग";
    else if (in_array($val, $ews)) return "आर्थिक दृष्ट्या दुर्बल घटक";
    else if (in_array($val, $open)) return "अराखीव";
    
    return "";
}


$next_bindu_kramaank = 1;
$max_result = $mysqli->query("SELECT MAX(CAST(bindu_no AS UNSIGNED)) as max_bindu FROM employees");
if ($max_result && $max_row = $max_result->fetch_assoc()) {
    $next_bindu_kramaank = $max_row['max_bindu'] + 1;
}

// Get initial category for new entry
$initial_category = getCategoryName($next_bindu_kramaank);