<div class="tp-banner-container">
  <div class="tp-banner">
    <ul>
									<?php 
								
										$slide_cek = getJSONTema($pdo, 'Slayt-Resimler');
										// Sıralama ve filtreleme
										$filtered_slides = array_filter($slide_cek, function($slide) {
											return isset($slide['Yayın_Durumu']) && $slide['Yayın_Durumu'] == 1;
										});
										usort($filtered_slides, 'sortSlides');
										
										foreach ($filtered_slides as $slide) {
											if ($slide['Yayın_Durumu'] == 1) {
												$slide_baslik = getLocalizedContent($slide['Başlık'], $dil_cek['kod']);
												$slide_aciklama = getLocalizedContent($slide['Açıklama'], $dil_cek['kod']);
												$slide_link = getLocalizedContent($slide['Link'], $dil_cek['kod']);
									?>
										  
									  <li data-slotamount="7" data-transition="3dcurtain-horizontal" data-masterspeed="1000" data-saveperformance="on"> <img alt="<?php echo $slide_baslik; ?>" src="images/dummy.png" data-lazyload="<?php echo $slide['Resim']; ?>">
										<div class="caption lft large-title tp-resizeme slidertext2" data-x="center" data-y="150" data-speed="600" data-start="2200"><span><?php echo $slide_baslik; ?></div>
										<div class="caption lfb large-title tp-resizeme slidertext3" data-x="center" data-y="260" data-speed="600" data-start="2800"><?php echo $slide_aciklama; ?></div>
										<div class="caption lfb large-title tp-resizeme slidertext4" data-x="center" data-y="340" data-speed="600" data-start="3500"><a href="<?php echo $slide_link; ?>">Detaylar </a></div>
									  </li>										  
										  
	  									<?php
											}
										}
									?>
    </ul>
  </div>
</div>

