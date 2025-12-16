  <?php $query = $pdo->query("SELECT id, kod, baslik, varsayilan, COALESCE(NULLIF(resim, ''), 'resimler/resim-yok.jpg') AS resim FROM diller"); $diller = $query->fetchAll(PDO::FETCH_ASSOC); 
  
  $query = $pdo->query("SELECT COUNT(*) FROM diller WHERE yayin_durumu = 1");
$dilSayisi = $query->fetchColumn();
  ?>

<!-- tema-ayarlari.php -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><?php echo $sayfa['baslik']; ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <a href="<?php echo $baseurl; ?>">Gösterge Paneli</a>
                        </li>
                        <li class="breadcrumb-item active"><?php echo $sayfa['baslik']; ?></li>
                       
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <section class="content"> 
                <div class="row">
                    <div class="col-md-3">
						<!------------------------------>
                        <div class="card collapsed-card">
                            <div class="card-header" data-card-widget="collapse">
                                <h3 class="card-title">Site Ayarları</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0" style="display: none;">
                                <ul class="nav nav-pills flex-column">
                                    <li class="nav-item">
                                        <a href="#" class="nav-link tema-menu-link" data-veri_cek_dizin_yolu="template/sayfalar/tema-ayarlari/genel-ayarlar/logolar.php" data-veri_cek_tablo_adi="tema" data-veri_cek_sutun_adi="tur"  data-veri_cek_satir_adi="logolar" data-veri_cek_icerik_cek="Resim, Başlık">Logo Ayarları</a>
                                        <a href="#" class="nav-link  " data-toggle="modal" data-target="#SayfaAyarModal">Anasayfa Meta ayarları</a>
										<a href="#" class="nav-link tema-menu-link" data-veri_cek_dizin_yolu="template/sayfalar/tema-ayarlari/genel-ayarlar/renk-ayarlari.php" data-veri_cek_tablo_adi="tema" data-veri_cek_sutun_adi="tur"  data-veri_cek_satir_adi="renkayar" data-veri_cek_icerik_cek="Başlık">Renk Ayarları</a>
                                    </li>								
                                </ul>
                            </div>
                        </div>

						<!------------------------------>
                        <div class="card collapsed-card">
                            <div class="card-header" data-card-widget="collapse">
                                <h3 class="card-title">Tema İçerik Ayarları</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0" style="display: none;">
                                <ul class="nav nav-pills flex-column">
                                    <li class="nav-item">
                                        <a href="#" class="nav-link tema-menu-link" data-veri_cek_dizin_yolu="template/sayfalar/tema-ayarlari/slide-alani/slide.php" data-veri_cek_tablo_adi="tema" data-veri_cek_sutun_adi="tur"  data-veri_cek_satir_adi="Slayt-Resimler" data-veri_cek_icerik_cek="Resim, Başlık, Link, Sıra_No, Yayın_Durumu">Slayt Resimler</a>
                                    </li>                                   
                                </ul>
                            </div>						
                        </div>	                     					
                    </div>
                    <div class="col-md-9" id="tema-genelAyarlar">  </div>
                </div>
            </section>
        </div>
    </div>
    <!-- /.content -->
</div>


<!-- Sayfa Genel Ayarlar Modal-->
<div class="modal fade" id="SayfaAyarModal" 
     data-sayfa_ayar_tablo_adi="sayfalar"
     data-sayfa_ayar_tablo_satir_id="1"
     tabindex="-1" role="dialog" aria-labelledby="SayfaAyarModalLabel" aria-hidden="true">
    <!-- Modal içeriği -->
   <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="SayfaAyarModalLabel">Ana Sayfa Meta Ayarları</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	    		<form id="SayfaAyarlarForm" enctype="multipart/form-data">
      <div class="modal-body">
	  

    
				<div class="card-body">
				<div class="tab-content" id="custom-tabs-four-tabContent">
<?php
// Sorguyu hazırla
include '../config.php';
$sql = "SELECT veri, yayin_durumu FROM sayfalar WHERE id = 1";
$stmt = $pdo->prepare($sql);
$stmt->execute();

// Veriyi çek
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    $veri = json_decode($row['veri'], true);
    $yayin_durumu = $row['yayin_durumu'];

    // Veri yapısını kontrol et ve düzelt
    if (is_array($veri) && !empty($veri)) {
        if (!isset($veri[0]['data'])) {
            // Eğer 'data' anahtarı yoksa, mevcut yapıyı 'data' anahtarı altına taşı
            $veri = [['data' => $veri[0]]];
        }
        $sayfa_data = $veri[0]['data'];
        
        // Türkçe verileri al
        $tr_data = $sayfa_data['diller']['tr'] ?? [];
        
        // Ortak alanları al
        $ortak_alanlar = $sayfa_data['ortak_alanlar'] ?? [];

        // Eksik alanları varsayılan değerlerle doldur
        $tr_data = array_merge([
            'baslik' => '',
            'aciklama' => '',
            'meta_baslik' => '',
            'meta_aciklama' => '',
            'link' => '',
            'etiketler' => []
        ], $tr_data);

        // Form içeriğini oluştur
        ?>
					<div class="tab-pane fade show active" id="genel-ekle" role="tabpanel" aria-labelledby="genel-ekle-tab">

						<ul class="nav nav-tabs" id="custom-content-below-tab" role="tablist">
						
						<?php if ($dilSayisi >= 2) {

							foreach ($diller as $index => $dil): ?>
							<li class="nav-item">
							<a class="nav-link <?php echo $index === 0 ? 'active' : ''; ?>" id="dil-<?php echo $dil['kod']; ?>-tab" data-toggle="pill" href="#dil-<?php echo $dil['kod']; ?>" role="tab" aria-controls="dil-<?php echo $dil['kod']; ?>" aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>">
							   <?php echo $dil['kod']; ?>
							</a>
							</li>
							
						<?php 
						
							endforeach;
							
							}	
							
							?>
						</ul>
						<div class="tab-content" id="custom-content-below-tabContent">
						<?php foreach ($diller as $index => $dil): ?>
							<div class="tab-pane fade <?php echo $index === 0 ? 'show active' : ''; ?>" id="sayfa_dil-<?php echo $dil['kod']; ?>" role="tabpanel" aria-labelledby="dil-<?php echo $dil['kod']; ?>-tab">
								<br>
								<div class="row">

							<div class="col-12">

								<div class="form-group">
								  <label for="sayfa_meta_baslik_<?php echo $dil['kod']; ?>" class="required-field">Meta Başlık (<?php echo $dil['kod']; ?>)</label>
								  <input type="text" class="form-control" id="sayfa_meta_baslik_<?php echo $dil['kod']; ?>" name="sayfa_meta_baslik_<?php echo $dil['kod']; ?>" data-text_cek_input="sayfa_baslik_<?php echo $dil['kod']; ?>"  data-form_bos_kontrol="evet" value="<?php echo htmlspecialchars($tr_data['meta_baslik']); ?>">
								</div>


								<div class="form-group">
								  <label for="sayfa_meta_aciklama_<?php echo $dil['kod']; ?>">Meta Açıklama (<?php echo $dil['kod']; ?>)</label>
									
									  <textarea class="form-control" id="sayfa_meta_aciklama_<?php echo $dil['kod']; ?>" name="sayfa_meta_aciklama_<?php echo $dil['kod']; ?>"><?php echo htmlspecialchars($tr_data['meta_aciklama']); ?></textarea>
								</div>

								
								<div class="form-group">
								  <label for="sayfa_etiketler">Etiketler (<?php echo $dil['kod']; ?>)</label>
								  <input type="text" class="form-control" id="sayfa_etiketler_<?php echo $dil['kod']; ?>" name="sayfa_etiketler_<?php echo $dil['kod']; ?>" value="<?php echo implode(", ", array_map('htmlspecialchars', $tr_data['etiketler'])); ?>">
								</div>		
								
							
							</div>
							</div>
							</div>
						<?php endforeach; ?>
						</div>

					</div>				

<?php 

    } else {
        echo "Veri formatı beklendiği gibi değil.";
    }
} else {
    echo "Belirtilen ID'ye sahip sayfa bulunamadı.";
}
?>
				</div>
				</div>
		
		
		
		</div>
		<div class="modal-footer">
			<div class="row" style="width: 100%;">

				<div class="col-12">
				  <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
				  <button type="button" class="btn btn-primary" id="sayfa_EkleButon">Ekle</button>
				</div>
		

		
			</div>
    </div>
	</form>
      </div>

    </div> 
  </div>