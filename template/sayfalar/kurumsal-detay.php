<?php
try {

    // Sayfa ID'sini al
    $sayfaData_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    // Sayfayı veritabanından çek
    $stmt = $pdo->prepare("SELECT * FROM kurumsal WHERE id = ?");
    $stmt->execute([$sayfaData_id]);
    $sayfaData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$sayfaData) {
        throw new Exception("Sayfa bulunamadı.");
    }
    
    $sayfaData_veri = json_decode($sayfaData['veri'], true);

    // Sayfa bilgilerini al
    $sayfaData_baslik = $sayfaData_veri[0]['data']['diller']['tr']['baslik'] ?? 'Kurumsal Sayfa';
    $sayfaData_meta_baslik = $sayfaData_veri[0]['data']['diller']['tr']['meta_baslik'] ?? $sayfaData_baslik;
    $sayfaData_meta_aciklama = $sayfaData_veri[0]['data']['diller']['tr']['meta_aciklama'] ?? '';
    $sayfaData_etiketler = $sayfaData_veri[0]['data']['diller']['tr']['etiketler'] ?? [];
    $sayfaData_link = $sayfaData_veri[0]['data']['diller']['tr']['link'] ?? '';
    $sayfaData_aciklama = $sayfaData_veri[0]['data']['diller']['tr']['aciklama'] ?? '';
	
	// baslik_gizle değerini kontrol et
    $baslik_gizle = $sayfaData_veri[0]['data']['ortak_alanlar']['baslik_gizle'] ?? '0';	
	
	// iletisim_bilgileri değerini kontrol et
    $iletisim_bilgileri_gizle = $sayfaData_veri[0]['data']['ortak_alanlar']['iletisim_bilgileri_gizle'] ?? '0';
	
	    // Bilgi sayfaları ID'lerini alın
    $medya_galeri_idler = $sayfaData_veri[0]['data']['ortak_alanlar']['medya_galeri'] ?? [];

    // Resim seçme fonksiyonu
    function selectImage($data) {
        if (isset($data[0]['data']['resimler']) && is_array($data[0]['data']['resimler'])) {
            // Önce kapak resmini ara
            foreach ($data[0]['data']['resimler'] as $resim) {
                if (isset($resim['kapak_resim']) && $resim['kapak_resim'] == 'evet') {
                    return $resim['dosya_adi'];
                }
            }
            // Kapak resmi yoksa, ilk resmi döndür
            if (!empty($data[0]['data']['resimler'])) {
                return $data[0]['data']['resimler'][0]['dosya_adi'];
            }
        }
        return null; // Hiç resim yoksa null döndür
    }

    // Resmi seç
    $selectedImage = selectImage($sayfaData_veri);


    // Mevcut sayfanın ID'sini ve tablo adını al
    $current_page_id = $sayfaData_id;
    $current_table = isset($_GET['tablo']) ? $_GET['tablo'] : 'kurumsal';

    // $baseurl_onyuz değişkeninin tanımlı olduğundan emin olun
    global $baseurl_onyuz;

		function getKategoriListesi($pdo, $current_page_id, $current_table, $sirket_url, $baseurl_onyuz) {
			$stmt = $pdo->query("SELECT id, veri FROM kurumsal ORDER BY sira ASC");
			$kategoriler = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$kategori_listesi = '';
			foreach ($kategoriler as $kategori) {
				$veri = json_decode($kategori['veri'], true);
				
				if (!$veri || !isset($veri[0]['data']['diller']['tr'])) {
					continue;
				}
				$kategori_data = $veri[0]['data']['diller']['tr'];
				$baslik = $kategori_data['baslik'] ?? '';
				$link = $kategori_data['link'] ?? '';
				$hariciLink = $kategori_data['hariciLink'] ?? '';
				$is_current = ($kategori['id'] == $current_page_id && $current_table == 'kurumsal');
				$title = htmlspecialchars($baslik);
				
				if (!empty($hariciLink)) {
					if (!preg_match("~^(?:f|ht)tps?://~i", $hariciLink)) {
						$full_link = '/' . ltrim($hariciLink, '/');
					} else {
						$full_link = $hariciLink;
					}
				} else {
					$full_link = $sirket_url . $baseurl_onyuz . 'kurumsal-detay/' . $kategori['id'] . '/' . $link;
				}
				
				$full_link = htmlspecialchars($full_link);
				
				if ($is_current) {
					$kategori_listesi .= "<li><a href=\"{$full_link}\" style=\"color: orange;\"><b>{$title}</b></a></li>";
				} else {
					$kategori_listesi .= "<li><a href=\"{$full_link}\">{$title}</a></li>";
				}
			} 
			return $kategori_listesi;
		}


    function getBilgiSayfalariListesi($pdo, $current_page_id, $current_table, $sirket_url, $baseurl_onyuz) {
        $stmt = $pdo->query("SELECT id, veri FROM bilgi_sayfalari ORDER BY sira ASC");
        $bilgi_sayfalari = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $bilgi_sayfalari_listesi = '';
        foreach ($bilgi_sayfalari as $sayfa) {
            $veri = json_decode($sayfa['veri'], true);
            
            if (!$veri || !isset($veri[0]['data']['diller']['tr'])) {
                continue;
            }

            $sayfa_data = $veri[0]['data']['diller']['tr'];
            $baslik = $sayfa_data['baslik'] ?? '';
            $link = $sayfa_data['link'] ?? '';

            $is_current = ($sayfa['id'] == $current_page_id && $current_table == 'bilgi_sayfalari');
            $title = htmlspecialchars($baslik);
            $full_link = htmlspecialchars($sirket_url . $baseurl_onyuz . 'bilgi-sayfalari-detay/' . $sayfa['id'] . '/' . $link);
            
            if ($is_current) {
                $bilgi_sayfalari_listesi .= "<li><a href=\"{$full_link}\" style=\"color: orange;\"><b>{$title}</b></a></li>";
            } else {
                $bilgi_sayfalari_listesi .= "<li><a href=\"{$full_link}\">{$title}</a></li>";
            }
        }

        return $bilgi_sayfalari_listesi;
    }
	
	$bilgi_sayfalari_listesi = getBilgiSayfalariListesi($pdo, $current_page_id, $current_table, $sirket_url, $baseurl_onyuz);
    $kategori_listesi = getKategoriListesi($pdo, $current_page_id, $current_table, $sirket_url, $baseurl_onyuz);
   
	
	
    // Meta açıklama boşsa, içerikten kısa bir özet oluştur
    if (empty($sayfaData_meta_aciklama)) {
        $sayfaData_meta_aciklama = substr(strip_tags($sayfaData_aciklama), 0, 160);
    }

    // Etiketleri string'e çevir (boş etiketleri filtrele + SEO anahtar kelimeler)
    $etiket_dizisi = is_array($sayfaData_etiketler) ? array_filter($sayfaData_etiketler) : [];
    $seo_keywords = ['umre turları', 'umre turu', 'umre fiyatları', 'hac turları', 'yakut turizm'];
    $sayfaData_etiketler_string = implode(', ', array_merge($etiket_dizisi, $seo_keywords));

    // Tam URL oluştur (çift slash sorununu önlemek için)
    $tam_url = 'https://' . $_SERVER['HTTP_HOST'] . '/kurumsal-detay/' . $sayfaData_id . '/' . $sayfaData_link;

    // PageData'yı ayarla
    PageData::set(
        $sayfaData_meta_baslik . ' - ' . $sirket_adi,
        $sayfaData_meta_aciklama,
        $sayfaData_etiketler_string,
        [
            'og:title' => $sayfaData_meta_baslik,
            'og:description' => $sayfaData_meta_aciklama,
            'og:type' => 'website',
            'og:url' => $tam_url,
            'og:site_name' => $sirket_adi,
            'twitter:card' => 'summary',
            'twitter:title' => $sayfaData_meta_baslik,
            'twitter:description' => $sayfaData_meta_aciklama
        ]
    );

} catch (Exception $e) {
    die("Hata: " . $e->getMessage());
}
?>
<!--Inner Heading Start-->
<div class="innerHeading">
  <div class="container">
    <h1><?= htmlspecialchars($sayfaData_baslik) ?></h1>
  </div>
</div>
<!--Inner Heading End--> 

<!--Inner Content Start-->
<div class="innercontent">
  <div class="container">
    <div class="row blog_details">
      <div class="col-lg-8">
        <div class="property_box wow fadeInUp">
          <?php if ($selectedImage): ?>
<div class="propertyImg"><img alt="<?= htmlspecialchars($sayfaData_baslik) ?>" src="<?= $baseurl_onyuz . htmlspecialchars($selectedImage) ?>"></div>
<?php endif; ?>
         <h3 <?= $baslik_gizle === "1" ? 'style="display:none;"' : '' ?>>
  <a href="#"><?= htmlspecialchars($sayfaData_baslik) ?></a>
</h3>
		  

	<style>
		.hesapno-info {
			box-shadow: none;
			background: #fdfdfd;
			border: 1px solid #dcdcdcab;
			padding: 15px;
			position: relative;
			margin-bottom: 30px;
			border-radius: 13px;
			box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);

		}	

		.hesapno-name {
			font-size: 18px;
			color: #328bc3;
			margin-top: 16px;
			text-transform: uppercase;
			font-weight: bold;
			line-height: 22px;
			margin-bottom: 10px;
		}

		.hesapno-p {
			font-size: 18px;
			line-height: 24px;
			color: #333;
		}
		.hesapno-copy {
			float: right;
			padding: .15rem .45rem;
		}

		.hesapno-copy-all {
			float: right;
			padding: .15rem .45rem;
		}	
	</style>
			
		
<?php //echo $sayfaData_aciklama; ?>

<?php


function extractData($html) {
    $allData = [];
    
    // Her bir banka bilgisi bloğunu bul (her <td> etiketi bir banka bilgisi içerir)
    preg_match_all('/<td>(.*?)<\/td>/s', $html, $bankBlocks);

    foreach ($bankBlocks[1] as $bankBlock) {
        $data = [];
        
        // Logo
        if (preg_match('/logo{<img.*?src="(data:image\/png;base64,[^"]+)"/', $bankBlock, $matches)) {
            $data['logo'] = $matches[1];
        }

        // Diğer bilgileri çıkar
        $patterns = [
            'banka_adi' => '/banka_adi{([^}]+)}/',
            'hesap_adi' => '/hesap_adi{([^}]+)}/',
            'sube' => '/sube{([^}]+)}/',
            'sube_no' => '/sube_no{([^}]+)}/',
            'swift_no' => '/swift_no{([^}]+)}/',
            'iban_try' => '/iban_try{([^}]+)}/',
            'iban_us' => '/iban_us{([^}]+)}/',
            'iban_euro' => '/iban_euro{([^}]+)}/',
            'hesap_no_try' => '/hesap_no_try{([^}]+)}/',
            'hesap_no_usd' => '/hesap_no_usd{([^}]+)}/',
            'hesap_no_euro' => '/hesap_no_euro{([^}]+)}/'
        ];

        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $bankBlock, $matches)) {
                $data[$key] = trim($matches[1]);
            }
        }

        $allData[] = $data;
    }

    return $allData;
}

function generateHTML($allData) {
    $html = '';
    foreach ($allData as $data) {
        $html .= '
        <div class="testService">
            <div class="hesapno-info">
                <div class="hesapno-image">';
        
        if (!empty($data['logo'])) {
            $html .= '<img src="' . htmlspecialchars($data['logo']) . '" style="width: 170px;">';
        }
        
        $html .= '</div>
                <div class="hesapno-name">
                    <span class="banka-adi">' . $data['banka_adi'] . '</span>
                    <button class="btn btn-app hesapno-copy-all" onclick="kopyalaTum(this)" title="Tüm Bilgileri Kopyala">
                        <i class="fas fa-copy"></i> 
                    </button>
                </div>
                <hr>
                <p class="hesapno-p"><b>Hesap Adı:</b> <span class="hesap-adi">' . htmlspecialchars($data['hesap_adi']) . '</span> <button class="btn btn-app hesapno-copy" onclick="kopyala(this)" data-kopyala="' . htmlspecialchars($data['hesap_adi']) . '"><i class="fas fa-copy"></i></button></p>
                <p class="hesapno-p"><b>Şube:</b> <span class="sube">' . htmlspecialchars($data['sube']) . '</span></p>
                <p class="hesapno-p"><b>Şube No:</b> <span class="sube-no">' . htmlspecialchars($data['sube_no']) . '</span></p>
                <p class="hesapno-p"><b>Swift No:</b> <span class="swift-no">' . htmlspecialchars($data['swift_no']) . '</span> <button class="btn btn-app hesapno-copy" onclick="kopyala(this)" data-kopyala="' . htmlspecialchars($data['swift_no']) . '"><i class="fas fa-copy"></i></button></p>
                <hr>
				<p class="hesapno-p"><b>Türk Lirası:</b></p>
                <p class="hesapno-p"><b>Hesap No:</b> <span class="hesap-no-try">' . htmlspecialchars($data['hesap_no_try']) . '</span> <button class="btn btn-app hesapno-copy" onclick="kopyala(this)" data-kopyala="' . htmlspecialchars($data['hesap_no_try']) . '"><i class="fas fa-copy"></i></button></p>
                <p class="hesapno-p"><b>İban:</b> <span class="iban-try">' . htmlspecialchars($data['iban_try']) . '</span> <button class="btn btn-app hesapno-copy" onclick="kopyala(this)" data-kopyala="' . htmlspecialchars($data['iban_try']) . '"><i class="fas fa-copy"></i></button></p>
                <hr>
                <p class="hesapno-p"><b>Dolar:</b></p>
                <p class="hesapno-p"><b>Hesap No:</b> <span class="hesap-no-usd">' . htmlspecialchars($data['hesap_no_usd']) . '</span> <button class="btn btn-app hesapno-copy" onclick="kopyala(this)" data-kopyala="' . htmlspecialchars($data['hesap_no_usd']) . '"><i class="fas fa-copy"></i></button></p>
                <p class="hesapno-p"><b>İban:</b> <span class="iban-us">' . htmlspecialchars($data['iban_us']) . '</span> <button class="btn btn-app hesapno-copy" onclick="kopyala(this)" data-kopyala="' . htmlspecialchars($data['iban_us']) . '"><i class="fas fa-copy"></i></button></p>                <hr>
                
				<p class="hesapno-p"><b>Euro:</b></p>
                <p class="hesapno-p"><b>Hesap No:</b> <span class="hesap-no-euro">' . htmlspecialchars($data['hesap_no_euro']) . '</span> <button class="btn btn-app hesapno-copy" onclick="kopyala(this)" data-kopyala="' . htmlspecialchars($data['hesap_no_euro']) . '"><i class="fas fa-copy"></i></button></p>
                <p class="hesapno-p"><b>İban:</b> <span class="iban-euro">' . htmlspecialchars($data['iban_euro']) . '</span> <button class="btn btn-app hesapno-copy" onclick="kopyala(this)" data-kopyala="' . htmlspecialchars($data['iban_euro']) . '"><i class="fas fa-copy"></i></button></p>
            </div>
        </div>';
    }
    
    return $html;
}

function processTabloContent($content) {
    // {tablo} ve {/tablo} arasındaki içeriği bul ve işle
    $pattern = '/\{tablo\}(.*?)\{\/tablo\}/s';
    return preg_replace_callback($pattern, function($matches) {
        $tableContent = $matches[1];
        $allData = extractData($tableContent);
        return generateHTML($allData); // {tablo} ve {/tablo} etiketlerini kaldır
    }, $content);
}

// JavaScript fonksiyonları
$jsCode = '
<script>
function kopyala(button) {
    var text = button.getAttribute("data-kopyala");
    kopyalaMetin(text, button);
}

function kopyalaTum(button) {
    var container = button.closest(".hesapno-info");
    var tumBilgiler = "Banka Adı: " + container.querySelector(".banka-adi").textContent.trim() + "\n" +
                      "Hesap Adı: " + container.querySelector(".hesap-adi").textContent.trim() + "\n" +
                      "Şube: " + container.querySelector(".sube").textContent.trim() + "\n" +
                      "Şube No: " + container.querySelector(".sube-no").textContent.trim() + "\n" +
                      "Swift No: " + container.querySelector(".swift-no").textContent.trim() + "\n" +
                      "------\n" +
                      "Türk Lirası:\n" +
                      "Hesap No: " + container.querySelector(".hesap-no-try").textContent.trim() + "\n" +
                      "İban: " + container.querySelector(".iban-try").textContent.trim() + "\n" +
                      "------\n" +
                      "ABD Doları:\n" +
                      "Hesap No: " + container.querySelector(".hesap-no-usd").textContent.trim() + "\n" +
                      "İban: " + container.querySelector(".iban-us").textContent.trim();
    kopyalaMetin(tumBilgiler, button);
}

function kopyalaMetin(text, button) {
    navigator.clipboard.writeText(text).then(function() {
        var originalText = button.innerHTML;
        button.innerHTML = "<i class=\'fas fa-check\'></i> Kopyalandı!";
        setTimeout(function() {
            button.innerHTML = originalText;
        }, 2000);
    }).catch(function(err) {
        console.error("Kopyalama başarısız oldu: ", err);
    });
}
</script>';

// Ana işlem
$input_html = $sayfaData_aciklama;
$processed_html = processTabloContent($input_html);
$output_html = $processed_html . $jsCode;
$sayfaData_aciklama = $output_html;

echo $sayfaData_aciklama;
?>
    </div>
	
	
	<?php include 'template/bloklar/medya_galeri.php'; ?>
  

	<?php if($iletisim_bilgileri_gizle == 1){ ?>
		<div class="property_box wow fadeInUp" style="visibility: visible; animation-name: fadeInUp; margin-top: 30px;">
		
		
		<?php

		$kurumsalData = getKurumsalContactData($pdo);

		if ($kurumsalData) {
			?>
		<div class="list-group">
				<h4>İletişim Blgilerimiz</h4>
			  <a href="<?php echo $kurumsalData['contact']['harita-yol-tarifi']; ?>" target="_blank" class="list-group-item list-group-item-action flex-column align-items-start">

						<strong>Adres:</strong> <?php echo $kurumsalData['contact']['adres'] ." ". $kurumsalData['contact']['ilce'] ." / ". $kurumsalData['contact']['il']; ?>   
			 
			 </a>

			  <a href="tel:<?php echo $kurumsalData['contact']['sabit-telefon']; ?>" target="_blank" class="list-group-item list-group-item-action flex-column align-items-start">
				
					
						<strong>Sabit Telefon: </strong>   <?php echo $kurumsalData['contact']['sabit-telefon']; ?>
					
				
			 </a>
			 
			  <a href="tel:<?php echo $kurumsalData['contact']['mobil-telefon']; ?>" target="_blank" class="list-group-item list-group-item-action flex-column align-items-start">
				
					
						<strong>Mobil Telefon: </strong>   <?php echo $kurumsalData['contact']['mobil-telefon']; ?>

		 
			 </a>
			 
			  <a href="https://wa.me/9<?php echo $kurumsalData['contact']['whatsapp']; ?>" target="_blank" class="list-group-item list-group-item-action flex-column align-items-start">
				
						<strong>WhatSapp:</strong>  <?php echo $kurumsalData['contact']['whatsapp']; ?> 

			 </a>			 
			  <a href="mailto:<?php echo $kurumsalData['contact']['mail']; ?>" target="_blank" class="list-group-item list-group-item-action flex-column align-items-start">
				
						<strong>E-Mail:</strong>  <?php echo $kurumsalData['contact']['mail']; ?> 

			 </a>
			</div>		
			
			<?php
			


		} else {
			echo "<p>İletişim bilgileri yüklenirken bir hata oluştu.</p>";
		}
		?>	


      </div>
	  
		<div class="property_box wow fadeInUp" style="visibility: visible; animation-name: fadeInUp; margin-top: 30px;">
		
		
			<?php

			$kurumsalData = getKurumsalContactData($pdo);

			if ($kurumsalData) {

				// Harita gösterme
				if (isset($kurumsalData['contact']['harita-iframe'])) {

					echo "<h4>Konum</h4>";
					echo "<iframe src='{$kurumsalData['contact']['harita-iframe']}' width='100%' height='300' style='border:0;' allowfullscreen='' loading='lazy' referrerpolicy='no-referrer-when-downgrade'></iframe>";

				} 
				?>
				<div style="margin-top: 13px;">
						<a href="<?php echo $kurumsalData['contact']['harita-kisa-link']; ?>" target="_blank" class="btn btn-primary btn-sm"><i class="fa fa-search-plus"></i> Google Haritalarda Göster</a>
						<a href="<?php echo $kurumsalData['contact']['harita-yol-tarifi']; ?>" target="_blank" class="btn btn-warning btn-sm"><i class="fa fa-directions"></i> Yol Tarifi Al</a>
						<a href=" https://api.whatsapp.com/send?text=<?php echo $sirket_adi; ?> Haritalardaki Konumu <?php echo $kurumsalData['contact']['harita-kisa-link']; ?>" target="_blank" class="btn btn-success btn-sm"> <i class="fab fa-whatsapp" aria-hidden="true"></i> Haritayı Paylaş</a>
				</div>
				<?php

			} else {
				echo "<p>İletişim bilgileri yüklenirken bir hata oluştu.</p>";
			}
			?>		

      </div>		 
	  
		<div class="property_box wow fadeInUp" style="visibility: visible; animation-name: fadeInUp; margin-top: 30px;">
		
<h4>Bize Yazın</h4>

<form class="form mb-md50" method="post" id="contactForm" data-form_turu="iletisim" novalidate>
  <input type="hidden" name="form_type" value="iletisim">
  <div class="messages"></div>
  <div class="controls"> 
    <div class="row"> 
      <div class="col-lg-12">
        <div class="form-group">
          <label for="form_name">İsim Soyisim</label> 
          <input id="form_name" class="form-control" type="text" name="name" placeholder="İsim Soyisim" required maxlength="150">
          <div class="invalid-feedback"></div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="form-group">
          <label for="form_email">Email Adresiniz</label>
          <input id="form_email" class="form-control" type="email" name="email" placeholder="Email Adresiniz" required>
          <div class="invalid-feedback"></div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="form-group">
          <label for="form_phone">Telefon Numaranız</label>
          <input id="form_phone" class="form-control" type="tel" name="phone" placeholder="Telefon Numaranız" required>
          <div class="invalid-feedback"></div>
        </div>
      </div>
      <div class="col-lg-12">
        <div class="form-group">
          <label for="form_subject">Mesajın Konusu</label>
          <input id="form_subject" class="form-control" type="text" name="subject" placeholder="Mesajın Konusu" required maxlength="200">
          <div class="invalid-feedback"></div>
        </div>
      </div>
      <div class="col-lg-12">
        <div class="form-group">
          <label for="form_message">Mesajınız</label>
          <textarea id="form_message" class="form-control" name="message" placeholder="Mesajınız" rows="4" required maxlength="500"></textarea>
          <div class="invalid-feedback"></div>
        </div>
      </div>
      <div class="col-lg-12">
        <div class="form-check">
          <input type="checkbox" class="form-check-input" id="kvkk-iletisim" name="kvkk" required>
          <label class="form-check-label" for="kvkk-iletisim">
            <span id="kvkkLink" data-toggle="modal" data-target=".bd-example-modal-lg" span>Kişisel verilerin işlenmesine ilişkin Aydınlatma Metnini. Okudum Onaylıyorum</a> .
		  </label>
          
        </div>
      </div>     
	  <div class="col-lg-12">
        <div class="form-check">
          <input type="checkbox" class="form-check-input" id="haber-duyuru-onay" name="haber-duyuru-onay" value="1">
          <label class="form-check-label" for="haber-duyuru-onay"> Haber Ve duyurulardan haberdar olmak İstiyorum. </label>
          <div class="invalid-feedback"></div>
        </div>
      </div>
      <div class="col-lg-12 contact-wrap">
        <div class="contact-btn">
          <button type="submit" class="sub">Mesajı Gönder <i class="fa fa-arrow-circle-right" aria-hidden="true"></i></button>
        </div>
      </div>
    </div>
  </div>
</form>

	 </div>	  
	  
	<?php } ?>
	  


      </div>
		<div class="col-lg-4">
			<div class="single-widgets widget_category fadeInRight wow" style="margin-top: 0px">
				<h4>Kurumsal</h4>
				<ul>
					<?php echo $kategori_listesi; ?>
				</ul>
			</div>

			<div class="single-widgets widget_category fadeInRight wow">
				<h4>Bilgi Sayfaları</h4>
				<ul>
					<?php echo $bilgi_sayfalari_listesi; ?>
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
							if (!empty($social['link'])) {  // Boş link kontrolü
								echo '<li>';
								echo '<a href="' . htmlspecialchars($social['link']) . '" target="_blank">';
								echo '<i class="' . $social['icon'] . '" aria-hidden="true"></i>';
								echo '</a>';
								echo '</li>';
							}
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
</div>
<!--Inner Content End--> 



<?php
$stmt = $pdo->prepare("SELECT veri FROM bilgi_sayfalari WHERE id = 9");
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
    $data = json_decode($result['veri'], true);
    $baslik = trim($data[0]['data']['diller']['tr']['baslik']);
    $aciklama = trim($data[0]['data']['diller']['tr']['aciklama']);

} 
?>	
<div class="modal fade bd-example-modal-lg iletisim-modal" tabindex="-1" role="dialog" aria-labelledby="kvkkModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="kvkkModalLabel"><?php echo htmlspecialchars($baslik); ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div> 
      <div class="modal-body">
        <div id="kvkkContent" style="max-height: 400px; overflow-y: auto;">
          <?php echo $aciklama; ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
        <button type="button" class="btn btn-primary" id="kvkkAccept">Kabul Ediyorum</button>
      </div>
    </div>
  </div>
</div>



