			  <?php
				// Veritabanındaki kullanıcılar tablosundan verileri alın
				$query = $pdo->query("SELECT id, kod, baslik, resim FROM diller");
				$diller = $query->fetchAll(PDO::FETCH_ASSOC);
				?>

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
	  <form id="veriForm">
        <div class="row">
          <!-- left column -->
          <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Dil Ekle</small></h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
					
						<div class="card-body">
							<div class="form-group">
								<label for="baslik">Başlık</label>
								<input type="text" name="baslik-tr" class="form-control" data-sifrele="" id="baslik" placeholder="Başlık">
							</div>
					
						<div class="form-group">
								<label for="kod">Kod</label>
								<input type="text" name="kod" class="form-control" data-sifrele="" id="kod" placeholder="Kod">
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
							<div class="col-12">	
								<input type="submit" class="btn btn-primary float-left" value="Kaydet" data-form-turu="veri_ekle" data-tablo-adi="diller" >	
							</div> 					
	
						</div>						
					</div>
				</div>
			</div>	
			
            </div>			

          <!--/.col (right) -->
       
		</form>
        <!-- /.row --> </div>
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  
