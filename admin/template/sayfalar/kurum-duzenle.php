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
                <h3 class="card-title">Kurum Ekle</small></h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
					
						<div class="card-body">
							<div class="form-group">
								<label for="yetkili_adi">Yetkili Adı Soyadı</label>
								<input type="text" name="yetkili_adi-tr" class="form-control" data-sifrele="" id="yetkili_adi" placeholder="Yetkili Adı Soyadı" value="<?php echo $veri['yetkili_adi']; ?>">
							<input type="hidden" name="id-<?php echo $veri['dil']; ?>" class="form-control" data-sifrele=""  id="id" value="<?php echo $veri['id']; ?>">
							</div>

						<div class="form-group">
								<label for="telefon">Telefon</label>
								<input type="text" name="yetkili_telefon" class="form-control" data-sifrele="" id="telefon" placeholder="Telefon"  value="<?php echo $veri['yetkili_telefon']; ?>">
							</div>
						<div class="form-group">
								<label for="aciklama">Açıklama</label>
								<input type="text" name="aciklama" class="form-control" data-sifrele="" id="aciklama" placeholder="Açıklama"  value="<?php echo $veri['aciklama']; ?>">
							</div>


							<div class="form-group" style="display:none">
								<label for="resim">Resim</label>
								<input type="file" name="resim"  data-resim-tablosu="kurumlar" data-hedef-dizin="resimler/kurumlar" data-resim-en="100" data-resim-boy="100" data-resim-kalite="75" class="form-control" id="dosyaYukleInput" multiple>
							</div>							

						</div> 
						<!-- /.card-body -->

            </div>
            <!-- /.card -->
            </div>
          <!--/.col (left) -->

          <!-- left column -->
          <div class="col-md-6">
            <!-- jquery validation -->
				<div class="card card-primary">
				  <div class="card-header">
					<h3 class="card-title">...</small></h3>
				  </div>
				  <!-- /.card-header -->
				  <!-- form start -->
						
					<div class="card-body">
						<div class="form-group">
							<label for="alan_adi">Alan Adı</label>
							<input type="text" name="alan_adi" class="form-control" data-sifrele="" id="alan_adi" placeholder="Alan Adı" value="<?php echo $veri['alan_adi']; ?>">
						</div>

						
						<div class="form-group">
							<label for="kurum_ip">Kurum İp Adres</label>
							<input type="text" name="kurum_ip" class="form-control" data-sifrele="" id="kurum_ip" placeholder="kurum_ip" value="<?php echo $veri['kurum_ip']; ?>">
						</div>
					<div class="form-group">
						<label for="api_kodu">Api Kod</label>
						<?php $randomCode = generateRandomCode(); ?>
						<div class="input-group mb-3">
							<input type="text" name="api_kodu" class="form-control" data-sifrele="" id="api_kodu" placeholder="api_kodu" value="<?php echo $veri['api_kodu']; ?>">
							<div class="input-group-append">
								<button type="button" class="btn btn-success" onclick="yenileApiKodu()"><i class="fa fa-retweet" aria-hidden="true"></i></button>
							</div>
						</div>
					</div>


					</div> 

				</div>
            <!-- /.card -->
            </div>
          <!--/.col (left) -->
			
			<div class="col-md-12">

				<div class="card card-primary">
					<div class="card-body">
						<div class="row">
							<div class="col-8">	
							<input type="submit" class="btn btn-primary float-left" value="Düzenle" data-sifrele="" data-form-turu="veri-duzenle" data-tablo-adi="kurumlar">	

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

          <!--/.col (right) -->
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
  
