
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
                 data-tablo_adi="yorumlar" 
                 data-sutun_adi="veri" 
                 data-json_sutun_adi="mesaj_tarih, baslik, yayin_durumu" 
                 data-tablo_basliklari="Tarih, Gönderen, Yayın Durumu" 
                 data-ek_form="evet"  
                 data-user_dil="tr">
            </div>
	  
	  </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  <?php
	
	$query = $pdo->query("SELECT id, kod, baslik, varsayilan, COALESCE(NULLIF(resim, ''), 'resimler/resim-yok.jpg') AS resim FROM diller"); $diller = $query->fetchAll(PDO::FETCH_ASSOC);  
	$query = $pdo->query("SELECT COUNT(*) FROM diller WHERE yayin_durumu = 1");
	$dilSayisi = $query->fetchColumn();
	  
  ?>


<!-- Veri İşlem Modal (Ekleme/Güncelleme için) -->
<div class="modal fade" id="VeriIslemModal" tabindex="-1" role="dialog" aria-labelledby="VeriIslemModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
	<div class="overlay" style="display:none;"> <i class="fas fa-2x fa-sync fa-spin"></i> </div>
      <div class="modal-header">
        <h5 >Mesajlar</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
				<div class="card card-primary card-outline card-outline-tabs" style="display:none">
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

			<style>
				  .message-content {
					min-height: 200px;
					max-height: 500px;
					overflow-y: auto;
					white-space: pre-wrap;
					word-wrap: break-word;
					background-color: #f8f9fa;
					border: 1px solid #ced4da;
					border-radius: 0.25rem;
					padding: 0.375rem 0.75rem;
				  }
				.form-control:disabled, .form-control[readonly] {
					background-color: #ffffff;
					opacity: 1;
				}	
				
				.gizle {
					display: none !important;
				}

						
			</style>			
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
									<label for="baslik_<?php echo $dil['kod']; ?>">İsim</label>
									<input type="text" class="form-control" id="baslik_<?php echo $dil['kod']; ?>" name="baslik_<?php echo $dil['kod']; ?>" disabled>								
								</div>			
								

								
								<div class="form-group">
									<label for="mesaj_<?php echo $dil['kod']; ?>">Mesaj</label>
									<textarea class="form-control message-content"  id="mesaj_<?php echo $dil['kod']; ?>" row="3" name="mesaj_<?php echo $dil['kod']; ?>" disabled></textarea>
								</div>		

							</div>
							<div class="col-4">
					
								
								<div class="form-group">
								  <label for="ip_address_<?php echo $dil['kod']; ?>">Gönderen İp Adresi</label>
								  <input type="text" class="form-control" id="ip_address_<?php echo $dil['kod']; ?>" name="ip_address_<?php echo $dil['kod']; ?>" disabled>
								</div>

								<div class="row">
									<div class="col-6">
									<div class="form-group">
										<label for="mesaj_tarih_<?php echo $dil['kod']; ?>">Tarih</label>
										<input type="text" class="form-control" id="mesaj_tarih_<?php echo $dil['kod']; ?>" name="mesaj_tarih_<?php echo $dil['kod']; ?>" disabled>								
									</div>									
									</div>	
									<div class="col-6">								
									<div class="form-group"> 
										<label for="mesaj_saat_<?php echo $dil['kod']; ?>">Saat</label>
										<input type="text" class="form-control" id="mesaj_saat_<?php echo $dil['kod']; ?>" name="mesaj_saat_<?php echo $dil['kod']; ?>" disabled>								
									</div>		
									</div>	
									<div class="col-12">								
									<div class="form-group"> 
										<label for="mesaj_url_<?php echo $dil['kod']; ?>">Mesajın Gönderildiği Url</label>
										<input type="text" class="form-control" id="mesaj_url_<?php echo $dil['kod']; ?>" name="mesaj_url_<?php echo $dil['kod']; ?>" disabled>								
									</div>		
									</div>		
								</div>							
							
							</div>
							</div>
							</div>
						<?php endforeach; ?>
						</div>
	
						
						<div id="ek-form-container" class="row"></div>
						

					
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

				<div class="col-12">
				  <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
				  
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



