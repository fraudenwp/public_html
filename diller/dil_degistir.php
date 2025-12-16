<?php
session_start();

// Seçilen dil kodunu al
if (isset($_POST['dil_kodu'])) {
    $dil_kodu = $_POST['dil_kodu'];

    // Seçilen dil kodunu session değişkenine ekle
    $_SESSION['language'] = $dil_kodu;

    // Başarılı yanıt gönder
    http_response_code(200);
} else {
    // Hata durumunda yanıt gönder
    http_response_code(400);
    echo "Dil kodu belirtilmedi.";
}
?>