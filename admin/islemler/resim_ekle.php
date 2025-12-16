<?php
// config.php dosyasını include et
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Kayıt ID'yi al
    $grupId = $_POST['grupId'];

    // Dosyayı al
    $dosya = $_FILES['dosya'];
    
    // Dosyanın yükleneceği dizini al
    $hedefDizin = $_POST['data-hedef-dizin']; 
    
    // Dosyanın Veri tabanı tablo asını al
    $hedefTablo = $_POST['data-resim-tablosu']; 
    
    // Dosya adı ve geçici yolunu al
    $dosyaAdi = $dosya['name'];
    $geciciYol = $dosya['tmp_name'];

    // Dosyanın yükleneceği dizin
    $hedefDizin = "../".$hedefDizin."/";

    // Hedef dizinin varlığını kontrol et, yoksa oluştur
    if (!file_exists($hedefDizin)) {
        mkdir($hedefDizin, 0777, true); // Eksik klasörleri oluştur
    }

    // Dosya uzantısını al
    $dosyaUzantisi = pathinfo($dosyaAdi, PATHINFO_EXTENSION);

    // Yeni dosya adını oluştur (örneğin: 143_resim.jpg)
    $yeniDosyaAdi = $grupId . "_resim." . $dosyaUzantisi;

    // Yüklenen resmi boyutlandır ve boyutlandırma oranlarını hesapla
    list($genislik, $yukseklik) = getimagesize($geciciYol);

    // Hedef boyutları belirle
    $hedefGenislik = $_POST['data-resim-en']; // Hedef genişlik
    $hedefYukseklik = $_POST['data-resim-boy']; // Hedef yükseklik
    $hedefKalite = $_POST['data-resim-kalite']; // Hedef kalite

    // Boyutlandırma oranlarını hesapla
    $oransalGenislik = $hedefGenislik / $genislik;
    $oransalYukseklik = $hedefYukseklik / $yukseklik;

    // En küçük oranı kullanarak resmi boyutlandır
    $oransal = min($oransalGenislik, $oransalYukseklik);
    $yeniGenislik = $genislik * $oransal;
    $yeniYukseklik = $yukseklik * $oransal;

    // Yeni boyutlarda bir resim oluştur
    $resim = imagecreatetruecolor($yeniGenislik, $yeniYukseklik);
    $resimKaynak = imagecreatefromjpeg($geciciYol); // veya imagecreatefrompng() gibi uygun bir fonksiyon kullanın

    // Yeni boyutlara göre resmi kopyala ve boyutlandır
    imagecopyresampled($resim, $resimKaynak, 0, 0, 0, 0, $yeniGenislik, $yeniYukseklik, $genislik, $yukseklik);

    // Eksik kalan kısımları iki kenardan eşit olarak kırpmak için gereken kırpma miktarını hesapla
    $kırpmaMiktariX = ($yeniGenislik - $hedefGenislik) / 2;
    $kırpmaMiktariY = ($yeniYukseklik - $hedefYukseklik) / 2;

    // Resmi kırp
    $kırpılmısResim = imagecrop($resim, ['x' => $kırpmaMiktariX, 'y' => $kırpmaMiktariY, 'width' => $hedefGenislik, 'height' => $hedefYukseklik]);

    // Eğer kırpma işlemi başarılıysa, kırpılmış resmi kaydet
    if ($kırpılmısResim !== false) {
        // Resmi kaydet
        imagejpeg($kırpılmısResim, $hedefDizin . $yeniDosyaAdi, $hedefKalite);
    }

    // Veri tabanına kayıt etme işlemini başlat
    try {
        // SQL sorgusunu hazırla
        $sql = "INSERT INTO $hedefTablo (resim, grup_id) VALUES (:resim, :grup_id)";

        // PDO prepare yöntemiyle sorguyu hazırla
        $stmt = $pdo->prepare($sql);
        
        $resim_yolu = str_replace('../', '', $hedefDizin) . $yeniDosyaAdi;

        // Değişken değerlerini bağla
        $stmt->bindParam(':resim', $resim_yolu, PDO::PARAM_STR);
        $stmt->bindParam(':grup_id', $grupId, PDO::PARAM_STR);

        // Sorguyu çalıştır
        $stmt->execute();

        // Veri tabanına kayıt edildi
        $veriKayitDurumu = true;
    } catch(PDOException $e) {
        // Veri tabanına kayıt edilirken hata oluştu
        $veriKayitDurumu = false;
    }

    // Veriler kayıt edildi mi?
    if ($veriKayitDurumu) {
        // Veriler başarıyla kaydedildi
        echo "Resim başarıyla yüklendi, işlemi başarıyla tamamlandı.";
        echo "\n".$resim_yolu;
    } else {
        // Veriler kaydedilemedi
        echo "Resim başarıyla yüklendi. Ancak, veri tabanına kayıt edilirken bir hata oluştu. Yüklenen Resim Silindi.";
        echo "\nHedef tablo:". $hedefTablo. "\nHedef dizin:" . $hedefDizin;
        // Dizine eklenen resmi sil
        unlink($hedefDizin . $yeniDosyaAdi);
    }

    // Bellekten resim nesnelerini temizle
    imagedestroy($resim);
    imagedestroy($resimKaynak);
    if (isset($kırpılmısResim)) {
        imagedestroy($kırpılmısResim);
    }
} else {
    // POST isteği değilse, bir hata mesajı gönder
    echo "Hatalı istek.";
}
?>
