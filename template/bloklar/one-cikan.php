<!--Properties Start-->
<div class="popular_wrap wow fadeInUp">
  <div class="container">
    
      <h2>Güncel Umre Turları<span>İşte sizin için öne çıkan Umre turlarımız.</span></h2>
    
   
    <!--Row Start-->
<?php
function getKapakResmiOneCikan($resimler) {
    if (!empty($resimler)) {
        foreach ($resimler as $resim) {
            if (isset($resim['kapak_resim']) && $resim['kapak_resim'] === 'evet') {
                return $resim['dosya_adi'];
            }
        }
        // Kapak resmi yoksa ilk resmi döndür
        return $resimler[0]['dosya_adi'];
    }
    // Hiç resim yoksa varsayılan resmi döndür
    return 'resimler/gorsel-hazirlaniyor-one-cikan.jpg';
}





// Veritabanı sorguları
$query = $pdo->prepare("
    SELECT * FROM paketler 
    WHERE yayin_durumu = 1 
    AND JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.one-cikar') = '1'
    AND JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.arsivle') = !'1'
    ORDER BY JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.tur_baslangic_tarihi') ASC 
    LIMIT 6
");
$query->execute();
$paketler = $query->fetchAll(PDO::FETCH_ASSOC);

if (count($paketler) == 0) {
$query = $pdo->prepare("
    SELECT * FROM paketler 
    WHERE yayin_durumu = 1 
    ORDER BY JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.tur_baslangic_tarihi') ASC 
    LIMIT 6
");
    $query->execute();
    $paketler = $query->fetchAll(PDO::FETCH_ASSOC);
}
?>

<ul class="row">
<?php foreach ($paketler as $paket): 
    $veri = is_string($paket['veri']) ? json_decode($paket['veri'], true) : $paket['veri'];
    $veri = $veri[0]['data'] ?? [];
    $baslik = $veri['diller'][$user_dil]['baslik'] ?? '';
    $resimler = $veri['resimler'] ?? [];
    $resim = getKapakResmiOneCikan($resimler);
    $link = $veri['diller'][$user_dil]['link'] ?? '#';
    $ortak = $veri['ortak_alanlar'] ?? [];
    $vurgulama_yazi = $ortak['vurgulama_yazi'] ?? false;
	$manuel_gun = $ortak['kac_gun'] ?? null;
	$manuel_gece = $ortak['kac_gece'] ?? null;	
    $konaklama_suresi = hesaplaKonaklamaSuresi($ortak['tur_baslangic_tarihi'] ?? '', $ortak['tur_bitis_tarihi'] ?? '', $manuel_gun, $manuel_gece);
    $kampanyali = isset($ortak['kampanyali']) && $ortak['kampanyali'] == '1';
    $tukendi = isset($ortak['tukendi']) && $ortak['tukendi'] == '1';
    $tukendi_resim = 'resimler/tukendi.png';
	$link = $baseurl_onyuz . 'tur-detay/' . $paket['id'].'/'.$link;	  
	$turKodu = $ortak['tur_kodu'] ?? '';  // Tur kodunu al
	$alt_baslik_tr = $ortak['alt_baslik_tr'] ?? '';  // Tur kodunu al
?>    	
    <li class="col-lg-4">
        <div class="property_box wow fadeInUp">
            <?php if ($tukendi): ?>
                <div class="tukendi-overlay">
                    <img src="<?php echo $baseurl_onyuz . $tukendi_resim; ?>" alt="Tükendi" class="tukendi-image">
                </div>
            <?php endif; ?>
            <div class="propertyImg">
                <?php if ($kampanyali): ?>
                    <div class="kampanya-etiketi">Kampanyalı</div>
                <?php endif; ?>
				<?php if ($vurgulama_yazi): ?>
                   	<div class="vurgulama_yazi"><?php echo htmlspecialchars($vurgulama_yazi); ?> </div> 
                <?php endif; ?>				
                <img alt="<?php echo htmlspecialchars($baslik); ?>" src="<?php echo htmlspecialchars($baseurl_onyuz . $resim); ?>">
            </div>
				<small style="font-size: 12px;color: #ffb900;"><b># Tur Kodu:  <?php echo htmlspecialchars($turKodu); ?></b> </small>
            <h3 style="margin-top: 0px;"><a href="<?php echo htmlspecialchars($link); ?>"><?php echo htmlspecialchars($baslik); ?></a></h3>
			
			<h4 style="font-size: 14px; line-height: 0; margin-bottom: 20px; line-height: 0;margin-bottom: 20px;"><?php echo htmlspecialchars($alt_baslik_tr ?? ''); ?> </h4>			  

			
			
			
			
            <div class="property_location"><i class="fa fa-plane" aria-hidden="true"></i><?php echo htmlspecialchars($ortak['tur_baslangic_tarihi_aciklama'] . ' : ' . formatTarih($ortak['tur_baslangic_tarihi'])); ?></div>
            <div class="property_location" ><i class="fa fa-bus" aria-hidden="true" style="padding-right: 8px;"></i><?php echo htmlspecialchars($ortak['tur_ara_gecis_tarihi_aciklama'] . ' : ' . formatTarih($ortak['tur_ara_gecis_tarihi'])); ?></div>
            <div class="property_location"><i class="fa fa-plane" aria-hidden="true"></i><?php echo htmlspecialchars($ortak['tur_bitis_tarihi_aciklama'] . ' : ' . formatTarih($ortak['tur_bitis_tarihi'])); ?></div>
           
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

		   <div class="propert_info" style="display: none">
                <ul class="row">
                    <li class="col-4">
                        <div class="proprty_icon">
                            <h5>2'Li Oda</h5>
                            <span class="property_price"><?php echo htmlspecialchars($ortak['ikili_oda_fiyatı'] . $ortak['para_birimi']); ?></span>
                        </div>
                    </li>
                    <li class="col-4">
                        <div class="proprty_icon">
                            <h5>3'Lü Oda</h5>
                            <span class="property_price"><?php echo htmlspecialchars($ortak['uclu_oda_fiyatı'] . $ortak['para_birimi']); ?></span>
                        </div>
                    </li>
                    <li class="col-4">
                        <div class="proprty_icon">
                            <h5>4'Lü Oda</h5>
                            <span class="property_price"><?php echo htmlspecialchars($ortak['dorlu_oda_fiyatı'] . $ortak['para_birimi']); ?></span>
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
</ul>
    <!--Row End--> 
    
  </div>
</div>
<!--Properties End--> 

<?php 
function getFeaturedGalleries($pdo) {
    try {
        // Tüm galerileri çek
        $stmt = $pdo->query("SELECT veri FROM medya_galeri");
        
        $output = '';
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $galleries = json_decode($row['veri'], true);
            
            // JSON decode kontrolü
            if (!$galleries || !is_array($galleries)) {
                continue;
            }
            
            foreach ($galleries as $gallery) {
                // Gerekli alanların varlığını kontrol et
                if (!isset($gallery['data'])) {
                    continue;
                }
                
                $galleryData = $gallery['data'];
                
                // Öne çıkarma kontrolü - one-cikar 1 değilse göster
                if (!isset($galleryData['ortak_alanlar']['one-cikar']) || 
                    $galleryData['ortak_alanlar']['one-cikar'] !== "1") {
                    continue;
                }
                               
                
                $title = $galleryData['diller']['tr']['baslik'] ?? '';
                $images = $galleryData['resimler'] ?? [];
                
                if (empty($images)) {
                    continue;
                }
                
                $output .= '<section class="popular_wrap wow fadeInUp" style="padding: 0px 0px 40px 0px;">
                    <div class="container">
                        <h3 style="font-size: 46px; font-weight: 400; text-align: center; margin-bottom: 10px; ">Umre turlarımızdan kareler</h3>
                        <span style=" margin-bottom: 40px;">Görsellerimiz, turlarımızın her aşamasında sunduğumuz hizmetlerin kalitesini ve misafirlerimize sunduğumuz rahatlığı gözler önüne seriyor.</span>
                        <div class="row">';
                
                // İlk büyük resim
                if (!empty($images[0])) {
                    $output .= '<div class="col-md-8">
                        <div class="colorpickerposition-relative propertyImg">
						<a href="' . htmlspecialchars($images[0]['dosya_adi']) . '" data-lightbox="image-gallery">
                            <img alt="' . htmlspecialchars($images[0]['alt_etiketi'] ?? 'ekonomik umre turları') . '" 
                                 src="/' . htmlspecialchars($images[0]['dosya_adi']) . '">
								 </a>
                        </div>
                    </div>';
                }
                
									
				
				
                // Sağdaki iki küçük resim
                if (!empty($images[1]) || !empty($images[2])) {
                    $output .= '<div class="col-md-4 mt_md">';
                    
                    if (!empty($images[1])) {
                        $output .= '<div class="colorpickerposition-relative propertyImg">
						<a href="' . htmlspecialchars($images[1]['dosya_adi']) . '" data-lightbox="image-gallery">
                            <img alt="' . htmlspecialchars($images[1]['alt_etiketi'] ?? 'Umre Tur programı') . '" 
                                 src="/' . htmlspecialchars($images[1]['dosya_adi']) . '">
								  </a>
                        </div>';
                    }
                    
                    if (!empty($images[2])) {
                        $output .= '<div class="colorpickerposition-relative propertyImg mt">
						<a href="' . htmlspecialchars($images[2]['dosya_adi']) . '" data-lightbox="image-gallery">
                            <img alt="' . htmlspecialchars($images[2]['alt_etiketi'] ?? 'Umre Turlarımız') . '" 
                                 src="/' . htmlspecialchars($images[2]['dosya_adi']) . '">
								 </a>
                        </div>';
                    }
                    
                    $output .= '</div>';
                }
                
                // Alt sıradaki üç resim
                for ($i = 3; $i < min(6, count($images)); $i++) {
                    if (!empty($images[$i])) {
                        $output .= '<div class="col-md-4">
                            <div class="colorpickerposition-relative propertyImg mt">
							<a href="' . htmlspecialchars($images[$i]['dosya_adi']) . '" data-lightbox="image-gallery">
                                <img alt="' . htmlspecialchars($images[$i]['alt_etiketi'] ?? 'Umre Turları') . '" 
                                     src="/' . htmlspecialchars($images[$i]['dosya_adi']) . '">
									 </a>
                            </div>
                        </div>';
                    }
                }
                
                $output .= '</div></div></section>';
            }
        }
        
        if (empty($output)) {
            return ''; 
        }
        
        return $output;
        
    } catch (PDOException $e) {
        error_log("Galeri görüntüleme hatası: " . $e->getMessage());
        return '<p>Galeri yüklenirken bir hata oluştu.</p>';
    }
}
echo getFeaturedGalleries($pdo);


?>

<div class="readmore wow fadeInUp" style="margin-bottom: 0px; text-align: center;">
		 
			<a href="/medya"  style="height: 57px; padding: 17px 36px; font-size: 17px; width: 100%; text-align: center; font-weight: 700; max-width: 350px;"><i class="fas fa-images" aria-hidden="true"></i> Daha Fazla Resim Göster </a>
		 
		 </div>







<!--Buy and Sell Start-->