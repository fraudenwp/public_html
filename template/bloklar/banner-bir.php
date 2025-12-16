        <!-- banner__area-start -->
        <section class="banner__area pt-20 pb-10">
            <div class="container">
                <div class="row">
								<?php 
								
									$slide_cek = getJSONTema($pdo, 'banner-bir');
									// Sıralama ve filtreleme
									$filtered_slides = array_filter($slide_cek, function($slide) {
										return isset($slide['Yayın_Durumu']) && $slide['Yayın_Durumu'] == 1;
									});
									usort($filtered_slides, 'sortSlides');
									
									foreach ($filtered_slides as $slide) {
										
											$slide_baslik = getLocalizedContent($slide['Başlık'], $dil_cek['kod']);
											$slide_aciklama = getLocalizedContent($slide['Açıklama'], $dil_cek['kod']);
											$slide_link = getLocalizedContent($slide['Link'], $dil_cek['kod']);
									?>					
                    <div class="col-xl-4 col-lg-4 col-md-6">
                        <div class="banner__item p-relative w-img mb-30">
                            <div class="banner__img">
                                <a href="<?php echo $slide_link; ?>"><img src="<?php echo $slide['Resim']; ?>" alt="<?php echo $slide_baslik; ?>"></a>
                            </div>
                            <div class="banner__content">
                                <h6><a href="<?php echo $slide_link; ?>"><?php echo $slide_baslik; ?></a></h6>
                                <p><?php echo $slide_aciklama; ?> </p>
                            </div>
                        </div>
                    </div>
									<?php
										
									}
									?>					

                </div>
            </div>
        </section>
        <!-- banner__area-end -->