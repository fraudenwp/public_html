<section class="topsell__area-2 pt-15">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="section__head d-flex justify-content-between mb-10">
                    <div class="section__title">
                        <h5 class="st-titile">Öne Çıkan Kategoriler</h5>
                    </div>
                    <div class="product__nav-tab"> 
                        <ul class="nav nav-tabs" id="flast-sell-tab" role="tablist">
							<?php 
							$slide_cek = getJSONTema($pdo, 'kategori-en-cok');
							$filtered_slides = array_filter($slide_cek, function($slide) {
								return isset($slide['Yayın_Durumu']) && $slide['Yayın_Durumu'] == 1;
							});
							usort($filtered_slides, 'sortSlides');

							$first = true;
							foreach ($filtered_slides as $slide) {
								// İlk olarak seçilen dilde kategoriyi arayalım
								$query = $pdo->prepare("
									SELECT baslik, dil
									FROM kategoriler
									WHERE grup = :grup
									ORDER BY CASE WHEN dil = :dil THEN 0 ELSE 1 END, dil
									LIMIT 1
								");
								$query->execute(['grup' => $slide['Kategori'], 'dil' => $_SESSION['language']]);
								$kategori = $query->fetch(PDO::FETCH_ASSOC);
								
								if ($kategori) {
									$active = $first ? 'active' : '';
									$first = false;
									?>
									<li class="nav-item" role="presentation">
									   <button class="nav-link <?php echo $active; ?>" 
											   id="tab-<?php echo htmlspecialchars($slide['id']); ?>" 
											   data-bs-toggle="tab" 
											   data-bs-target="#content-<?php echo htmlspecialchars($slide['id']); ?>" 
											   type="button" 
											   role="tab" 
											   aria-controls="content-<?php echo htmlspecialchars($slide['id']); ?>" 
											   aria-selected="<?php echo $active ? 'true' : 'false'; ?>">
										   <?php 
										   echo htmlspecialchars($kategori['baslik']);
										   if ($kategori['dil'] !== $_SESSION['language']) {
											   echo ' (' . strtoupper($kategori['dil']) . ')';
										   }
										   ?>
									   </button>
									</li>
									<?php
								}
							}
							?>                                 
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-12">
                <div class="tab-content" id="flast-sell-tabContent">
<?php 
$slide_cek = getJSONTema($pdo, 'kategori-en-cok');
$filtered_slides = array_filter($slide_cek, function($slide) {
    return isset($slide['Yayın_Durumu']) && $slide['Yayın_Durumu'] == 1;
});
usort($filtered_slides, 'sortSlides');

$first = true;
foreach ($filtered_slides as $slide) {
    $query = $pdo->prepare("
        SELECT baslik, dil
        FROM kategoriler
        WHERE grup = :grup
        ORDER BY CASE WHEN dil = :dil THEN 0 ELSE 1 END, dil
        LIMIT 1
    ");
    $query->execute(['grup' => $slide['Kategori'], 'dil' => $_SESSION['language']]);
    $kategori = $query->fetch(PDO::FETCH_ASSOC);
    
    if ($kategori) {
        $active = $first ? 'show active' : '';
        $first = false;
        ?>							
        <div class="tab-pane fade <?php echo $active; ?>" id="content-<?php echo htmlspecialchars($slide['id']); ?>" role="tabpanel" aria-labelledby="tab-<?php echo htmlspecialchars($slide['id']); ?>">
            <div class="product-bs-slider-2">
                <div class="product-slider-2 swiper-container">
                    <div class="swiper-wrapper">
                    <?php 
                    // İlk olarak belirtilen dilde ürünleri al
                    $query = $pdo->prepare("
                        SELECT id, yayin_durumu, baslik, grup
                        FROM urunler 
                        WHERE kat_id = :kategori AND dil = :dil AND yayin_durumu = 1
                    ");
                    $query->execute(['kategori' => $slide['Kategori'], 'dil' => $_SESSION['language']]);
                    $urunler = $query->fetchAll(PDO::FETCH_ASSOC);

                    $alternatif_dil_kullanildi = false;

                    // Eğer belirtilen dilde ürün yoksa, diğer dillerdeki ürünleri al
                    if (empty($urunler)) {
                        $query = $pdo->prepare("
                            SELECT id, yayin_durumu, baslik, grup, dil
                            FROM urunler 
                            WHERE kat_id = :kategori AND yayin_durumu = 1
                            GROUP BY grup  -- Her gruptan sadece bir ürün al
                        ");
                        $query->execute(['kategori' => $slide['Kategori']]);
                        $urunler = $query->fetchAll(PDO::FETCH_ASSOC);
                        $alternatif_dil_kullanildi = true;
                    }

                    foreach ($urunler as $urun) {
                        // Her durumda grup sütununa göre resim çek
                        $query = $pdo->prepare("
                            SELECT ur.resim 
                            FROM urun_resim ur
                            JOIN urunler u ON ur.sayfa_id = u.grup
                            WHERE u.grup = :grup
                            LIMIT 1
                        ");
                        $query->execute(['grup' => $urun['grup']]);
                        $urun_resim = $query->fetch(PDO::FETCH_ASSOC);

                        $resim_url = $urun_resim ? htmlspecialchars($urun_resim['resim']) : '/path/to/default/image.jpg';
                        ?>
                        <div class="product__item swiper-slide">
                            <div class="product__thumb fix">
                                <div class="product-image w-img">
                                    <a href="product-details.html">
                                        <img src="<?php echo $resim_url; ?>" alt="<?php echo htmlspecialchars($urun['baslik']); ?>">
                                    </a>
                                </div>
                                <div class="product__offer">
                                    <span class="discount">-15%</span>
                                </div>
                                <div class="product-action">
                                    <a href="#" class="icon-box icon-box-1" data-bs-toggle="modal" data-bs-target="#productModalId">
                                        <i class="fal fa-eye"></i>
                                        <i class="fal fa-eye"></i>
                                    </a>
                                    <a href="#" class="icon-box icon-box-1">
                                        <i class="fal fa-heart"></i>
                                        <i class="fal fa-heart"></i>
                                    </a>
                                    <a href="#" class="icon-box icon-box-1">
                                        <i class="fal fa-layer-group"></i>
                                        <i class="fal fa-layer-group"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="product__content">
                                <h6><a href="product-details.html"><?php echo htmlspecialchars($urun['baslik']); ?></a></h6>
                                <?php if ($alternatif_dil_kullanildi && isset($urun['dil']) && $urun['dil'] != $_SESSION['language']) : ?>
                                    <small>(<?php echo htmlspecialchars($urun['dil']); ?>)</small>
                                <?php endif; ?>
                                <div class="rating mb-5">
                                    <span>Açıklama Gelecek</span>
                                </div>
                                <div class="price">
                                    <span>Fiyat Bilgisi Gelecek</span>
                                </div>
                            </div>
                            <div class="product__add-cart text-center">
                                <button type="button" class="cart-btn product-modal-sidebar-open-btn d-flex align-items-center justify-content-center w-100">
                                İncele
                                </button>
                            </div>
                        </div>
                        <?php
                    }
                    ?>	
                    </div>
                </div>
                <!-- If we need navigation buttons -->
                <div class="bs-button bs2-button-prev"><i class="fal fa-chevron-left"></i></div>
                <div class="bs-button bs2-button-next"><i class="fal fa-chevron-right"></i></div>
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
</section>