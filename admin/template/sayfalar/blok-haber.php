
 <div class="content-wrapper">
    <div class="content-header"> 
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0"> <?php echo $sayfa['baslik']. ' - ' . $user_dil; ?></h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?php echo $baseurl; ?>">Gösterge Paneli</a></li>
              <li class="breadcrumb-item active"><?php echo $sayfa['baslik']; ?></li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div> 
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content"> 
	
      <div class="container-fluid">
	  
            <div class="col-md-12" id="sayfa-cek" 
                 data-dizin_yolu="template/sayfalar/veri-list.php" 
                 data-tablo_adi="blok_haber" 
                 data-sutun_adi="veri" 
                 data-json_sutun_adi="resim, baslik, sira, yayin_durumu" 
                 data-tablo_basliklari="Resim, Başlık, Sıra No, Yayın Durumu" 
                 data-ek_form="evet" 
                 data-user_dil="tr"
                 data-sayfa_ayar_tablo_sutun_adi="veri">
            </div>
	  
	  </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  <?php $query = $pdo->query("SELECT id, kod, baslik, varsayilan, COALESCE(NULLIF(resim, ''), 'resimler/resim-yok.jpg') AS resim FROM diller"); $diller = $query->fetchAll(PDO::FETCH_ASSOC); 
  
  $query = $pdo->query("SELECT COUNT(*) FROM diller WHERE yayin_durumu = 1");
$dilSayisi = $query->fetchColumn();
  ?>


<!-- Veri İşlem Modal (Ekleme/Güncelleme için) -->
<div class="modal fade" id="VeriIslemModal" tabindex="-1" role="dialog" aria-labelledby="VeriIslemModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
	<div class="overlay" style="display:none;"> <i class="fas fa-2x fa-sync fa-spin"></i> </div>
      <div class="modal-header">
        <h5 class="modal-title" id="VeriIslemModalLabel">Veri İşlem</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
		<div class="card card-primary card-outline card-outline-tabs">
			<div class="card-header p-0 border-bottom-0">
				<ul class="nav nav-tabs" id="custom-tabs-four-tab" role="tablist">
					<li class="nav-item">
					<a class="nav-link active" id="genel-ekle-tab" data-toggle="pill" href="#genel-ekle" role="tab" aria-controls="genel-ekle" aria-selected="true">Genel</a>
					</li>
					<li class="nav-item">
					<a class="nav-link" id="medya-ekle-tab" data-toggle="pill" href="#medya-ekle" role="tab" aria-controls="medya-ekle" aria-selected="false">Medya</a>
					</li>
				<!-- Dinamik olarak eklenecek ek sekmeler buraya gelecek -->
				</ul>
			</div>
		</div>

				
		<form id="VeriEkleForm" enctype="multipart/form-data">
				<div class="card-body">
				<div class="tab-content" id="custom-tabs-four-tabContent">
				
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
							<div class="tab-pane fade <?php echo $index === 0 ? 'show active' : ''; ?>" id="dil-<?php echo $dil['kod']; ?>" role="tabpanel" aria-labelledby="dil-<?php echo $dil['kod']; ?>-tab">
								<br>
								<div class="row">

								<div class="col-8">
								
								<div class="form-group">
									<label for="baslik_<?php echo $dil['kod']; ?>" class="required-field">Başlık (<?php echo $dil['kod']; ?>)</label>
									<input type="text" class="form-control" id="baslik_<?php echo $dil['kod']; ?>" name="baslik_<?php echo $dil['kod']; ?>" data-form_bos_kontrol="evet">								
								</div>		
								
								<div class="form-group">
								  <label for="kisa-aciklama">Kısa Açıklama</label>
									
									  <textarea class="form-control" id="kisa-aciklama" name="kisa_aciklama" data-json_ortak_alan="evet"></textarea>
								</div>
								
								<div class="form-group">
									<label for="aciklama_<?php echo $dil['kod']; ?>">Açıklama (<?php echo $dil['kod']; ?>)</label>
									<textarea class="summernote" id="aciklama_<?php echo $dil['kod']; ?>" name="aciklama_<?php echo $dil['kod']; ?>"></textarea>
								</div>		

							</div>
							<div class="col-4">

								<div class="form-group">
								  <label for="meta_baslik_<?php echo $dil['kod']; ?>" class="required-field">Meta Başlık (<?php echo $dil['kod']; ?>)</label>
								  <input type="text" class="form-control" id="meta_baslik_<?php echo $dil['kod']; ?>" name="meta_baslik_<?php echo $dil['kod']; ?>" data-text_cek_input="baslik_<?php echo $dil['kod']; ?>"  data-form_bos_kontrol="evet">
								</div>


								<div class="form-group">
								  <label for="meta-aciklama_<?php echo $dil['kod']; ?>">Meta Açıklama (<?php echo $dil['kod']; ?>)</label>
									
									  <textarea class="form-control" id="meta-aciklama_<?php echo $dil['kod']; ?>" name="meta_aciklama_<?php echo $dil['kod']; ?>"></textarea>
								</div>
								
								<div class="form-group">
								  <label for="link" class="required-field">Seo Link (<?php echo $dil['kod']; ?>)</label>
								  <input type="text" class="form-control" data-form_bos_kontrol="evet" data-text_cek_input="baslik_<?php echo $dil['kod']; ?>" data-text_cek_input_seo_donustur="evet" id="link_<?php echo $dil['kod']; ?>" name="link_<?php echo $dil['kod']; ?>">
								</div> 
								
								<div class="form-group">
								  <label for="etiketler">Etiketler (<?php echo $dil['kod']; ?>)</label>
								  <input type="text" class="form-control" id="etiketler_<?php echo $dil['kod']; ?>" name="etiketler_<?php echo $dil['kod']; ?>">
								</div>		
								
								<div class="form-group">
								  <label for="hariciLink" style="margin-bottom: 0px;">Harici Link (<?php echo $dil['kod']; ?>)</label><br>
								  <small>Buraya veri yazıldığında bu sayfa link moduna geçer ve ilgili harici linke yönlendirme yapılır.</small>
								  <input type="text" class="form-control" id="hariciLink_<?php echo $dil['kod']; ?>" name="hariciLink_<?php echo $dil['kod']; ?>">
								</div>							
							
							</div>
							</div>
							</div>
						<?php endforeach; ?>
						</div>
						

						
						<div class="card card-primary card-outline">
							<div class="card-header">
							<h3 class="card-title"><b>Listeleme Şekli</b></h3>
							</div>
							<div class="card-body">		
								<div class="row">
									<div class="col-8">
										<div class="form-group">
										<label for="kisa_aciklama">Hangi Kategoride Listelensin</label>
										<select class="form-control select2" id="kisa_aciklama" name="kisa_aciklama" multiple>
										</select>
										</div>
									</div>
									<div class="col-4">	
										<div class="form-group">
											<label for="sira">Hangi Sırada Listelensin (Sıra No)</label>
											<input type="number" class="form-control" id="sira" name="sira" value="0">
										</div>	
									</div>	
									
								</div>
							</div>
						</div>
						
						<div id="ek-form-container" class="row"></div>
						
						<div class="card card-primary card-outline">
						<div class="card-header">
						<h3 class="card-title"><b><i class="fa fa-info-circle" aria-hidden="true"></i> Seçenekler</b></h3>
						</div>
						<div class="card-body">	
						<div class="row">	
									
							<div class="col-2">
								<div class="form-group">
									<div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
									<input type="checkbox" class="custom-control-input" id="yayin_durumu" name="yayin_durumu" value="1">
									<label class="custom-control-label" for="yayin_durumu">Yayın Durumu</label>
									</div>
								</div>				  
							</div>

							<div class="col-2">
								<div class="form-group">
									<div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
										<input type="checkbox" class="custom-control-input" id="one-cikar" name="one-cikar" value="1" data-json_ortak_alan="evet">
										<label class="custom-control-label" for="one-cikar">Öne Çıkar</label>
									</div>
								</div>			  
							</div>
									<div class="col-2">
										<div class="form-group">
											<div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
												<input type="checkbox" class="custom-control-input" id="baslik_gizle" name="baslik_gizle" value="1" data-json_ortak_alan="evet">
												<label class="custom-control-label" for="baslik_gizle">Başlığı Gizle</label>
											</div>
										</div>			  
									</div> 
									
									<div class="col-3">
										<div class="form-group">
											<div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
												<input type="checkbox" class="custom-control-input" id="iletisim_bilgileri_gizle" name="iletisim_bilgileri_gizle" value="1" data-json_ortak_alan="evet">
												<label class="custom-control-label" for="iletisim_bilgileri_gizle">İletişim Bilgilerini Göster</label>
											</div>
										</div>			  
									</div> 						
									
						</div>								
						</div>				
						</div>				

					</div>				

					<div class="tab-pane fade" id="medya-ekle" role="tabpanel" aria-labelledby="medya-ekle-tab">
						<div class="form-group" id="resim-upload-area">
							<label for="dosya_adi">Resim</label>
							<input type="file" id="file-input" class="form-control" name="dosya_adi[]" 
								   data-resim_eni="800" 
								   data-resim_boyu="175" 
								   data-resim_kayit_dizini="resimler/kurumsal/" 
								   data-resmi_kirp="evet" 
								   data-resmi_doldur="hayır" 
								   multiple>
						</div>
						
						<div class="row">
							<div class="col-10">
							<div id="resim-preview-container" class="mt-3">
								<!-- Resim önizlemeleri burada gösterilecek -->
							</div>
							</div>
							<div class="col-2">			
								<div id="image-actions" class="" style="display:none;">
									<button id="delete-images" class="btn btn-danger btn-xs mr-2"><i class="fas fa-trash"></i> Sil</button>
									<button id="make-cover" class="btn btn-warning btn-xs"><i class="fas fa-star"></i> Kapak Resmi Yap</button>
								</div>						
							</div>
						</div>
						

					</div>

					
				
				</div>
				</div>
		
		
		
		</div>
		<div class="modal-footer">
			<div class="row" style="width: 100%;">

				<div class="col-10">
				  <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
				  <button type="button" class="btn btn-primary" id="EkleButon">Ekle</button>
				</div>
				<div class="col-2">
					<div class="form-group">
						<div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
						<input type="checkbox" class="custom-control-input" id="yayin_durumu" name="yayin_durumu" value="1">
						<label class="custom-control-label" for="yayin_durumu">Yayın Durumu</label>
						</div>
					</div>				  
				</div>

		
			</div>
    </div>
	</form>
  </div>
</div>
</div>



<!-- Sil Modal-->
<div class="modal fade" id="SilModal" tabindex="-1" role="dialog" aria-labelledby="SilModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="SilModalLabel">Sil</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="sil-uyari">Seçilen verileri silmek istediğinize emin misiniz?</p>
      </div>
      <div class="modal-footer">
        <button type="button" id="iptalButon" class="btn btn-secondary" data-dismiss="modal">İptal</button>
        <button type="button" id="SilButon" class="btn btn-primary">Evet</button>
      </div>
    </div>
  </div>
</div>

<!-- Sayfa Genel Ayarlar Modal-->
<div class="modal fade" id="SayfaAyarModal" 
     data-sayfa_ayar_tablo_adi="sayfalar"
     data-sayfa_ayar_tablo_satir_id="39"
     tabindex="-1" role="dialog" aria-labelledby="SayfaAyarModalLabel" aria-hidden="true">
    <!-- Modal içeriği -->
   <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="SayfaAyarModalLabel">Sayfa Genel Ayarlar</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
	  

      		<form id="SayfaAyarlarForm" enctype="multipart/form-data">
				<div class="card-body">
				<div class="tab-content" id="custom-tabs-four-tabContent">
<?php
// Sorguyu hazırla
$sql = "SELECT veri, yayin_durumu FROM sayfalar WHERE id = 39";
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

								<div class="col-8">
								
								<div class="form-group">
									<label for="sayfa_baslik_<?php echo $dil['kod']; ?>" class="required-field">Başlık (<?php echo $dil['kod']; ?>)</label>
									<input type="text" class="form-control" id="sayfa_baslik_<?php echo $dil['kod']; ?>" name="sayfa_baslik_<?php echo $dil['kod']; ?>" data-form_bos_kontrol="evet" value="<?php echo htmlspecialchars($tr_data['baslik']); ?>">								
								</div>		
								
								
								<div class="form-group">
									<label for="sayfa_aciklama_<?php echo $dil['kod']; ?>">Açıklama (<?php echo $dil['kod']; ?>)</label>
									<textarea class="summernote" id="sayfa_aciklama_<?php echo $dil['kod']; ?>" name="sayfa_aciklama_<?php echo $dil['kod']; ?>"><?php echo htmlspecialchars($tr_data['aciklama']); ?></textarea>
								</div>		

							</div>
							<div class="col-4">

								<div class="form-group">
								  <label for="sayfa_meta_baslik_<?php echo $dil['kod']; ?>" class="required-field">Meta Başlık (<?php echo $dil['kod']; ?>)</label>
								  <input type="text" class="form-control" id="sayfa_meta_baslik_<?php echo $dil['kod']; ?>" name="sayfa_meta_baslik_<?php echo $dil['kod']; ?>" data-text_cek_input="sayfa_baslik_<?php echo $dil['kod']; ?>"  data-form_bos_kontrol="evet" value="<?php echo htmlspecialchars($tr_data['meta_baslik']); ?>">
								</div>


								<div class="form-group">
								  <label for="sayfa_meta_aciklama_<?php echo $dil['kod']; ?>">Meta Açıklama (<?php echo $dil['kod']; ?>)</label>
									
									  <textarea class="form-control" id="sayfa_meta_aciklama_<?php echo $dil['kod']; ?>" name="sayfa_meta_aciklama_<?php echo $dil['kod']; ?>"><?php echo htmlspecialchars($tr_data['meta_aciklama']); ?></textarea>
								</div>
								
								<div class="form-group">
								  <label for="sayfa_link" class="required-field">Seo Link (<?php echo $dil['kod']; ?>)</label>
								  <input type="text" class="form-control" data-form_bos_kontrol="evet" data-text_cek_input="sayfa_baslik_<?php echo $dil['kod']; ?>" data-text_cek_input_seo_donustur="evet" id="sayfa_link_<?php echo $dil['kod']; ?>" name="sayfa_link_<?php echo $dil['kod']; ?>" value="<?php echo htmlspecialchars($tr_data['link']); ?>">
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

				<div class="col-10">
				  <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
				  <button type="button" class="btn btn-primary" id="sayfa_EkleButon">Ekle</button>
				</div>
				<div class="col-2">
            <div class="form-group">
                <div class="custom-control custom-switch <?php echo $yayin_durumu ? 'custom-switch-on-success' : 'custom-switch-off-danger'; ?>">
                    <input type="checkbox" class="custom-control-input" id="sayfa_yayin_durumu" name="sayfa_yayin_durumu" value="1" <?php echo $yayin_durumu ? 'checked' : ''; ?>>
                    <label class="custom-control-label" for="sayfa_yayin_durumu">Yayın Durumu</label>
                </div>
            </div>				  
				</div>

		
			</div>
    </div>
	</form>
      </div>

    </div>
  </div>
  
 


</div>

