<?php
// veri-list.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Özel hata işleyici tanımla
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

// Çıktı tamponlamasını başlat
ob_start();

try {
    // config.php dosyasını dahil et
    require_once '../../../config.php';

    // Hata ayıklama bilgilerini saklamak için bir dizi oluştur
    $debug_info = [];
    $debug_info[] = "PHP Version: " . phpversion();

    function sanitizeTableName($tableName) {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);
    }

    function getNestedValue($array, $keys) {
        foreach ($keys as $key) {
            if (!is_array($array) || !array_key_exists($key, $array)) {
                return null;
            }
            $array = $array[$key];
        }
        return $array;
    }

    function getKapakResmi($jsonData) {
        $data = json_decode($jsonData, true);
        if (isset($data[0]['data']['resimler']) && is_array($data[0]['data']['resimler'])) {
            $kapakResim = null;
            foreach ($data[0]['data']['resimler'] as $resim) {
                if ($resim['kapak_resim'] === 'evet') {
                    $kapakResim = $resim['dosya_adi'];
                    break;
                }
            }
            
            if ($kapakResim) {
                return $kapakResim;
            } elseif (!empty($data[0]['data']['resimler'])) {
                // Kapak resmi yoksa ilk resmi göster
                return $data[0]['data']['resimler'][0]['dosya_adi'];
            }
        }
        
        return null;
    }

    // Veritabanı bağlantısını kontrol et
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception("Geçerli bir PDO bağlantısı bulunamadı.");
    }

    $debug_info[] = "PDO bağlantısı başarıyla kuruldu.";

    // POST verilerini kontrol et
    $required_fields = ['tablo_adi', 'sutun_adi', 'json_sutun_adi', 'tablo_basliklari', 'user_dil'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field])) {
            throw new Exception("Gerekli alan eksik: $field");
        }
    }

    $tablo_adi = sanitizeTableName($_POST['tablo_adi']);
    $json_sutun_adi = $_POST['sutun_adi'];  // JSON verilerinin bulunduğu sütun adı
    $istenilen_sutunlar = explode(', ', $_POST['json_sutun_adi']);
    $tablo_basliklari = explode(', ', $_POST['tablo_basliklari']);
    $user_dil = $_POST['user_dil'];

    $debug_info[] = "Gelen parametreler işlendi: tablo_adi=$tablo_adi, json_sutun_adi=$json_sutun_adi, user_dil=$user_dil";

// Filtreleme parametrelerini al
$filtre_baslik = isset($_POST['veri_filtrele_baslik']) ? $_POST['veri_filtrele_baslik'] : null;
$filtre_veri = isset($_POST['veri_filtrele_veri']) ? $_POST['veri_filtrele_veri'] : null;

// Veritabanı sorgusunu oluştur
$sql = "SELECT * FROM " . $tablo_adi;

// Eğer filtreleme parametreleri varsa sorguya WHERE koşulu ekle
if ($filtre_baslik && $filtre_veri !== null) {
    $sql .= " WHERE veri LIKE '%\"" . $filtre_baslik . "\":\"" . $filtre_veri . "\"%'";
}

$sql .= " ORDER BY sira ASC"; 

$debug_info[] = "SQL sorgusu: $sql";

    $stmt = $pdo->query($sql);

    $html = '<div class="card"><div class="card-body">';
    $html .= '<table id="example1" class="table table-bordered table-striped">';
    $html .= '<thead><tr>';

    // Seçim sütunu için başlık ekle
    $html .= '<th><input type="checkbox" id="select-all" /></th>';

    // Diğer tablo başlıklarını oluştur
    foreach ($tablo_basliklari as $baslik) {
        $html .= "<th>" . htmlspecialchars($baslik) . "</th>";
    }
    $html .= '</tr></thead><tbody>';

    $row_count = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $debug_info[] = "Satır verileri: " . json_encode($row);

        $json_data = json_decode($row[$json_sutun_adi] ?? '[]', true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $debug_info[] = "JSON ayrıştırma hatası: " . json_last_error_msg();
            $json_data = [];
        }
        if (!is_array($json_data) || !isset($json_data[0]['data'])) {
            $json_data = [['data' => $json_data]];
        }

        $html .= '<tr>';
        
        // Seçim kutusu ekle
        $html .= '<td><input type="checkbox" class="row-select" value="' . $row['id'] . '" /></td>';
        
        foreach ($istenilen_sutunlar as $sutun) {
            $html .= '<td>';
            
            switch($sutun) {
                case 'resim':
                    $resim = getKapakResmi($row[$json_sutun_adi]);
                    if ($resim) {
                        $html .= "<img src='" . $baseurl_onyuz . $resim . "' alt='Resim' style='max-width: 65px;'>";
                    } else {
                        $html .= "Resim eklenemedi";
                    }
                    break;
                case 'baslik':
                    $baslik = getNestedValue($json_data[0], ['data', 'diller', $user_dil, 'baslik']);
                    $html .= '<a href="#" class="update-link" data-id="' . $row['id'] . '">'. htmlspecialchars($baslik ?? '') . '</a>';
                    break;
					
                case 'tur_kodu':
                    $tur_kodu = getNestedValue($json_data[0], ['data', 'ortak_alanlar', 'tur_kodu']);
                    $html .= '<b><a href="#" class="update-link" data-id="' . $row['id'] . '">'. htmlspecialchars($tur_kodu ?? '') . '</a></b>';
                    break;					

                case 'sira':
                    $html .= htmlspecialchars($row['sira'] ?? '');
                    break;

				case 'tarih_araligi':
					$baslangic_tarihi = getNestedValue($json_data[0], ['data', 'ortak_alanlar', 'tur_baslangic_tarihi']);
					$bitis_tarihi = getNestedValue($json_data[0], ['data', 'ortak_alanlar', 'tur_bitis_tarihi']);
					
					if ($baslangic_tarihi && $bitis_tarihi) {
						$baslangic_formated = date('d.m.Y', strtotime($baslangic_tarihi));
						$bitis_formated = date('d.m.Y', strtotime($bitis_tarihi));
						$html .= htmlspecialchars("$baslangic_formated / $bitis_formated");
					} else {
						$html .= "Tarih bilgisi mevcut değil";
					}
					break;					
					
                case 'yayin_durumu':
                    $yayin_durumu_id = 'yayin_durumu_' . $row['id'];
                    $yayin_durumu = isset($row['yayin_durumu']) ? intval($row['yayin_durumu']) : 0;
                    $checked = $yayin_durumu === 1 ? 'checked' : '';
                    $label = $yayin_durumu === 1 ? 'Aktif' : 'Pasif';
                   
                    $html .= '<div class="form-group">
                        <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                        <input type="checkbox" class="custom-control-input yayin-durumu-switch" id="' . $yayin_durumu_id . '" ' . $checked . ' data-id="' . $row['id'] . '">
                        <label class="custom-control-label" for="' . $yayin_durumu_id . '"></label>
                        </div>
                    </div>'; 
                    break;
                default:
                    $value = getNestedValue($json_data[0], ['data', 'diller', $user_dil, $sutun]);
                    $html .= is_array($value) ? htmlspecialchars(json_encode($value)) : htmlspecialchars($value ?? '');
            }
            $html .= '</td>';
        }
        $html .= '</tr>';
        $row_count++;
    }

    $html .= '</tbody></table></div></div>';

    $debug_info[] = "Toplam $row_count satır işlendi.";

    $response = json_encode(['html' => $html, 'debug' => $debug_info, 'success' => true]);
    if ($response === false) {
        throw new Exception("JSON kodlama hatası: " . json_last_error_msg());
    }
    echo $response;
} catch (Throwable $e) {
    $error_message = "Hata oluştu: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine();
    error_log($error_message);
    echo json_encode(['error' => $error_message, 'debug' => $debug_info ?? [], 'success' => false]);
} finally {
    // Çıktı tamponlamasını temizle ve sonlandır
    $output = ob_get_clean();
    if (empty($output)) {
        echo json_encode(['error' => 'Boş yanıt', 'debug' => ['Boş çıktı tamponu'], 'success' => false]);
    } else {
        echo $output;
    }
}
?>