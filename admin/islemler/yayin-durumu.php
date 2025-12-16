<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require_once('../config.php');
    header('Content-Type: application/json; charset=utf-8');

    // Gelen verileri al
    $id = $_POST['id']; // Güncellenecek satırın ID'si
    $veri = $_POST['veri']; // Güncellenecek yayin_durumu verisi
    $tablo = $_POST['tablo']; // Tablo adı

    try {
        // UPDATE sorgusunu oluştur ve çalıştır
        $stmt = $pdo->prepare("UPDATE $tablo SET yayin_durumu = :veri WHERE grup = :id");
        $stmt->bindParam(':veri', $veri);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        // Başarılı yanıtı döndür
        echo json_encode(
		array(
		"success" => true,
		"durum-yes" => "Yayın durumu güncelleme işlemi başarılı"),
		JSON_UNESCAPED_UNICODE
		);

    } catch (PDOException $e) {
        // Hata durumunda hata mesajını döndür
			echo json_encode(
				array(
					"success" => false,
					"durum-no" => "Yayın durumu güncelleme işlemi başarılı değil.",
					"error" => $e->getMessage()
				),
				JSON_UNESCAPED_UNICODE
			);
			
			

    }
?>
