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
          <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Dil Düzenle</small></h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
					
						<div class="card-body">
							<div class="form-group">
								<label for="baslik">Başlık</label>
								<input type="text" name="baslik-<?php echo $veri['dil']; ?>" class="form-control" data-sifrele="" id="baslik" placeholder="Başlık" value="<?php echo $veri['baslik']; ?>">
								<input type="hidden" name="id-<?php echo $veri['dil']; ?>" class="form-control" data-sifrele=""  id="id" value="<?php echo $veri['id']; ?>">
							</div>
					
						<div class="form-group">
								<label for="kod">Kod</label>
								<input type="text" name="kod" class="form-control" data-sifrele="" id="kod" placeholder="Kod" value="<?php echo $veri['kod']; ?>">
							</div>


							<div class="form-group">
								<label for="resim">Resim</label>
								<input type="file" name="resim"  data-resim-tablosu="diller" data-hedef-dizin="resimler/bayrak" data-resim-en="100" data-resim-boy="100" data-resim-kalite="75" class="form-control" id="dosyaYukleInput" multiple>
							</div>							

						</div> 
						<!-- /.card-body -->

            </div>
            <!-- /.card -->
            </div>
          <!--/.col (left) -->

          <!-- left column -->

          <!--/.col (left) -->
			
			<div class="col-md-12">
				<div class="card card-primary">
					<div class="card-body">
						<div class="row">
							<div class="col-8">	
								<input type="submit" class="btn btn-primary float-left" value="Düzenle" data-form-turu="veri-duzenle" data-tablo-adi="diller">
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
       
		</form>
			<?php

				} else {
					echo "Kayıt bulunamadı.";
				}
			} else {
				echo "Geçersiz parametreler.";
			}
			 ?>
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  
