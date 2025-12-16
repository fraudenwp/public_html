

<!--Buy and Sell Start-->
<div class="buy-wrap wow fadeInUp" style="margin-top: 40px;">
  <div class="container">
    <div class="title">
      <h1>Sizin Umreniz Hangisi?</h1>
    </div>
    <p style="font-size: 30px;">Sizin için en ideal umre turunu beraber seçelim.</p>
    <div class="start_btn"> <span><a href="kurumsal-detay/3/iletisim">Hemen Ara Bilgi Al</a></span> </div>
  </div>
</div>
<!--Buy and Sell Start-->



<?php
// Veritabanı bağlantısı ve diğer gerekli işlemler burada...
$user_dil = 'tr';
// Tüm paketleri çek
$query = $pdo->prepare("
    SELECT veri
    FROM paketler 
    WHERE yayin_durumu = 1 
    AND JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.tukendi') = '0'
    AND JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.arsivle') = '0'
");
$query->execute();
$paketler = $query->fetchAll(PDO::FETCH_ASSOC);

$turSureleri = [];
$turDonemleri = [];
$oteller = [];

foreach ($paketler as $paket) {
    $veri = json_decode($paket['veri'], true)[0]['data'];
    $ortak_alanlar = $veri['ortak_alanlar'];
    
    // Manuel gün değeri varsa onu kullan, yoksa hesapla
    if (isset($ortak_alanlar['kac_gun']) && !empty($ortak_alanlar['kac_gun'])) {
        $turSuresi = $ortak_alanlar['kac_gun'];
    } else {
        $baslangic = new DateTime($ortak_alanlar['tur_baslangic_tarihi']);
        $bitis = new DateTime($ortak_alanlar['tur_bitis_tarihi']);
        $sureFark = $baslangic->diff($bitis);
        $turSuresi = $sureFark->days + 1;
    }
    
    $turSureleri[$turSuresi] = $turSuresi . ' Günlük Umre Turu';
    $turDonemleri = array_merge($turDonemleri, $ortak_alanlar['donem'] ?? []);
    $oteller[] = $ortak_alanlar['otel_bir'];
    $oteller[] = $ortak_alanlar['otel_iki'];
}

$turSureleri = array_unique($turSureleri);
$turDonemleri = array_unique($turDonemleri);
$oteller = array_unique(array_filter($oteller));

// Dönem ve otel isimlerini al
$donemQuery = $pdo->prepare("SELECT id, veri FROM donem WHERE id IN (" . implode(',', $turDonemleri) . ")");
$donemQuery->execute();
$donemler = $donemQuery->fetchAll(PDO::FETCH_ASSOC);

$otelQuery = $pdo->prepare("SELECT id, veri FROM oteller WHERE id IN (" . implode(',', $oteller) . ")");
$otelQuery->execute();
$otellerData = $otelQuery->fetchAll(PDO::FETCH_ASSOC);

$donemlerFormatted = [];
$otellerFormatted = [];

foreach ($donemler as $donem) {
    $veri = json_decode($donem['veri'], true)[0]['data'];
    $donemlerFormatted[$donem['id']] = $veri['diller'][$user_dil]['baslik'];
}

foreach ($otellerData as $otel) {
    $veri = json_decode($otel['veri'], true)[0]['data'];
    $otellerFormatted[$otel['id']] = $veri['diller'][$user_dil]['baslik'];
}

$jsonData = json_encode([
    'paketler' => $paketler,
    'turSureleri' => $turSureleri,
    'turDonemleri' => $donemlerFormatted,
    'oteller' => $otellerFormatted
]);

echo "<script>var turData = " . $jsonData . ";</script>";
?>

<div class="form_sec slider-wrap wow fadeInUp">
  <div class="container">
    <div class="form-wrap">
      <form id="turForm">
        <div class="row">
          <div class="col-lg-3 end_date">
            <div class="input-group">
              <select id="turSuresi" name="turSuresi[]" class="form-control">
                <option value="">Tur Süresi Seçin</option>
                <!-- Tur süreleri burada listelenecek -->
              </select>
            </div>
          </div>
          <div class="col-lg-3 economy">
            <div class="input-group">
              <select id="turDonemi" name="turDonemi[]" class="form-control" disabled>
                <option value="">Tur Dönemi Seçin</option>
                <!-- Tur dönemleri burada listelenecek -->
              </select>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="input-group">
              <select id="otel" name="otel[]" class="form-control" disabled>
                <option value="">Otel Seçin</option>
                <!-- Oteller burada listelenecek -->
              </select>
            </div>
          </div>
          <div class="col-lg-2">
            <div class="input-btn">
              <button type="submit" class="sbutn"><i class="fa fa-search" aria-hidden="true"></i> Ara </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>





<div class="property-wrap wow fadeInUp" style="margin-top: 40px;">
  <div class="container">

<ul class="blog_post row">
            <?php 			
				// Öne çıkan turları al
				$oneCikanQuery = "SELECT veri, id FROM blok_haber 
								  WHERE yayin_durumu = 1 							  
								  AND JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.one-cikar') = '1'
								  ORDER BY JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.tur_baslangic_tarihi') ASC
								  LIMIT 10";

				try {
					$oneCikanStmt = $pdo->query($oneCikanQuery);
					$oneCikanTurlar = $oneCikanStmt->fetchAll(PDO::FETCH_ASSOC);
				} catch (PDOException $e) {
					die("Öne çıkan turlar sorgusu başarısız oldu: " . $e->getMessage());
				}			
				
				foreach ($oneCikanTurlar as $tur): 
				  $turVeri = json_decode($tur['veri'], true)[0]['data'];
				  $turBaslik = $turVeri['diller'][$user_dil]['baslik'] ?? '';
				  $turAciklama = $turVeri['diller'][$user_dil]['aciklama'] ?? '';
				  $turKisaAciklama = $turVeri['ortak_alanlar']['kisa_aciklama'] ?? '';
				  $turResim = $turVeri['resimler'][0]['dosya_adi'] ?? 'resimler/haber-blok-resim-hazirlaniyor.jpg';
				  $turLink = $turVeri['diller'][$user_dil]['link'] ?? '#';
				  $turid = $tur['id'];
				  $detay_url = $baseurl_onyuz . "blok-haber-detay/{$turid}/{$turLink}";
            ?>

          <li class="col-lg-4 col-md-6"> 
            <div class="property_box wow fadeInUp" style="min-height: 250px;">
              <div class="row">
                <div class="col-lg-12 col-md-6">
                  <div class="propertyImg"><img alt="<?php echo $turBaslik; ?>" src="<?php echo $baseurl_onyuz . htmlspecialchars($turResim); ?>"></div>
                </div>
                <div class="col-lg-12 col-md-12">
                  <h3><a href="<?php echo $detay_url; ?>"><?php echo $turBaslik; ?></a></h3>                 
                  <p><?php echo $turKisaAciklama; ?> </p>
                </div>
              </div>
            </div>
          </li>


       		

		<?php endforeach; ?>
       </ul>
	   
      
		  </div>
  </div>
