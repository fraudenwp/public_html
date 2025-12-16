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
								<input type="text" name="ad_soyad-tr" class="form-control" data-sifrele=""  id="ad_soyad" placeholder="İsim Soyisim">
							</div>
							<div class="form-group">
								<label for="telefon">Telefon</label>
								<input type="telefon" name="telefon" data-sifrele="aes" class="form-control" id="telefon" placeholder="Telefon">
							</div>
							<div class="form-group">
								<label for="mail">Email</label>
								<input type="mail" name="mail" data-sifrele="aes" class="form-control" id="mail" placeholder="Email">
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
								<input type="file" name="resim" multiple data-resim-tablosu="resimler" class="form-control" id="dosyaYukleInput">
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
								<input type="text" name="k_adi" data-sifrele="" class="form-control" id="k_adi" placeholder="k_adi">
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
            <!-- jquery validation -->
            <div class="card card-primary">

              <!-- /.card-header -->
              <!-- form start -->
					
						<div class="card-body">
							<input type="submit" class="btn btn-primary" value="Kaydet" data-form-turu="veri_ekle"  data-tablo-adi="kullanicilar">
						</div> 
					
            </div>
            <!-- /.card -->
            </div>			

          <!--/.col (right) -->
        </div>
		</form>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  
