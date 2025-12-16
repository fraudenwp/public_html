
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
                 data-tablo_adi="paketler" 
                 data-sutun_adi="veri" 
                 data-json_sutun_adi="resim, tur_kodu, baslik, tarih_araligi, yayin_durumu" 
                 data-tablo_basliklari="Resim, Tur Kodu, Başlık, Başlangıç / Bitiş Tarihi, Yayın Durumu" 
				 data-veri-filtrele-baslik="arsivle";
				 data-veri-filtrele-veri="1";
                 data-user_dil="tr">
            </div>
	  
	  </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  <?php $query = $pdo->query("SELECT id, kod, baslik, varsayilan, COALESCE(NULLIF(resim, ''), 'resimler/resim-yok.jpg') AS resim FROM diller"); $diller = $query->fetchAll(PDO::FETCH_ASSOC); 
  
  $query = $pdo->query("SELECT COUNT(*) FROM diller WHERE yayin_durumu = 1 ");
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
									<label for="baslik_<?php echo $dil['kod']; ?>" class="required-field">Başlık </label>
									<input type="text" class="form-control" id="baslik_<?php echo $dil['kod']; ?>" name="baslik_<?php echo $dil['kod']; ?>" data-form_bos_kontrol="evet">								
								</div>								

								<div class="form-group">
									<label for="aciklama_<?php echo $dil['kod']; ?>">Açıklama </label>
									<textarea class="summernote" id="aciklama_<?php echo $dil['kod']; ?>" name="aciklama_<?php echo $dil['kod']; ?>"></textarea>
								</div>		


								
							</div>
							<div class="col-4">

								<div class="form-group">
								  <label for="meta_baslik_<?php echo $dil['kod']; ?>" class="required-field">Meta Başlık </label>
								  <input type="text" class="form-control" id="meta_baslik_<?php echo $dil['kod']; ?>" name="meta_baslik_<?php echo $dil['kod']; ?>" data-text_cek_input="baslik_<?php echo $dil['kod']; ?>"  data-form_bos_kontrol="evet">
								</div>


								<div class="form-group">
								  <label for="meta-aciklama_<?php echo $dil['kod']; ?>">Meta Açıklama </label>
									
									  <textarea class="form-control" id="meta-aciklama_<?php echo $dil['kod']; ?>" name="meta_aciklama_<?php echo $dil['kod']; ?>"></textarea>
								</div>

								
								<div class="form-group">
								  <label for="link" class="required-field">Link <small>(Benzersiz Olmalıdır!)</small></label>
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
										<label for="tur_kodu" class="required-field">Tur Kodu </label> 
										<input type="text" id="tur_kodu" class="form-control" name="tur_kodu" data-form_bos_kontrol="evet" data-json_ortak_alan="evet">
									</div>	
								</div>	
								
							</div>
						</div>
						</div>
						
						
						<div class="card card-primary card-outline">
						<div class="card-header">
						<h3 class="card-title"><b><i class="fa fa-calendar" aria-hidden="true"></i> Tarihleri Ayarla</b></h3>
						</div>
						<div class="card-body">					
							<div class="row">
								<div class="col-3">
									<div class="form-group">
										<label for="tur_baslangic_tarihi">Başlangıç Tarihi</label>
										<input type="date" class="form-control" id="tur_baslangic_tarihi" name="tur_baslangic_tarihi" data-json_ortak_alan="evet">
									</div>	
									<div class="form-group">
										<label for="tur_baslangic_tarihi_aciklama">Başlangıç Açıklama</label>
										<input type="text" class="form-control" id="tur_baslangic_tarihi_aciklama" name="tur_baslangic_tarihi_aciklama" data-json_ortak_alan="evet">
									</div>								
								</div>	
								<div class="col-3">
									<div class="form-group">
										<label for="tur_ara_gecis_tarihi">Ara Geçiş Tarihi</label>
										<input type="date" class="form-control" id="tur_ara_gecis_tarihi" name="tur_ara_gecis_tarihi" data-json_ortak_alan="evet">
									</div>
									<div class="form-group">
										<label for="tur_ara_gecis_tarihi_aciklama">Ara Geçiş Açıklama</label>
										<input type="text" class="form-control" id="tur_ara_gecis_tarihi_aciklama" name="tur_ara_gecis_tarihi_aciklama" data-json_ortak_alan="evet">
									</div>								
								</div>	
								<div class="col-3">	
									<div class="form-group">
										<label for="tur_bitis_tarihi">Bitiş Tarihi</label>
										<input type="date" class="form-control" id="tur_bitis_tarihi" name="tur_bitis_tarihi" data-json_ortak_alan="evet">
									</div>
									<div class="form-group">
										<label for="tur_bitis_tarihi_aciklama">Bitiş Açıklama</label>
										<input type="text" class="form-control" id="tur_bitis_tarihi_aciklama" name="tur_bitis_tarihi_aciklama" data-json_ortak_alan="evet">
									</div>								
								</div>	
								<div class="col-3">	
									<div class="form-group">
										<label for="kac_gun">Kaç Gün</label>
										<input type="number" class="form-control" id="kac_gun" name="kac_gun" data-json_ortak_alan="evet">
									</div>
									<div class="form-group">
										<label for="kac_gece">Kaç Gece</label>
										<input type="number" class="form-control" id="kac_gece" name="kac_gece" data-json_ortak_alan="evet">
									</div>								
								</div>
							</div>
						</div>
						</div>
		
						
						<div class="card card-primary card-outline">
						<div class="card-header">
						<h3 class="card-title"><b><i class="fa fa-plane" aria-hidden="true"></i> Hava Yollarını Ayarla</b></h3>
						</div>
						<div class="card-body">
						<div class="row">
							<div class="col-6">					
								<div class="form-group"> 
									<label for="gidis_hava_yolu">Gidiş Hava Yolu Şirketi</label>
									<select class="form-control select2" id="gidis_hava_yolu" name="gidis_hava_yolu" data-select_tablo_adi="havayolu" data-json_ortak_alan="evet">
									<option value="">Seç</option>
								</select>
								</div>
								<div class="form-group">
									<label for="gidis_hava_yolu_saat">Saat</label>
									<input type="time" class="form-control" id="gidis_hava_yolu_saat" name="gidis_hava_yolu_saat" data-json_ortak_alan="evet">
								</div>
								<div class="form-group">
									<label for="gidis_hava_yolu_ucus_kodu">Uçuş Kodu</label>
									<input type="text" class="form-control" id="gidis_hava_yolu_ucus_kodu" name="gidis_hava_yolu_ucus_kodu" data-json_ortak_alan="evet">
								</div>							
							</div>	
							<div class="col-6">					
								<div class="form-group">
								<label for="gelis_hava_yolu">Geliş Hava Yolu Şirketi</label>
									<select class="form-control select2" id="gelis_hava_yolu" name="gelis_hava_yolu" data-select_tablo_adi="havayolu" data-json_ortak_alan="evet">
									<option value="">Seç</option>
								</select>
								</div>
								<div class="form-group">
									<label for="gelis_hava_yolu_saat">Saat</label>
									<input type="time" class="form-control" id="gelis_hava_yolu_saat" name="gelis_hava_yolu_saat" data-json_ortak_alan="evet">
								</div>
								<div class="form-group">
									<label for="gelis_hava_yolu_ucus_kodu">Uçuş Kodu</label>
									<input type="text" class="form-control" id="gelis_hava_yolu_ucus_kodu" name="gelis_hava_yolu_ucus_kodu" data-json_ortak_alan="evet">
								</div>							
							</div>
						</div>
						</div>
						</div>					

						
						<div class="card card-primary card-outline">
						<div class="card-header">
						<h3 class="card-title"><b><i class="fa fa-bed" aria-hidden="true"></i> Otelleri Ayarla</b></h3>
						</div>
						<div class="card-body">
						<div class="row">
							<div class="col-6">					
							<div class="form-group">
							<label for="otel_bir">1.Otel</label>
								<select class="form-control select2" id="otel_bir" name="otel_bir" data-select_tablo_adi="oteller" data-json_ortak_alan="evet">
								<option value="">Seç</option>
							</select>
							</div>
								<div class="form-group">
									<label for="otel_bir_aciklama">Açıklama</label>
									<input type="text" class="form-control" id="otel_bir_aciklama" name="otel_bir_aciklama" data-json_ortak_alan="evet">
								</div>						
							</div>
							<div class="col-6">	
							<div class="form-group">
							<label for="otel_iki">2. Otel</label>
								<select class="form-control select2" id="otel_iki" name="otel_iki" data-select_tablo_adi="oteller" data-json_ortak_alan="evet">
								<option value="">Seç</option>
							</select>
							</div>
								<div class="form-group">
									<label for="otel_iki_aciklama">Açıklama</label>
									<input type="text" class="form-control" id="otel_iki_aciklama" name="otel_iki_aciklama" data-json_ortak_alan="evet">
								</div>						
							</div>					
						</div>
						</div>
						</div>


						<div class="card card-primary card-outline">
						<div class="card-header">
						<h3 class="card-title"><b><i class="fa fa-bed" aria-hidden="true"></i> Oda Seçimlerini Ayarla</b></h3>
						</div>
						<div class="card-body">
						<div class="row">
							<div class="col-12">					
							<div class="form-group">
							<label for="para_birimi">Para Birimi</label>
								<select class="form-control select2" id="para_birimi" name="para_birimi" data-json_ortak_alan="evet">
								<option value="$">Dolar ($)</option>
								<option value="₺">Türk Lirası (₺)</option>
								<option value="€">Euro (€)</option>
								<option value="SAR">Riyal (SAR)</option>
							</select>
							</div>						
							</div>							
							<div class="col-6">					
								<div class="form-group">
									<label for="tekli_oda_fiyatı">Tekli Oda Fiyatı</label>
									<input type="text" class="form-control number-input" id="tekli_oda_fiyatı" name="tekli_oda_fiyatı" data-json_ortak_alan="evet">
								</div>						
							</div>
							<div class="col-6">	
								<div class="form-group">
									<label for="ikili_oda_fiyatı">ikili Oda Fiyat</label>
									<input type="text" class="form-control number-input" id="ikili_oda_fiyatı" name="ikili_oda_fiyatı" data-json_ortak_alan="evet">
								</div>
							</div>
							<div class="col-6">									
								<div class="form-group">
									<label for="uclu_oda_fiyatı">Üçlü Oda Fiyat</label>
									<input type="text" class="form-control number-input" id="uclu_oda_fiyatı" name="uclu_oda_fiyatı" data-json_ortak_alan="evet">
								</div>	
							</div>
							<div class="col-6">								
								<div class="form-group">
									<label for="dorlu_oda_fiyatı">Dörtlü Oda Fiyat</label>
									<input type="text" class="form-control number-input" id="dorlu_oda_fiyatı" name="dorlu_oda_fiyatı" data-json_ortak_alan="evet">
								</div>
							</div> 
							<div class="col-6">									
								<div class="form-group">
									<label for="cocuk_oda_fiyatı">Çocuk Oda Fiyat</label>
									<input type="text" class="form-control number-input" id="cocuk_oda_fiyatı" name="cocuk_oda_fiyatı" data-json_ortak_alan="evet">
								</div>
							</div>
							<div class="col-6">									
								<div class="form-group">
									<label for="bebek_oda_fiyatı">Bebek Oda Fiyat</label>
									<input type="text" class="form-control number-input" id="bebek_oda_fiyatı" name="bebek_oda_fiyatı" data-json_ortak_alan="evet">
								</div>						
							</div>					
						</div>
						</div>
						</div>
						
						<div class="card card-primary card-outline">
						<div class="card-header">
						<h3 class="card-title"><b><i class="fa fa-info-circle" aria-hidden="true"></i> Detay Sayfaları</b></h3>
						</div>
						<div class="card-body">	
								<div class="form-group">
								<label for="bilgi_sayfalari">Bilgi Sayfalarından Seçim Yap</label>
									<select class="form-control select2" id="bilgi_sayfalari" name="bilgi_sayfalari" data-select_tablo_adi="bilgi_sayfalari" data-json_ortak_alan="evet" multiple>
								</select>
								</div>							
						</div>				
						</div>	
						
						<div class="card card-primary card-outline">
						<div class="card-header">
						<h3 class="card-title"><b><i class="fa fa-info-circle" aria-hidden="true"></i> Tur Dönemi</b></h3>
						</div>
						<div class="card-body">	
								<div class="form-group">
								<label for="donem">Tur Dönemlerini Seç</label>
									<select class="form-control select2" id="donem" name="donem" data-select_tablo_adi="donem" data-json_ortak_alan="evet" multiple>
								</select>
								</div>							
						</div>				
						</div>	
						
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
										<input type="checkbox" class="custom-control-input" id="tukendi" name="tukendi" value="1" data-json_ortak_alan="evet">
										<label class="custom-control-label" for="tukendi">Tükendi</label>
									</div>
								</div>			  
							</div>
							<div class="col-2">
								<div class="form-group">
									<div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
										<input type="checkbox" class="custom-control-input" id="arsivle" name="arsivle" value="1" data-json_ortak_alan="evet">
										<label class="custom-control-label" for="arsivle">Arşivle</label>
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
										<input type="checkbox" class="custom-control-input" id="kampanyali" name="kampanyali" value="1" data-json_ortak_alan="evet">
										<label class="custom-control-label" for="kampanyali">Kampanyalı</label>
									</div>
								</div>			  
							</div>						
									
						</div>								
						</div>				
						</div>	

						<div class="card card-primary card-outline">
							<div class="card-header">
							<h3 class="card-title"><b><i class="fa fa-info-circle" aria-hidden="true"></i> Vurgula</b></h3>
							</div>
							<div class="card-body">	
								<div class="form-group">
									<label for="vurgulama_yazi">Vurgulama Yazısı</label>
									<input type="text" class="form-control number-input" id="vurgulama_yazi" name="vurgulama_yazi" data-json_ortak_alan="evet">
								</div>							
							</div>				
						</div>						
					
					
					</div>				

					<div class="tab-pane fade" id="medya-ekle" role="tabpanel" aria-labelledby="medya-ekle-tab">
						<div class="form-group" id="resim-upload-area">
							<label for="dosya_adi">Resim</label>
							<input type="file" id="file-input" class="form-control" name="dosya_adi[]" 
								   data-resim_eni="329" 
								   data-resim_boyu="230" 
								   data-resim_kayit_dizini="resimler/paketler/" 
								   data-resmi_kirp="evet" 
								   data-resmi_doldur="evet" 
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

				<div class="col-3">
				  <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
				  <button type="button" class="btn btn-primary" id="EkleButon">Ekle</button>
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
     data-sayfa_ayar_tablo_satir_id="26"
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
$sql = "SELECT veri, yayin_durumu FROM sayfalar WHERE id = 26";
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



