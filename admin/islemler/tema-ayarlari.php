
<?php
// islemler/tema-ayarlari.php
use Verot\Upload\Upload;
require_once 'verot/class.upload.php';
require_once '../config.php';
header('Content-Type: application/json; charset=utf-8');

function json_response($status, $message, $data = null) {
    $response = ['status' => $status, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

try {
    $form_data = [
        'tabloadi' => sanitize_input($_POST['tabloadi'] ?? ''),
        'sutun-adi' => sanitize_input($_POST['sutun-adi'] ?? ''),
        'satir-adi' => sanitize_input($_POST['satir-adi'] ?? ''),
        'formturu' => sanitize_input($_POST['formturu'] ?? '')
    ];

    switch ($form_data['formturu']) {
		
		
######## Veri ekle ###########
		
 case 'veri-ekle':
    $yeni_veri = ['id' => uniqid()];

    // Gelen tüm POST verilerini logla
    error_log("Gelen POST verileri: " . print_r($_POST, true));

	// Resim özelliklerini al ve doğrula
	$resim_en = intval($_POST['data-resim_en'] ?? 400);
	$resim_boy = intval($_POST['data-resim_boy'] ?? 150);
	$resim_turu = $_POST['data-resim_turu'] ?? 'jpg';
	$resim_doldur = filter_var($_POST['data-resim_doldur'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
	$resim_kirp = filter_var($_POST['data-resim_kirp'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
	$resim_dizin = $_POST['data-resim_yolu'] ?? '../../resimler/Logolar/';

	// Resim türünü doğrula
	$gecerli_turler = ['jpg', 'jpeg', 'png', 'gif'];
	$resim_turu = in_array($resim_turu, $gecerli_turler) ? $resim_turu : 'jpg';


    // Gelen verileri işle
    foreach ($_POST as $key => $value) {
        if (!in_array($key, ['tabloadi', 'sutun-adi', 'satir-adi', 'formturu'])) {
            $decoded_value = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                // JSON ise decode edilmiş halini kullan
                $yeni_veri[$key] = $decoded_value;
            } else {
                // JSON değilse direkt değeri kullan
                $yeni_veri[$key] = sanitize_input($value);
            }
        }
    }

    // Resim yükleme işlemi
    if (isset($_FILES['resim']) && $_FILES['resim']['error'] == 0) {
        $handle = new Upload($_FILES['resim']);
        if ($handle->uploaded) {
        $handle->file_new_name_body = uniqid();
        $handle->image_resize = true;
        $handle->image_x = $resim_en;
        $handle->image_y = $resim_boy;
        $handle->image_convert = $resim_turu;
		
        // Resim doldurma ve kırpma özelliklerini ayarla
        if ($resim_doldur) {
            $handle->image_ratio_fill = true;
        }
        if ($resim_kirp) {
            $handle->image_ratio_crop = true;
        }		
		
		
		
            $handle->process($resim_dizin);
            if ($handle->processed) {
                // Resim yolunu düzenle
                $resim_yolu = $resim_dizin . $handle->file_dst_name;
                $resim_yolu = str_replace('../../', '', $resim_yolu); // '../' karakterlerini kaldır
                $yeni_veri['Resim'] = $resim_yolu;
                $handle->clean();
            } else {
                throw new Exception('Resim yüklenirken hata oluştu: ' . $handle->error);
            }
        }
    }

    // İşlenmiş yeni veriyi logla
    error_log("Resim işleme özellikleri: Doldur: " . ($resim_doldur ? 'true' : 'false') . ", Kırp: " . ($resim_kirp ? 'true' : 'false'));

    // Veritabanı işlemleri
    $stmt = $pdo->prepare("SELECT veri FROM {$form_data['tabloadi']} WHERE {$form_data['sutun-adi']} = :satir_adi");
    $stmt->execute(['satir_adi' => $form_data['satir-adi']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $mevcut_veri = $result ? json_decode($result['veri'], true) : [];

    $mevcut_veri[] = $yeni_veri;
    $json_veri = json_encode($mevcut_veri, JSON_UNESCAPED_UNICODE);

    // Güncellenmiş veriyi logla
    //error_log("Güncellenmiş veri: " . print_r($mevcut_veri, true));

    $stmt = $pdo->prepare("UPDATE {$form_data['tabloadi']} SET veri = :veri WHERE {$form_data['sutun-adi']} = :satir_adi");
    $stmt->execute([
        'veri' => $json_veri,
        'satir_adi' => $form_data['satir-adi']
    ]);

    json_response('success', 'Veri başarıyla kaydedildi.');
    break;

	########## Veri Sil ####################################################################################################

			case 'veri-sil':
				// Veritabanından mevcut veriyi al
				$stmt = $pdo->prepare("SELECT veri FROM {$form_data['tabloadi']} WHERE {$form_data['sutun-adi']} = :satir_adi");
				$stmt->execute(['satir_adi' => $form_data['satir-adi']]);
				$result = $stmt->fetch(PDO::FETCH_ASSOC);

				if ($result && isset($result['veri'])) {
					// JSON formatındaki veriyi diziye çevir
					$mevcut_veri = json_decode($result['veri'], true);

					// Seçilen checkbox değerlerini al
					$selected_checkboxes = json_decode($_POST['selectedCheckboxes'], true);

					// Seçilen checkbox değerlerini mevcut veriden çıkar
					$mevcut_veri = array_filter($mevcut_veri, function($veri) use ($selected_checkboxes) {
						return !in_array($veri['id'], $selected_checkboxes);
					});

					// Veriyi tekrar JSON formatına çevir
					$json_veri = json_encode(array_values($mevcut_veri), JSON_UNESCAPED_UNICODE);

					// Veritabanına güncel veriyi kaydet
					$stmt = $pdo->prepare("UPDATE {$form_data['tabloadi']} SET veri = :veri WHERE {$form_data['sutun-adi']} = :satir_adi");
					$stmt->execute([
						'veri' => $json_veri,
						'satir_adi' => $form_data['satir-adi']
					]);

					// Başarılı yanıt döndür
					json_response('success', 'Veri başarıyla silindi.');
				} else {
					// Mevcut veri bulunamadı durumunda hata yanıtı döndür
					json_response('error', 'Mevcut veri bulunamadı.');
				}
				break;
###################### Veri Güncelle ######################################

case 'veri-guncelle':
    try {
        // Temel verileri al
        $id = sanitize_input($_POST['id'] ?? '');
        $eski_resim_yolu = sanitize_input($_POST['Eski-Resim-Yolu'] ?? '');
        $yeni_veri = [];

        // Gelen tüm POST verilerini işle
        foreach ($_POST as $key => $value) {
            if (!in_array($key, ['tabloadi', 'sutun-adi', 'satir-adi', 'formturu', 'data-resim_en', 'data-resim_boy', 'data-resim_turu', 'data-resim_yolu', 'data-resim_doldur', 'data-resim_kirp'])) {
                // Çoklu dil alanları için JSON decode
                $decoded_value = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded_value)) {
                    $yeni_veri[$key] = $decoded_value;
                } else {
                    $yeni_veri[$key] = sanitize_input($value);
                }
            }
        }

        // Resim yükleme işlemi - sadece yeni resim seçilmişse
        if (isset($_FILES['resim']) && $_FILES['resim']['error'] == 0 && $_FILES['resim']['size'] > 0) {
            $handle = new Upload($_FILES['resim']);
            if ($handle->uploaded) {
                $handle->file_new_name_body = uniqid();
                $handle->image_resize = true;
                $handle->image_x = intval($_POST['data-resim_en'] ?? 400);
                $handle->image_y = intval($_POST['data-resim_boy'] ?? 150);
                $handle->image_convert = $_POST['data-resim_turu'] ?? 'jpg';
                $handle->image_ratio_fill = filter_var($_POST['data-resim_doldur'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
                $handle->image_ratio_crop = filter_var($_POST['data-resim_kirp'] ?? 'false', FILTER_VALIDATE_BOOLEAN);

                $resim_dizin = $_POST['data-resim_yolu'] ?? '../../resimler/Logolar/';
                $handle->process($resim_dizin);
                if ($handle->processed) {
                    $yeni_veri['Resim'] = str_replace('../../', '', $resim_dizin . $handle->file_dst_name);
                    $handle->clean();
                    // Eski resmi sil
                    if (!empty($eski_resim_yolu) && file_exists('../../' . $eski_resim_yolu)) {
                        unlink('../../' . $eski_resim_yolu);
                    }
                } else {
                    throw new Exception('Resim yüklenirken hata oluştu: ' . $handle->error);
                }
            }
        } else {
            // Yeni resim yüklenmemişse, Resim alanını yeni veriden kaldır
            unset($yeni_veri['Resim']);
        }

        // Veritabanından mevcut veriyi al
        $stmt = $pdo->prepare("SELECT veri FROM {$_POST['tabloadi']} WHERE {$_POST['sutun-adi']} = :satir_adi");
        $stmt->execute(['satir_adi' => $_POST['satir-adi']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $mevcut_veri = $result ? json_decode($result['veri'], true) : [];

        // Güncelleme işlemi
        $guncellendi = false;
        foreach ($mevcut_veri as &$veri) {
            if ($veri['id'] == $id) {
                // Resim yüklenmemişse, mevcut resim yolunu koru
                if (!isset($yeni_veri['Resim'])) {
                    $yeni_veri['Resim'] = $veri['Resim'];
                }
                
                // Mevcut veriyi yeni veri ile birleştir
                foreach ($yeni_veri as $key => $value) {
                    if (is_array($value)) {
                        // Çoklu dil desteği olan alanlar için
                        if (!isset($veri[$key]) || !is_array($veri[$key])) {
                            $veri[$key] = [];
                        }
                        $veri[$key] = array_merge($veri[$key], $value);
                    } else {
                        // Diğer alanlar için
                        $veri[$key] = $value;
                    }
                }
                
                $guncellendi = true;
                break;
            }
        }

        if ($guncellendi) {
            $json_veri = json_encode($mevcut_veri, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
            $stmt = $pdo->prepare("UPDATE {$_POST['tabloadi']} SET veri = :veri WHERE {$_POST['sutun-adi']} = :satir_adi");
            $stmt->execute([
                'veri' => $json_veri,
                'satir_adi' => $_POST['satir-adi']
            ]);
            
            json_response('success', 'Veri başarıyla güncellendi.');
        } else {
            throw new Exception('Güncellenecek veri bulunamadı.');
        }
    } catch (PDOException $e) {
        error_log('Veritabanı hatası: ' . $e->getMessage());
        json_response('error', 'Veritabanı hatası oluştu: ' . $e->getMessage());
    } catch (Exception $e) {
        error_log('Hata: ' . $e->getMessage());
        json_response('error', $e->getMessage());
    }
    break;
	
################## Yayın durumu ##########################################################
case 'yayin-durumu':
    error_log("yayin-durumu case'ine girildi");
    $id = sanitize_input($_POST['id'] ?? '');
    $yayin_durumu = intval($_POST['yayin_durumu'] ?? 0);
    $tabloadi = sanitize_input($_POST['tabloadi'] ?? '');
    $sutun_adi = sanitize_input($_POST['sutun-adi'] ?? '');
    $satir_adi = sanitize_input($_POST['satir-adi'] ?? '');

    error_log("Alınan veriler: id=$id, yayin_durumu=$yayin_durumu, tabloadi=$tabloadi, sutun_adi=$sutun_adi, satir_adi=$satir_adi");

    if (empty($id) || empty($tabloadi) || empty($sutun_adi) || empty($satir_adi)) {
        error_log("Eksik veri: id, tabloadi, sutun_adi veya satir_adi boş");
        json_response('error', 'Eksik veri gönderildi.');
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT veri FROM $tabloadi WHERE $sutun_adi = :satir_adi");
        $stmt->execute(['satir_adi' => $satir_adi]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $mevcut_veri = json_decode($result['veri'], true);
            error_log("Mevcut veri: " . print_r($mevcut_veri, true));

            $guncellendi = false;
            foreach ($mevcut_veri as &$veri) {
                if ($veri['id'] == $id) {
                    $veri['Yayın_Durumu'] = $yayin_durumu;
                    $guncellendi = true;
                    break;
                }
            }

            if ($guncellendi) {
                $json_veri = json_encode($mevcut_veri, JSON_UNESCAPED_UNICODE);
                $stmt = $pdo->prepare("UPDATE $tabloadi SET veri = :veri WHERE $sutun_adi = :satir_adi");
                $stmt->execute([
                    'veri' => $json_veri,
                    'satir_adi' => $satir_adi
                ]);

                error_log("Veri güncellendi");
                json_response('success', 'Yayın durumu başarıyla güncellendi.');
            } else {
                error_log("Güncellenecek veri bulunamadı: id=$id");
                json_response('error', 'Güncellenecek veri bulunamadı.');
            }
        } else {
            error_log("Veri bulunamadı: tabloadi=$tabloadi, sutun_adi=$sutun_adi, satir_adi=$satir_adi");
            json_response('error', 'Veri bulunamadı.');
        }
    } catch (PDOException $e) {
        error_log("PDO hatası: " . $e->getMessage());
        json_response('error', 'Veritabanı hatası oluştu.');
    } catch (Exception $e) {
        error_log("Genel hata: " . $e->getMessage());
        json_response('error', 'Bir hata oluştu.');
    }
    break;
			

case 'veri-cek':
    $id = sanitize_input($_POST['id'] ?? '');
    $stmt = $pdo->prepare("SELECT veri FROM {$form_data['tabloadi']} WHERE {$form_data['sutun-adi']} = :satir_adi");
    $stmt->execute(['satir_adi' => $form_data['satir-adi']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && isset($result['veri'])) {
        $veri_array = json_decode($result['veri'], true);
        $veri = array_filter($veri_array, function($item) use ($id) {
            return $item['id'] === $id;
        });

        if (!empty($veri)) {
            json_response('success', 'Veri başarıyla alındı.', reset($veri));
        } else {
            json_response('error', 'Belirtilen ID ile veri bulunamadı.');
        }
    } else {
        json_response('error', 'Veri bulunamadı.');
    }
    break;

			

        default:
            json_response('error', 'Geçersiz form türü.');
    }

} catch (PDOException $e) {
    error_log($e->getMessage());
    json_response('error', 'Veritabanı hatası oluştu: ' . $e->getMessage());
} catch (Exception $e) {
    json_response('error', $e->getMessage());
}
?> 
