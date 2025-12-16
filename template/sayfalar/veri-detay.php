<?php
try {
	// Paketleri Çek
	$paket_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
	$link = isset($_GET['link']) ? intval($_GET['link']) : 0;

	$stmt = $pdo->prepare("SELECT * FROM paketler WHERE id = ?");
	$stmt->execute([$paket_id]);
	$paket = $stmt->fetch(PDO::FETCH_ASSOC);

	if (!$paket) {
		throw new Exception("Paket bulunamadı.");
	}

	$paket_veri = json_decode($paket['veri'], true);

	/// Paket Resimleri Çek
	$paket_resim_yolu = '';
	if (!empty($paket_veri[0]['data']['resimler']) && is_array($paket_veri[0]['data']['resimler'])) {
		foreach ($paket_veri[0]['data']['resimler'] as $resim) {
			if (isset($resim['kapak_resim']) && $resim['kapak_resim'] === 'evet') {
				$paket_resim_yolu = htmlspecialchars($resim['dosya_adi']);
				break;
			}
		}
		// Eğer kapak resmi bulunamazsa, ilk resmi kullan
		if (empty($paket_resim_yolu) && !empty($paket_veri[0]['data']['resimler'][0]['dosya_adi'])) {
			$paket_resim_yolu = htmlspecialchars($paket_veri[0]['data']['resimler'][0]['dosya_adi']);
		}
	}
	// Resim yoksa varsayılan bir resim kullan
	$paket_resim_yolu = $baseurl_onyuz . $paket_resim_yolu ?: $baseurl_onyuz . 'resimler/gorsel-hazirlaniyor-one-cikan.jpg';

	/// Paket Fiyatları Çek
	function fiyat_goster($fiyat, $para_birimi)
	{
		if (isset($fiyat) && $fiyat !== '') {
			return htmlspecialchars($fiyat) . ' ' . htmlspecialchars($para_birimi);
		}
		return 'Sorunuz';
	}

	// Bilgi sayfaları ID'lerini alın
	$bilgi_sayfalari_idler = $paket_veri[0]['data']['ortak_alanlar']['bilgi_sayfalari'] ?? [];

	// Otel ve Havayolu ID'lerini al
	$otel_bir_id = $paket_veri[0]['data']['ortak_alanlar']['otel_bir'] ?? 0;
	$otel_iki_id = $paket_veri[0]['data']['ortak_alanlar']['otel_iki'] ?? 0;
	$gidis_hava_yolu_id = $paket_veri[0]['data']['ortak_alanlar']['gidis_hava_yolu'] ?? 0;
	$gelis_hava_yolu_id = $paket_veri[0]['data']['ortak_alanlar']['gelis_hava_yolu'] ?? 0;

	// Helper fonksiyonları tanımla
	function get_otel_resimleri($otel_veri)
	{
		$varsayilan_resim = 'resimler/gorsel-hazirlaniyor-one-cikan.jpg';
		$kapak_resim = $varsayilan_resim;
		$tum_resimler = [];

		if (isset($otel_veri[0]['data']['resimler']) && is_array($otel_veri[0]['data']['resimler'])) {
			foreach ($otel_veri[0]['data']['resimler'] as $resim) {
				if (isset($resim['dosya_adi'])) {
					$tum_resimler[] = $resim['dosya_adi'];

					// Kapak resmini bul
					if (isset($resim['kapak_resim']) && $resim['kapak_resim'] === 'evet') {
						$kapak_resim = $resim['dosya_adi'];
					}
				}
			}

			// Eğer kapak resmi bulunamadıysa ve başka resimler varsa, ilk resmi kapak resmi yap
			if ($kapak_resim === $varsayilan_resim && !empty($tum_resimler)) {
				$kapak_resim = $tum_resimler[0];
			}
		}

		return [
			'kapak_resim' => $kapak_resim,
			'tum_resimler' => $tum_resimler
		];
	}

	function get_otel_olanaklar($otel_veri)
	{
		$olanaklar = [];

		// "ortak_alanlar" anahtarını kontrol et
		if (isset($otel_veri[0]['data']['ortak_alanlar']) && is_array($otel_veri[0]['data']['ortak_alanlar'])) {
			// "ortak_alanlar" içindeki anahtarları dolaş
			foreach ($otel_veri[0]['data']['ortak_alanlar'] as $key => $value) {
				// Anahtar "otel_olanaklar" ile başlıyorsa, bunu olanaklar listesine ekle
				if (strpos($key, 'otel_olanaklar') === 0) {
					$olanaklar[$key] = $value;
				}
			}
		}

		return $olanaklar;
	}

	// Otelleri çek
	$stmt = $pdo->prepare("SELECT * FROM oteller WHERE id IN (?, ?)");
	$stmt->execute([$otel_bir_id, $otel_iki_id]);
	$oteller = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$otel_bir = $otel_iki = null;
	$ikinci_otel_goster = false; // İkinci otelin gösterilip gösterilmeyeceğini belirleyen flag

	foreach ($oteller as $otel) {
		$otel_veri = json_decode($otel['veri'], true);
		$resimler = get_otel_resimleri($otel_veri);

		// İlk oteli ayarla
		if ($otel['id'] == $otel_bir_id) {
			$otel_bir = [
				'veri' => $otel_veri,
				'kapak_resim' => $resimler['kapak_resim'],
				'tum_resimler' => $resimler['tum_resimler'],
				'olanaklar' => get_otel_olanaklar($otel_veri)
			];
		}

		// İkinci oteli ayarla - sadece farklı bir otel ID'si varsa
		if ($otel['id'] == $otel_iki_id && $otel_iki_id != $otel_bir_id && $otel_iki_id != 0) {
			$otel_iki = [
				'veri' => $otel_veri,
				'kapak_resim' => $resimler['kapak_resim'],
				'tum_resimler' => $resimler['tum_resimler'],
				'olanaklar' => get_otel_olanaklar($otel_veri)
			];
			$ikinci_otel_goster = true; // İkinci otel var ve gösterilecek
		}
	}

	// Eğer birinci otel boşsa, varsayılan değerlerle doldur
	if (!isset($otel_bir)) {
		$otel_bir = [
			'veri' => [0 => ['data' => ['diller' => ['tr' => ['baslik' => 'Otel Bilgisi Yok', 'aciklama' => '']], 'ortak_alanlar' => []]]],
			'kapak_resim' => 'resimler/gorsel-hazirlaniyor-one-cikan.jpg',
			'tum_resimler' => [],
			'olanaklar' => []
		];
	}

	// Havayollarını çek
	$stmt = $pdo->prepare("SELECT * FROM havayolu WHERE id = ?");
	$stmt->execute([$gidis_hava_yolu_id]);
	$gidis_hava_yolu = $stmt->fetch(PDO::FETCH_ASSOC);
	$gidis_hava_yolu = $gidis_hava_yolu ? json_decode($gidis_hava_yolu['veri'], true) : null;

	$gelis_hava_yolu = $gidis_hava_yolu_id === $gelis_hava_yolu_id ? $gidis_hava_yolu : null;
	if (!$gelis_hava_yolu) {
		$stmt->execute([$gelis_hava_yolu_id]);
		$gelis_hava_yolu = $stmt->fetch(PDO::FETCH_ASSOC);
		$gelis_hava_yolu = $gelis_hava_yolu ? json_decode($gelis_hava_yolu['veri'], true) : null;
	}

} catch (Exception $e) {
	die("Hata: " . $e->getMessage());
}

// Meta verileri hazırla
$paket_baslik = $paket_veri[0]['data']['diller']['tr']['baslik'] ?? 'Tur Detayı';
$paket_meta_baslik = $paket_veri[0]['data']['diller']['tr']['meta_baslik'] ?? $paket_baslik;
$paket_meta_aciklama = $paket_veri[0]['data']['diller']['tr']['meta_aciklama'] ?? '';
$paket_etiketler = $paket_veri[0]['data']['diller']['tr']['etiketler'] ?? [];
$paket_link = $paket_veri[0]['data']['diller']['tr']['link'] ?? [];

// Ortak alanlardan fiyat ve tarih bilgilerini al
$ortak = $paket_veri[0]['data']['ortak_alanlar'] ?? [];
$fiyat = $ortak['ikili_oda_fiyatı'] ?? $ortak['uclu_oda_fiyatı'] ?? '';
$para_birimi = $ortak['para_birimi'] ?? '₺';
$baslangic_tarihi = $ortak['tur_baslangic_tarihi'] ?? '';
$bitis_tarihi = $ortak['tur_bitis_tarihi'] ?? '';

// Benzersiz meta açıklama oluştur (duplicate content sorununu çözmek için)
if (empty($paket_meta_aciklama)) {
	$aciklama_parcalari = [];
	$aciklama_parcalari[] = $paket_baslik;
	if (!empty($fiyat)) {
		$aciklama_parcalari[] = 'kişi başı ' . $fiyat . $para_birimi . ' fiyatla';
	}
	if (!empty($baslangic_tarihi)) {
		$aciklama_parcalari[] = $baslangic_tarihi . ' tarihinde';
	}
	$aciklama_parcalari[] = 'Yakut Turizm güvencesiyle. Detaylı bilgi ve rezervasyon için hemen inceleyin.';
	$paket_meta_aciklama = implode(' ', $aciklama_parcalari);
	$paket_meta_aciklama = substr($paket_meta_aciklama, 0, 160);
} else {
    // Mevcut açıklama varsa, tarih ekleyerek benzersizleştir
    if (!empty($baslangic_tarihi)) {
        $paket_meta_aciklama .= ' - Tur Tarihi: ' . $baslangic_tarihi;
    }
}

// Benzersiz title oluştur (ID ekleyerek duplicate önleme)
if ($paket_meta_baslik === $paket_baslik && !empty($baslangic_tarihi)) {
	// Tarih bilgisi varsa title'a ekle
	$paket_meta_baslik = $paket_baslik . ' | ' . $baslangic_tarihi;
}

$paket_etiketler_string = implode(', ', array_merge($paket_etiketler, ['tur', 'seyahat']));

$tam_url = $sirket_url . '/tur-detay/' . $paket_id . '/' . $paket_link;

PageData::set(
	$paket_meta_baslik . ' - ' . $sirket_adi,
	$paket_meta_aciklama,
	$paket_etiketler_string,
	[
		'og:title' => $paket_meta_baslik,
		'og:description' => $paket_meta_aciklama,
		'og:type' => 'website',
		'og:url' => $tam_url,
		'og:image' => $sirket_url . $paket_resim_yolu,
		'og:site_name' => $sirket_adi,
		'twitter:card' => 'summary_large_image',
		'twitter:title' => $paket_meta_baslik,
		'twitter:description' => $paket_meta_aciklama,
		'twitter:image' => $sirket_url . $paket_resim_yolu
	]
);

// Canonical URL'i ayarla - SEO duplicate content sorununu çözmek için
PageData::setMeta('canonical', $tam_url);

$vurgulama_yazi = $paket_veri[0]['data']['ortak_alanlar']['vurgulama_yazi'] ?? false;

// Schema.org TouristTrip yapılandırılmış veri
$ortak_alanlar = $paket_veri[0]['data']['ortak_alanlar'];
$para_birimi_kod = ($ortak_alanlar['para_birimi'] ?? '') === '$' ? 'USD' : (($ortak_alanlar['para_birimi'] ?? '') === '€' ? 'EUR' : 'TRY');

$schema_tur = [
	'@context' => 'https://schema.org',
	'@type' => 'TouristTrip',
	'name' => $paket_baslik,
	'description' => $paket_meta_aciklama,
	'url' => $tam_url,
	'image' => $sirket_url . $paket_resim_yolu,
	'touristType' => 'Umre Yolcusu',
	'itinerary' => [
		'@type' => 'ItemList',
		'itemListElement' => [
			[
				'@type' => 'ListItem',
				'position' => 1,
				'name' => 'Gidiş: ' . ($ortak_alanlar['tur_baslangic_tarihi'] ?? '')
			],
			[
				'@type' => 'ListItem',
				'position' => 2,
				'name' => 'Dönüş: ' . ($ortak_alanlar['tur_bitis_tarihi'] ?? '')
			]
		]
	],
	'offers' => [
		'@type' => 'AggregateOffer',
		'lowPrice' => $ortak_alanlar['dorlu_oda_fiyatı'] ?? $ortak_alanlar['uclu_oda_fiyatı'] ?? '',
		'highPrice' => $ortak_alanlar['tekli_oda_fiyatı'] ?? $ortak_alanlar['ikili_oda_fiyatı'] ?? '',
		'priceCurrency' => $para_birimi_kod,
		'availability' => ($ortak_alanlar['tukendi'] ?? false) ? 'https://schema.org/SoldOut' : 'https://schema.org/InStock',
		'validFrom' => date('Y-m-d'),
		'offerCount' => 4
	],
	'provider' => [
		'@type' => 'TravelAgency',
		'name' => $sirket_adi ?? 'Yakut Turizm',
		'url' => $sirket_url,
		'telephone' => '+90 212 524 34 35',
		'address' => [
			'@type' => 'PostalAddress',
			'streetAddress' => 'Atikali Mahallesi Fevzi Paşa Caddesi No:126/6',
			'addressLocality' => 'Fatih',
			'addressRegion' => 'İstanbul',
			'postalCode' => '34083',
			'addressCountry' => 'TR'
		]
	]
];
?>
<script type="application/ld+json">
<?php echo json_encode($schema_tur, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>
</script>

<div class="innerHeading">
	<div class="container" style="position: relative;">
		<h1><?= htmlspecialchars($paket_veri[0]['data']['diller']['tr']['baslik'] ?? 'Başlık Güncelleniyor') ?> </h1>



	</div>
</div>

<!--Inner Content Start-->
<div class="innercontent">
	<div class="container">
		<div class="row">

			<div class="col-lg-12">

				<h2 class="property_price">
					<?= htmlspecialchars($paket_veri[0]['data']['diller']['tr']['baslik'] ?? 'Başlık Güncelleniyor') ?>
					Detaylar</h2>
				<?php if ($vurgulama_yazi): ?>
					<span class="campaign-text">** <?php echo htmlspecialchars($vurgulama_yazi); ?> ** </span>

				<?php endif; ?>
				<p style="margin-bottom: 0px;"><b>Tur Kodu:</b>
					<?= htmlspecialchars($paket_veri[0]['data']['ortak_alanlar']['tur_kodu'] ?? '') ?></p>
			</div>
			<div class="col-lg-4">


				<div class="single-widgets widget_category fadeInUp wow">



					<div class="tur-resim-container" style="position: relative; display: inline-block;">
						<img class="img-thumbnail"
							alt="<?= htmlspecialchars($paket_veri[0]['data']['diller']['tr']['baslik'] ?? 'Başlık Güncelleniyor') ?>"
							src="<?= $paket_resim_yolu ?>" style="margin-bottom: 15px;">

					</div>
					<h2 class="property_price">
						<?= htmlspecialchars($paket_veri[0]['data']['diller']['tr']['baslik'] ?? 'Başlık Güncelleniyor') ?>
						Fiyat Listesi</h2>
					<ul>
						<li style="font-weight: 600;">Tekli Oda
							<span style="float: right;" class="property_price">
								<?= fiyat_goster($paket_veri[0]['data']['ortak_alanlar']['tekli_oda_fiyatı'] ?? null, $paket_veri[0]['data']['ortak_alanlar']['para_birimi'] ?? '') ?>
							</span>
						</li>
						<li style="font-weight: 600;">2'li Odalarda Kişi Başı
							<span style="float: right;" class="property_price">
								<?= fiyat_goster($paket_veri[0]['data']['ortak_alanlar']['ikili_oda_fiyatı'] ?? null, $paket_veri[0]['data']['ortak_alanlar']['para_birimi'] ?? '') ?>
							</span>
						</li>
						<li style="font-weight: 600;">3'lü Odalarda Kişi Başı
							<span style="float: right;" class="property_price">
								<?= fiyat_goster($paket_veri[0]['data']['ortak_alanlar']['uclu_oda_fiyatı'] ?? null, $paket_veri[0]['data']['ortak_alanlar']['para_birimi'] ?? '') ?>
							</span>
						</li>
						<li style="font-weight: 600;">4'lü Odalarda Kişi Başı
							<span style="float: right;" class="property_price">
								<?= fiyat_goster($paket_veri[0]['data']['ortak_alanlar']['dorlu_oda_fiyatı'] ?? null, $paket_veri[0]['data']['ortak_alanlar']['para_birimi'] ?? '') ?>
							</span>
						</li>
						<li style="font-weight: 600;">Çocuk (Yataksız)
							<span style="float: right;" class="property_price">
								<?= fiyat_goster($paket_veri[0]['data']['ortak_alanlar']['cocuk_oda_fiyatı'] ?? null, $paket_veri[0]['data']['ortak_alanlar']['para_birimi'] ?? '') ?>
							</span>
						</li>
						<li style="font-weight: 600;">Bebek (Yataksız)
							<span style="float: right;" class="property_price">
								<?= fiyat_goster($paket_veri[0]['data']['ortak_alanlar']['bebek_oda_fiyatı'] ?? null, $paket_veri[0]['data']['ortak_alanlar']['para_birimi'] ?? '') ?>
							</span>
						</li>
					</ul>
					<hr>
					<p>Lütfen Bizimle İletişime Geçmekten Çekinmeyin. Düşüncelerinizi duymak ve aklınızdaki tüm soruları
						yanıtlamak isteriz.</p>
					<?php
					$kurumsalData = getKurumsalContactData($pdo);

					if ($kurumsalData) { ?>
						<div class="readmore" style="margin-bottom: 10px;">


							<a href="https://wa.me/<?php echo formatWhatsAppNumber($kurumsalData['contact']['whatsapp']); ?>"
								target="_blank"
								style="padding: 8px 36px;background: #28a745; font-size: 17px; width: 100%; text-align: center; font-weight: 700;">
								<i class="fab fa-whatsapp"></i> Mesaj Gönderin</a>

						</div>
						<div class="readmore" style="margin-bottom: 10px;">


							<a href="tel:<?php echo $kurumsalData['contact']['sabit-telefon']; ?>" target="_blank"
								style="padding: 8px 36px;background: blue; font-size: 17px; width: 100%; text-align: center; font-weight: 700;">
								<i class="fas fa-phone-alt"></i> Bizi Arayın</a>

						</div>
					<?php } ?>
					<div class="readmore" style="margin-bottom: 10px;">

						<a href="#" data-toggle="modal" data-target="#bilgi-iste"
							style="padding: 8px 36px;font-size: 17px; width: 100%; text-align: center; font-weight: 700; background: #17a2b8;">
							<i class="fas fa-envelope" aria-hidden="true"></i> Bilgi İsteyin </a>

					</div>


					<div class="readmore" style="margin-bottom: 0px;">

						<a href="#" data-toggle="modal" data-target="#yorum-yap"
							style="padding: 8px 36px;font-size: 17px; width: 100%; text-align: center; font-weight: 700;"><i
								class="fas fa-comment" aria-hidden="true"></i> Yorum Yapın </a>

					</div>

				</div>



				<div class="single-widgets widget_category fadeInUp wow mobile-hide">
					<h4>Öne Çıkan Turlar</h4>
					<ul class="property_sec">
						<?php
						// Öne çıkan turları çekmek için PDO sorgusu
						$stmt = $pdo->query("SELECT * FROM paketler WHERE JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.one-cikar') = '1' LIMIT 5");
						$one_cikan_turlar = $stmt->fetchAll(PDO::FETCH_ASSOC);

						foreach ($one_cikan_turlar as $tur) {
							$tur_veri = json_decode($tur['veri'], true);
							$baslik = $tur_veri[0]['data']['diller']['tr']['baslik'] ?? 'Başlık Güncelleniyor';
							$link = $tur_veri[0]['data']['diller']['tr']['link'] ?? '#';
							$tur_kodu = $tur_veri[0]['data']['ortak_alanlar']['tur_kodu'] ?? '';
							$tur_id = $tur['id'];
							$detay_url = $baseurl_onyuz . "tur-detay/{$tur_id}/{$link}";

							// Resim seçme mantığı
							$kapak_resim = 'resimler/gorsel-hazirlaniyor-one-cikan.jpg'; // Varsayılan resim
							if (!empty($tur_veri[0]['data']['resimler'])) {
								$kapak_resim_bulundu = false;
								foreach ($tur_veri[0]['data']['resimler'] as $resim) {
									if (isset($resim['kapak_resim']) && $resim['kapak_resim'] === 'evet') {
										$kapak_resim = $resim['dosya_adi'];
										$kapak_resim_bulundu = true;
										break;
									}
								}
								// Eğer kapak resmi bulunamadıysa, ilk resmi kullan
								if (!$kapak_resim_bulundu && !empty($tur_veri[0]['data']['resimler'][0]['dosya_adi'])) {
									$kapak_resim = $tur_veri[0]['data']['resimler'][0]['dosya_adi'];
								}
							}


							/// Uçuş kodu işlemleri
						
							if (!function_exists('ucus_kodu_aktif_mi')) {
								function ucus_kodu_aktif_mi($tarih, $saat)
								{
									if (empty($tarih) || empty($saat)) {
										return false;
									}

									$current_time = new DateTime();
									$ucus_zamani = new DateTime($tarih . ' ' . $saat);

									$aktiflik_baslangic = clone $ucus_zamani;
									$aktiflik_baslangic->modify('-5 hours');

									$aktiflik_bitis = clone $ucus_zamani;
									$aktiflik_bitis->modify('+10 hours');

									return ($current_time >= $aktiflik_baslangic && $current_time <= $aktiflik_bitis);
								}
							}

							// Gidiş uçuşu için kontrol
							$gidis_aktif = ucus_kodu_aktif_mi(
								$paket_veri[0]['data']['ortak_alanlar']['tur_baslangic_tarihi'] ?? '',
								$paket_veri[0]['data']['ortak_alanlar']['gidis_hava_yolu_saat'] ?? ''
							);

							// Dönüş uçuşu için kontrol
							$donus_aktif = ucus_kodu_aktif_mi(
								$paket_veri[0]['data']['ortak_alanlar']['tur_bitis_tarihi'] ?? '',
								$paket_veri[0]['data']['ortak_alanlar']['gelis_hava_yolu_saat'] ?? ''
							);
							?>
							<li>
								<div class="rec_proprty">
									<div class="propertyImg"><img alt="<?= htmlspecialchars($baslik) ?>"
											src="<?php echo $baseurl_onyuz; ?><?= htmlspecialchars($kapak_resim) ?>"
											style="width: 100px;"></div>
									<div class="property_info" style="width: 55%;">
										<h4 style="line-height: 24px;"><a
												href="<?= htmlspecialchars($detay_url) ?>"><?= htmlspecialchars($baslik) ?></a>
										</h4>
										<p style="color: #ffb900;">Tur Kodu: <?= htmlspecialchars($tur_kodu) ?></p>
									</div>
								</div>
							</li>
							<?php
						}

						if (empty($one_cikan_turlar)) {
							echo '<li></li>';
						}
						?>
					</ul>
				</div>

			</div>

			<div class="col-lg-8">

				<div class="property_details">

					<div class="property_widget wow fadeInUp">

						<div class="list-group">

							<div class="list-group-item list-group-item-action flex-column align-items-start">
								<div class="d-flex w-100 justify-content-between">

									<h5 class="mb-1 tarih-baslik-text">
										<b>Gidiş Tarihi:</b>
										<?= htmlspecialchars(formatTarih($paket_veri[0]['data']['ortak_alanlar']['tur_baslangic_tarihi'] ?? '')) ?>
										Saat
										<?= htmlspecialchars($paket_veri[0]['data']['ortak_alanlar']['gidis_hava_yolu_saat'] ?? '') ?>
									</h5>

									<?php if ($gidis_aktif): ?>
										<small class="pc-hide">
											<a href="https://tr.flightaware.com/live/flight/<?= htmlspecialchars($paket_veri[0]['data']['ortak_alanlar']['gidis_hava_yolu_ucus_kodu'] ?? '') ?>"
												target="_blank">
												<i class="fa fa-plane" aria-hidden="true"></i>
												Üçuş Kodu :
												<?= htmlspecialchars($paket_veri[0]['data']['ortak_alanlar']['gidis_hava_yolu_ucus_kodu'] ?? '') ?>
											</a>
										</small>
									<?php endif; ?>
								</div>
								<p class="mb-1"><i class="fa fa-plane" aria-hidden="true"></i>
									<?= htmlspecialchars($gidis_hava_yolu[0]['data']['diller']['tr']['baslik'] ?? '') ?>
									(
									<?= htmlspecialchars($paket_veri[0]['data']['ortak_alanlar']['tur_baslangic_tarihi_aciklama'] ?? '') ?>
									)</p>
								<?php if ($gidis_aktif): ?>
									<small class="text-muted mobile-hide">
										<a href="https://tr.flightaware.com/live/flight/<?= htmlspecialchars($paket_veri[0]['data']['ortak_alanlar']['gidis_hava_yolu_ucus_kodu'] ?? '') ?>"
											target="_blank">
											<i class="fa fa-plane" aria-hidden="true"></i>
											Üçuş Kodu :
											<?= htmlspecialchars($paket_veri[0]['data']['ortak_alanlar']['gidis_hava_yolu_ucus_kodu'] ?? '') ?>
										</a>
									</small>
								<?php endif; ?>

							</div>

							<div class="list-group-item list-group-item-action flex-column align-items-start">
								<div class="d-flex w-100 justify-content-between">
									<h5 class="mb-1 tarih-baslik-text"><b>Ara Geçiş Tarihi:</b>
										<?= htmlspecialchars(formatTarih($paket_veri[0]['data']['ortak_alanlar']['tur_ara_gecis_tarihi'] ?? '')) ?>
									</h5>
									<small class="text-muted"></small>
								</div>

								<p class="mb-1"><i class="fa fa-bus" aria-hidden="true"></i> (
									<?= htmlspecialchars($paket_veri[0]['data']['ortak_alanlar']['tur_ara_gecis_tarihi_aciklama'] ?? '') ?>
									) </p>

							</div>

							<div class="list-group-item list-group-item-action flex-column align-items-start">
								<div class="d-flex w-100 justify-content-between">
									<h5 class="mb-1 tarih-baslik-text">
										<b>Dönüş Tarihi:</b>
										<?= htmlspecialchars(formatTarih($paket_veri[0]['data']['ortak_alanlar']['tur_bitis_tarihi'] ?? '')) ?>
										Saat
										<?= htmlspecialchars($paket_veri[0]['data']['ortak_alanlar']['gelis_hava_yolu_saat'] ?? '') ?>

									</h5>
									<?php if ($donus_aktif): ?>

										<small class="text-muted mobile-hide">
											<a href="https://tr.flightaware.com/live/flight/<?= htmlspecialchars($paket_veri[0]['data']['ortak_alanlar']['gelis_hava_yolu_ucus_kodu'] ?? '') ?>"
												target="_blank">
												<i class="fa fa-plane" aria-hidden="true"></i>
												Üçuş Kodu :
												<?= htmlspecialchars($paket_veri[0]['data']['ortak_alanlar']['gelis_hava_yolu_ucus_kodu'] ?? '') ?>
											</a>
										</small>
									<?php endif; ?>
								</div>
								<p class="mb-1"><i class="fa fa-plane" aria-hidden="true"></i>
									<?= htmlspecialchars($gelis_hava_yolu[0]['data']['diller']['tr']['baslik'] ?? '') ?>
									(
									<?= htmlspecialchars($paket_veri[0]['data']['ortak_alanlar']['tur_bitis_tarihi_aciklama'] ?? '') ?>
									)</p>

								<?php if ($donus_aktif): ?>
									<small class="pc-hide">
										<a href="https://tr.flightaware.com/live/flight/<?= htmlspecialchars($paket_veri[0]['data']['ortak_alanlar']['gelis_hava_yolu_ucus_kodu'] ?? '') ?>"
											target="_blank">
											Üçuş Kodu :
											<?= htmlspecialchars($paket_veri[0]['data']['ortak_alanlar']['gelis_hava_yolu_ucus_kodu'] ?? '') ?>
										</a>
									</small>
								<?php endif; ?>


							</div>

							<?php
							$baslangic_tarihi = $paket_veri[0]['data']['ortak_alanlar']['tur_baslangic_tarihi'] ?? null;
							$bitis_tarihi = $paket_veri[0]['data']['ortak_alanlar']['tur_bitis_tarihi'] ?? null;
							$manuel_gun = $paket_veri[0]['data']['ortak_alanlar']['kac_gun'] ?? null;
							$manuel_gece = $paket_veri[0]['data']['ortak_alanlar']['kac_gece'] ?? null;

							$konaklama_suresi = hesaplaKonaklamaSuresi(
								$baslangic_tarihi,
								$bitis_tarihi,
								$manuel_gun,
								$manuel_gece
							);
							?>
							<a href="#tur-bilgileri" class="scroll-trigger" style="color: inherit;">
								<div class="list-group-item list-group-item-action flex-column align-items-start">
									<div class="d-flex w-100 justify-content-between">
										<h5 class="mb-1 tarih-baslik-text">
											<b>Toplam Tur Süresi:</b> <?php echo htmlspecialchars($konaklama_suresi); ?>
											dir.
										</h5>
										<small class="text-muted"></small>
									</div>
									<?php if (!empty($bilgi_sayfalari_idler)) { ?>
										<p class="mb-1">

											<i class="fa fa-info-circle" aria-hidden="true"></i>
											<?= htmlspecialchars($paket_veri[0]['data']['diller']['tr']['baslik'] ?? '') ?>
											Bilgilerini Görmek için Tıklayınız.

										</p>
									<?php } ?>
								</div>
							</a>


						</div>
					</div>

				</div>

				<div class="property_widget wow fadeInUp">
					<h3 class="property_price">
						<?= htmlspecialchars($paket_veri[0]['data']['diller']['tr']['baslik'] ?? '') ?> Açıklaması</h3>
					<p><?= nl2br($paket_veri[0]['data']['diller']['tr']['aciklama']) ?></p>
				</div>


				<div class="property_widget wow fadeInUp">
					<h3 class="desc_head">
						<?= htmlspecialchars($paket_veri[0]['data']['diller']['tr']['baslik'] ?? '') ?> Otel Bilgileri
					</h3>
					<ul class="row">
						<!--col-lg-4 Start-->
						<li class="<?= $ikinci_otel_goster ? 'col-lg-6' : 'col-lg-12' ?>">
							<div class="property_box wow fadeInUp"
								style="visibility: visible; animation-name: fadeInUp;">
								<div class="propertyImg"><img
										src="<?php echo $baseurl_onyuz; ?><?= htmlspecialchars($otel_bir['kapak_resim']) ?>"
										alt="<?= htmlspecialchars($otel_bir['veri'][0]['data']['diller']['tr']['baslik'] ?? 'Otel Adı') ?>">
								</div>
								<h3><?= htmlspecialchars($otel_bir['veri'][0]['data']['diller']['tr']['baslik'] ?? 'Otel Adı') ?>
								</h3>
								<p style="height: 50px;"><b>Adres:</b>
									<?= htmlspecialchars($otel_bir['veri'][0]['data']['ortak_alanlar']['adres'] ?? 'Güncelleniyor') ?>
								</p>
								<p><b>Telefon:</b>
									<?= htmlspecialchars($otel_bir['veri'][0]['data']['ortak_alanlar']['telefon'] ?? 'Güncelleniyor') ?>
								</p>

								<div class="rent_info">
									<div class="apart"><i class="fas fa-map-marker-alt"></i>
										<?= htmlspecialchars($otel_bir['veri'][0]['data']['ortak_alanlar']['sehir'] ?? '') ?>
									</div>
									<a href="#" type="button" class="sale" data-toggle="modal"
										data-target="#otel_bir">Detay</a>
								</div>
							</div>
						</li>
						<!--col-lg-4 End-->

						<?php if ($ikinci_otel_goster): // İkinci otel varsa göster ?>
							<li class="col-lg-6">
								<div class="property_box wow fadeInUp"
									style="visibility: visible; animation-name: fadeInUp;">
									<div class="propertyImg"><img
											src="<?php echo $baseurl_onyuz; ?><?= htmlspecialchars($otel_iki['kapak_resim']) ?>"
											alt="<?= htmlspecialchars($otel_iki['veri'][0]['data']['diller']['tr']['baslik'] ?? 'Otel Adı') ?>">
									</div>
									<h3><?= htmlspecialchars($otel_iki['veri'][0]['data']['diller']['tr']['baslik'] ?? 'Otel Adı') ?>
									</h3>
									<p style="height: 50px;"><b>Adres:</b>
										<?= htmlspecialchars($otel_iki['veri'][0]['data']['ortak_alanlar']['adres'] ?? 'Adres bilgisi yok') ?>
									</p>
									<p><b>Telefon:</b>
										<?= htmlspecialchars($otel_iki['veri'][0]['data']['ortak_alanlar']['telefon'] ?? 'Telefon bilgisi yok') ?>
									</p>

									<div class="rent_info">
										<div class="apart"><i class="fas fa-map-marker-alt"></i>
											<?= htmlspecialchars($otel_iki['veri'][0]['data']['ortak_alanlar']['sehir'] ?? '') ?>
										</div>
										<a href="#" type="button" class="sale" data-toggle="modal"
											data-target="#otel_iki">Detay</a>
									</div>
								</div>
							</li>
						<?php endif; ?>
					</ul>
				</div>


				<div class="property_widget wow fadeInUp">
					<div class="floor_plans faqs">
						<div class="panel-group" id="accordion">

							<h3 class="desc_head" id="tur-bilgileri">
								<?= htmlspecialchars($paket_veri[0]['data']['diller']['tr']['baslik'] ?? 'Başlık Güncelleniyor') ?>
								Tur Bilgileri</h3>

							<!-- Akordion kısmı -->
							<?php
							if (!empty($bilgi_sayfalari_idler)) {
								$bilgi_sayfalari_idler_str = implode(',', $bilgi_sayfalari_idler);
								$stmt = $pdo->query("SELECT * FROM bilgi_sayfalari WHERE id IN ($bilgi_sayfalari_idler_str) AND yayin_durumu = 1 ORDER BY sira ASC");
								$bilgi_sayfalari = $stmt->fetchAll(PDO::FETCH_ASSOC);

								$ilk_akordion_id = null;
								foreach ($bilgi_sayfalari as $bilgi_sayfa) {
									$bilgi_veri = json_decode($bilgi_sayfa['veri'], true);
									// İlk akordionun ID'sini kaydet
									if ($ilk_akordion_id === null) {
										$ilk_akordion_id = "collapsed" . $bilgi_sayfa['id'];
									}
									?>
									<div class="panel panel-default">
										<div class="panel-heading">
											<h4 class="panel-title">
												<a data-toggle="collapse" data-parent="#accordion" class="collapsed"
													href="#collapsed<?php echo $bilgi_sayfa['id']; ?>">
													<?php echo htmlspecialchars($bilgi_veri[0]['data']['diller']['tr']['baslik'] ?? '') ?>
												</a>
											</h4>
										</div>
										<div id="collapsed<?php echo $bilgi_sayfa['id']; ?>" class="panel-collapse collapse">
											<div class="panel-body">
												<?php echo $bilgi_veri[0]['data']['diller']['tr']['aciklama'] ?? ''; ?>
											</div>
										</div>
									</div>
									<?php
								}
							}
							?>


						</div>
					</div>
				</div>
			</div>
		</div>




	</div>
</div>
</div>




<div id="otel_bir" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
	aria-labelledby="myLargeModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content" style="max-width: 728px;">
			<div class="modal-header" style="background: #ffb900;">
				<h5 class="modal-title" style="color: #fff; font-weight: 600;">
					<?= htmlspecialchars($otel_bir['veri'][0]['data']['diller']['tr']['baslik'] ?? 'Otel Adı') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
			<div class="modal-body">

				<ul id="image-gallery-2" class="gallery list-unstyled cS-hidden" style="height: 530px;">
					<?php
					if (!empty($otel_bir['tum_resimler']) && is_array($otel_bir['tum_resimler'])):
						foreach ($otel_bir['tum_resimler'] as $resim):
							$resim_yolu = $_SERVER['DOCUMENT_ROOT'] . $baseurl_onyuz . $resim;

							// Dosyanın var olup olmadığını ve okunabilir olduğunu kontrol et
							if (file_exists($resim_yolu) && is_readable($resim_yolu)):
								$resim_url = $baseurl_onyuz . htmlspecialchars($resim, ENT_QUOTES, 'UTF-8');
								?>
								<li data-thumb="<?php echo $resim_url; ?>">
									<img src="<?php echo $resim_url; ?>" alt="Otel Resmi" />
								</li>
								<?php
							else:
								error_log("Resim bulunamadı veya okunamadı: " . $resim_yolu);
							endif;
						endforeach;
					else:
						// Dizi boşsa veya dizi değilse buraya bir varsayılan resim
						$varsayilan_resim = $baseurl_onyuz . 'resimler/gorsel-hazirlaniyor-one-cikan.jpg';
						?>
						<li data-thumb="<?php echo $varsayilan_resim; ?>">
							<img src="<?php echo $varsayilan_resim; ?>" alt="Varsayılan Otel Resmi" />
						</li>
						<?php
					endif;
					?>
				</ul>

				<br>
				<?= $otel_bir['veri'][0]['data']['diller']['tr']['aciklama'] ?? ''; ?>

				<?php
				// Başlangıçta başlığı ve listeyi oluştur
				$html = '';
				$başlık_göster = false;

				// `otel_bir['olanaklar']` dizisinin tanımlı ve geçerli olup olmadığını kontrol edin
				if (isset($otel_bir['olanaklar']) && is_array($otel_bir['olanaklar'])) {
					$list_html = '<ul class="list-unstyled icon-checkbox">';

					// Diziyi foreach döngüsü ile işleme
					foreach ($otel_bir['olanaklar'] as $key => $value) {
						// Değer 1 ise listeye ekle
						if ($value == 1) {
							// Anahtarın başındaki 'otel_olanaklar_' kısmını çıkararak sadece olanak adını al
							$olanak_ad = str_replace('otel_olanaklar_', '', $key);
							// Anahtarın alt çizgilerini boşluk ile değiştir ve ilk harfi büyük yap
							$olanak_ad = ucfirst(str_replace('_', ' ', $olanak_ad));
							// Olanak adını liste elemanı olarak ekle
							$list_html .= '<li>' . htmlspecialchars($olanak_ad, ENT_QUOTES, 'UTF-8') . '</li>';
							$başlık_göster = true; // Listeye en az bir eleman eklenirse başlığı göster
						}
					}

					// Listeyi kapat
					$list_html .= '</ul>';

					// Liste boş değilse HTML'e ekle
					if ($başlık_göster) {
						$html .= '<br><br><h4 class="desc_head">Otelin Öne Çıkan Olanakları</h4>';
						$html .= $list_html;
					}
				}

				// HTML çıktısını yazdır
				echo $html;
				?>

				<div class="row">
					<div class="col-lg-12 col-md-12">
						<br>
						<h4 class="desc_head">İletişim Bilgileri</h3>
					</div>
					<div class="col-lg-6 col-md-6">

						<b>Adres:</b><br>
						<b><?= $otel_bir['veri'][0]['data']['diller']['tr']['baslik'] ?? ''; ?> </b>
						<p><?= htmlspecialchars($otel_bir['veri'][0]['data']['ortak_alanlar']['adres'] ?? 'Güncelleniyor') ?>
						</p>

					</div>
					<div class="col-lg-6 col-md-6">
						<b>Telefon:</b>
						<p><?= htmlspecialchars($otel_bir['veri'][0]['data']['ortak_alanlar']['telefon'] ?? 'Güncelleniyor') ?>
						</p>
						<b>Email:</b>
						<p><?= htmlspecialchars($otel_bir['veri'][0]['data']['ortak_alanlar']['email'] ?? 'Güncelleniyor') ?>
						</p>

					</div>
				</div>

				<h4 class="desc_head">Haritadaki Yeri </h3>
					<div style="border: solid 1px #ffb900; padding: 5px; border-radius: 5px;">
						<?php
						$harita_src = $otel_bir['veri'][0]['data']['ortak_alanlar']['harita_iframe'] ?? '';

						if (!empty($harita_src)) {
							echo '<iframe src="' . htmlspecialchars($harita_src) . '" width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
						} else {
							echo 'Harita bilgisi güncelleniyor.';
						}
						?>
					</div>
					<div style="margin-top: 13px;">
						<?php
						$harita_link = $otel_bir['veri'][0]['data']['ortak_alanlar']['harita_link'] ?? '';
						$yol_tarifi = $otel_bir['veri'][0]['data']['ortak_alanlar']['yol_tarifi'] ?? '';
						$sehir = $otel_bir['veri'][0]['data']['ortak_alanlar']['sehir'] ?? '';
						$otel_adi = $otel_bir['veri'][0]['data']['diller']['tr']['baslik'] ?? '';

						$disabled_class = 'btn-secondary disabled';
						$disabled_attr = 'disabled aria-disabled="true"';
						?>

						<a href="<?= !empty($harita_link) ? htmlspecialchars($harita_link) : '#' ?>" target="_blank"
							class="btn btn-sm <?= empty($harita_link) ? $disabled_class : 'btn-primary' ?>"
							<?= empty($harita_link) ? $disabled_attr : '' ?>>
							<i class="fa fa-search-plus"></i> Google Haritalarda Göster
						</a>

						<a href="<?= !empty($yol_tarifi) ? htmlspecialchars($yol_tarifi) : '#' ?>" target="_blank"
							class="btn btn-sm <?= empty($yol_tarifi) ? $disabled_class : 'btn-warning' ?>"
							<?= empty($yol_tarifi) ? $disabled_attr : '' ?>>
							<i class="fa fa-directions"></i> Yol Tarifi Al
						</a>

						<a href="<?= !empty($harita_link) ? 'https://api.whatsapp.com/send?text=' . urlencode("$sehir: $otel_adi Haritalardaki Konumu $harita_link") : '#' ?>"
							target="_blank"
							class="btn btn-sm <?= empty($harita_link) ? $disabled_class : 'btn-success' ?>"
							<?= empty($harita_link) ? $disabled_attr : '' ?>>
							<i class="fab fa-whatsapp" aria-hidden="true"></i> Haritayı Paylaş
						</a>
					</div>

			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>

			</div>
		</div>
	</div>
</div>

<div id="otel_iki" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
	aria-labelledby="myLargeModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content" style="max-width: 728px;">
			<div class="modal-header" style="background: #ffb900;">
				<h5 class="modal-title" style="color: #fff; font-weight: 600;">
					<?= htmlspecialchars($otel_iki['veri'][0]['data']['diller']['tr']['baslik'] ?? 'Otel Adı') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>
			<div class="modal-body">


				<ul id="image-gallery" class="gallery list-unstyled cS-hidden" style="height: 530px;">
					<?php
					// $otel_iki['tum_resimler'] değişkeninin bir dizi olup olmadığını kontrol et
					if (isset($otel_iki['tum_resimler']) && is_array($otel_iki['tum_resimler'])):
						foreach ($otel_iki['tum_resimler'] as $resim):
							$resim_yolu = $_SERVER['DOCUMENT_ROOT'] . $baseurl_onyuz . $resim;

							// Dosyanın var olup olmadığını ve okunabilir olduğunu kontrol et
							if (file_exists($resim_yolu) && is_readable($resim_yolu)):
								$resim_url = $baseurl_onyuz . htmlspecialchars($resim, ENT_QUOTES, 'UTF-8');
								?>
								<li data-thumb="<?php echo $resim_url; ?>">
									<img src="<?php echo $resim_url; ?>" alt="Otel Resmi" />
								</li>
								<?php
							else:
								error_log("Resim bulunamadı veya okunamadı: " . $resim_yolu);
							endif;
						endforeach;
					else:
						// Dizi yoksa varsayılan bir resim görüntüle
						$varsayilan_resim = $baseurl_onyuz . 'resimler/gorsel-hazirlaniyor-one-cikan.jpg';
						?>
						<li data-thumb="<?php echo $varsayilan_resim; ?>">
							<img src="<?php echo $varsayilan_resim; ?>" alt="Varsayılan Otel Resmi" />
						</li>
					<?php endif; ?>
				</ul>

				<br>
				<?= $otel_iki['veri'][0]['data']['diller']['tr']['aciklama'] ?? 'Otel Adı'; ?>
				<br>
				<br>
				<h4 class="desc_head">Otelin Öne Çıkan Olanakları</h4>
				<?php

				if (isset($otel_iki['olanaklar']) && is_array($otel_iki['olanaklar'])) {
					$html = '<ul class="list-unstyled icon-checkbox">';

					// Diziyi foreach döngüsü ile işleme
					foreach ($otel_iki['olanaklar'] as $key => $value) {
						// Değer 1 ise listeye ekle
						if ($value == 1) {
							// Anahtarın başındaki 'otel_olanaklar_' kısmını çıkararak sadece olanak adını al
							$olanak_ad = str_replace('otel_olanaklar_', '', $key);
							// Anahtarın alt çizgilerini boşluk ile değiştir ve ilk harfi büyük yap
							$olanak_ad = ucfirst(str_replace('_', ' ', $olanak_ad));
							// Olanak adını liste elemanı olarak ekle
							$html .= '<li>' . htmlspecialchars($olanak_ad, ENT_QUOTES, 'UTF-8') . '</li>';
						}
					}

					// Listeyi kapat
					$html .= '</ul>';

					// HTML çıktısını yazdır
					echo $html;
				} else {
					// Eğer olanaklar dizisi tanımlı değilse veya bir dizi değilse
					echo '<p>Otel olanakları bilgisi mevcut değil.</p>';
				}

				?>
				<div class="row">
					<div class="col-lg-12 col-md-12">
						<br>
						<h4 class="desc_head">İletişim Bilgileri</h4>
					</div>
					<div class="col-lg-6 col-md-6">

						<b>Adres:</b><br>
						<b><?= $otel_iki['veri'][0]['data']['diller']['tr']['baslik'] ?? ''; ?> </b>
						<p><?= htmlspecialchars($otel_iki['veri'][0]['data']['ortak_alanlar']['adres'] ?? 'Güncelleniyor') ?>
						</p>

					</div>
					<div class="col-lg-6 col-md-6">
						<b>Telefon:</b>
						<p><?= htmlspecialchars($otel_iki['veri'][0]['data']['ortak_alanlar']['telefon'] ?? 'Güncelleniyor') ?>
						</p>
						<b>Email:</b>
						<p><?= htmlspecialchars($otel_iki['veri'][0]['data']['ortak_alanlar']['email'] ?? 'Güncelleniyor') ?>
						</p>

					</div>
				</div>

				<h4 class="desc_head">Konumu</h4>
				<div style="border: solid 1px #ffb900; padding: 5px; border-radius: 5px;">
					<?php
					$iframe_kod = $otel_iki['veri'][0]['data']['ortak_alanlar']['harita_iframe'] ?? 'Güncelleniyor';

					if ($iframe_kod !== 'Güncelleniyor') {
						// width özelliğini değiştir
						$iframe_kod = preg_replace('/width="[^"]*"/', 'width="100%"', $iframe_kod);

						// height özelliğini değiştir
						$iframe_kod = preg_replace('/height="[^"]*"/', 'height="250"', $iframe_kod);
					}

					//echo $iframe_kod;
					?>

					<iframe src="<?php echo $iframe_kod; ?>" width="100%" height="250" style="border:0;"
						allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
				</div>
				<div style="margin-top: 13px;">
					<a href="<?= htmlspecialchars($otel_iki['veri'][0]['data']['ortak_alanlar']['harita_link'] ?? 'Güncelleniyor') ?>"
						target="_blank" class="btn btn-primary btn-sm"><i class="fa fa-search-plus"></i> Google
						Haritalarda Göster</a>
					<a href="<?= htmlspecialchars($otel_iki['veri'][0]['data']['ortak_alanlar']['yol_tarifi'] ?? 'Güncelleniyor') ?>"
						target="_blank" class="btn btn-warning btn-sm"><i class="fa fa-directions"></i> Yol Tarifi
						Al</a>
					<a href="https://api.whatsapp.com/send?text=<?= htmlspecialchars($otel_bir['veri'][0]['data']['ortak_alanlar']['sehir'] ?? 'Güncelleniyor') ?>: <?= $otel_iki['veri'][0]['data']['diller']['tr']['baslik'] ?? ''; ?> Haritalardaki Konumu <?= htmlspecialchars($otel_iki['veri'][0]['data']['ortak_alanlar']['harita_link'] ?? 'Güncelleniyor') ?>"
						target="_blank" class="btn btn-success btn-sm"> <i class="fab fa-whatsapp"
							aria-hidden="true"></i> Haritayı Paylaş</a>
				</div>

			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>

			</div>
		</div>
	</div>
</div>



<div id="yorum-yap" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
	aria-labelledby="myLargeModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header" style="background: #ffb900;">
				<h5 class="modal-title" style="color: #fff; font-weight: 600;">YORUM YAP</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>

			<div class="modal-body">
				<form class="form mb-md50" method="post" id="contactForm" data-form_turu="yorumForm" novalidate>
					<input type="hidden" name="form_type" value="yorumForm">
					<div class="messages"></div>
					<div class="controls">
						<div class="row">
							<div class="col-lg-12">
								<div class="form-group">
									<label for="form_name">İsim Soyisim</label>
									<input id="form_name" class="form-control" type="text" name="name"
										placeholder="İsim Soyisim" required maxlength="150">
									<div class="invalid-feedback"></div>
								</div>
							</div>

							<div class="col-lg-12">
								<div class="form-group">
									<label for="form_message">Yorumunuz</label>
									<textarea id="form_message" class="form-control" name="message"
										placeholder="Mesajınız" rows="4" required maxlength="500"></textarea>
									<div class="invalid-feedback"></div>
								</div>
							</div>
							<div class="col-lg-12">
								<div class="form-check">
									<input type="checkbox" class="form-check-input" id="kvkk-yorum_yap" name="kvkk"
										required>
									<label class="form-check-label" for="kvkk">
										<span id="kvkkLink" data-toggle="modal"
											data-target=".bd-example-modal-lg iletisim-modal" span><small>Kişisel
												verilerin işlenmesine ilişkin Aydınlatma Metnini. Okudum
												Onaylıyorum</small></a> .
									</label>

								</div>
							</div>
							<div class="col-lg-12">
								<div class="form-check">
									<input type="checkbox" class="form-check-input" id="haber-duyuru"
										name="haber-duyuru-onay" value="1">
									<label class="form-check-label" for="haber-duyuru-onay"> <small>Haber Ve
											duyurulardan haberdar olmak İstiyorum. </small></label>
									<div class="invalid-feedback"></div>
								</div>
							</div>
							<div class="col-lg-12 contact-wrap">
								<div class="contact-btn">
									<button type="submit" class="sub">Mesajı Gönder <i class="fa fa-arrow-circle-right"
											aria-hidden="true"></i></button>
								</div>
							</div>
						</div>
					</div>
				</form>

			</div>


		</div>
	</div>
</div>



<div id="bilgi-iste" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
	aria-labelledby="myLargeModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header" style="background: #ffb900;">
				<h5 class="modal-title" style="color: #fff; font-weight: 600;">BİLGİ İSTE</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
			</div>

			<div class="modal-body">
				<form class="form mb-md50" method="post" id="contactForm" data-form_turu="bilgiIsteForm" novalidate>
					<input type="hidden" name="form_type" value="bilgiIsteForm">
					<div class="messages"></div>
					<div class="controls">
						<div class="row">
							<div class="col-lg-12">
								<div class="form-group">
									<label for="form_name">İsim Soyisim</label>
									<input id="form_name" class="form-control" type="text" name="name"
										placeholder="İsim Soyisim" required maxlength="150">
									<div class="invalid-feedback"></div>
								</div>
							</div>
							<div class="col-lg-12">
								<div class="form-group">
									<label for="form_email">Email Adresiniz</label>
									<input id="form_email" class="form-control" type="email" name="email"
										placeholder="Email Adresiniz">

								</div>
							</div>
							<div class="col-lg-12">
								<div class="form-group">
									<label for="form_phone">Telefon Numaranız</label>
									<input id="form_phone" class="form-control" type="tel" name="phone"
										placeholder="Telefon Numaranız" required>
									<div class="invalid-feedback"></div>
								</div>
							</div>

							<div class="col-lg-12">
								<div class="form-group">
									<label for="form_message">Mesajınız</label>
									<textarea id="form_message" class="form-control" name="message"
										placeholder="Mesajınız" rows="4" required maxlength="500"></textarea>
									<div class="invalid-feedback"></div>
								</div>
							</div>
							<div class="col-lg-12">
								<div class="form-check">
									<input type="checkbox" class="form-check-input" id="kvkk-bilgi" name="kvkk"
										required>
									<label class="form-check-label" for="kvkk">
										<span id="kvkkLink" data-toggle="modal"
											data-target=".bd-example-modal-lg iletisim-modal" span><small>Kişisel
												verilerin işlenmesine ilişkin Aydınlatma Metnini. Okudum
												Onaylıyorum</small></a> .
									</label>

								</div>
							</div>
							<div class="col-lg-12">
								<div class="form-check">
									<input type="checkbox" class="form-check-input" id="haber-duyuru-onay"
										name="haber-duyuru-onay" value="1">
									<label class="form-check-label" for="haber-duyuru-onay"> <small>Haber Ve
											duyurulardan haberdar olmak İstiyorum. </small></label>
									<div class="invalid-feedback"></div>
								</div>
							</div>
							<div class="col-lg-12 contact-wrap">
								<div class="contact-btn">
									<button type="submit" class="sub">Mesajı Gönder <i class="fa fa-arrow-circle-right"
											aria-hidden="true"></i></button>
								</div>
							</div>
						</div>
					</div>
				</form>

			</div>


		</div>
	</div>
</div>


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
<div class="modal fade bd-example-modal-lg iletisim-modal" tabindex="-1" role="dialog" aria-labelledby="kvkkModalLabel"
	aria-hidden="true">
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


<?php if (!empty($bilgi_sayfalari_idler)) { ?>
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			// Link tıklama olayını dinle
			document.querySelector('.scroll-trigger').addEventListener('click', function (e) {
				e.preventDefault();

				// Hedef elemana yumuşak geçiş yap
				document.querySelector('#tur-bilgileri').scrollIntoView({
					behavior: 'smooth'
				});

				// Geçiş tamamlandıktan sonra akordionu aç (500ms bekle)
				setTimeout(function () {
					// İlk akordionu seç ve yavaşça aç
					var firstAccordion = document.querySelector('#<?php echo $ilk_akordion_id; ?>');
					if (firstAccordion) {
						// Bootstrap collapse metodunu kullan
						$(firstAccordion).collapse('show');

						// Ekstra görsel efekt için
						firstAccordion.style.transition = 'all 0.5s ease-in-out';
					}
				}, 800); // Scroll tamamlandıktan sonra akordionu aç
			});
		});
	</script>

<?php } ?>