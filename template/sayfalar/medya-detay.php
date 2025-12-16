<?php
// Herhangi bir çıktıdan önce ob_clean kullanın
ob_clean();

// Kesinlikle en başta header'ı ayarlayın
header('Content-Type: application/json');

// Hata raporlamayı kapatın
error_reporting(0);
ini_set('display_errors', 0);

// Veritabanı bağlantısı
require_once '../../config.php';

// POST verilerini al
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id'])) {
    die(json_encode(['success' => false, 'message' => 'ID eksik']));
}

$resimId = intval($input['id']);

try {
    $stmt = $pdo->prepare("SELECT veri FROM medya_galeri WHERE id = :id AND yayin_durumu = 1");
    $stmt->bindParam(':id', $resimId, PDO::PARAM_INT);
    $stmt->execute();
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        $json_data = json_decode($row['veri'], true);
        $resimler = $json_data[0]['data']['resimler'] ?? [];
        die(json_encode(['success' => true, 'images' => $resimler]));
    } else {
        die(json_encode(['success' => false, 'message' => 'Veri bulunamadı']));
    }
} catch (PDOException $e) {
    die(json_encode(['success' => false, 'message' => 'Veritabanı hatası']));
}
?>