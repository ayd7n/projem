<?php
include 'config.php';

echo "Starting Iterative Currency Fix...\n";

// 1. Get all orders with missing currency
$query = "SELECT siparis_id FROM siparisler WHERE para_birimi IS NULL OR para_birimi = '' OR para_birimi = 'TL'";
$result = $connection->query($query);

if ($result) {
    echo "Found " . $result->num_rows . " orders to check.\n";
    while ($row = $result->fetch_assoc()) {
        $siparis_id = $row['siparis_id'];

        // Find currency from items (check products if item is TL)
        // First check items directly
        $item_curr_query = "SELECT para_birimi FROM siparis_kalemleri WHERE siparis_id = $siparis_id AND para_birimi != 'TRY' LIMIT 1";
        $item_res = $connection->query($item_curr_query);
        $currency = 'TL';

        if ($item_res && $item_res->num_rows > 0) {
            $currency = $item_res->fetch_assoc()['para_birimi'];
        } else {
            // Check products if items don't have explicit currency
            $prod_curr_query = "SELECT u.satis_fiyati_para_birimi 
                                FROM siparis_kalemleri sk 
                                JOIN urunler u ON sk.urun_kodu = u.urun_kodu 
                                WHERE sk.siparis_id = $siparis_id 
                                AND u.satis_fiyati_para_birimi != 'TRY' 
                                LIMIT 1";
            $prod_res = $connection->query($prod_curr_query);
            if ($prod_res && $prod_res->num_rows > 0) {
                $currency = $prod_res->fetch_assoc()['satis_fiyati_para_birimi'];
            }
        }

        if ($currency != 'TL') {
            $update_sql = "UPDATE siparisler SET para_birimi = '$currency' WHERE siparis_id = $siparis_id";
            if ($connection->query($update_sql)) {
                echo "Updated Order #$siparis_id to $currency\n";
            }
        }
    }
} else {
    echo "Error fetching orders.\n";
}

echo "Done.\n";
?>