<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0"> <?php echo $sayfa['baslik']; ?></h1>
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
			  <?php
				// GET parametrelerini al
				$table = $_GET['list'] ?? null;
				$id = $_GET['id'] ?? null;

				if ($table && $id) {
					$veri = getVeri($table, $id);

					if ($veri) {
				?>	  
		<form id="veriForm">
	  
			<div class="row">
			  <!-- left column -->
			  <div class="col-md-6">
				<!-- jquery validation -->
					<div class="card card-primary">
					  <div class="card-header">
						<h3 class="card-title">Kişisel Bilgiler</small></h3>
					  </div>
					  <!-- /.card-header -->
					  <!-- form start -->				
								<div class="card-body">
									<div class="form-group">
										<label for="ad_soyad">İsim Soyisim</label>
										<input type="text" name="ad_soyad" class="form-control" data-sifrele=""  id="ad_soyad" placeholder="İsim Soyisim" value="<?php echo $veri['ad_soyad']; ?>">
										<input type="hidden" name="id-<?php echo $veri['dil']; ?>" class="form-control" data-sifrele=""  id="id" value="<?php echo $veri['id']; ?>">
									</div>
									<div class="form-group">
										<label for="telefon">Telefon</label>
										<input type="telefon" name="telefon" data-sifrele="aes" class="form-control" id="telefon" placeholder="Telefon" value="<?php echo aes_coz(base64_decode($veri['telefon'])); ?>">
									</div>
									<div class="form-group">
										<label for="mail">Email</label>
										<input type="mail" name="mail" data-sifrele="aes" class="form-control" id="mail" placeholder="Email" value="<?php echo aes_coz(base64_decode($veri['mail'])); ?>">
									</div>
								</div>  
					</div>
					<!-- /.card -->
				</div>
			  <!--/.col (left) -->

				  <!-- left column -->
					<div class="col-md-6">
						<!-- jquery validation -->
						<div class="card card-primary">
						  <div class="card-header">
							<h3 class="card-title">Panel Bilgileri</small></h3>
						  </div>
						  <!-- /.card-header -->
						  <!-- form start -->
								
									<div class="card-body">

										<div class="form-group" style="display: none;">
											<label for="resim">Resim</label>
											<input type="file" name="resim" data-resim-tablosu="resimler" class="form-control" id="dosyaYukleInput" multiple >
										</div>
										
											<div class="form-group">
												<label>Yetkilendir</label>
												<select class="custom-select" name="yetki" data-sifrele="">									
													<option value="1">Yönetici</option>
													<option value="2">Operatör</option>

												</select>
											</div>	

																								
										<div class="form-group">
											<label for="k_adi">Kullanıcı Adı</label>
											<input type="text" name="k_adi" data-sifrele="" class="form-control" id="k_adi" placeholder="k_adi" value="<?php echo $veri['k_adi']; ?>">
										</div>
																								
										<div class="form-group">
											<label for="sifre">Şifre</label>
											<input type="password" name="sifre" data-sifrele="md5" class="form-control" id="sifre" placeholder="Password">
										</div>
									</div> 
									<!-- /.card-body -->

						</div>
						<!-- /.card -->
					</div>
				
					<div class="col-md-12">
						<div class="card card-primary">							
							<div class="card-body">
								<div class="row">
									<div class="col-8">					
							<input type="submit" class="btn btn-primary float-left" value="Düzenle" data-form-turu="veri-duzenle" data-tablo-adi="kullanicilar">	
									
									</div> 					
									<div class="col-4">																	
										<div class="custom-control custom-switch custom-switch-on-success float-right">
											<input name="yayin_durumu" type="checkbox" class="custom-control-input" id="yayin_durumu" value="1" <?php echo ($veri['yayin_durumu'] == 1) ? 'checked' : ''; ?>>
											<label class="custom-control-label" for="yayin_durumu">Yayın Durumu</label>                    
										</div>								
									</div>					
								</div> 								
							</div>
						</div>			
					</div>
				</div>
		</form>
		
			<?php

				} else {
					echo "Kayıt bulunamadı.";
				}
			} else {
				echo "Geçersiz parametreler.";
			}
			 ?>			
		
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  
