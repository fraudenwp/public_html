
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
                 data-tablo_adi="oteller" 
                 data-sutun_adi="veri" 
                 data-json_sutun_adi="resim, baslik, sira, yayin_durumu" 
                 data-tablo_basliklari="Resim, Başlık, Sıra No, Yayın Durumu" 
                 data-user_dil="tr">
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
								  <label for="link" class="required-field">Link (<?php echo $dil['kod']; ?>)</label>
								  <input type="text" class="form-control" data-form_bos_kontrol="evet" data-text_cek_input="baslik_<?php echo $dil['kod']; ?>" data-text_cek_input_seo_donustur="evet" id="link_<?php echo $dil['kod']; ?>" name="link_<?php echo $dil['kod']; ?>">
								</div>
								
								<div class="form-group">
								  <label for="etiketler">Etiketler (<?php echo $dil['kod']; ?>)</label>
								  <input type="text" class="form-control" id="etiketler_<?php echo $dil['kod']; ?>" name="etiketler_<?php echo $dil['kod']; ?>">
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
									<label for="ust_kategori_id">Hangi Kategoride Listelensin</label>
									<select class="form-control select2" id="ust_kategori_id" name="ust_kategori_id" multiple>
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
						
						<div class="card card-primary card-outline">
						<div class="card-header">
						<h3 class="card-title"><b>Otel İletişim Bilgileri</b></h3>
						</div>
						<div class="card-body">		
							<div class="row">
								<div class="col-4">
									<div class="form-group">
										<label for="sehir">Şehir</label>
										<input type="text" class="form-control" id="sehir" name="sehir" value="" data-json_ortak_alan="evet">
									</div>
								</div>
								<div class="col-8">
									<div class="form-group">
										<label for="adres">Açık Adres</label>
										<input type="text" class="form-control" id="adres" name="adres" value="" data-json_ortak_alan="evet">
									</div>
								</div>
								<div class="col-3">
									<div class="form-group">
										<label for="telefon">Telefon</label>
										<input type="text" class="form-control" id="telefon" name="telefon" value="" data-json_ortak_alan="evet">
									</div>
								</div>
								<div class="col-4">
									<div class="form-group">
										<label for="mail">E-Mail</label>
										<input type="text" class="form-control" id="email" name="email" value="" data-json_ortak_alan="evet">
									</div>
								</div>
								<div class="col-5">
									<div class="form-group">
										<label for="video">Video Linki ( Yotube )</label>
										<input type="text" class="form-control" id="video" name="video" value="" data-json_ortak_alan="evet">
									</div>
								</div>								
								<div class="col-12">
									<div class="form-group">
										<label for="harita_link">Google Harita Paylaşım Linki</label>
										<input type="text" class="form-control" id="harita_link" name="harita_link" value="" data-json_ortak_alan="evet">
									</div>
								</div>	
								
								<div class="col-12">
									<div class="form-group">
										<label for="yol_tarifi">Google Harita Yol Tarifi</label>
										<input type="text" class="form-control" id="yol_tarifi" name="yol_tarifi" value="" data-json_ortak_alan="evet">
									</div>
								</div>
								
								<div class="col-12">
									<div class="form-group">
										<label for="harita_iframe">Google Harita İframe Kodu</label>
										<textarea class="form-control" id="harita_iframe" name="harita_iframe" data-json_ortak_alan="evet"></textarea>
									</div>
								</div>
								
								
							</div>
						</div>
						</div>						
						
						<div class="card card-primary card-outline">
						<div class="card-header">
						<h3 class="card-title"><b>Otel Özelikleri</b></h3>
						</div>
						<div class="card-body">		
							<div class="row">
				<div class="col-3">
					<div class="form-group">
						<div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
							<input type="checkbox" class="custom-control-input" id="wifi" name="otel_olanaklar_wifi" value="Wifi" data-json_ortak_alan="evet">
							<label class="custom-control-label" for="wifi">Wifi</label>
						</div>
					</div>			  
				</div>
				<div class="col-3">
					<div class="form-group">
						<div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
							<input type="checkbox" class="custom-control-input" id="klima" name="otel_olanaklar_klima" value="Klima" data-json_ortak_alan="evet">
							<label class="custom-control-label" for="klima">Klima</label>
						</div>
					</div>			  
				</div>
				<div class="col-3">
					<div class="form-group">
						<div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
							<input type="checkbox" class="custom-control-input" id="kat_hizmeti" name="otel_olanaklar_Günlük_kat_hizmeti" value="Günlük kat hizmetleri" data-json_ortak_alan="evet">
							<label class="custom-control-label" for="kat_hizmeti">Günlük kat hizmetleri</label>
						</div>
					</div>			  
				</div>
				<div class="col-3">
					<div class="form-group">
						<div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
							<input type="checkbox" class="custom-control-input" id="doktor" name="otel_olanaklar_Doktor_(tesis_bünyesinde)" value="Doktor (tesis bünyesinde)" data-json_ortak_alan="evet">
							<label class="custom-control-label" for="doktor">Doktor (tesis bünyesinde)</label>
						</div>
					</div>			  
				</div>
				<div class="col-3">
					<div class="form-group">
						<div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
							<input type="checkbox" class="custom-control-input" id="asansor" name="otel_olanaklar_asansör" value="Asansör" data-json_ortak_alan="evet">
							<label class="custom-control-label" for="asansor">Asansör</label>
						</div>
					</div>			  
				</div>
				<div class="col-3">
					<div class="form-group">
						<div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
							<input type="checkbox" class="custom-control-input" id="oda_yemek_servisi" name="otel_olanaklar_oda_yemek_servisi" value="Odaya yemek servisi" data-json_ortak_alan="evet">
							<label class="custom-control-label" for="oda_yemek_servisi">Odaya yemek servisi</label>
						</div>
					</div>			  
				</div>
				<div class="col-3">
					<div class="form-group">
						<div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
							<input type="checkbox" class="custom-control-input" id="sigara_icilmeyen_odalar" name="otel_olanaklar_sigara_içilmeyen_odalar" value="Sigara içilmeyen odalar" data-json_ortak_alan="evet">
							<label class="custom-control-label" for="sigara_icilmeyen_odalar">Sigara içilmeyen odalar</label>
						</div>
					</div>			  
				</div>
				<div class="col-3">
					<div class="form-group">
						<div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
							<input type="checkbox" class="custom-control-input" id="engelli_odalari" name="otel_olanaklar_engelli_odalari" value="Engelli odaları" data-json_ortak_alan="evet">
							<label class="custom-control-label" for="engelli_odalari">Engelli odaları</label>
						</div>
					</div>			  
				</div>
				<div class="col-3">
					<div class="form-group">
						<div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
							<input type="checkbox" class="custom-control-input" id="kuru_temizleme" name="otel_olanaklar_kuru_temizleme" value="Kuru temizleme" data-json_ortak_alan="evet">
							<label class="custom-control-label" for="kuru_temizleme">Kuru temizleme</label>
						</div>
					</div>			  
				</div>
				<div class="col-3">
					<div class="form-group">
						<div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
							<input type="checkbox" class="custom-control-input" id="camasir_hane" name="otel_olanaklar_camasir_hane" value="Çamaşırhane" data-json_ortak_alan="evet">
							<label class="custom-control-label" for="camasir_hane">Çamaşırhane</label>
						</div>
					</div>			  
				</div>
				<div class="col-3">
					<div class="form-group">
						<div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
							<input type="checkbox" class="custom-control-input" id="emanet_kasa" name="otel_olanaklar_emanet_kasa" value="Emanet Kasa" data-json_ortak_alan="evet">
							<label class="custom-control-label" for="emanet_kasa">Emanet Kasa</label>
						</div>
					</div>			  
				</div>
				<div class="col-3">
					<div class="form-group">
						<div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
							<input type="checkbox" class="custom-control-input" id="yangin_merdiveni" name="otel_olanaklar_yangin_merdiveni" value="Yangın Merdiveni" data-json_ortak_alan="evet">
							<label class="custom-control-label" for="yangin_merdiveni">Yangın Merdiveni</label>
						</div>
					</div>			  
				</div>
				<div class="col-3">
					<div class="form-group">
						<div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
							<input type="checkbox" class="custom-control-input" id="ek_yatak" name="otel_olanaklar_ek_yatak" value="Ek Yatak" data-json_ortak_alan="evet">
							<label class="custom-control-label" for="ek_yatak">Ek Yatak</label>
						</div>
					</div>			  
				</div>
				<div class="col-3">
					<div class="form-group">
						<div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
							<input type="checkbox" class="custom-control-input" id="televizyon" name="otel_olanaklar_televizyon" value="Televizyon" data-json_ortak_alan="evet">
							<label class="custom-control-label" for="televizyon">Televizyon</label>
						</div>
					</div>			  
				</div>
				<div class="col-3">
					<div class="form-group">
						<div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
							<input type="checkbox" class="custom-control-input" id="telefon" name="otel_olanaklar_telefon" value="Telefon" data-json_ortak_alan="evet">
							<label class="custom-control-label" for="telefon">Telefon</label>
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
								   data-resim_eni="728" 
								   data-resim_boyu="530" 
								   data-resim_kayit_dizini="resimler/oteller/" 
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
     data-sayfa_ayar_tablo_satir_id="24"
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
$sql = "SELECT veri, yayin_durumu FROM sayfalar WHERE id = 24";
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

