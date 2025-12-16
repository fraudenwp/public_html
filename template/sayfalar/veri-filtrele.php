<?php
$user_dil = 'tr';

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
$query = "SELECT id, veri FROM paketler WHERE yayin_durumu = 1 
          AND JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.arsivle') = '0'";

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
$orderByClause = " ORDER BY JSON_UNQUOTE(JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.tur_baslangic_tarihi')) ASC";
$limitOffsetClause = " LIMIT " . $limit . " OFFSET " . $offset;
$query .= $orderByClause . $limitOffsetClause;

// Debug için sorguyu yazdır
//echo "SQL Query before execution: " . $query . "<br>";
//echo "Parameters: " . print_r($params, true) . "<br>";

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



// Öne çıkan turları al
$oneCikanQuery = "SELECT veri FROM paketler 
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

// Kategori verilerini çek
$kategoriQuery = "SELECT id, veri FROM kategori WHERE yayin_durumu = 1 ORDER BY sira ASC";
try {
    $kategoriStmt = $pdo->query($kategoriQuery);
    $kategoriler = $kategoriStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Kategori sorgusu başarısız oldu: " . $e->getMessage());
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

?>



<div class="innerHeading">
  <div class="container">
    <h1>Umre Turları</h1>
  </div>
</div>

<div class="innercontent">
  <div class="container">
    <div class="row listing_wrap">
      <div class="col-lg-4">
	  
<form>
  <div class="sidebar_form card card-body wow fadeInUp">
    <div class="advanceWrp faqs">
              <div class="panel-group" id="accordionKategori">
                <div class="panel panel-default">
                  <div class="panel-heading">
                    <h4 class="panel-title"> <a data-toggle="collapse" data-parent="#accordionKategori" class="collapsed" href="#collapseKategori">Tur Kategori</a> </h4>
                  </div>
                  <div id="collapseKategori" class="panel-collapse collapse show">
                    <div class="panel-body">
        <?php foreach ($kategoriler as $kategori):
          $kategoriVeri = json_decode($kategori['veri'], true)[0]['data'];
          $kategoriBaslik = $kategoriVeri['diller'][$user_dil]['baslik'] ?? 'Bilinmeyen Kategori';
          $checked = in_array($kategori['id'], $selectedKategoriler) ? 'checked' : '';
        ?>
          <div class="input-group checkboxx">
            <input type="checkbox" name="kategori[]" class="custom-checkboxx" id="kategori<?php echo $kategori['id']; ?>" value="<?php echo $kategori['id']; ?>" <?php echo $checked; ?>>
            <label for="kategori<?php echo $kategori['id']; ?>"></label><?php echo htmlspecialchars($kategoriBaslik); ?>
          </div>
        <?php endforeach; ?>
                    </div>
                  </div>
                </div>
              </div>
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
          <h4>Öne Çıkan Turlar</h4>
          <ul class="property_sec">
            <?php foreach ($oneCikanTurlar as $tur): 
              $turVeri = json_decode($tur['veri'], true)[0]['data'];
              $turBaslik = $turVeri['diller'][$user_dil]['baslik'] ?? '';
              $turResim = $turVeri['resimler'][0]['dosya_adi'] ?? 'resimler/gorsel-hazirlaniyor-one-cikan.jpg';
              $turLink = $turVeri['diller'][$user_dil]['link'] ?? '#';
            ?>
            <li>
              <div class="rec_proprty">
                <div class="propertyImg"><img src="<?php echo htmlspecialchars($turResim); ?>" style="max-width: 120px;"></div>
                <div class="property_info" style="max-width: 55%;">
                  <h4 style="line-height: 20px;"><a href="<?php echo htmlspecialchars($turLink); ?>"><?php echo htmlspecialchars($turBaslik); ?></a></h4>
                 
                </div>
              </div>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>
      
      </div>
    
	
	
      <div class="col-lg-8">
        <div class="nav sortWrp wow fadeInUp" role="tablist">
          <?php 
          if ($totalResults > 0) {
              echo "$totalResults adet tur bulundu.";
          } else {
              echo "Hiç sonuç bulunamadı.";
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
                  <img src="resimler/tukendi.png" alt="Tükendi" class="tukendi-image">
                </div>
              <?php endif; ?>
              <div class="propertyImg">
                <?php if ($kampanyali): ?>
                  <div class="kampanya-etiketi">Kampanyalı</div>
                <?php endif; ?>
                <img alt="<?php echo htmlspecialchars($baslik); ?>" src="<?php echo htmlspecialchars($resim); ?>">
				
              </div>
			  
              <h3><a href="<?php echo htmlspecialchars($link); ?>"><?php echo htmlspecialchars($baslik); ?></a></h3>
			  
              <div class="property_location"><i class="fa fa-plane" aria-hidden="true"></i> <?php echo htmlspecialchars($ortak['tur_baslangic_tarihi_aciklama'] . ' : ' . formatTarih($ortak['tur_baslangic_tarihi'])); ?></div>
              <div class="property_location"><i class="fa fa-bus" aria-hidden="true"></i> <?php echo htmlspecialchars($ortak['tur_ara_gecis_tarihi_aciklama'] . ' : ' . formatTarih($ortak['tur_ara_gecis_tarihi'])); ?></div>
              <div class="property_location"><i class="fa fa-plane" aria-hidden="true"></i> <?php echo htmlspecialchars($ortak['tur_bitis_tarihi_aciklama'] . ' : ' . formatTarih($ortak['tur_bitis_tarihi'])); ?></div>
              <div class="propert_info">
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
              <a href="<?php echo htmlspecialchars($link); ?>" class="rent_info">
                <div class="apart"><?php echo $konaklama_suresi; ?></div>
                <div class="sale">Detay</div>
              </a>
            </div> 
           </li>
          <?php endforeach; ?>
        <?php else: ?>
          <li class="col-lg-12">
            <p>Seçilen kriterlere uygun tur bulunamadı.</p>
          </li>
        <?php endif; ?>
        </ul>
      
	  
     <!-- Sayfalama linkleri -->
        <?php if ($totalPages > 1): ?>
        <div class="blog-pagination text-center">
          <?php
          $queryParams = $_GET;
          
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
    </div>
  </div>
</div>