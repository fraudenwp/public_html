
		  <ul class="blog_post">
							<?php 

								// Resim seçme fonksiyonu
								function selectImagee($data) {
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
														

							?>
				<?php 

					// Bilgi sayfaları bilgilerini çekmek için SQL sorgusu
					if (!empty($medya_galeri_idler)) {
						// ID'leri virgülle ayrılmış bir string haline getirin
						$medya_galeri_idler_str = implode(',', $medya_galeri_idler);
						
						// Bilgi sayfaları için SQL sorgusu hazırlayın
						$stmt = $pdo->query("SELECT * FROM medya_galeri WHERE id IN ($medya_galeri_idler_str) AND yayin_durumu = 1 ORDER BY sira ASC");
						$medya_galeri = $stmt->fetchAll(PDO::FETCH_ASSOC);

						// Bilgi sayfalarını işleme
						foreach ($medya_galeri as $bilgi_sayfa) {
							$bilgi_veri = json_decode($bilgi_sayfa['veri'], true);
							// Bilgi sayfasının başlığını ve içeriğini ekrana yazdırın


						$selectedImagee = selectImagee($bilgi_veri);	
				
				
				?>	

				
					  <li>
						<div class="property_box wow fadeInUp" style="visibility: visible; animation-name: fadeInUp; margin-top: 30px; min-height: 50px;">
						  <div class="row">
							<div class="col-lg-3 col-md-3">
							  <div class="propertyImg">
								  <a href="<?= $baseurl_onyuz . htmlspecialchars($selectedImagee) ?>" data-lightbox="image-gallery">
									<img alt="Umre Turlarımızdan Kareler" src="<?= $baseurl_onyuz . htmlspecialchars($selectedImagee) ?>" class="img-thumbnail">
								 </a>
							 </div>
							</div>
							<div class="col-lg-9 col-md-9">
							  <h3><a href="<?= $baseurl_onyuz . htmlspecialchars($selectedImagee) ?>"  data-lightbox="image-gallery"><?php  echo htmlspecialchars($bilgi_veri[0]['data']['diller']['tr']['baslik'] ?? '') ?></a></h3>
							  
							  <p><?php  echo $bilgi_veri[0]['data']['diller']['tr']['aciklama'] ?? '' ?> </p>
							 
							</div>
						  </div>
						</div>
					  </li>
  
		  
				<?php 
						}}
				?>				

        </ul>