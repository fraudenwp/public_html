 
 <?php $user_dil = $_SESSION['language']; ?>
 <!-- preloader start -->

<!--Topbar Start-->
<div class="topbar-wrap mobile-hide">
  <div class="container"> 
    <div class="row">
      <div class="col-lg-4 col-md-4">
        <ul class="social_media style_none">
		
				<?php
				$kurumsalData = getKurumsalContactData($pdo);
				if ($kurumsalData && isset($kurumsalData['socialMedia'])) {
					$socialMediaLinks = processSocialMediaInfo($kurumsalData['socialMedia']);
					
					if (!empty($socialMediaLinks)) {
						
						foreach ($socialMediaLinks as $social) {
							if (!empty($social['link'])) {  // Boş link kontrolü
								echo '<li style="margin-right: 5px;">';
								echo '<a href="' . htmlspecialchars($social['link']) . '" target="_blank">';
								echo '<i class="' . $social['icon'] . '" aria-hidden="true"></i>';
								echo '</a>';
								echo '</li>';
							}
						}
						
					}
				}
				?>			
		
      
        </ul>
      </div>
      <div class="col-lg-8 col-md-8">
        <div class="top_right">
<?php 		
		$kurumsalData = getKurumsalContactData($pdo);

		if ($kurumsalData) {?>		
          <div class="topbar_phone" style="margin-right: 10px;"><a href="tel:<?php echo $kurumsalData['contact']['sabit-telefon']; ?>" target="_blank"><i class="fas fa-phone-alt" aria-hidden="true"></i> <?php echo $kurumsalData['contact']['sabit-telefon']; ?> </a>  </div>
          <div class="topbar_phone" style="margin-right: 10px;"><a href="https://wa.me/<?php echo formatWhatsAppNumber($kurumsalData['contact']['whatsapp']); ?>" target="_blank"><i class="fab fa-whatsapp"></i> <?php echo $kurumsalData['contact']['whatsapp']; ?>  </a>  </div>
          <div class="topbar_phone" style="margin-right: 10px;"><a href="mailto:<?php echo $kurumsalData['contact']['mail']; ?>" target="_blank"><i class="fas fa-envelope" aria-hidden="true"></i> <?php echo $kurumsalData['contact']['mail']; ?>  </a></div>
<?php } ?> 
        </div>
      </div>
    </div>
  </div>
</div>
<!--Topbar End--> 

<!--Header Start-->
<div class="header-wrap wow fadeInUp">
  <div class="container">
    <div class="row">
      <div class="col-lg-2 navbar navbar-expand-lg navbar-light">
        <div class="header_logo"><a href="<?php echo $baseurl_onyuz; ?>"><img alt="Yakut Turizm Umre Turları" src="<?php echo $baseurl_onyuz; ?>template/assets/images/logo.png"></a></div>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"> <span class="navbar-toggler-icon"></span> </button>
      </div>
      <div class="col-lg-10">
        <nav class="navbar navbar-expand-lg navbar-light"> <a class="navbar-brand" href="#">Navbar</a>
          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <button class="close-toggler" type="button" data-toggle="offcanvas"> <span><i class="fas fa-times-circle" aria-hidden="true"></i></span> </button>
            <ul class="navbar-nav mr-auto">
              <li class="nav-item active"><a class="nav-link" href="<?php echo $baseurl_onyuz; ?>"> Anasayfa </a> </li>

			  
				<?php		
				try {
					// Verileri çek
					$stmt = $pdo->query("SELECT id, veri, yayin_durumu FROM kategori");
					
					while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
						$id = $row["id"];
						$json_data = json_decode($row["veri"], true);
						$yayin_durumu = $row["yayin_durumu"];
						
						// JSON verisinden gerekli bilgileri al
						$link = $json_data[0]['data']['diller']['tr']['link'];
						$hariciLink = $json_data[0]['data']['diller']['tr']['hariciLink'] ?? null;
						$baslik = $json_data[0]['data']['diller']['tr']['baslik'];
						$alt_menu = $json_data[0]['data']['ortak_alanlar']['ust_menu'] ?? '0';

						// Sadece alt_menu 1 ve yayin_durumu 1 ise listele
						if ($alt_menu === '1' && $yayin_durumu == 1) {
							// hariciLink varsa onu kullan, yoksa normal link'i kullan
							$hedefLink = !empty($hariciLink) ? $hariciLink : "turlar/{$link}";
							echo "<li class='nav-item'><a class='nav-link' href='{$baseurl_onyuz}{$hedefLink}' title='{$baslik}' alt='{$baslik}'>{$baslik}</a></li>";
						}
					}
					
				} catch(PDOException $e) {
					echo "Veritabanı hatası: " . $e->getMessage();
				}     
				?>				  

				<li class="nav-item ust-kategori"><a class="nav-link " href="javascript:void(0);"> Kurumsal <span class="caret"><i class="fas fa-caret-down"></i></span></a> <i class="fas fa-caret-down"></i>
					<ul class="submenu">
					<?php		
					try {
						// Verileri çek
						$stmt = $pdo->query("SELECT id, veri, yayin_durumu FROM kurumsal");
						
						while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
							$id = $row["id"];
							$json_data = json_decode($row["veri"], true);
							$yayin_durumu = $row["yayin_durumu"];
							
							// JSON verisinden gerekli bilgileri al
							$link = $json_data[0]['data']['diller']['tr']['link'];
							$hariciLink = $json_data[0]['data']['diller']['tr']['hariciLink'] ?? null;
							$baslik = $json_data[0]['data']['diller']['tr']['baslik'];
							$alt_menu = $json_data[0]['data']['ortak_alanlar']['ust_menu'] ?? '0';

							// Sadece alt_menu 1 ve yayin_durumu 1 ise listele
							if ($alt_menu === '1' && $yayin_durumu == 1) {
								// hariciLink varsa onu kullan, yoksa normal link'i kullan
								$hedefLink = !empty($hariciLink) ? $hariciLink : "kurumsal-detay/{$id}/{$link}";
								echo "<li><a href='{$baseurl_onyuz}{$hedefLink}'>{$baslik}</a></li>";
							}
						}
						
					} catch(PDOException $e) {
						echo "Veritabanı hatası: " . $e->getMessage();
					}     
					?>
					</ul>
				</li> 
			  
              <li class="nav-item"><a class="nav-link" href="<?php echo $baseurl_onyuz;?>kurumsal-detay/3/iletisim"> İletişim</a></li>
			 
            </ul>
          </div>
        </nav>
      </div>
    </div>
  </div>
</div>
<!--Header End--> 
