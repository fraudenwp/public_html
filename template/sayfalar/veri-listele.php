<?php
$user_dil = 'tr';

// Günlük arşivleme kontrolü yap
$bugun = date('Y-m-d');

// Son arşivleme tarihini veritabanından kontrol et
$kontrol_query = "SELECT deger FROM ayarlar WHERE anahtar = 'son_arsiv_kontrol'";
$stmt = $pdo->prepare($kontrol_query);
$stmt->execute();
$son_kontrol = $stmt->fetch(PDO::FETCH_ASSOC);
$son_kontrol_tarihi = $son_kontrol ? $son_kontrol['deger'] : '2000-01-01';

if($son_kontrol_tarihi != $bugun) {
    // Süresi 15 gün geçmiş turları arşivle
    try {
        $arsivleme_query = "UPDATE paketler 
                            SET veri = JSON_SET(
                                veri,
                                '$[0].data.ortak_alanlar.arsivle',
                                '1'
                            )
                            WHERE yayin_durumu = 1 
                            AND JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.arsivle') = '0'
                            AND DATE(JSON_UNQUOTE(JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.tur_bitis_tarihi'))) <= DATE_SUB(CURDATE(), INTERVAL 15 DAY)";
        
        $stmt = $pdo->prepare($arsivleme_query);
        $stmt->execute();
        
        // Arşivlenen tur sayısını kaydet
        $arsivlenen_sayi = $stmt->rowCount();
        
        // Kontrol tarihini ve arşivlenen sayıyı güncelle/ekle
        $pdo->prepare("INSERT INTO ayarlar (anahtar, deger) VALUES ('son_arsiv_kontrol', ?) ON DUPLICATE KEY UPDATE deger = ?")->execute([$bugun, $bugun]);
        $pdo->prepare("INSERT INTO ayarlar (anahtar, deger) VALUES ('son_arsiv_sayisi', ?) ON DUPLICATE KEY UPDATE deger = ?")->execute([$arsivlenen_sayi, $arsivlenen_sayi]);
        
    } catch(PDOException $e) {
        error_log("Arşivleme hatası: " . $e->getMessage());
    }
}

// URL'den alt kategoriyi al
$alt_kategori = isset($_GET['alt_kategori']) ? $_GET['alt_kategori'] : 'tum-turlar';

// Kategori bilgilerini veritabanından çek
$kategoriQuery = "SELECT id, veri FROM kategori WHERE yayin_durumu = 1 ORDER BY sira ASC";
$kategoriStmt = $pdo->query($kategoriQuery);
$kategoriler = $kategoriStmt->fetchAll(PDO::FETCH_ASSOC);

// Kategori isimlerini ve ID'lerini saklayacak bir dizi oluştur
$kategoriMap = [];
$kategori_basligi = 'Tüm Turlar'; // Varsayılan başlık

// Kategori bilgilerini çektikten sonra
foreach ($kategoriler as $kategori) {
    $kategoriVeri = json_decode($kategori['veri'], true)[0]['data'];
    $kategoriLink = $kategoriVeri['diller'][$user_dil]['link'];
    $kategoriBaslik = $kategoriVeri['diller'][$user_dil]['baslik'];
    $kategoriMetaBaslik = $kategoriVeri['diller'][$user_dil]['meta_baslik'] ?? $kategoriBaslik;
    $kategoriMetaAciklama = $kategoriVeri['diller'][$user_dil]['meta_aciklama'] ?? '';
    $kategoriEtiketler = $kategoriVeri['diller'][$user_dil]['etiketler'] ?? [];
	$alt_aciklama = $kategoriVeri['ortak_alanlar']['alt_aciklama'] ?? '';
	
    $kategoriMap[$kategoriLink] = [
        'id' => $kategori['id'],
        'baslik' => $kategoriBaslik,
        'meta_baslik' => $kategoriMetaBaslik,
        'meta_aciklama' => $kategoriMetaAciklama,
        'etiketler' => $kategoriEtiketler
    ];
    
    // Eğer bu kategori seçili ise, bilgileri güncelle
    if ($kategoriLink == $alt_kategori) {
        $kategori_basligi = $kategoriBaslik;
        $kategori_meta_baslik = $kategoriMetaBaslik;
        $kategori_meta_aciklama = $kategoriMetaAciklama;
        $kategori_etiketler = $kategoriEtiketler;
    }
}

// Alt kategoriye göre sorguyu oluştur
$where_clause = "WHERE yayin_durumu = 1 AND JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.arsivle') = '0'";

if ($alt_kategori !== 'tum-turlar') {
    if (isset($kategoriMap[$alt_kategori])) {
        $where_clause .= " AND ust_kategori_id = " . $kategoriMap[$alt_kategori]['id'];
    } else {
        // Geçersiz kategori durumu - 404 sayfasına yönlendir
                echo "<script>window.location.href = '{$baseurl_onyuz}404';</script>";
      
        exit();
    }
}

// GET parametrelerini al ve boş değerleri filtrele
$turSuresi = array_filter(!empty($_GET['turSuresi']) ? $_GET['turSuresi'] : [], function($value) { return $value !== ''; });
$turDonemi = array_filter(!empty($_GET['turDonemi']) ? $_GET['turDonemi'] : [], function($value) { return $value !== ''; });
$otel = array_filter(!empty($_GET['otel']) ? $_GET['otel'] : [], function($value) { return $value !== ''; });
$selectedKategoriler = array_filter(!empty($_GET['kategori']) ? $_GET['kategori'] : [], function($value) { return $value !== ''; });

// Sayfalama için değişkenler
$limit = 10; // Her sayfada gösterilecek kayıt sayısı
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Mevcut sayfa numarası
$offset = ($page - 1) * $limit; // OFFSET değeri

// Ana sorguyu oluştur
$query = "SELECT id, veri, 
          CASE 
            WHEN JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.tukendi') = 'true' THEN 2
            WHEN JSON_UNQUOTE(JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.tur_baslangic_tarihi')) < CURDATE() THEN 1 
            ELSE 0 
          END as tur_durum,
          CASE 
            WHEN JSON_UNQUOTE(JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.tur_baslangic_tarihi')) >= CURDATE() 
            THEN DATEDIFF(JSON_UNQUOTE(JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.tur_baslangic_tarihi')), CURDATE())
            ELSE 99999 
          END as gunler_kaldi
          FROM paketler 
          $where_clause";

$params = [];


// Tur Süresi filtresi
if (!empty($turSuresi)) {  
    $sureFarkQuery = "CASE 
        WHEN JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.kac_gun') IS NOT NULL 
            AND JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.kac_gun') != 'null'
            AND TRIM(JSON_UNQUOTE(JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.kac_gun'))) != ''
        THEN CAST(JSON_UNQUOTE(JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.kac_gun')) AS SIGNED)
        ELSE (
            DATEDIFF(
                JSON_UNQUOTE(JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.tur_bitis_tarihi')),
                JSON_UNQUOTE(JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.tur_baslangic_tarihi'))
            ) + 1
        )
    END";
    
    $query .= " AND " . $sureFarkQuery . " IN (" . implode(',', array_fill(0, count($turSuresi), '?')) . ")";
    $params = array_merge($params, $turSuresi);
}

// Tur Dönemi filtresi
if (!empty($turDonemi)) {
    $donemConditions = [];
    foreach ($turDonemi as $donem) {
        $donemConditions[] = "JSON_CONTAINS(JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.donem'), JSON_ARRAY(?))";
        $params[] = $donem;
    }
    $query .= " AND (" . implode(' OR ', $donemConditions) . ")";
}

// Otel filtresi
if (!empty($otel)) {
    $otelConditions = [];
    foreach ($otel as $o) {
        $otelConditions[] = "(JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.otel_bir') = ? OR JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.otel_iki') = ?)";
        $params[] = $o;
        $params[] = $o;
    }
    $query .= " AND (" . implode(' OR ', $otelConditions) . ")";
}

// Kategori filtresi
if (!empty($selectedKategoriler)) {
    $kategoriConditions = [];
    foreach ($selectedKategoriler as $kategoriId) {
        $kategoriConditions[] = "FIND_IN_SET(?, ust_kategori_id) > 0";
        $params[] = $kategoriId;
    }
    $query .= " AND (" . implode(' OR ', $kategoriConditions) . ")";
}

// Toplam kayıt sayısını al
$countQuery = "SELECT COUNT(*) as total FROM (" . $query . ") as filtered_results";
try {
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $totalResults = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    die("Toplam kayıt sayısı sorgusu başarısız oldu: " . $e->getMessage());
}

// Toplam sayfa sayısını hesapla
$totalPages = ceil($totalResults / $limit);

// Ana sorguya ORDER BY, LIMIT ve OFFSET ekle
$orderByClause = " ORDER BY tur_durum ASC, gunler_kaldi ASC, JSON_UNQUOTE(JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.tur_baslangic_tarihi')) ASC";
$limitOffsetClause = " LIMIT " . $limit . " OFFSET " . $offset;
$query .= $orderByClause . $limitOffsetClause;

// Verileri çek
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $paketler = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error occurred. Full query: " . $query . "<br>";
    echo "Parameters: " . print_r($params, true) . "<br>";
    die("Veritabanı sorgusu başarısız oldu: " . $e->getMessage());
}

// Paketleri sırala
usort($paketler, function($a, $b) {
    $a_veri = json_decode($a['veri'], true)[0]['data']['ortak_alanlar'];
    $b_veri = json_decode($b['veri'], true)[0]['data']['ortak_alanlar'];
    
    $a_tukendi = $a_veri['tukendi'] ?? false;
    $b_tukendi = $b_veri['tukendi'] ?? false;
    
    $a_baslangic = new DateTime($a_veri['tur_baslangic_tarihi']);
    $b_baslangic = new DateTime($b_veri['tur_baslangic_tarihi']);
    
    $a_bitis = new DateTime($a_veri['tur_bitis_tarihi']);
    $b_bitis = new DateTime($b_veri['tur_bitis_tarihi']);
    
    $now = new DateTime();

    // Tükenenler en sona
    if ($a_tukendi && !$b_tukendi) return 1;
    if (!$a_tukendi && $b_tukendi) return -1;

    // Süresi dolanlar tükenenlerin önüne
    if ($a_baslangic < $now && $b_baslangic >= $now) return 1;
    if ($a_baslangic >= $now && $b_baslangic < $now) return -1;

    // Normal olanlar en yakın tarihe göre
    if ($a_baslangic >= $now && $b_baslangic >= $now) {
        if ($a_baslangic == $b_baslangic) {
            // Eğer başlangıç tarihleri aynıysa, bitiş tarihine göre sırala
            return $a_bitis <=> $b_bitis;
        }
        return $a_baslangic <=> $b_baslangic;
    }

    // Eğer her ikisi de süresi dolmuşsa, başlangıç tarihine göre ters sırala
    if ($a_baslangic < $now && $b_baslangic < $now) {
        if ($a_baslangic == $b_baslangic) {
            // Eğer başlangıç tarihleri aynıysa, bitiş tarihine göre sırala
            return $a_bitis <=> $b_bitis;
        }
        return $b_baslangic <=> $a_baslangic;
    }

    return 0;
});

// Öne çıkan turları al
$oneCikanQuery = "SELECT veri, id FROM paketler 
                  WHERE yayin_durumu = 1 
                  AND JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.arsivle') = '0'
                  AND JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.one-cikar') = '1'
                  ORDER BY JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.tur_baslangic_tarihi') ASC
                  LIMIT 10";

try {
    $oneCikanStmt = $pdo->query($oneCikanQuery);
    $oneCikanTurlar = $oneCikanStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Öne çıkan turlar sorgusu başarısız oldu: " . $e->getMessage());
}

$kategoriQuery = "SELECT id, veri FROM kategori WHERE yayin_durumu = 1 ORDER BY sira ASC";
try {
    $kategoriStmt = $pdo->query($kategoriQuery);
    $kategoriler = $kategoriStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Kategori sorgusu başarısız oldu: " . $e->getMessage());
}

// Seçilen kategori açıklaması için değişken
$secilen_kategori_aciklama = '';

// URL'den gelen alt kategori bilgisini al
$url_kategori = isset($_GET['alt_kategori']) ? $_GET['alt_kategori'] : 'tum-turlar';

// Kategori açıklamasını bul
foreach ($kategoriler as $kategori) {
    $kategoriVeri = json_decode($kategori['veri'], true)[0]['data'];
    $kategoriLink = $kategoriVeri['diller'][$user_dil]['link'];
	$alt_aciklama = $kategoriVeri['ortak_alanlar']['alt_aciklama'] ?? '';
    
    if ($kategoriLink == $url_kategori) {
        // URL'den gelen kategori ile eşleşen kategoriyi bulduk
        $secilen_kategori_aciklama = $kategoriVeri['diller'][$user_dil]['aciklama'] ?? '';
        $secilen_kategori_baslik = $kategoriVeri['diller'][$user_dil]['baslik'] ?? '';
		$alt_aciklama = $kategoriVeri['ortak_alanlar']['alt_aciklama'] ?? '';
        break;
    }
}

// Eğer URL'den gelen kategori bulunamazsa ve tek bir kategori seçilmişse
if (empty($secilen_kategori_aciklama) && count($selectedKategoriler) === 1) {
    $secilen_kategori_id = $selectedKategoriler[0];
    foreach ($kategoriler as $kategori) {
        if ($kategori['id'] == $secilen_kategori_id) {
            $kategoriVeri = json_decode($kategori['veri'], true)[0]['data'];
            $secilen_kategori_aciklama = $kategoriVeri['diller'][$user_dil]['aciklama'] ?? '';
            $secilen_kategori_baslik = $kategoriVeri['diller'][$user_dil]['baslik'] ?? '';
			$alt_aciklama = $kategoriVeri['ortak_alanlar']['alt_aciklama'] ?? '';
			
            break;
        }
    }
}

// Hala açıklama bulunamadıysa varsayılan bir açıklama kullan
if (empty($secilen_kategori_aciklama)) {
	if (empty($secilen_kategori_baslik)) { $secilen_kategori_baslik = ''; } else { }
    $secilen_kategori_aciklama = $secilen_kategori_baslik ." Size özel hazırlanmış, unutulmaz deneyimler sunan turlarımız arasından seçim yapın.";
}// Hala açıklama bulunamadıysa varsayılan bir açıklama kullan
if (empty($secilen_kategori_baslik)) {
    $secilen_kategori_baslik = "";
}

// Tüm paketleri çek
$allPackagesQuery = "SELECT veri FROM paketler WHERE yayin_durumu = 1 
          AND JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.arsivle') = '0'";

try {
    $stmt = $pdo->query($allPackagesQuery);
    $allPackages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Veritabanı sorgusu başarısız oldu: " . $e->getMessage());
}

$turSureleri = [];
$turDonemleri = [];
$oteller = [];

foreach ($allPackages as $paket) {
    $veri = json_decode($paket['veri'], true)[0]['data'];
    $ortak = $veri['ortak_alanlar'];
    
    // Tur süresini hesapla
    // Manuel gün değeri varsa onu kullan, yoksa hesapla
    if (isset($ortak['kac_gun']) && !empty($ortak['kac_gun'])) {
        $sureFark = $ortak['kac_gun'];
    } else {
        $baslangic = new DateTime($ortak['tur_baslangic_tarihi']);
        $bitis = new DateTime($ortak['tur_bitis_tarihi']);
        $sureFark = $baslangic->diff($bitis)->days + 1;
    }
	
	$turSureleri[$sureFark] = $sureFark . ' Günlük Umre Turu';
    
    // Tur dönemlerini topla
    if (isset($ortak['donem']) && is_array($ortak['donem'])) {
        foreach ($ortak['donem'] as $donemId) {
            $turDonemleri[$donemId] = $donemId; // Dönem adını daha sonra alacağız
        }
    }
    
    // Otelleri topla
    if (isset($ortak['otel_bir'])) $oteller[$ortak['otel_bir']] = $ortak['otel_bir'];
    if (isset($ortak['otel_iki'])) $oteller[$ortak['otel_iki']] = $ortak['otel_iki'];
}

// Dönem ve otel isimlerini al
$donemQuery = $pdo->prepare("SELECT id, veri FROM donem WHERE id IN (" . implode(',', array_keys($turDonemleri)) . ")");
$donemQuery->execute();
$donemler = $donemQuery->fetchAll(PDO::FETCH_ASSOC);

$otelQuery = $pdo->prepare("SELECT id, veri FROM oteller WHERE id IN (" . implode(',', array_keys($oteller)) . ")");
$otelQuery->execute();
$otellerData = $otelQuery->fetchAll(PDO::FETCH_ASSOC);

// Dönem ve otel isimlerini güncelle
foreach ($donemler as $donem) {
    $donemVeri = json_decode($donem['veri'], true)[0]['data'];
    $turDonemleri[$donem['id']] = $donemVeri['diller'][$user_dil]['baslik'] ?? 'Bilinmeyen Dönem';
}

foreach ($otellerData as $otel) {
    $otelVeri = json_decode($otel['veri'], true)[0]['data'];
    $oteller[$otel['id']] = $otelVeri['diller'][$user_dil]['baslik'] ?? 'Bilinmeyen Otel';
}

// Sırala
ksort($turSureleri);
asort($turDonemleri);
asort($oteller);

$selectedTurSureleri = isset($_GET['turSuresi']) ? (array)$_GET['turSuresi'] : [];
$selectedTurDonemleri = isset($_GET['turDonemi']) ? (array)$_GET['turDonemi'] : [];
$selectedOteller = isset($_GET['otel']) ? (array)$_GET['otel'] : [];

// Etiketleri string'e çevir - SEO için genişletilmiş
$default_etiketler = [
    'umre turları', 'umre turları 2025', 'umre turları fiyatları', 'umre turları 2024',
    'diyanet umre turları', 'ekonomik umre turları', 'lüks umre turları', '5 yıldızlı umre turları',
    'ankara umre turları', 'istanbul umre turları', 'izmir umre turları', 'bursa umre turları',
    'en iyi umre turları', 'ucuz umre turları', 'uygun umre turları', 'ramazan umre turları',
    'hac umre turları', 'vip umre turları', 'kısa umre turları', '3 günlük umre turları fiyatları'
];
$kategori_etiketler_string = implode(', ', $kategori_etiketler ?? $default_etiketler);

// Meta açıklama boşsa, varsayılan bir açıklama oluştur - SEO optimize edilmiş
if (empty($kategori_meta_aciklama)) {
    $kategori_meta_aciklama = $kategori_basligi . ' 2025-2026 fiyatları ve programları. Ankara, İstanbul, İzmir çıkışlı ekonomik ve lüks umre turları. TÜRSAB lisanslı, en uygun fiyatlar, taksit imkanı.';
}

// Kategori resmi (eğer varsa)
$kategori_resim_yolu = $kategoriVeri['resimler'][0]['dosya_adi'] ?? 'resimler/varsayilan-kategori-resmi.jpg';
$tam_resim_yolu = $sirket_url . $baseurl_onyuz . $kategori_resim_yolu;


PageData::set(
    $kategori_meta_baslik = $kategori_meta_baslik ?? $kategori_basligi ?? 'Tüm Turlar',
    $kategori_meta_aciklama,
    $kategori_etiketler_string,
    [
        'og:title' => $kategori_meta_baslik,
        'og:description' => $kategori_meta_aciklama,
        'og:type' => 'website',
        'og:url' => 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
        'og:image' => $tam_resim_yolu,
        'og:site_name' => $sirket_adi,
        'twitter:card' => 'summary_large_image',
        'twitter:title' => $kategori_meta_baslik,
        'twitter:description' => $kategori_meta_aciklama,
        'twitter:image' => $tam_resim_yolu
    ]
);

// Schema.org CollectionPage yapılandırılmış veri
$schema_items = [];
foreach ($paketler as $paket) {
    $veri = json_decode($paket['veri'], true)[0]['data'];
    $baslik = $veri['diller'][$user_dil]['baslik'] ?? '';
    $link = $veri['diller'][$user_dil]['link'] ?? '';
    $resim = $veri['resimler'][0]['dosya_adi'] ?? '';
    $ortak = $veri['ortak_alanlar'];
    $id = $paket['id'];

    $schema_items[] = [
        '@type' => 'TouristTrip',
        'name' => $baslik,
        'url' => $sirket_url . '/tur-detay/' . $id . '/' . $link,
        'image' => !empty($resim) ? $sirket_url . '/' . $resim : '',
        'touristType' => 'Umre Yolcusu',
        'offers' => [
            '@type' => 'Offer',
            'price' => $ortak['ikili_oda_fiyatı'] ?? '',
            'priceCurrency' => ($ortak['para_birimi'] ?? '') === '$' ? 'USD' : (($ortak['para_birimi'] ?? '') === '€' ? 'EUR' : 'TRY'),
            'availability' => ($ortak['tukendi'] ?? false) ? 'https://schema.org/SoldOut' : 'https://schema.org/InStock'
        ]
    ];
}

$schema_data = [
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => $kategori_meta_baslik ?? $kategori_basligi,
    'description' => $kategori_meta_aciklama,
    'url' => 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
    'mainEntity' => [
        '@type' => 'ItemList',
        'itemListElement' => array_map(function($item, $index) {
            return [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'item' => $item
            ];
        }, $schema_items, array_keys($schema_items))
    ]
];
?>
<script type="application/ld+json">
<?php echo json_encode($schema_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>
</script>

<div class="innerHeading">
  <div class="container">
    <h1><?php echo htmlspecialchars($kategori_basligi); ?></h1>
    <?php if (strpos(strtolower($kategori_basligi), 'umre') !== false): ?>
    <p style="color: #fff; font-size: 14px; margin-top: 10px; opacity: 0.9;">
      2025 - 2026 yılı umre turları programları ve kişi başı fiyatları. Ankara, İstanbul, İzmir, Bursa çıkışlı ekonomik ve lüks paketler. Detaylı bilgileri aşağıdaki turlardan inceleyebilirsiniz.
    </p>
    <?php endif; ?>
  </div>
</div>

<div class="innercontent">
  <div class="container">
    <div class="row listing_wrap">

		<div class="col-lg-12 contact-wrap pc-hide">
			<div class="contact-btn" style="position: fixed; bottom: 35px; left: 15px; z-index: 9999;">
				<a href="#" data-toggle="modal" data-target="#veri-filrele" class="sub" style="box-shadow: 0px 0px 1px 3px rgb(0, 0, 0); color: black; border-radius: 20px; border-radius: 20px; padding: 6px 10px;font-size: 15px; background: #ffb900;">Filtrele <i class="fa fa-arrow-circle-right" aria-hidden="true" style="margin-left: 5px;"></i></a>
			</div>
		</div>	
		
      <div class="col-lg-8">

        <div class="nav sortWrp wow fadeInUp" role="tablist">
          <?php 
          if ($totalResults > 0) {
              echo "$totalResults adet tur bulundu."; 
          } else {
              echo "Aradığınız kıriterlere göre tur bulunamadı.";

          }
          ?>
        </div>
		
        <ul class="row">
			<?php if ($totalResults > 0): ?>
				<?php foreach ($paketler as $paket): 
					$veri = json_decode($paket['veri'], true)[0]['data'];
					$baslik = $veri['diller'][$user_dil]['baslik'] ?? '';
					$resim = $veri['resimler'][0]['dosya_adi'] ?? 'resimler/gorsel-hazirlaniyor-one-cikan.jpg';
					$link = $veri['diller'][$user_dil]['link'] ?? '#';
					$ortak = $veri['ortak_alanlar'];
					$tukendi = $ortak['tukendi'] ?? false;
					$kampanyali = $ortak['kampanyali'] ?? false;
					$vurgulama_yazi = $ortak['vurgulama_yazi'] ?? false;
					$suresiDoldu = strtotime($ortak['tur_baslangic_tarihi']) < time();
					$turKodu = $ortak['tur_kodu'] ?? 'N/A';  // Tur kodunu al
					$id = $paket['id'];
					$detay_url = $baseurl_onyuz . "tur-detay/{$id}/{$link}";		

					$baslangicTarihi = DateTime::createFromFormat('Y-m-d', $ortak['tur_baslangic_tarihi']);
					$araGecisTarihi = DateTime::createFromFormat('Y-m-d', $ortak['tur_ara_gecis_tarihi']);
					$bitisTarihi = DateTime::createFromFormat('Y-m-d', $ortak['tur_bitis_tarihi']);
					$manuel_gun = $ortak['kac_gun'] ?? null;
					$manuel_gece = $ortak['kac_gece'] ?? null;
					$konaklama_suresi = hesaplaKonaklamaSuresi(
						$ortak['tur_baslangic_tarihi'],
						$ortak['tur_bitis_tarihi'],
						$manuel_gun,
						$manuel_gece
					);
				?>
					
		<li class="col-lg-6">
            <div class="property_box wow fadeInUp">
              <?php if ($tukendi): ?>
                <div class="tukendi-overlay">
                  <img src="<?php echo $baseurl_onyuz; ?>resimler/tukendi.png" alt="Tükendi" class="tukendi-image">
                </div>
              <?php endif; ?>
              <div class="propertyImg">
			  		  
			  
                <?php if ($kampanyali): ?>
                  <div class="kampanya-etiketi">Kampanyalı</div>
                <?php endif; ?>

				<?php if ($suresiDoldu): ?>
                   <div class="tur-kodu">Süresi Doldu</div>
                <?php endif; ?>	
				<?php if ($vurgulama_yazi): ?>
                   	<div class="vurgulama_yazi"><?php echo htmlspecialchars($vurgulama_yazi); ?> </div> 
                <?php endif; ?>	
				
				  
                <img alt="<?php echo htmlspecialchars($baslik); ?>" src="<?php echo $baseurl_onyuz . htmlspecialchars($resim); ?>">
				
              </div>
			  
				<small style="font-size: 12px;color: #ffb900;"><b># Tur Kodu:  <?php echo htmlspecialchars($turKodu); ?></b> </small>
              <h3 style="margin-top: 0px;"><a href="<?php echo htmlspecialchars($detay_url); ?>" title="<?php echo htmlspecialchars($baslik); ?>"><?php echo htmlspecialchars($baslik); ?></a></h3>
             <h4 style="font-size: 14px; line-height: 0; margin-bottom: 20px; line-height: 0;margin-bottom: 20px;"><?php echo htmlspecialchars($ortak['alt_baslik_tr'] ?? ''); ?> </h4>			  
             
			 <div class="property_location"><i class="fa fa-plane" aria-hidden="true"></i> <?php echo htmlspecialchars($ortak['tur_baslangic_tarihi_aciklama'] . ' : ' . formatTarih($ortak['tur_baslangic_tarihi'])); ?></div>
              <div class="property_location"><i class="fa fa-bus" aria-hidden="true"></i> <?php echo htmlspecialchars($ortak['tur_ara_gecis_tarihi_aciklama'] . ' : ' . formatTarih($ortak['tur_ara_gecis_tarihi'])); ?></div>
              <div class="property_location"><i class="fa fa-plane" aria-hidden="true"></i> <?php echo htmlspecialchars($ortak['tur_bitis_tarihi_aciklama'] . ' : ' . formatTarih($ortak['tur_bitis_tarihi'])); ?></div>
				
				<style>
				

				</style>

				<div class="row" style="margin-top: 10px;">
					<div class="col-4 fiyat-padding-right">
						<ul class="list-group">
						  <li class="list-group-item list-fiyat-baslik">2'li Oda</li>
						  <li class="list-group-item list-fiyat-fiyat"><?php echo htmlspecialchars($ortak['ikili_oda_fiyatı'] . ' ' . $ortak['para_birimi']); ?></li>
						</ul> 
					</div>
					<div class="col-4 fiyat-padding-left fiyat-padding-right">
						<ul class="list-group">
						  <li class="list-group-item list-fiyat-baslik">3'Lü Oda</li>
						  <li class="list-group-item list-fiyat-fiyat"><?php echo htmlspecialchars($ortak['uclu_oda_fiyatı'] . ' ' . $ortak['para_birimi']); ?></li>
						</ul> 
					</div>
					<div class="col-4 fiyat-padding-left">
						<ul class="list-group">
						  <li class="list-group-item list-fiyat-baslik">4'Lü Oda</li>
						  <li class="list-group-item list-fiyat-fiyat"><?php echo htmlspecialchars($ortak['dorlu_oda_fiyatı'] . ' ' . $ortak['para_birimi']); ?></li>
						</ul> 
					</div>
				</div>


			 <div class="propert_info" style="display: none;">
                <ul class="row">  
                 
				  <li class="col-4">
                    <div class="proprty_icon">
                      <h5>2'Li Oda</h5>
                      <span class="property_price"><?php echo htmlspecialchars($ortak['ikili_oda_fiyatı'] . ' ' . $ortak['para_birimi']); ?></span>
                    </div>
                  </li>
                  <li class="col-4">
                    <div class="proprty_icon">
                      <h5>3'Lü Oda</h5>
                      <span class="property_price"><?php echo htmlspecialchars($ortak['uclu_oda_fiyatı'] . ' ' . $ortak['para_birimi']); ?></span>
                    </div>
                  </li>
                  <li class="col-4">
                    <div class="proprty_icon">
                      <h5>4'Lü Oda</h5>
                      <span class="property_price"><?php echo htmlspecialchars($ortak['dorlu_oda_fiyatı'] . ' ' . $ortak['para_birimi']); ?></span>
                    </div>
                  </li>
                </ul>
              </div>
              <a href="<?php echo htmlspecialchars($detay_url); ?>" class="rent_info">
                <div class="apart"><?php echo $konaklama_suresi; ?></div>
                <div class="sale">Detay</div>
              </a>
            </div> 
           </li>
          <?php endforeach; ?>
        <?php else: ?>
          <li class="col-lg-12">
            <p></p>
          </li>
        <?php endif; ?>
        </ul>
      
	  	   <?php if (!empty($secilen_kategori_aciklama)): ?>
        <div class="sortWrp wow fadeInUp" role="tablist">
          	     
        <h2><?php echo $secilen_kategori_baslik ; ?></h2>
        <p><?php echo $secilen_kategori_aciklama; ?></p>

        </div>	
		<?php endif; ?>		
	

     <!-- Sayfalama linkleri -->
        <?php if ($totalPages > 1): ?>
        <div class="blog-pagination text-center">
          <?php
          $queryParams = $_GET;
		unset($queryParams['url']);
		unset($queryParams['alt_kategori']);

		$baseUrl = "/turlar/$alt_kategori?";
          
          // Önceki sayfa linki
          if ($page > 1) {
              $queryParams['page'] = $page - 1;
              $prevLink = '?' . http_build_query($queryParams);
              echo "<a href='$prevLink'><i class='fas fa-angle-left'></i></a>";
          } else {
              echo "<a href='#0'><i class='fas fa-angle-left'></i></a>";
          }

          // Sayfa numaraları
          $startPage = max(1, $page - 2);
          $endPage = min($totalPages, $page + 2);

          for ($i = $startPage; $i <= $endPage; $i++) {
              $queryParams['page'] = $i;
              $link = '?' . http_build_query($queryParams);
              $class = ($i == $page) ? 'class="active"' : '';
              echo "<a href='$link' $class>". sprintf("%02d", $i) ."</a>";
          }

          // Sonraki sayfa linki
          if ($page < $totalPages) {
              $queryParams['page'] = $page + 1;
              $nextLink = '?' . http_build_query($queryParams);
              echo "<a href='$nextLink'><i class='fas fa-angle-right'></i></a>";
          } else {
              echo "<a href='#0'><i class='fas fa-angle-right'></i></a>";
          }
          ?>
        </div>
        <?php endif; ?>

      </div>
    
	
      
      <div class="col-lg-4">
	  
<form action="<?php echo $baseurl_onyuz .'turlar'; ?>" method="GET" class="mobile-hide">
  <div class="sidebar_form card card-body wow fadeInUp">
  <h4>Umre Turları Filtrele</h4>
  <p style="font-size: 12px; color: #666; margin-bottom: 15px;">2025 umre turları için uygun paketi bulun</p>
    <div class="advanceWrp faqs"> 
			<?php
			// Kategorileri döngüye almadan önce
			$kategoriListesi = '';

			foreach ($kategoriler as $kategori):
				$kategoriVeri = json_decode($kategori['veri'], true)[0]['data'];
				$kategoriBaslik = $kategoriVeri['diller'][$user_dil]['baslik'] ?? 'Bilinmeyen Kategori';
				$checked = in_array($kategori['id'], $selectedKategoriler) ? 'checked' : '';
				
				// filtre_menu kontrolü
				$filtre_menu = $kategoriVeri['ortak_alanlar']['filtre_menu'] ?? '0';
				
				if ($filtre_menu === '1') {
					$kategoriListesi .= '<div class="input-group checkboxx">
						<input type="checkbox" name="kategori[]" class="custom-checkboxx" id="kategori' . $kategori['id'] . '" value="' . $kategori['id'] . '" ' . $checked . '>
						<label for="kategori' . $kategori['id'] . '"></label>' . htmlspecialchars($kategoriBaslik) . '
					</div>';
				}
			endforeach;

			// Eğer kategori listesi boş değilse, paneli oluştur
			if (!empty($kategoriListesi)) {
				echo '<div class="panel-group" id="accordionKategori">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title"> <a data-toggle="collapse" data-parent="#accordionKategori" class="collapsed" href="#collapseKategori">Tur Kategori</a> </h4>
						</div>
						<div id="collapseKategori" class="panel-collapse collapse show">
							<div class="panel-body">
								' . $kategoriListesi . '
							</div>
						</div>
					</div>
				</div>';
			}
			?>
              <hr>	
      <div class="panel-group" id="accordionTurSuresi">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title"> <a data-toggle="collapse" data-parent="#accordionTurSuresi" class="collapsed" href="#collapseTurSuresi">Tur Süresi</a> </h4>
          </div>
          <div id="collapseTurSuresi" class="panel-collapse collapse show">
            <div class="panel-body">
              <?php // Tur Süresi checkbox'ları için
					foreach ($turSureleri as $sure => $label):
						$checked = in_array($sure, $selectedTurSureleri) ? 'checked' : '';
					?>
						<div class="input-group checkboxx"> 
							<input type="checkbox" class="custom-checkboxx" name="turSuresi[]" id="turSuresi<?php echo $sure; ?>" value="<?php echo $sure; ?>" <?php echo $checked; ?>>
							<label for="turSuresi<?php echo $sure; ?>"></label><?php echo htmlspecialchars($label); ?>
						</div>
					<?php endforeach; ?>
            </div> 
          </div>
        </div>
      </div>
      <hr>
      <div class="panel-group" id="accordionTurDonemi">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title"> <a data-toggle="collapse" data-parent="#accordionTurDonemi" class="collapsed" href="#collapseTurDonemi">Tur Dönemi</a> </h4>
          </div>
          <div id="collapseTurDonemi" class="panel-collapse collapse show">
            <div class="panel-body">
              <?php foreach ($turDonemleri as $id => $donem):
					$checked = in_array($id, $selectedTurDonemleri) ? 'checked' : '';
				?>
					<div class="input-group checkboxx">
						<input type="checkbox" name="turDonemi[]" class="custom-checkboxx" id="turDonemi<?php echo $id; ?>" value="<?php echo $id; ?>" <?php echo $checked; ?>>
						<label for="turDonemi<?php echo $id; ?>"></label><?php echo htmlspecialchars($donem); ?>
					</div>
				<?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
      <hr>
      <div class="panel-group" id="accordionOtel">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title"> <a data-toggle="collapse" data-parent="#accordionOtel" class="collapsed" href="#collapseOtel">Otel</a> </h4>
          </div>
          <div id="collapseOtel" class="panel-collapse collapse show">
            <div class="panel-body">
              <?php // Otel checkbox'ları için
				foreach ($oteller as $id => $otel):
					$checked = in_array($id, $selectedOteller) ? 'checked' : '';
				?>
					<div class="input-group checkboxx">
						<input type="checkbox" name="otel[]" class="custom-checkboxx" id="otel<?php echo $id; ?>" value="<?php echo $id; ?>" <?php echo $checked; ?>>
						<label for="otel<?php echo $id; ?>"></label><?php echo htmlspecialchars($otel); ?>
					</div>
				<?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="input-group">
      <input type="submit" class="submit" value="Listele">
    </div>
  </div>
</form>

		<div class="single-widgets widget_category fadeInUp wow">
          <h4>Öne Çıkan Umre Turları</h4>
          <ul class="property_sec">
            <?php foreach ($oneCikanTurlar as $tur): 
              $turVeri = json_decode($tur['veri'], true)[0]['data'];
              $turBaslik = $turVeri['diller'][$user_dil]['baslik'] ?? '';
              $turResim = $turVeri['resimler'][0]['dosya_adi'] ?? 'resimler/gorsel-hazirlaniyor-one-cikan.jpg';
              $turLink = $turVeri['diller'][$user_dil]['link'] ?? '#';
			  $turid = $tur['id'];
			  $detay_url = $baseurl_onyuz . "tur-detay/{$turid}/{$turLink}";
            ?>
            <li>
              <div class="rec_proprty">
                <div class="propertyImg"><img src="<?php echo $baseurl_onyuz . htmlspecialchars($turResim); ?>" style="max-width: 120px;"></div>
                <div class="property_info" style="max-width: 55%;">
                  <h4 style="line-height: 20px;"><a href="<?php echo htmlspecialchars($detay_url); ?>"><?php echo htmlspecialchars($turBaslik); ?></a></h4>
                 
                </div>
              </div>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>
      
      </div>
	  
	  

	<?php

// Alt açıklama varsa ve boş değilse div'i ekle
if (!empty($alt_aciklama)) {
    echo '<div class="col-lg-12">
            <div class="single-widgets widget_category fadeInUp wow">
            ' . $alt_aciklama . '
            </div>
          </div>';
}

// Umre kategorisi için SEO footer metni
if (strpos(strtolower($kategori_basligi), 'umre') !== false) {
    echo '<div class="col-lg-12">
            <div class="seo-content fadeInUp wow" style="background: #f8f9fa; padding: 30px; border-radius: 10px; margin-top: 30px;">
                <h2 style="color: #1a5f2a; margin-bottom: 20px;">2025 - 2026 Umre Turları Fiyatları ve Programları</h2>
                <p>Mekke ve Medine\'yi ziyaret ederek ibadetlerinizi yerine getirmek istiyorsanız doğru yerdesiniz.
                <strong>Yakut Turizm</strong> olarak 2023, 2024, 2025 ve 2026 yılı umre turları programlarımızla sizlere kaliteli ve güvenilir hizmet sunuyoruz.
                İstanbul, Ankara, İzmir, Bursa, Konya, Antalya, Gaziantep, Kayseri ve tüm Türkiye\'den umre turları düzenliyoruz.</p>

                <h3 style="color: #1a5f2a; margin-top: 20px; font-size: 18px;">Umre Turları Fiyatları 2025 - Kişi Başı Uygun Fiyatlar</h3>
                <p>2025 umre turları fiyatlarımız; ekonomik umre turlarından 5 yıldızlı lüks umre turlarına kadar geniş bir seçenek sunmaktadır.
                3 günlük umre turları, 7 günlük umre turları, 10 günlük ve 15 günlük paketlerimiz mevcuttur.
                Kişi başı fiyatlarımız; uçak bileti, 4-5 yıldızlı otel konaklaması, vize işlemleri, rehberlik hizmetleri ve transferleri kapsamaktadır.
                Ramazan umre turları, Ağustos umre turları ve Şevval ayı umre turları için özel fiyatlarımız bulunmaktadır.</p>

                <h3 style="color: #1a5f2a; margin-top: 20px; font-size: 18px;">Şehir Çıkışlı Umre Programları</h3>
                <p>Ankara çıkışlı umre turları, İzmir çıkışlı umre turları, İstanbul umre turları, Bursa umre turları, Konya umre turları,
                Antalya, Adana, Gaziantep, Trabzon, Kayseri, Denizli, Diyarbakır, Sivas ve diğer şehirlerden umre turları düzenliyoruz.
                Diyanet umre turları işbirliğimizle güvenilir ve ekonomik umre paketleri sunuyoruz.</p>

                <h3 style="color: #1a5f2a; margin-top: 20px; font-size: 18px;">En İyi Umre Turları - Neden Yakut Turizm?</h3>
                <ul style="margin-left: 20px;">
                    <li>TÜRSAB lisanslı güvenilir seyahat acentesi - Umre ve hac turları uzmanı</li>
                    <li>En ucuz umre turları fiyat garantisi ve taksit imkanı</li>
                    <li>Deneyimli din görevlisi ve profesyonel rehberler</li>
                    <li>Harem\'e yakın 4-5 yıldızlı otel seçenekleri, VIP ve lüks paketler</li>
                    <li>Kısa süreli umre turları ve uzun süreli programlar</li>
                    <li>7/24 müşteri desteği</li>
                    <li>Binlerce memnun misafir yorumları</li>
                </ul>

                <h3 style="color: #1a5f2a; margin-top: 20px; font-size: 18px;">Umre Turları Hakkında Bilgi</h3>
                <p>Uygun fiyatlı umre turları arıyorsanız, kısa süreli 3 günlük umre turları fiyatları veya lüx umre turları seçeneklerimizi inceleyebilirsiniz.
                2025 yılı umre turları için erken rezervasyon fırsatlarından yararlanın. En iyi umre turları yorumları için referanslarımıza göz atın.</p>

                <p style="margin-top: 20px;"><strong>Umre turları 2025 fiyatları ve detaylı bilgi için hemen iletişime geçin:</strong><br>
                <a href="tel:+902125243435" style="color: #1a5f2a; font-weight: bold;">0212 524 34 35</a><br>
                <small>Ankara, İstanbul, İzmir, Bursa, Konya ve tüm Türkiye\'den umre turları</small></p>
            </div>
          </div>';
}

	?>


  
	</div>
	

  </div>
</div>


<!-- Modal -->
<div class="modal fade" id="veri-filrele" tabindex="-1" role="dialog" aria-labelledby="veri-filreleLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: #ffb900;">
        <h5 class="modal-title" id="veri-filreleLabel">Filtrele</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
			<form action="<?php echo $baseurl_onyuz .'turlar'; ?>" method="GET" >
			  
				<div class="advanceWrp faqs"> 
						  <div class="panel-group" id="accordionKategori2">
							<div class="panel panel-default">
							  <div class="panel-heading">
								<h4 class="panel-title"> <a data-toggle="collapse" data-parent="#accordionKategori2" class="collapsed" href="#collapseKategori2">Tur Kategori</a> </h4>
							  </div>
							  <div id="collapseKategori2" class="panel-collapse collapse show">
								<div class="panel-body">
									<?php foreach ($kategoriler as $kategori):
									  $kategoriVeri = json_decode($kategori['veri'], true)[0]['data'];
									  $kategoriBaslik = $kategoriVeri['diller'][$user_dil]['baslik'] ?? 'Bilinmeyen Kategori';
									  $checked = in_array($kategori['id'], $selectedKategoriler) ? 'checked' : '';
									?>
									  <div class="input-group checkboxx" style="margin-top: 8px;">
										<input type="checkbox" name="kategori[]" class="custom-checkboxx" id="1kategori<?php echo $kategori['id']; ?>" value="<?php echo $kategori['id']; ?>" <?php echo $checked; ?>>
										<label for="1kategori<?php echo $kategori['id']; ?>"></label><?php echo htmlspecialchars($kategoriBaslik); ?>
									  </div>
									<?php endforeach; ?>
								</div>
							  </div>
							</div>
						  </div>
						  <hr>	
				  <div class="panel-group" id="accordionTurSuresi2">
					<div class="panel panel-default">
					  <div class="panel-heading">
						<h4 class="panel-title"> <a data-toggle="collapse" data-parent="#accordionTurSuresi2" class="collapsed" href="#collapseTurSuresi2">Tur Süresi</a> </h4>
					  </div>
					  <div id="collapseTurSuresi2" class="panel-collapse collapse show">
						<div class="panel-body">
						  <?php // Tur Süresi checkbox'ları için
								foreach ($turSureleri as $sure => $label):
									$checked = in_array($sure, $selectedTurSureleri) ? 'checked' : '';
								?>
									<div class="input-group checkboxx" style="margin-top: 8px;"> 
										<input type="checkbox" class="custom-checkboxx" name="turSuresi[]" id="1turSuresi<?php echo $sure; ?>" value="<?php echo $sure; ?>" <?php echo $checked; ?>>
										<label for="1turSuresi<?php echo $sure; ?>"></label><?php echo htmlspecialchars($label); ?>
									</div>
								<?php endforeach; ?>
						</div> 
					  </div>
					</div>
				  </div>
				  <hr>
				  <div class="panel-group" id="accordionTurDonemi2">
					<div class="panel panel-default">
					  <div class="panel-heading">
						<h4 class="panel-title"> <a data-toggle="collapse" data-parent="#accordionTurDonemi2" class="collapsed" href="#collapseTurDonemi2">Tur Dönemi</a> </h4>
					  </div>
					  <div id="collapseTurDonemi2" class="panel-collapse collapse show">
						<div class="panel-body">
						  <?php foreach ($turDonemleri as $id => $donem):
								$checked = in_array($id, $selectedTurDonemleri) ? 'checked' : '';
							?>
								<div class="input-group checkboxx" style="margin-top: 8px;">
									<input type="checkbox" name="turDonemi[]" class="custom-checkboxx" id="1turDonemi<?php echo $id; ?>" value="<?php echo $id; ?>" <?php echo $checked; ?>>
									<label for="1turDonemi<?php echo $id; ?>"></label><?php echo htmlspecialchars($donem); ?>
								</div>
							<?php endforeach; ?>
						</div>
					  </div>
					</div>
				  </div>
				  <hr>
				  <div class="panel-group" id="accordionOtel2">
					<div class="panel panel-default">
					  <div class="panel-heading">
						<h4 class="panel-title"> <a data-toggle="collapse" data-parent="#accordionOtel2" class="collapsed" href="#collapseOtel2">Otel</a> </h4>
					  </div>
					  <div id="collapseOtel2" class="panel-collapse collapse show">
						<div class="panel-body">
						  <?php // Otel checkbox'ları için
							foreach ($oteller as $id => $otel):
								$checked = in_array($id, $selectedOteller) ? 'checked' : '';
							?>
								<div class="input-group checkboxx" style="margin-top: 8px;">
									<input type="checkbox" name="otel[]" class="custom-checkboxx" id="1otel<?php echo $id; ?>" value="<?php echo $id; ?>" <?php echo $checked; ?>>
									<label for="1otel<?php echo $id; ?>"></label><?php echo htmlspecialchars($otel); ?>
								</div>
							<?php endforeach; ?>
						</div>
					  </div>
					</div>
				  </div>
				</div>
				<div class="input-group" style="margin-top: 20px;">
				  <input type="submit" class="submit" value="Listele">
				</div>
			 
			</form>

      </div>

    </div>
  </div>
</div>