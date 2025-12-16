<?php
// Sayfalar tablosundan veri çekme
$stmt = $pdo->prepare("SELECT veri FROM sayfalar WHERE id = 27");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    $sayfa_veri = json_decode($row['veri'], true);

    if (isset($sayfa_veri[0]['diller']['tr'])) {
        $tr_data = $sayfa_veri[0]['diller']['tr'];

        // Meta verilerini ayarla
        $sayfa_baslik = $tr_data['baslik'] ?? 'Seyahat, Seyahat Bilgileri, Önemli Bilgiler';
        $sayfa_meta_baslik = $tr_data['meta_baslik'] ?? $sayfa_baslik;
        $sayfa_meta_aciklama = $tr_data['meta_aciklama'] ?? '';
        $sayfa_etiketler = $tr_data['etiketler'] ?? [];
        $sayfa_link = $tr_data['link'] ?? '';

        if (empty($sayfa_meta_aciklama)) {
            $sayfa_meta_aciklama = substr(strip_tags($tr_data['aciklama'] ?? ''), 0, 160);
        }

        $sayfa_etiketler_string = implode(', ', $sayfa_etiketler);
        $tam_url = $sirket_url . '/' . $sayfa_link;

        // Kısa açıklama için ortak alanları kontrol et
        $sayfa_kisa_aciklama = $sayfa_veri[0]['ortak_alanlar']['sayfa_kisa-aciklama'] ?? '';

        // PageData'yı ayarla
        PageData::set(
            $sayfa_meta_baslik . ' - ' . $sirket_adi,
            $sayfa_meta_aciklama,
            $sayfa_etiketler_string,
            [
                'og:title' => $sayfa_meta_baslik,
                'og:description' => $sayfa_meta_aciklama,
                'og:type' => 'website',
                'og:url' => 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
                'og:site_name' => $sirket_adi,
                'twitter:card' => 'summary_large_image',
                'twitter:title' => $sayfa_meta_baslik,
                'twitter:description' => $sayfa_meta_aciklama
            ]
        );

        // Eğer kısa açıklama varsa, onu da meta verilerine ekle
        if (!empty($sayfa_kisa_aciklama)) {
            PageData::setMeta('og:description', $sayfa_kisa_aciklama);
            PageData::setMeta('twitter:description', $sayfa_kisa_aciklama);
        }
    } else {
        // Veri yapısı beklenen formatta değilse varsayılan değerleri kullan
        setDefaultMetaData();
    }
} else {
    // Eğer veri bulunamazsa varsayılan değerleri kullan
    setDefaultMetaData();
}

function setDefaultMetaData() {
    PageData::set(
        'Blok, Haber, Makale - ' . $sirket_adi,
        'Blok, Haber ve Makaleler hakkında bilgiler',
        'Blok, Haber, Makale',
        [
            'og:title' => 'Blok, Haber, Makale',
            'og:description' => 'Blok, Haber ve Makaleler hakkında bilgiler',
            'og:type' => 'website',
            'og:url' => $sirket_url . '/blok-haber',
            'og:site_name' => $sirket_adi,
            'twitter:card' => 'summary_large_image',
            'twitter:title' => 'Blok, Haber, Makale',
            'twitter:description' => 'Blok, Haber ve Makaleler hakkında bilgiler'
        ]
    );
}
?>

<div class="innerHeading">
  <div class="container">
    <h1>Bilgi Sayfaları</h1>

  </div>
</div>
<div class="innercontent">
<div class="container">
    <div class="row listing_wrap">
      <div class="col-lg-8">
<div class="list-group">
  
<?php

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


// Varsayılan dil ve resim yolu tanımlamaları
$user_dil = $user_dil ?? 'tr'; // Eğer $user_dil tanımlı değilse, varsayılan olarak 'tr' kullan
$varsayilan_resim = '/yol/varsayilan_resim.jpg'; // Varsayılan resim yolu

try {
    // Verileri çek ve sırala
    $stmt = $pdo->prepare("
        SELECT id, veri, yayin_durumu, sira 
        FROM bilgi_sayfalari 
        WHERE yayin_durumu = 1 
        ORDER BY CASE WHEN sira IS NULL OR sira = 0 THEN 1 ELSE 0 END, sira ASC
    ");
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = $row["id"];
        $json_data = json_decode($row["veri"], true);
        
        // Dil verilerini al
        $dil_verileri = $json_data[0]['data']['diller'][$user_dil] ?? null;
        
        if ($dil_verileri) {
            $baslik = $dil_verileri['baslik'];
            $link = $dil_verileri['link'];
            $aciklama = strip_tags($dil_verileri['aciklama']); // HTML etiketlerini kaldır
            $aciklama = mb_substr($aciklama, 0, 300) . (mb_strlen($aciklama) > 300 ? '...' : '');

            // Resim seçimi
            $resim = $varsayilan_resim;
            foreach ($json_data[0]['data']['resimler'] as $resim_data) {
                if ($resim_data['kapak_resim'] == 'evet') {
                    $resim = $resim_data['dosya_adi'];
                    break;
                }
            }
            if ($resim == $varsayilan_resim && !empty($json_data[0]['data']['resimler'])) {
                $resim = $json_data[0]['data']['resimler'][0]['dosya'];
            }
			
?>			
			


<li class="list-group-item d-flex justify-content-between align-items-center">

  <a href="<?php echo $baseurl_onyuz.'bilgi-sayfalari-detay/'.$id.'/'.$link; ?>"><?php echo $baslik; ?></a>
 <span class="fas fa-info-circle" aria-hidden="true"></span> 

</li>
			
			
<?php			
        }
    }
} catch(PDOException $e) {
    echo "Veritabanı hatası: " . $e->getMessage();
}
?>

 
  </div>

  
  </div>
   <div class="col-lg-4">
   
   
    		<div class="single-widgets widget_category fadeInUp wow" style="margin: 0px 0 40px;">
          <h4>Öne Çıkan Turlar</h4>
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
		
			<div class="single-widgets social-icons footer_icon fadeInRight wow">
				<h4>Sosyal Medyada Biz</h4>
			   
				<?php
					$kurumsalData = getKurumsalContactData($pdo);

					if ($kurumsalData && isset($kurumsalData['socialMedia'])) {
						$socialMediaLinks = processSocialMediaInfo($kurumsalData['socialMedia']);
						
						if (!empty($socialMediaLinks)) {
							echo '<div class="social-icons footer_icon">';
							echo '<ul>';
							foreach ($socialMediaLinks as $social) {
								echo '<li>';
								echo '<a href="' . htmlspecialchars($social['link']) . '" target="_blank">';
								echo '<i class="' . $social['icon'] . '" aria-hidden="true"></i>';
								echo '</a>';
								echo '</li>';
							}
							echo '</ul>';
							echo '</div>';
						}
					}
								?>

								
								
								

				
			</div>		
		
  </div>
  </div>
  </div>