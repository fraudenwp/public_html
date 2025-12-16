<?php
// Değişkenleri tanımla
$alan_adi   = $_SERVER['SERVER_NAME']; // Sunucu adını alır
$ip_adres   = $_SERVER['SERVER_ADDR']; // Sunucunun IP adresini alır
$api_kod    = '4HHE6)kldyUq25A89mtXSdI8JUQ(w2'; // API kodunu tanımlar
$istek_url  = 'https://eminonline.com/api/istek-al.php'; // İstek URL'sini tanımlar

// POST verilerini al
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$veritabani = isset($data['veritabani']) ? $data['veritabani'] : '';
$veritabani_resim = isset($data['veritabani_resim']) ? $data['veritabani_resim'] : '';

// Verileri bir diziye aktar
$response = [
    'alan_adi' => $alan_adi,
    'ip_adres' => $ip_adres,
    'api_kod' => $api_kod,
    'istek_url' => $istek_url,
    'veritabani' => $veritabani,
    'veritabani_resim' => $veritabani_resim
];

// JSON olarak yazdır
header('Content-Type: application/json');
echo json_encode($response);
?>
