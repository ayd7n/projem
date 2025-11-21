<?php
include 'config.php';

echo "Temizlik işlemi başlıyor...\n";

// Delete dummy contracts
$query_contracts = "DELETE FROM cerceve_sozlesmeler WHERE aciklama LIKE 'Otomatik oluşturulan test sözleşmesi%'";
if ($connection->query($query_contracts)) {
    echo "Dummy sözleşmeler silindi. Etkilenen kayıt: " . $connection->affected_rows . "\n";
} else {
    echo "Sözleşme silme hatası: " . $connection->error . "\n";
}

// Delete dummy expenses
$query_expenses = "DELETE FROM gider_yonetimi WHERE aciklama LIKE '%(Oto-Test)'";
if ($connection->query($query_expenses)) {
    echo "Dummy giderler silindi. Etkilenen kayıt: " . $connection->affected_rows . "\n";
} else {
    echo "Gider silme hatası: " . $connection->error . "\n";
}

echo "Temizlik tamamlandı.\n";
?>
