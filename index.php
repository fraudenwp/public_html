<?php
session_start();
// Hata raporlama düzeyini ayarla
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Özel hata işleyicisini tanımla
function custom_error_handler($errno, $errstr, $errfile, $errline) {
    echo "<b>Hata:</b> [$errno] $errstr - Dosya: $errfile, Satır: $errline<br />";
    die();
}
// Özel hata işleyicisini etkinleştir
set_error_handler("custom_error_handler");

require_once('config.php');
require_once('fonksiyon.php');
require_once 'PageData.php';

// Sayfayı Oluştur
$sayfa = gosterSayfa($url, $pdo); // Sayfa gövdesi fonksiyonu çağır.

ob_start(); // Çıktı tamponlamayı başlat

// Sayfa içeriğini yükle
if (file_exists($sayfa['dosya_yolu'])) {
    include $sayfa['dosya_yolu'];
} else {
    // Dosya bulunamadı, uyarı mesajı gösterin
    $hata_mesaji = 'Belirtilen dizi yok';
    $aciklama = 'İstek yapılan dosya: '.$sayfa['dosya_yolu'];
    require_once('template/sayfalar/500.php');
}

$content = ob_get_clean(); // Tamponlanmış içeriği al ve temizle

// Şimdi üst blokları göster (head.php dahil)
bloklariGoster('ust', $pdo, $sayfa);

echo '<main>';
echo $content; // Sayfa içeriğini göster
echo '</main>';

bloklariGoster('alt', $pdo, $sayfa); // Alttaki blokları göstermek için fonksiyonu çağırın
?>