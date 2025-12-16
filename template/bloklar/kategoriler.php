<?php

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
    
    return 'resimler/gorsel-hazirlaniyor-kategori.jpg'; // Eğer resim bulunamazsa varsayılan bir resim döndür
}

// Veritabanı bağlantısı ve sorgu
$query = $pdo->prepare("
    SELECT * FROM kategori 
    WHERE yayin_durumu = 1 
    AND JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.one-cikar') = '1'
    ORDER BY sira ASC 
    LIMIT 6
");
$query->execute();
$kategoriler = $query->fetchAll(PDO::FETCH_ASSOC);

// Eğer öne çıkarılan kategori yoksa, normal kategorileri göster
if (count($kategoriler) == 0) {
    $query = $pdo->prepare("
        SELECT * FROM kategori 
        WHERE yayin_durumu = 1 
        ORDER BY sira ASC 
        LIMIT 6
    ");
    $query->execute();
    $kategoriler = $query->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!-- popular start -->
<section class="popular_wrap wow fadeInUp">
  <div class="container">
    <h2>Umre Turları</h2>
    <span>  </span>
    <div class="row">
      <?php foreach ($kategoriler as $kategori): 
        $veri = json_decode($kategori['veri'], true);
        $baslik = $veri[0]['data']['diller'][$user_dil]['baslik'] ?? '';
        $resim = getKapakResmi($kategori['veri']);
        $link = $veri[0]['data']['diller'][$user_dil]['link'] ?? '#';
      ?>
	  
        <div class="col-md-6">
		<a href="<?php echo $baseurl_onyuz; ?>turlar/<?php echo htmlspecialchars($link); ?>">
          <div class="popular_img position-relative mt">
            <img alt="<?php echo htmlspecialchars($baslik); ?>" src="<?php echo $baseurl_onyuz . $resim; ?>">
            <div class="popular_img_text">
              <?php echo htmlspecialchars($baslik); ?>
            </div>
          </div>
		  </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>