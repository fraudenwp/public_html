<style>
.custom-gallery-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.95);
    z-index: 9999;
    display: none;
}

.gallery-content {
    height: 100vh;
    display: flex;
    flex-direction: column;
}

/* Logo */
.gallery-logo {
    position: absolute;
    top: 20px;
    right: 20px;
    max-width: 150px;
    z-index: 1000;
}

/* Ana resim container */
.main-image-container {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 60px;
    position: relative;
}

.main-image-container img {
    max-width: 100%;
    max-height: calc(100vh - 180px); /* Thumbnail yüksekliğini hesaba katarak */
    object-fit: contain;
}

/* Başlık */
.image-description {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    text-align: center;
    color: white;

    background: rgba(0, 0, 0, 0.5);
    font-size: 18px;
}

/* Thumbnail container */
.thumbnails-container {
    height: 100px;
    background: rgba(0, 0, 0, 0.8);
    padding: 10px;
    display: flex;
    gap: 10px;
    overflow-x: auto;
    justify-content: center;
    align-items: center;
}

/* Thumbnails */
.thumbnail {
    width: 120px;
    height: 80px;
    object-fit: cover;
    border: 2px solid transparent;
    transition: all 0.3s;
    cursor: pointer;
}

.thumbnail.active {
    border-color: #ffb900; /* Yakut Turizm sarı rengi */
}

/* Navigasyon butonları */
.nav-button {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 50px;
    height: 50px;
    background: rgba(0, 0, 0, 0.5);
    border: none;
    border-radius: 50%;
    color: white;
    font-size: 24px;
    cursor: pointer;
    transition: all 0.3s;
    z-index: 1000;
	border: 1px solid #fff;
}

.prev-button {
    left: 20px;
}

.next-button {
    right: 20px;
}

.nav-button:hover {
    background: rgba(0, 0, 0, 0.8);
}

/* Kapat butonu */
.close-button {
width: 50px;
    height: 50px;
    background: rgba(0, 0, 0, 0.5);
    border: none;
    border-radius: 50%;
    color: white;
    font-size: 24px;
    cursor: pointer;
    transition: all 0.3s;
    z-index: 1000;
    position: absolute;
    top: 34px;
    right: 36px;
	border: 3px solid #ddd;
}

/* Mobil responsive düzenlemeler */
@media (max-width: 768px) {
    .main-image-container {
        padding: 20px;
    }

    .nav-button {
        width: 40px;
        height: 40px;
        font-size: 20px;
    }

    .thumbnail {
        width: 80px;
        height: 60px;
    }

    .thumbnails-container {
        height: 120px;
    }

    .gallery-logo {
        max-width: 100px;
        top: 10px;
        right: 10px;
    }

    .close-button {
        top: 10px;
        right: 10px;
        font-size: 24px;
    }

    .image-description {
        font-size: 14px;
        padding: 10px;
    }
}

/* Küçük ekranlar için ek düzenlemeler */
@media (max-width: 480px) {
    .thumbnails-container {
        justify-content: flex-start; /* Mobilde thumbnailları sola yasla */
    }

    .thumbnail {
width: 120px;
    height: 80px;
    }

    .nav-button {
        width: 35px;
        height: 35px;
        font-size: 18px;
    }
}

/* Thumbnail scrollbar özelleştirmesi */
.thumbnails-container::-webkit-scrollbar {
    height: 6px;
}

.thumbnails-container::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
}

.thumbnails-container::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 3px;
}

.thumbnails-container::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}
</style>

<?php
// Sayfalar tablosundan veri çekme
$stmt = $pdo->prepare("SELECT veri FROM sayfalar WHERE id = 44");
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
    <h1>Medya Galeri</h1>

  </div>
</div>
<div class="innercontent">
<div class="container">
    <div class="row listing_wrap">
      <div class="col-lg-8">
<ul class="row blog_post">
  
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
        FROM medya_galeri 
        WHERE yayin_durumu = 1 
        ORDER BY CASE WHEN sira IS NULL OR sira = 0 THEN 1 ELSE 0 END, sira ASC
    ");
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = $row["id"];
        $json_data = json_decode($row["veri"], true);
        
        // Dil verilerini al
        $dil_verileri = $json_data[0]['data']['diller'][$user_dil] ?? null;
 
        // Medya gizleme kontrolü
        if (isset($json_data[0]['data']['ortak_alanlar']['medya_gizle']) && 
            $json_data[0]['data']['ortak_alanlar']['medya_gizle'] == 1) {
            continue; // Bu öğeyi atla
        }
 
        if ($dil_verileri) {
            $baslik = $dil_verileri['baslik'];
            $link = $dil_verileri['link'];
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

<li class="col-lg-6 col-md-6">     
    <div class="property_box wow fadeInUp" style="visibility: visible; animation-name: fadeInUp; min-height: 357px;">
        <?php
        $galleryImages = $json_data[0]['data']['resimler'] ?? [];
        $coverImage = !empty($galleryImages) ? $galleryImages[0]['dosya_adi'] : $varsayilan_resim;
        ?>
        <div class="propertyImg">
            <a href="<?php echo $coverImage; ?>" class="custom-lightbox" data-gallery="gallery-<?php echo $id; ?>">
                <img alt="<?php echo $baslik; ?>" src="<?php echo $resim; ?>">
            </a>
        </div>
        <h3 style="text-align: center;font-size: 23px;line-height: 32px;"><?php echo $baslik; ?></h3>

        <!-- Gizli galeri resimleri -->
        <div class="hidden-gallery" style="display: none;">
            <?php
            if (!empty($galleryImages)) {
                foreach ($galleryImages as $index => $image) {
                    echo '<img src="' . $image['dosya_adi'] . '" alt="' . $baslik . '" data-index="' . $index . '">';
                }
            }
            ?>
        </div>
    </div>          
</li>
					
		<?php			
				}
			}
		} catch(PDOException $e) {
			echo "Veritabanı hatası: " . $e->getMessage();
		}
		?>

 
  </ul>

  
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
   
  


