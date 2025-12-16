<?php
// veri-islem.php
use Verot\Upload\Upload;
require_once 'verot/class.upload.php';
require_once '../config.php';
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
error_log("POST verisi: " . print_r($_POST, true));
error_log("FILES verisi: " . print_r($_FILES, true));
header('Content-Type: application/json');

// Hata yakalama fonksiyonu
function errorHandler($errno, $errstr, $errfile, $errline) {
    echo json_encode([
        'success' => false,
        'message' => "PHP Error [$errno] $errstr on line $errline in file $errfile",
    ]);
    exit;
}
set_error_handler("errorHandler");

$response = ['success' => false, 'message' => '', 'data' => null, 'debug' => []];

function debug_log($message) {
    global $response;
    $response['debug'][] = $message;
}

function sanitizeTableName($tableName) {
    return preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? null;
    $tableName = sanitizeTableName($_POST['tablo_adi'] ?? '');

    debug_log("Action: " . $action);
    debug_log("ID: " . ($id ?? 'null'));
    debug_log("Tablo Adı: " . $tableName);

    switch ($action) {
        case 'add':
        case 'update':
            handleAddOrUpdate($action, $id, $tableName);
            break;
		case 'copy':
			handleCopy($tableName);
			break;			
		case 'delete_image':
            handleDeleteImage($tableName);
            break;	
			
		case 'update_sayfa_ayarlar':
            handleUpdateSayfaAyarlar($tableName);
            break;			

		case 'delete_multiple_images':
			handleDeleteMultipleImages($tableName);
			break;
			
		case 'update_cover_image':
			handleUpdateCoverImage($tableName);
			break;			
			
        case 'delete':
            handleDelete($tableName); 
            break;
        case 'update_yayin_durumu':
            handleUpdateYayinDurumu($tableName);
            break;
        case 'get_categories':
            $categories = getCategories($pdo, $_POST['user_lang'] ?? 'tr');
            echo json_encode(['success' => true, 'categories' => $categories]);
            exit;
        case 'get_select_options':
            $tableName = sanitizeTableName($_POST['table_name'] ?? '');
            $userLang = $_POST['user_lang'] ?? 'tr';
            $options = getSelectOptions($pdo, $tableName, $userLang);
            echo json_encode(['success' => true, 'data' => $options]);
            exit;			
        default:
            $response['message'] = 'Geçersiz işlem';
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    $tableName = sanitizeTableName($_GET['tablo_adi'] ?? 'kategori');
    handleGet($tableName);
}

echo json_encode($response);
$output = ob_get_clean(); 
echo $output;

function getSelectOptions($pdo, $tableName, $userLang) {
    $sql = "SELECT id, veri FROM $tableName WHERE yayin_durumu = 1 ORDER BY sira ASC";
    $stmt = $pdo->query($sql);
    $options = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data = json_decode($row['veri'], true);
        $title = '';

        if (isset($data[0]['data']['diller'][$userLang]['baslik'])) {
            $title = $data[0]['data']['diller'][$userLang]['baslik'];
        } elseif (isset($data[0]['data']['diller']['tr']['baslik'])) {
            $title = $data[0]['data']['diller']['tr']['baslik'];
        } else {
            $title = 'Başlık bulunamadı';
        }

        $options[] = ['id' => $row['id'], 'name' => $title];
    }
    return $options;
}

function handleCopy($tableName) {
    global $pdo, $response;
    
    if (isset($_POST['ids']) && is_array($_POST['ids'])) {
        $ids = array_map('intval', $_POST['ids']);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("SELECT * FROM $tableName WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $insertStmt = $pdo->prepare("INSERT INTO $tableName (veri, sira, yayin_durumu, ust_kategori_id) VALUES (?, ?, 0, ?)");
            
            foreach ($rows as $row) {
                $veri = json_decode($row['veri'], true);
                
                // Benzersiz bir son ek oluştur
                $uniqueSuffix = generateUniqueSuffix();
                
                // Başlığa "Kopya" ekleyin, link'i güncelleyin ve resimleri kaldırın
                foreach ($veri[0]['data']['diller'] as $lang => $langData) {
                    $veri[0]['data']['diller'][$lang]['baslik'] = 'Kopya - ' . $langData['baslik'];
                    
                    // Mevcut link'i al ve sonuna benzersiz son ek ekle
                    $currentLink = $langData['link'];
                    $newLink = $currentLink . '-' . $uniqueSuffix;
                    
                    // Yeni link'i set et
                    $veri[0]['data']['diller'][$lang]['link'] = $newLink;
                }
                
                // Resimleri kaldır
                if (isset($veri[0]['data']['resimler'])) {
                    unset($veri[0]['data']['resimler']);
                }
                
                $newVeri = json_encode($veri);
                $insertStmt->execute([$newVeri, $row['sira'], $row['ust_kategori_id']]);
            }
            
            $pdo->commit();
            
            $response['success'] = true;
            $response['message'] = count($rows) . ' kayıt başarıyla kopyalandı (resimler hariç).';
        } catch (Exception $e) {
            $pdo->rollBack();
            $response['success'] = false;
            $response['message'] = 'Kopyalama işlemi sırasında bir hata oluştu: ' . $e->getMessage();
        }
    } else {
        $response['success'] = false;
        $response['message'] = 'Kopyalanacak kayıt seçilmedi.';
    }
}

function generateUniqueSuffix() {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $suffix = '';
    for ($i = 0; $i < 6; $i++) {
        $suffix .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $suffix;
}

function handleAddOrUpdate($action, $id, $tableName) {
    global $pdo, $response;

    try {
        // Ek_form tablosunun varlığını kontrol et
        $ekFormTableExists = checkEkFormTableExists($tableName);

        $formData = $_POST;
        unset($formData['action']);
        unset($formData['tablo_adi']);

        $jsonData = [
            'data' => [
                'diller' => [],
                'resimler' => [],
                'ortak_alanlar' => []
            ]
        ];

        // Dilleri veritabanından çek
        $diller_sorgu = $pdo->query("SELECT kod FROM diller WHERE yayin_durumu = 1 ORDER BY sira ASC");
        $languages = $diller_sorgu->fetchAll(PDO::FETCH_COLUMN);

        // Dil verilerini işle
        foreach ($languages as $lang) {
            $jsonData['data']['diller'][$lang] = [
                'baslik' => $formData['baslik_' . $lang] ?? '',
                'aciklama' => $formData['aciklama_' . $lang] ?? '',
                'meta_baslik' => $formData['meta_baslik_' . $lang] ?? '',
                'meta_aciklama' => $formData['meta_aciklama_' . $lang] ?? '',
                'link' => $formData['link_' . $lang] ?? '',
                'hariciLink' => $formData['hariciLink_' . $lang] ?? '',
                'etiketler' => isset($formData['etiketler_' . $lang]) ? explode(',', $formData['etiketler_' . $lang]) : []
            ];
        }

// Ortak alanları işle
if (isset($formData['json_ortak_alanlar'])) {
    $jsonOrtakAlanlar = json_decode($formData['json_ortak_alanlar'], true);
    if (json_last_error() === JSON_ERROR_NONE) {
        foreach ($jsonOrtakAlanlar as $key => $value) {
            // iframe kontrolü
            if (is_string($value) && strpos($value, '<iframe') !== false) {
                preg_match('/src="([^"]+)"/', $value, $matches);
                if (isset($matches[1])) {
                    $jsonOrtakAlanlar[$key] = $matches[1];
                }
            }
        }
        $jsonData['data']['ortak_alanlar'] = $jsonOrtakAlanlar;
    } else {
        throw new Exception("JSON decode hatası: " . json_last_error_msg());
    }
}

        // Mevcut resimleri işle
        if (isset($formData['existing_images'])) {
            $existingImages = json_decode($formData['existing_images'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                foreach ($existingImages as $image) {
                    $jsonData['data']['resimler'][] = [
                        'dosya_adi' => $image['dosya_adi'],
                        'alt_etiketi' => '',
                        'kapak_resim' => $image['kapak_resim']
                    ];
                }
            }
        }

        // Yeni yüklenen resimleri işle
        if (isset($_FILES['dosya_adi']) && !empty($_FILES['dosya_adi']['name'][0])) {
            $uploadOptions = [
                'resim_eni' => $formData['resim_eni'] ?? 500,
                'resim_boyu' => $formData['resim_boyu'] ?? 500,
                'resim_kayit_dizini' => $formData['resim_kayit_dizini'] ?? '../../resimler/',
                'resmi_kirp' => $formData['resmi_kirp'] ?? 'hayir',
                'resmi_doldur' => $formData['resmi_doldur'] ?? 'hayir',
                'baslik' => $formData['baslik_tr'] ?? ''
            ];
            $uploadedFiles = handleMultipleImageUpload($_FILES['dosya_adi'], $uploadOptions);
            if (!empty($uploadedFiles)) {
                $jsonData['data']['resimler'] = array_merge($jsonData['data']['resimler'], $uploadedFiles);
            }
        }

        // Ek formları işle (eğer ek_form tablosu varsa ve ekleme işlemi ise)
        if ($ekFormTableExists && $action === 'add') {
            $jsonData['data']['ek_formlar'] = handleEkFormlar($formData);
            $ekFormlar = implode(',', array_keys($jsonData['data']['ek_formlar']));
        }

        $veri = json_encode([$jsonData], JSON_UNESCAPED_UNICODE);
        $sira = $formData['sira'] ?? 0;
        $yayin_durumu = isset($formData['yayin_durumu']) && $formData['yayin_durumu'] === '1' ? 1 : 0;
        
        $ust_kategori_id = null;
        if (isset($formData['ust_kategori_id']) && is_array($formData['ust_kategori_id'])) {
            $ust_kategori_id = implode(',', array_filter($formData['ust_kategori_id']));
        } elseif (isset($formData['ust_kategori_id']) && is_string($formData['ust_kategori_id'])) {
            $ust_kategori_id = $formData['ust_kategori_id'];
        }

        if ($action === 'add') {
            if ($ekFormTableExists) {
                $stmt = $pdo->prepare("INSERT INTO $tableName (veri, sira, yayin_durumu, ust_kategori_id, ek_form) VALUES (?, ?, ?, ?, ?)");
                $result = $stmt->execute([$veri, $sira, $yayin_durumu, $ust_kategori_id, $ekFormlar]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO $tableName (veri, sira, yayin_durumu, ust_kategori_id) VALUES (?, ?, ?, ?)");
                $result = $stmt->execute([$veri, $sira, $yayin_durumu, $ust_kategori_id]);
            }
            $message = 'Veri başarıyla eklendi';
        } else {
            // Güncelleme işleminde ek_form sütununu değiştirme
            $stmt = $pdo->prepare("UPDATE $tableName SET veri = ?, sira = ?, yayin_durumu = ?, ust_kategori_id = ? WHERE id = ?");
            $result = $stmt->execute([$veri, $sira, $yayin_durumu, $ust_kategori_id, $id]);
            $message = 'Veri başarıyla güncellendi';
        }

        if ($result) {
            $response['success'] = true;
            $response['message'] = $message;
        } else {
            throw new Exception("Veritabanı işlemi başarısız");
        }
    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = 'İşlem sırasında bir hata oluştu: ' . $e->getMessage();
    }
}

function checkEkFormTableExists($tableName) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM $tableName LIKE 'ek_form'");
        $stmt->execute();
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

function handleEkFormlar($formData) {
    $ekFormlar = [];
    foreach ($formData as $key => $value) {
        if (strpos($key, 'ek_form[') === 0) {
            $parts = explode('[', str_replace(']', '', $key));
            $formName = $parts[1];
            $fieldName = $parts[2];
            $ekFormlar[$formName][$fieldName] = $value;
        }
    }
    return $ekFormlar;
}

function handleMultipleImageUpload($files, $options) {
    $uploadedFiles = [];
    
    if ($files && is_array($files['name'])) {
        foreach ($files['name'] as $key => $name) {
            if ($files['error'][$key] === UPLOAD_ERR_OK) {
                $upload = new Upload($files['tmp_name'][$key]);
                if ($upload->uploaded) {
                    if (!empty($options['resim_eni']) && !empty($options['resim_boyu'])) {
                        $upload->image_resize = true;
                        $upload->image_x = $options['resim_eni'];
                        $upload->image_y = $options['resim_boyu'];
                        
                        if ($options['resmi_kirp'] === 'evet') {
                            $upload->image_ratio_crop = true;
                        }
                        
                        if ($options['resmi_doldur'] === 'evet') {
                            $upload->image_ratio_fill = true;
                        } else {
                            $upload->image_ratio = true;
                        }
                    }

                    $upload->file_new_name_body = uniqid();
                    $upload->dir_auto_create = true;
                    $upload->dir_chmod = 0755;
                    $upload->process('../../' . $options['resim_kayit_dizini']);

                    if ($upload->processed) {
                        $uploadedFiles[] = [
                            'dosya_adi' => $options['resim_kayit_dizini']. '' . $upload->file_dst_name,
                            'alt_etiketi' => $options['baslik'],
                            'kapak_resim' => (empty($uploadedFiles)) ? 'evet' : 'hayir'
                        ];
                        $upload->clean();
                    } else {
                        throw new Exception("Dosya işleme hatası: " . $upload->error);
                    }
                } else {
                    throw new Exception("Dosya yükleme hatası: " . $upload->error);
                }
            } elseif ($files['error'][$key] !== UPLOAD_ERR_NO_FILE) {
                throw new Exception("Dosya yükleme hatası kodu: " . $files['error'][$key]);
            }
        }
    }
    
    return $uploadedFiles;
}

function handleDeleteMultipleImages($tableName) {
    global $pdo, $response;

    try {
        $images = json_decode($_POST['images'], true);
        $baseurl_onyuz = $_POST['baseurl_onyuz'] ?? '';
        
        if (!is_array($images)) {
            throw new Exception("Geçersiz resim verisi");
        }

        $pdo->beginTransaction();

        $successfulDeletes = 0;
        $errors = [];

        // Resimleri indekse göre sırala (büyükten küçüğe)
        usort($images, function($a, $b) {
            return $b['index'] - $a['index'];
        });

        foreach ($images as $image) {
            $id = $image['id'];
            $imageIndex = $image['index'];

            try {
                $stmt = $pdo->prepare("SELECT veri FROM $tableName WHERE id = ?");
                $stmt->execute([$id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$row) {
                    throw new Exception("ID: $id olan kayıt bulunamadı");
                }

                $data = json_decode($row['veri'], true);
                if (!isset($data[0]['data']['resimler'][$imageIndex])) {
                    throw new Exception("Belirtilen indekste resim bulunamadı");
                }

                $resim = $data[0]['data']['resimler'][$imageIndex];
                $filePath = $_SERVER['DOCUMENT_ROOT'] . '/' . $baseurl_onyuz . $resim['dosya_adi'];
                
                if (file_exists($filePath)) {
                    if (!unlink($filePath)) {
                        throw new Exception("Dosya silinemedi: $filePath");
                    }
                } else {
                    error_log("Dosya bulunamadı: $filePath");
                }

                // Resmi diziden kaldır
                array_splice($data[0]['data']['resimler'], $imageIndex, 1);

                $newVeri = json_encode($data);
                $stmt = $pdo->prepare("UPDATE $tableName SET veri = ? WHERE id = ?");
                $stmt->execute([$newVeri, $id]);

                $successfulDeletes++;
            } catch (Exception $e) {
                $errors[] = "Resim silinirken hata oluştu (ID: $id, Index: $imageIndex): " . $e->getMessage();
            }
        }

        $pdo->commit();

        if (count($errors) > 0) {
            $response['success'] = false;
            $response['message'] = "Bazı resimler silinemedi. $successfulDeletes resim başarıyla silindi. Hatalar: " . implode(", ", $errors);
        } else {
            $response['success'] = true;
            $response['message'] = "$successfulDeletes resim başarıyla silindi";
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $response['success'] = false;
        $response['message'] = 'Resimler silinirken bir hata oluştu: ' . $e->getMessage();
    }

    error_log("Silme işlemi sonucu: " . json_encode($response));
    echo json_encode($response);
    exit;
}

function handleDeleteImage($tableName) {
    global $pdo, $response;

    try {
        $id = $_POST['id'] ?? null;
        $imageIndex = $_POST['image_index'] ?? null;

        if (!$id || $imageIndex === null) {
            throw new Exception("Geçersiz ID veya resim indeksi");
        }

        $stmt = $pdo->prepare("SELECT veri FROM $tableName WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new Exception("ID: $id olan kayıt bulunamadı");
        }

        $data = json_decode($row['veri'], true);
        if (!isset($data[0]['data']['resimler'][$imageIndex])) {
            throw new Exception("Belirtilen indekste resim bulunamadı");
        }

        $resim = $data[0]['data']['resimler'][$imageIndex];
        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/' . $resim['dosya_adi'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        array_splice($data[0]['data']['resimler'], $imageIndex, 1);

        $newVeri = json_encode($data);
        $stmt = $pdo->prepare("UPDATE $tableName SET veri = ? WHERE id = ?");
        $result = $stmt->execute([$newVeri, $id]);

        if ($result) {
            $response['success'] = true;
            $response['message'] = 'Resim başarıyla silindi';
        } else {
            throw new Exception("Veritabanı güncelleme hatası");
        }
    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = 'Resim silinirken bir hata oluştu: ' . $e->getMessage();
    }
}

function handleDelete($tableName) {
    global $pdo, $response;
    if (isset($_POST['ids']) && is_array($_POST['ids'])) {
        $ids = array_map('intval', $_POST['ids']);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("DELETE FROM $tableName WHERE id IN ($placeholders)");
        $result = $stmt->execute($ids);

        if ($result) {
            $response['success'] = true;
            $response['message'] = 'Seçilen veriler başarıyla silindi';
        } else {
            $response['message'] = 'Silme işlemi sırasında bir hata oluştu';
        }
    } else {
        $response['message'] = 'Silinecek veri seçilmedi';
    }
}

function handleUpdateCoverImage($tableName) {
    global $pdo, $response;

    try {
        $id = $_POST['id'] ?? null;
        $imageIndex = $_POST['image_index'] ?? null;

        if (!$id || $imageIndex === null) {
            throw new Exception("Geçersiz ID veya resim indeksi");
        }

        $stmt = $pdo->prepare("SELECT veri FROM $tableName WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new Exception("ID: $id olan kayıt bulunamadı");
        }

        $data = json_decode($row['veri'], true);
        if (!isset($data[0]['data']['resimler'])) {
            throw new Exception("Resim verisi bulunamadı");
        }

        foreach ($data[0]['data']['resimler'] as &$resim) {
            $resim['kapak_resim'] = 'hayir';
        }

        if (!isset($data[0]['data']['resimler'][$imageIndex])) {
            throw new Exception("Belirtilen indekste resim bulunamadı");
        }

        $data[0]['data']['resimler'][$imageIndex]['kapak_resim'] = 'evet';

        $newVeri = json_encode($data);
        $stmt = $pdo->prepare("UPDATE $tableName SET veri = ? WHERE id = ?");
        $result = $stmt->execute([$newVeri, $id]);

        if ($result) {
            $response['success'] = true;
            $response['message'] = 'Kapak resmi başarıyla güncellendi';
        } else {
            throw new Exception("Veritabanı güncelleme hatası");
        }
    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = 'Kapak resmi güncellenirken bir hata oluştu: ' . $e->getMessage();
    }

    echo json_encode($response);
    exit;
}

function handleUpdateYayinDurumu($tableName) {
    global $pdo, $response;
    $id = $_POST['id'] ?? null;
    $yayinDurumu = $_POST['yayin_durumu'] ?? null;

    if ($id !== null && $yayinDurumu !== null) {
        $stmt = $pdo->prepare("UPDATE $tableName SET yayin_durumu = ? WHERE id = ?");
        $result = $stmt->execute([$yayinDurumu, $id]);

        if ($result) {
            $response['success'] = true;
            $response['message'] = 'Yayın durumu güncellendi.';
        } else {
            $response['success'] = false;
            $response['message'] = 'Yayın durumu güncellenirken bir hata oluştu.';
        }
    } else {
        $response['success'] = false;
        $response['message'] = 'Geçersiz veri.';
    }
}

function handleGet($tableName) {
    global $pdo, $response;
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($id > 0) {
        $ekFormTableExists = checkEkFormTableExists($tableName);

        $stmt = $pdo->prepare("SELECT * FROM $tableName WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            // Burada, veriyi logluyoruz
            error_log("Gönderilen veri: " . print_r($data, true));

            $response = [
                'success' => true,
                'data' => $data,
                'id' => $data['id'],
                'sira' => $data['sira'],
                'yayin_durumu' => intval($data['yayin_durumu']),
                'ust_kategori_id' => $data['ust_kategori_id']
            ];

            // Ek form bilgilerini ekle (eğer ek_form tablosu varsa)
            if ($ekFormTableExists && isset($data['ek_form'])) {
                $response['ek_formlar'] = explode(',', $data['ek_form']);
            }
        } else {
            $response = ['success' => false, 'message' => 'Veri bulunamadı.'];
        }
    } else {
        $response = ['success' => false, 'message' => 'Geçersiz ID.'];
    }
}

function getCategories($pdo, $userLang) {
    $sql = "SELECT id, veri FROM kategori WHERE yayin_durumu = 1 ORDER BY sira ASC";
    $stmt = $pdo->query($sql);
    $categories = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data = json_decode($row['veri'], true);
        if (json_last_error() === JSON_ERROR_NONE && isset($data[0]['data']['diller'][$userLang]['baslik'])) {
            $title = $data[0]['data']['diller'][$userLang]['baslik'];
        } else {
            // Eğer belirtilen dilde başlık yoksa veya JSON parse edilemezse, id'yi kullan
            $title = 'Kategori ' . $row['id'];
        }
        $categories[] = ['id' => $row['id'], 'title' => $title];
    }
    return $categories;
}

function handleUpdateSayfaAyarlar($tableName) {
    global $pdo, $response;
    try {
        $id = $_POST['satir_id'] ?? '';
        $id = intval($id);
        if ($id <= 0) {
            throw new Exception("Geçersiz satır ID'si");
        }

        // Yeni veri yapısını oluştur
        $veri = [
            'diller' => [],
            'ortak_alanlar' => []
        ];

        // Dil verilerini işle
        $diller = ['tr']; // Gerekirse diğer dilleri ekleyin
        foreach ($diller as $dil) {
            $veri['diller'][$dil] = [
                'baslik' => $_POST['sayfa_baslik_' . $dil] ?? '',
                'aciklama' => $_POST['sayfa_aciklama_' . $dil] ?? '',
                'meta_baslik' => $_POST['sayfa_meta_baslik_' . $dil] ?? '',
                'meta_aciklama' => $_POST['sayfa_meta_aciklama_' . $dil] ?? '',
                'link' => $_POST['sayfa_link_' . $dil] ?? '',
                'etiketler' => isset($_POST['sayfa_etiketler_' . $dil]) ? array_map('trim', explode(',', $_POST['sayfa_etiketler_' . $dil])) : []
            ];
        }

        // Ortak alanları işle
        $veri['ortak_alanlar'] = [
            'sayfa_kisa-aciklama' => $_POST['sayfa_kisa-aciklama'] ?? ''
        ];

        $yayin_durumu = isset($_POST['sayfa_yayin_durumu']) ? 1 : 0;
        
        // Yeni JSON verisini oluştur
        $jsonVeri = json_encode([$veri]);

        // Veritabanında güncelleme yap
        $stmt = $pdo->prepare("UPDATE `$tableName` SET veri = ?, yayin_durumu = ? WHERE id = ?");
        $result = $stmt->execute([$jsonVeri, $yayin_durumu, $id]);

        if ($result) {
            $response['success'] = true;
            $response['message'] = 'Sayfa ayarları başarıyla güncellendi';
        } else {
            // Eğer güncelleme başarısız olursa (örneğin, kayıt bulunamadıysa), yeni kayıt oluştur
            $insertStmt = $pdo->prepare("INSERT INTO `$tableName` (id, veri, yayin_durumu) VALUES (?, ?, ?)");
            $insertResult = $insertStmt->execute([$id, $jsonVeri, $yayin_durumu]);
            
            if ($insertResult) {
                $response['success'] = true;
                $response['message'] = 'Yeni sayfa ayarları başarıyla oluşturuldu';
            } else {
                throw new Exception("Veritabanı işlem hatası");
            }
        }
    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = 'Hata: ' . $e->getMessage();
    }
}