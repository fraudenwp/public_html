<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../config.php');

// Gelen verileri al
$formData = $_POST['formData']; // Form verilerini içeren objeyi al
$tabloAdi = $_POST['tabloAdi']; // Tablo adını al

// Verileri grupla ve şifrele
$groupedData = [];
$commonData = []; // Ortak verileri saklamak için

foreach ($formData as $key => $value) {
    // Sütun adını ve dil bilgisini ayır
    $parts = explode('-', $key);
    if (count($parts) === 2) {
        $sutunAdi = $parts[0]; // Sütun adı
        $dilKodu = $parts[1]; // Dil kodu (tr, en, vb.)
    } else {
        // Eğer dil kodu yoksa, ortak veriler arasına al
        $sutunAdi = $key;
        $dilKodu = null;
    }

    // Şifreleme algoritması belirtilmişse
    if (isset($value['sifrelemeAlgoritmasi']) && $value['sifrelemeAlgoritmasi'] !== "") {
        $sifrelemeAlgoritmasi = $value['sifrelemeAlgoritmasi'];
        $value = $value['value'];

        // Şifreleme işlemi
        if ($sifrelemeAlgoritmasi === 'md5') {
            $value = md5($value);
        } elseif ($sifrelemeAlgoritmasi === 'aes') {
            // AES şifreleme işlemi
            $iv_length = openssl_cipher_iv_length('aes-256-cbc');
            $iv = openssl_random_pseudo_bytes($iv_length);
            if (strlen($iv) !== $iv_length) {
                $iv = str_pad($iv, $iv_length, "\0");
            }
            $value = openssl_encrypt($value, 'aes-256-cbc', $sifreleme_anahtari, 0, $iv);
            $value = base64_encode($iv . $value);
        }
    } else {
        // Şifreleme algoritması belirtilmemişse veya boşsa, veriyi doğrudan al
        $value = $value['value'];
    }

    // Boş değer kontrolü
    if (empty($value)) {
        $value = null; // Boşsa null olarak ayarla
    }

    // Ortak verileri ve dil gruplarını ayır
    if ($dilKodu === null) {
        $commonData[$sutunAdi] = $value;
    } else {
        if (!isset($groupedData[$dilKodu])) {
            $groupedData[$dilKodu] = [];
        }
        $groupedData[$dilKodu][$sutunAdi] = $value;
    }
}

try {
    $responses = [];
    $ilkKayitId = null;
    $ilkDilKodu = null;

    // İlk dil grubu için kayıt ekleme işlemi
    foreach ($groupedData as $dilKodu => $data) {
        // Ortak verileri de gruba ekle
        foreach ($commonData as $commonKey => $commonValue) {
            $data[$commonKey] = $commonValue;
        }

        // Dil sütununu ekle
        $data['dil'] = $dilKodu;

        // İlk kaydın ID'sini alın
        if ($ilkKayitId === null) {
            $ilkDilKodu = $dilKodu;
        }

        // Sütun adlarını ve değerlerini alarak INSERT INTO sorgusu oluştur
        $sutunlar = implode(', ', array_keys($data));
        $placeholders = [];
        $values = [];
        foreach ($data as $key => $value) {
            $placeholders[] = ":$key";
            $values[":$key"] = $value;
        }

        // PDO bağlantısını kullanarak veritabanına ekleme işlemini gerçekleştir
        $stmt = $pdo->prepare("INSERT INTO $tabloAdi ($sutunlar) VALUES (".implode(", ", $placeholders).")");
        $stmt->execute($values);

        // Eklenen kaydın ID'sini al
        $kayitId = $pdo->lastInsertId();

        // İlk kaydın ID'sini kaydet
        if ($ilkKayitId === null) {
            $ilkKayitId = $kayitId;
        }

        // Grup sütununu güncelle
        if ($ilkKayitId !== null) {
            $stmt = $pdo->prepare("UPDATE $tabloAdi SET grup = ? WHERE id = ?");
            $stmt->execute([$ilkKayitId, $kayitId]);
        }

        // Yanıtı array'e ekle
        $responses[] = array(
            "success" => true,
            "kayitId" => $kayitId,
            "dil" => $dilKodu,
            "grupId" => $ilkKayitId // Grup ID'sini yanıt array'ine ekleyin
        );
    }

    // Başarılı yanıtı döndür
    echo json_encode($responses);
} catch (PDOException $e) {
    // Hata durumunda hata mesajını döndür
    echo json_encode(array("success" => false, "error" => $e->getMessage()));
}
?>
