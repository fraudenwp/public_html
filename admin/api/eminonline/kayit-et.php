<?php
// Hata raporlamayı aç
ini_set('display_errors', 1);
error_reporting(E_ALL);
file_put_contents('debug.log', print_r($_POST, true) . "\n\n", FILE_APPEND);
// Direkt erişimi engelle
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    http_response_code(403);
    die('Direkt erişim yasak');
}

require_once '../../config.php';

$input = file_get_contents('php://input');

// JSON verisini PHP dizisine dönüştürün
$data = json_decode($input, true);

// Verileri değişkenlere atayın
$veri = $data['veri'] ?? null;
$tableVeri = $data['table_veri'] ?? null;
$tableResim = $data['table_resim'] ?? null;
$receivedData = $data['data'] ?? null;

try {
    // Verilerin doğru alınıp alınmadığını kontrol edin
    if ($veri && $tableVeri && $tableResim && $receivedData) {
        $totalItems = count($receivedData);
        $processedItems = 0;

        foreach ($receivedData as $row) {
        $grup = $row['grup'];
        $dil = $row['dil'];

        // Veritabanındaki verilerle karşılaştır
        $query = "SELECT COUNT(*) FROM $tableVeri WHERE grup = :grup AND dil = :dil";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['grup' => $grup, 'dil' => $dil]);
        $count = $stmt->fetchColumn();

        // Dinamik sütun ve değer listesi oluşturma
        $columns = array_keys($row);
        $placeholders = array_map(function($col) { return ':' . $col; }, $columns);

        if ($veri == 'yeni-veriler' && $count == 0) {
			
                // Yeni kayıt ekleme
                $insertQuery = "INSERT INTO $tableVeri (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $insertStmt = $pdo->prepare($insertQuery);
                $insertStmt->execute($row);
			
        } elseif ($veri == 'tum-veriler') {
            if ($count == 0) {
				
                // Yeni kayıt ekleme
                $insertQuery = "INSERT INTO $tableVeri (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $insertStmt = $pdo->prepare($insertQuery);
                $insertStmt->execute($row);
				
            } else {
				
                // Kayıt güncelleme
                $updatePairs = array_map(function($col) { return "$col = :$col"; }, $columns);
                $updateQuery = "UPDATE $tableVeri SET " . implode(', ', $updatePairs) . " WHERE grup = :grup AND dil = :dil";
                $updateStmt = $pdo->prepare($updateQuery);
                $updateStmt->execute($row);
				
            }
        }

        $processedItems++;

        // Her işlem sonrası ilerlemeyi bildir
            echo json_encode([
                'status' => 'progress',
                'current' => $processedItems,
                'total' => $totalItems
            ]) . "\n";
            ob_flush();
            flush();
        }

// İşlem tamamlandığında
        echo json_encode([
            'status' => 'complete',
            'message' => 'Tüm işlemler tamamlandı'
        ]) . "\n";
    } else {
        throw new Exception('Eksik veri');
    }
} catch (Exception $e) {
    // Hata durumunda
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>