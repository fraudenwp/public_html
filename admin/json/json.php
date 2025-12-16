<?php
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

require_once('../config.php');
require_once('../fonksiyon.php');

// Gelen İstek Bilgileri
$istekKullaniciAdi = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
$istekSifre = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
$istekAPIKodu = isset($_GET['api_kodu']) ? $_GET['api_kodu'] : '';
$istekIP = isset($_GET['ip_adres']) ? $_GET['ip_adres'] : '';
$yayin_durumu = '1';

// Komut parametresini al
$komut = isset($_GET['komut']) ? $_GET['komut'] : '';

// Kullanıcı Adı Oluşturma Fonksiyonu
function kullaniciAdiOlustur($kurumKodu, $id) {
    return $kurumKodu . '-' . $id;
}

// Erişim Kontrol Fonksiyonu
function erisimKontrol($pdo, $istekKullaniciAdi, $istekSifre, $istekAPIKodu, $istekIP, $yayin_durumu) {
    // Kullanıcı adını kurumlar tablosundan oluştur
    $query = $pdo->prepare("SELECT kurum_kodu, id FROM kurumlar WHERE CONCAT(kurum_kodu, '-', id) = ?");
    $query->execute([$istekKullaniciAdi]);
    $kurumBilgisi = $query->fetch(PDO::FETCH_ASSOC);

    if (!$kurumBilgisi) {
        return false; // Kullanıcı adı geçersiz
    }

    // API Kodu, Şifre, IP Adresi ve Yayın Durumu kontrolü
    $query = $pdo->prepare("SELECT * FROM kurumlar WHERE kurum_kodu = ? AND api_kodu = ? AND sifre = ? AND kurum_ip = ? AND yayin_durumu = ?");
    $query->execute([$kurumBilgisi['kurum_kodu'], $istekAPIKodu, $istekSifre, $istekIP, $yayin_durumu]);
    $sonuc = $query->fetch(PDO::FETCH_ASSOC);

    if (!$sonuc) {
        return false; // Erişim reddedildi
    }

    return true; // Erişim izni verildi
}

// Erişim Kontrolü
if (!erisimKontrol($pdo, $istekKullaniciAdi, $istekSifre, $istekAPIKodu, $istekIP, $yayin_durumu)) {
    //header('HTTP/1.0 403 Forbidden');
    echo 'Erişim reddedildi.<br>';
    //exit;
} else {
    // Komut işleme
    switch ($komut) {
		case 'diller':
			// Dilleri Çek
			$query = $pdo->query("SELECT id, kod, baslik, COALESCE(NULLIF(resim, ''), 'resimler/resim-yok.jpg') AS resim FROM diller");
			$diller = $query->fetchAll(PDO::FETCH_ASSOC);

			// Dizini düzelt
			foreach ($diller as &$dil) {
				$dil['resim'] = $dizinurl . '' . $dil['resim'];
			}
			unset($dil); // Referansı temizle

			// JSON verisini oluştur
			$jsonveri = json_encode($diller, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

			// Dilleri JSON dosyasına kaydet
			$jsonDosyasi = $komut . '.json';
			file_put_contents($jsonDosyasi, $jsonveri);

			echo $jsonveri;
			break;

        case 'kategoriler':
            // Kategorileri çek
			$query = $pdo->query("SELECT * FROM kategoriler");
			$kategoriler = $query->fetchAll(PDO::FETCH_ASSOC);

			// JSON verisini oluştur
			$jsonveri = json_encode($kategoriler, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

			// Dilleri JSON dosyasına kaydet
			$jsonDosyasi = $komut . '.json';
			file_put_contents($jsonDosyasi, $jsonveri);
			
			echo $jsonveri;
			
            // Kategorileri Resim Çek

			$query = $pdo->query("SELECT * FROM kategori_resim");
			$resim = $query->fetchAll(PDO::FETCH_ASSOC);
			// JSON verisini oluştur
			$jsonveri = json_encode($resim, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
			// Dilleri JSON dosyasına kaydet
			$jsonDosyasi = 'kategori_resim.json';
			file_put_contents($jsonDosyasi, $jsonveri);			
			
            break;


        case 'urunler':
            // Kategorileri işle
            $query = $pdo->query("SELECT * FROM urunler");
			$urunler = $query->fetchAll(PDO::FETCH_ASSOC);
			// JSON verisini oluştur
			$jsonveri = json_encode($urunler, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
			// Ürünler JSON dosyasına kaydet
			$jsonDosyasi = $komut . '.json';
			file_put_contents($jsonDosyasi, $jsonveri);
			echo $jsonveri;
			
            // Ürün Resim Çek
			$query = $pdo->query("SELECT * FROM urun_resim");
			$resim = $query->fetchAll(PDO::FETCH_ASSOC);
			// JSON verisini oluştur
			$jsonveri = json_encode($resim, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
			// Dilleri JSON dosyasına kaydet
			$jsonDosyasi = 'urun_resim.json';
			file_put_contents($jsonDosyasi, $jsonveri);		

			
            break;
			
			
        default:
            echo 'Geçersiz komut.<br>';
            break;
    }
}
?>
