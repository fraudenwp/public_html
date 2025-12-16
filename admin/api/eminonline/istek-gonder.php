<?php
// CORS ayarları (gerekirse)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// POST verilerini al
$input = file_get_contents('php://input');

// Hata ayıklama: Gelen veriyi logla
error_log("Gelen veri: " . $input);

// Boş input kontrolü
if (empty($input)) {
    echo json_encode(array("error" => "Boş input alındı"));
    exit;
}

$data = json_decode($input, true);

// JSON decode hatasını kontrol et
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(array("error" => "JSON decode hatası: " . json_last_error_msg()));
    exit;
}

// Veri yapısını kontrol et
if (!is_array($data)) {
    echo json_encode(array("error" => "Geçersiz veri yapısı"));
    exit;
}

// Verileri doğrula ve işle
if (
    isset($data['alan_adi']) && 
    isset($data['ip_adres']) && 
    isset($data['api_kod']) && 
    isset($data['istek_url']) && 
    isset($data['veritabani']) && 
    isset($data['veritabani_resim'])
) {
    // API'ye istek gönder
    $ch = curl_init($data['istek_url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'X-Requested-With: XMLHttpRequest'
		));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Hata ayıklama: cURL yanıtını logla
    error_log("cURL yanıtı: " . $response);
    error_log("HTTP Kodu: " . $httpCode);
    
    if ($httpCode == 200) {
        echo $response;
    } else {
        echo json_encode(array("error" => "API isteği başarısız oldu. HTTP Kodu: " . $httpCode));
    }
    
    curl_close($ch);
} else {
    echo json_encode(array("error" => "Eksik veri"));
}
?>