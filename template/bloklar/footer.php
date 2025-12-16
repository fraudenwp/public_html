<!--Footer Start-->
<footer class="footer bg-style wow fadeInUp"> 
  <div class="container">
    <div class="footer-upper">
      <div class="row"> 

  

        <div class="col-lg-3 col-md-12">
          <div class="footer-widget contact">
            <h3 class="title">Bize Ulaşın</h3>  
			<ul class="footer-adress">
<?php 		
		$kurumsalData = getKurumsalContactData($pdo);

		if ($kurumsalData) {?>

			
          
              <li class="footer_address"> <i class="fas fa-map-signs" style="margin-right: 5px;"></i> <span>
			  <a href="<?php echo $kurumsalData['contact']['harita-yol-tarifi']; ?>" target="_blank">

					 <?php echo $kurumsalData['contact']['adres'] ." ". $kurumsalData['contact']['ilce'] ." / ". $kurumsalData['contact']['il']; ?>   
			 
			 </a></span> </li>
				
				<li class="footer_phone"> <i class="fas fa-phone-alt"></i> <span><a href="tel:<?php echo $kurumsalData['contact']['sabit-telefon']; ?>"> <?php echo $kurumsalData['contact']['sabit-telefon']; ?></a></span> </li>
				
				<li class="footer_phone"> <i class="fab fa-whatsapp"></i> <span><a href="https://wa.me/<?php echo formatWhatsAppNumber($kurumsalData['contact']['whatsapp']); ?>" target="_blank"> <?php echo $kurumsalData['contact']['whatsapp']; ?></a></span> </li>
				 <li class="footer_email"> <i class="fas fa-envelope" aria-hidden="true"></i> <span><a href="mailto:<?php echo $kurumsalData['contact']['mail']; ?>"> <?php echo $kurumsalData['contact']['mail']; ?> </a></span> </li>
<?php } ?>           
		   </ul>
 
          </div>
        </div>
        <div class="col-lg-3 col-md-12">
          <div class="footer-widget contact">
            <h3 class="title">Kurumsal</h3>
            <ul class="footer-adress">
				<?php		
					try {
						// Verileri çek
						$stmt = $pdo->query("SELECT id, veri, yayin_durumu FROM kurumsal");

						echo "<ul>";
						while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
							$id = $row["id"];
							$json_data = json_decode($row["veri"], true);
							$yayin_durumu = $row["yayin_durumu"];
							
							// JSON verisinden gerekli bilgileri al
							$link = $json_data[0]['data']['diller']['tr']['link'];
							$baslik = $json_data[0]['data']['diller']['tr']['baslik'];
							$alt_menu = $json_data[0]['data']['ortak_alanlar']['alt_menu'] ?? '0';
							$hariciLink = $json_data[0]['data']['diller']['tr']['hariciLink'] ?? null;

							// Sadece alt_menu 1 ve yayin_durumu 1 ise listele
							if ($alt_menu === '1' && $yayin_durumu == 1) {
								$hedefLink = !empty($hariciLink) ? $hariciLink : "kurumsal-detay/{$id}/{$link}";
								echo "<li><span><a href='" . $baseurl_onyuz . "{$hedefLink}'>{$baslik}</a></span></li>";
							}
						}
						echo "</ul>";
					} catch(PDOException $e) {
						echo "Veritabanı hatası: " . $e->getMessage();
					}     
				?>	  
            </ul>
 
          </div>
        </div>	
		
        <div class="col-lg-3 col-md-12">
          <div class="footer-widget contact">
            <h3 class="title">Bilgi Sayfaları</h3>
            <ul class="footer-adress">
				<?php		
					try {
						// Verileri çek
						$stmt = $pdo->query("SELECT id, veri, yayin_durumu FROM bilgi_sayfalari");

						echo "<ul>";
						while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
							$id = $row["id"];
							$json_data = json_decode($row["veri"], true);
							$yayin_durumu = $row["yayin_durumu"];
							
							// JSON verisinden gerekli bilgileri al
							$link = $json_data[0]['data']['diller']['tr']['link'];
							$baslik = $json_data[0]['data']['diller']['tr']['baslik'];
							$alt_menu = $json_data[0]['data']['ortak_alanlar']['alt_menu'] ?? '0';

							// Sadece alt_menu 1 ve yayin_durumu 1 ise listele
							if ($alt_menu === '1' && $yayin_durumu == 1) {
								echo "<li><span><a href='" . $baseurl_onyuz . "bilgi-sayfalari-detay/{$id}/{$link}'>{$baslik}</a></span></li>";
							}
						}
						echo "</ul>";
					} catch(PDOException $e) {
						echo "Veritabanı hatası: " . $e->getMessage();
					}     
				?>	  
            </ul>
 
          </div>
        </div>	


		
        <div class="col-lg-3 col-md-12">
          <div class="footer-widget contact">
            <h3 class="title">Sosyal Medya</h3>
 
            <div class="social-icons footer_icon">
              <ul>
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
              </ul>
            </div>
          </div>
        </div>		
	  <div class="col-lg-12 col-md-12">
		  <hr style="border-top: 1px solid rgb(255, 255, 255);">
		  <div class="row">
		  <div class="col-lg-5 col-md-5">
			<img class="img-responsive" style="padding: 10px;" src="/resimler/TURSAB-2.png" alt="tursab logo">
		 </div>
		  <div class="col-lg-3 col-md-3">
			
		 </div>
		  <div class="col-lg-4 col-md-4">
			<img class="img-responsive" style="padding: 10px;" src="/resimler/turkiye_logo.png" alt="turkiye logo">
		 </div>
		 </div>
	 </div>
      </div> 
    </div> 
  </div>
</footer>
<!--Footer End--> 
 
    <div class="wrapper-a">
        <nav class="menuShare-a">
            <input type="checkbox" href="#" class="menu-open-a" name="menu-open" id="menu-open">
            <label class="menu-open-button-a" for="menu-open">
                <i class="fab fa-whatsapp share-icon-a fa-gizle"></i>
                <i class="fas fa-times share-icon-a"></i>  
            </label>
            <a class="menu-item-a facebook_share_btn-a" target="_blank" href="tel:+905558025656" alt="Telefon">
                <i class="fas fa-phone-alt share-icon-a"></i>
				
            </a>
            <a class="menu-item-a google_plus_share_btn-a" target="_blank" href="https://wa.me/905558025656" alt="Whatsapp Mesaj Gönder">
                <i class="fab fa-whatsapp share-icon-a"></i>
            </a>
        </nav>
    </div>
 

<!-- Optional JavaScript --> 
<!-- jQuery first, then Popper.js, then Bootstrap JS --> 
<script src="<?php echo $baseurl_onyuz; ?>template/assets/js/jquery.min.js"></script> 
<script src="<?php echo $baseurl_onyuz; ?>template/assets/js/popper.min.js"></script> 
<script src="<?php echo $baseurl_onyuz; ?>template/assets/js/bootstrap.min.js"></script> 
<script type="text/javascript" src="<?php echo $baseurl_onyuz; ?>template/assets/rs-plugin/js/jquery.themepunch.tools.min.js"></script> 
<script type="text/javascript" src="<?php echo $baseurl_onyuz; ?>template/assets/rs-plugin/js/jquery.themepunch.revolution.min.js"></script> 
<!-- Owl Carousel --> 
<script src="<?php echo $baseurl_onyuz; ?>template/assets/js/owl.carousel.js"></script> 
<!-- wow js --> 
<script src="<?php echo $baseurl_onyuz; ?>template/assets/js/animate.js"></script> 
<script src="<?php echo $baseurl_onyuz; ?>template/assets/js/jquery.flexslider.js"></script> 
<script src="<?php echo $baseurl_onyuz; ?>template/assets/js/jquery.nice-select.js"></script> 
<script src="<?php echo $baseurl_onyuz; ?>template/assets/js/lightslider.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox.min.js"></script>


<?php

			$url = $_SERVER['REQUEST_URI'];
			$slugurl = explode('/', trim($url, '/'));
			$slugurl = $slugurl[0];
 if ($slugurl == 'tur-detay') {  ?>
<script>
$(document).ready(function() {
    var slider;

    $('#otel_iki').on('shown.bs.modal', function (e) {
        console.log("Modal açıldı, slider başlatılıyor");
        slider = $('#image-gallery').lightSlider({
            gallery: true,
            item: 1,
            thumbItem: 9,
            slideMargin: 0,
            speed: 500,
            auto: true,  // Otomatik geçişi etkinleştir
            pause: 3000, // Her 3 saniyede bir geçiş yap
            loop: true,
            adaptiveHeight: true,
            onSliderLoad: function(el) {
                console.log("Slider yüklendi");
                $('#image-gallery').removeClass('cS-hidden');
                el.lightGallery({
                    selector: '#image-gallery .lslide'
                });
            }
        });
        console.log("Slider başlatıldı");
    });

    $('#otel_bir').on('hidden.bs.modal', function (e) {
        console.log("Modal kapandı");
        if (slider) {
            console.log("Slider yok ediliyor");
            slider.destroy();
            console.log("Slider yok edildi");
        }
    });
	
    $('#otel_bir').on('shown.bs.modal', function (e) {
        console.log("Modal açıldı, slider başlatılıyor");
        slider = $('#image-gallery-2').lightSlider({
            gallery: true,
            item: 1,
            thumbItem: 9,
            slideMargin: 0,
            speed: 500,
            auto: true,  // Otomatik geçişi etkinleştir
            pause: 3000, // Her 3 saniyede bir geçiş yap
            loop: true,
            adaptiveHeight: true,
            onSliderLoad: function(el) {
                console.log("Slider yüklendi");
                $('#image-gallery-2').removeClass('cS-hidden');
                el.lightGallery({
                    selector: '#image-gallery-2 .lslide'
                });
            }
        });
        console.log("Slider başlatıldı");
    });

    $('#otel_iki').on('hidden.bs.modal', function (e) {
        console.log("Modal kapandı");
        if (slider) {
            console.log("Slider yok ediliyor");
            slider.destroy();
            console.log("Slider yok edildi");
        }
    });	
	
});



</script>
<?php } ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var turSuresiSelect = document.getElementById('turSuresi');
    var turDonemiSelect = document.getElementById('turDonemi');
    var otelSelect = document.getElementById('otel');

    // Tur sürelerini doldur
    Object.entries(turData.turSureleri).forEach(([value, text]) => {
        turSuresiSelect.add(new Option(text, value));
    });

    // Nice Select'i başlat
    $(turSuresiSelect).niceSelect();
    $(turDonemiSelect).niceSelect();
    $(otelSelect).niceSelect();

    $(turSuresiSelect).on('change', function() {
        var selectedValue = $(this).val();
        updateTurDonemi(selectedValue);
    });

    $(turDonemiSelect).on('change', function() {
        var selectedTurSuresi = $(turSuresiSelect).val();
        var selectedTurDonemi = $(this).val();
        updateOtel(selectedTurSuresi, selectedTurDonemi);
    });

    function updateTurDonemi(selectedTurSuresi) {
        turDonemiSelect.innerHTML = '<option value="">Tur Dönemi Seçin</option>';
        otelSelect.innerHTML = '<option value="">Otel Seçin</option>';
        
        if (selectedTurSuresi) {
            turDonemiSelect.disabled = false;
            var uygunDonemler = new Set();

            turData.paketler.forEach(paket => {
                var veri = JSON.parse(paket.veri)[0].data;
                var baslangic = new Date(veri.ortak_alanlar.tur_baslangic_tarihi);
                var bitis = new Date(veri.ortak_alanlar.tur_bitis_tarihi);
                var sureFark = Math.round((bitis - baslangic) / (1000 * 60 * 60 * 24)) + 1;

                if (sureFark == selectedTurSuresi) {
                    veri.ortak_alanlar.donem.forEach(donemId => uygunDonemler.add(donemId));
                }
            });

            uygunDonemler.forEach(donemId => {
                if (turData.turDonemleri[donemId]) {
                    turDonemiSelect.add(new Option(turData.turDonemleri[donemId], donemId));
                }
            });
        } else {
            turDonemiSelect.disabled = true;
            otelSelect.disabled = true;
        }

        $(turDonemiSelect).niceSelect('update');
        $(otelSelect).niceSelect('update');
    }

    function updateOtel(selectedTurSuresi, selectedTurDonemi) {
        otelSelect.innerHTML = '<option value="">Otel Seçin</option>';
        
        if (selectedTurSuresi && selectedTurDonemi) {
            otelSelect.disabled = false;
            var uygunOteller = new Set();

            turData.paketler.forEach(paket => {
                var veri = JSON.parse(paket.veri)[0].data;
                var baslangic = new Date(veri.ortak_alanlar.tur_baslangic_tarihi);
                var bitis = new Date(veri.ortak_alanlar.tur_bitis_tarihi);
                var sureFark = Math.round((bitis - baslangic) / (1000 * 60 * 60 * 24)) + 1;

                if (sureFark == selectedTurSuresi && veri.ortak_alanlar.donem.includes(selectedTurDonemi)) {
                    uygunOteller.add(veri.ortak_alanlar.otel_bir);
                    uygunOteller.add(veri.ortak_alanlar.otel_iki);
                }
            });

            uygunOteller.forEach(otelId => {
                if (turData.oteller[otelId]) {
                    otelSelect.add(new Option(turData.oteller[otelId], otelId));
                }
            });
        } else {
            otelSelect.disabled = true;
        }

        $(otelSelect).niceSelect('update');
    }
});

// Form gönderimi
$('#turForm').on('submit', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();

    // Form verilerini URL parametrelerine dönüştür
    var searchParams = new URLSearchParams(formData);
    
    // turlar.php sayfasına yönlendir
    window.location.href = 'turlar?' + searchParams.toString();
});


$(document).ready(function() {
    console.log("Document ready");
    try {
        var $forms = $('form#contactForm');
        var lastSubmissionTime = 0;
        var formURL = window.location.href;
        var currentKvkkCheckbox = null;
        var $kvkkModal = $('.iletisim-modal');
        var $kvkkAcceptButton = $('#kvkkAccept');

        function isValidEmail(email) {
            var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(String(email).toLowerCase());
        }

        function isValidPhone(phone) {
            var re = /^[+]?[(]?[0-9]{3}[)]?[-\s.]?[0-9]{3}[-\s.]?[0-9]{4,6}$/;
            return re.test(String(phone));
        }

        function sanitizeInput(input) {
            return $('<div>').text(input).html();
        }

        function showFieldError($field, message) {
            $field.addClass('is-invalid');
            $field.siblings('.invalid-feedback').text(message);
        }

        function clearFieldError($field) {
            $field.removeClass('is-invalid');
            $field.siblings('.invalid-feedback').text('');
        }

        function validateForm($form) {
            var isValid = true;
            $form.find('[required]').each(function() {
                var $field = $(this);
                var value = $field.val().trim();
                if (value === '' || ($field.attr('type') === 'checkbox' && !$field.prop('checked'))) {
                    isValid = false;
                    showFieldError($field, 'Bu alan zorunludur.');
                } else if ($field.attr('type') === 'email' && !isValidEmail(value)) {
                    isValid = false;
                    showFieldError($field, 'Geçerli bir e-posta adresi giriniz.');
                } else if ($field.attr('name') === 'phone' && !isValidPhone(value)) {
                    isValid = false;
                    showFieldError($field, 'Geçerli bir telefon numarası giriniz.');
                } else {
                    clearFieldError($field);
                }
            });
            return isValid;
        }

        $forms.each(function() {
            var $form = $(this);
            var formType = $form.data('form_turu');
            var $messageContainer = $form.find('.messages');
            var $kvkkCheckbox;
            
            // Form türüne göre doğru KVKK checkbox'ını seç
            if (formType === 'yorumForm') {
                $kvkkCheckbox = $form.find('#kvkk-yorum_yap');
            } else if (formType === 'bilgiIsteForm') {
                $kvkkCheckbox = $form.find('#kvkk-bilgi');            
            } else if (formType === 'iletisim') {
                $kvkkCheckbox = $form.find('#kvkk-iletisim');
            }

            $form.on('submit', function(e) {
                e.preventDefault();
                var currentTime = new Date().getTime();
                if (currentTime - lastSubmissionTime < 60000) {
                    $messageContainer.html('<div class="alert alert-warning">Lütfen bir dakika bekleyin ve tekrar deneyin.</div>');
                    return;
                }
     
                var isValid = validateForm($form);
                if (isValid) {
                    var formData = {
                        form_type: formType,
                        submission_url: formURL
                    };

                    $form.find('input, textarea').each(function() {
                        var $field = $(this);
                        if ($field.attr('type') === 'checkbox') {
                            formData[$field.attr('name')] = $field.is(':checked') ? '1' : '0';
                        } else {
                            formData[$field.attr('name')] = sanitizeInput($field.val().trim());
                        }
                    });

                    console.log("Gönderilen form verileri:", formData);  // Debugging için

                    $.ajax({
                        type: 'POST',
                        url: '/islemler.php',
                        data: formData,
                        dataType: 'json',
                        success: function(response) {
                            console.log('Sunucu yanıtı:', response);
                            if (response && response.success) {
                                $messageContainer.html('<div class="alert alert-success">' + response.message + '</div>');
                                $form[0].reset();
                                $kvkkCheckbox.prop('checked', false);
                                lastSubmissionTime = currentTime;
                                setTimeout(function() {
                                    $messageContainer.empty();
                                }, 60000);
                            } else {
                                $messageContainer.html('<div class="alert alert-danger">' + (response && response.message ? response.message : 'Bir hata oluştu.') + '</div>');
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.error('AJAX hatası:', textStatus, errorThrown);
                            console.log('Ham hata yanıtı:', jqXHR.responseText);
                            try {
                                var errorResponse = JSON.parse(jqXHR.responseText);
                                $messageContainer.html('<div class="alert alert-danger">' + (errorResponse.message || 'Bir hata oluştu.') + '</div>');
                            } catch (e) {
                                $messageContainer.html('<div class="alert alert-danger">Mesaj gönderilirken bir hata oluştu. Lütfen daha sonra tekrar deneyin.</div>');
                            }
                        }
                    });
                } else {
                    $messageContainer.html('<div class="alert alert-warning">Lütfen formdaki hataları düzeltin.</div>');
                }

                $('html, body').animate({
                    scrollTop: $messageContainer.offset().top - 100
                }, 200);
            });

            $form.find('input, textarea').on('input', function() { 
                clearFieldError($(this));
            });

            // KVKK checkbox'ına tıklandığında
            $kvkkCheckbox.on('click', function(e) {
                if (!$(this).prop('checked')) {
                    // Checkbox'ın seçimi kaldırılıyorsa, modal'ı açma
                    return;
                }
                // Checkbox seçiliyorsa, modal'ı aç
                e.preventDefault();
                currentKvkkCheckbox = $kvkkCheckbox;
                $kvkkModal.modal('show');
            });

            // KVKK metnine tıklandığında modal'ı aç
            $form.find('#kvkkLink').on('click', function(e) {
                e.preventDefault();
                currentKvkkCheckbox = $kvkkCheckbox;
                $kvkkModal.modal('show');
            });
        });

        // "Kabul Ediyorum" butonuna tıklandığında
        $kvkkAcceptButton.on('click', function() {
            if (currentKvkkCheckbox) {
                currentKvkkCheckbox.prop('checked', true);
                clearFieldError(currentKvkkCheckbox);
            }
            $kvkkModal.modal('hide');
        });

        // Modal kapandığında
        $kvkkModal.on('hidden.bs.modal', function () {
            if (currentKvkkCheckbox && !currentKvkkCheckbox.prop('checked')) {
                currentKvkkCheckbox.prop('checked', false);
            }
            currentKvkkCheckbox = null;
        });

    } catch (error) {
        console.error("Bir hata oluştu:", error);
        console.error("Hata stack trace:", error.stack);
    }
});

</script>
<script> 
document.addEventListener('DOMContentLoaded', function () {
  const links = document.querySelectorAll('a[data-resimid]');
  
  links.forEach(link => {
    link.addEventListener('click', function (event) {
      event.preventDefault();
      
      const resimId = this.getAttribute('data-resimid');
      
      // Ajax isteği ile resimleri çek
      fetch('template/sayfalar/medya-detay.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: resimId })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const modalImages = document.getElementById('modalImages');
          modalImages.innerHTML = ''; // Eski içeriği temizle
          
          const firstImageSrc = data.images.length > 0 ? data.images[0].dosya_adi : ''; // İlk resmin kaynağını al
          const largeImageContainer = document.getElementById('largeImageContainer');
          const largeImage = document.getElementById('largeImage');
          
          if (firstImageSrc) {
            // Modal açıldığında ilk resmi büyük resim olarak göster
            largeImage.src = firstImageSrc;
            largeImageContainer.style.display = 'block'; // Büyük resim alanını göster
            largeImageContainer.classList.add('img-thumbnail'); // Büyük resim alanını göster
          }

          data.images.forEach(image => {
            const imgElement = document.createElement('img');
            imgElement.src = image.dosya_adi;
            imgElement.alt = image.alt_etiketi || 'Resim';
            imgElement.classList.add('img-thumbnail', 'm-1');
            imgElement.style.maxWidth = '100px';
            
            // Resme tıklama olayı ekle
            imgElement.addEventListener('click', function () {
              largeImage.src = image.dosya_adi;
              largeImageContainer.style.display = 'block'; // Büyük resim alanını göster
            });
            
            modalImages.appendChild(imgElement);
          });
          
          $('#imageModal').modal('show'); // Modal'ı göster
        } else {
          alert('Resimler yüklenirken bir hata oluştu.');
        }
      })
      .catch(error => {
        console.error('Hata:', error);
      });
    });
  });
});


<?php if ($slugurl == 'medya') {  ?>

class CustomGallery {
    constructor() {
        this.currentIndex = 0;
        this.images = [];
        this.modal = null;
        this.initModal();
        this.bindEvents();
    }

    initModal() {
        // Modal HTML yapısını oluştur
        const modalHTML = `
            <div class="custom-gallery-modal">
                <div class="gallery-content">
				<div>
                    <button class="close-button">&times;</button>
					</div>
                    <button class="nav-button prev-button">&lt;</button>
                    <button class="nav-button next-button">&gt;</button>
					
                    <div class="main-image-container">
                        <img src="" alt="">
						 <div class="image-description"></div>
                    </div>
                   
                    <div class="thumbnails-container"></div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.modal = document.querySelector('.custom-gallery-modal');
    }

    bindEvents() {
        // Galeri açma olayı
        document.querySelectorAll('.custom-lightbox').forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                const galleryId = trigger.dataset.gallery;
                this.openGallery(galleryId);
            });
        });
        
        this.modal.querySelector('.prev-button').addEventListener('click', () => this.navigate(-1));
        this.modal.querySelector('.next-button').addEventListener('click', () => this.navigate(1));
        this.modal.querySelector('.close-button').addEventListener('click', () => this.closeGallery());
        
        // Boşluğa tıklama için yeni event listener
        this.modal.addEventListener('click', (e) => {
            // Eğer tıklanan element modal'ın kendisi ise (yani içeriği değil)
            if (e.target === this.modal) {
                this.closeGallery();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (this.modal.style.display === 'block') {
                if (e.key === 'ArrowLeft') this.navigate(-1);
                if (e.key === 'ArrowRight') this.navigate(1);
                if (e.key === 'Escape') this.closeGallery();
            }
        });
    }
    

    openGallery(galleryId) {
        const gallery = document.querySelector(`[data-gallery="${galleryId}"]`).closest('.property_box');
        this.images = Array.from(gallery.querySelector('.hidden-gallery').children);
        this.currentIndex = 0;
        this.updateGallery();
        this.modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    closeGallery() {
        this.modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    navigate(direction) {
        this.currentIndex = (this.currentIndex + direction + this.images.length) % this.images.length;
        this.updateGallery();
    }

    updateGallery() {
        const mainImage = this.modal.querySelector('.main-image-container img');
        const description = this.modal.querySelector('.image-description');
        const thumbnailsContainer = this.modal.querySelector('.thumbnails-container');

        // Ana resmi güncelle
        mainImage.src = this.images[this.currentIndex].src;
        description.textContent = this.images[this.currentIndex].alt;

        // Thumbnail'ları güncelle
        thumbnailsContainer.innerHTML = this.images.map((img, index) => `
            <img src="${img.src}" 
                 class="thumbnail ${index === this.currentIndex ? 'active' : ''}"
                 onclick="customGallery.setImage(${index})">
        `).join('');
    }

    setImage(index) {
        this.currentIndex = index;
        this.updateGallery();
    }
}

// Galeriyi başlat
const customGallery = new CustomGallery();

<?php } ?>
</script>

<script>
  new WOW().init();
</script> 
<!-- general script file --> 
<script src="<?php echo $baseurl_onyuz; ?>template/assets/js/wow.js"></script> 
<script src="<?php echo $baseurl_onyuz; ?>template/assets/js/script.js"></script>
<?php echo $slug; ?>
</body>
</html>