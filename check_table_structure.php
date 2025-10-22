<?php
require_once 'config.php';

// Get structure of esans_is_emirleri table
echo "Table: esans_is_emirleri" . PHP_EOL;
$structure_query = 'DESCRIBE esans_is_emirleri';
$result = $connection->query($structure_query);
if($result) {
    echo "Columns:" . PHP_EOL;
    while($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo 'Error: Could not fetch table structure for esans_is_emirleri.' . PHP_EOL;
}

echo PHP_EOL . "Table: esans_is_emri_malzeme_listesi" . PHP_EOL;
$structure_query2 = 'DESCRIBE esans_is_emri_malzeme_listesi';
$result2 = $connection->query($structure_query2);
if($result2) {
    echo "Columns:" . PHP_EOL;
    while($row = $result2->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo 'Error: Could not fetch table structure for esans_is_emri_malzeme_listesi.' . PHP_EOL;
}

echo PHP_EOL . "Table: esanslar" . PHP_EOL;
$structure_query3 = 'DESCRIBE esanslar';
$result3 = $connection->query($structure_query3);
if($result3) {
    echo "Columns:" . PHP_EOL;
    while($row = $result3->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo 'Error: Could not fetch table structure for esanslar.' . PHP_EOL;
}

echo PHP_EOL . "Table: urun_agaci (for essence trees)" . PHP_EOL;
$structure_query4 = 'DESCRIBE urun_agaci';
$result4 = $connection->query($structure_query4);
if($result4) {
    echo "Columns:" . PHP_EOL;
    while($row = $result4->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo 'Error: Could not fetch table structure for urun_agaci.' . PHP_EOL;
}

echo PHP_EOL . "Table: malzemeler" . PHP_EOL;
$structure_query5 = 'DESCRIBE malzemeler';
$result5 = $connection->query($structure_query5);
if($result5) {
    echo "Columns:" . PHP_EOL;
    while($row = $result5->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo 'Error: Could not fetch table structure for malzemeler.' . PHP_EOL;
}
?>