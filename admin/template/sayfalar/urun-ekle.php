<?php

$query = $pdo->query("SELECT id, yayin_durumu, baslik FROM kategoriler"); $kategoriler = $query->fetchAll(PDO::FETCH_ASSOC);

$query = $pdo->query("SELECT id, kod, baslik, varsayilan, COALESCE(NULLIF(resim, ''), 'resimler/resim-yok.jpg') AS resim FROM diller"); $diller = $query->fetchAll(PDO::FETCH_ASSOC);
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
	  
		<div class="card card-primary card-outline card-outline-tabs">			
			<div class="card-header p-0 border-bottom-0">
				<ul class="nav nav-tabs" id="custom-tabs-four-tab" role="tablist">
					<?php foreach ($diller as $dil): ?>
						<li class="nav-item">
							<a class="nav-link <?php echo $dil['varsayilan'] == 1 ? 'active' : ''; ?>" id="custom-tabs-<?php echo $dil['id']; ?>-tab" data-toggle="pill" href="#custom-tabs-<?php echo $dil['id']; ?>" role="tab" aria-controls="custom-tabs-<?php echo $dil['id']; ?>" aria-selected="<?php echo $dil['varsayilan'] == 1 ? 'true' : 'false'; ?>">
							<img src="<?php echo resizeImage($dil['resim'], 'resimler/onbellek/bayrak', 40, 40); ?>" alt="<?php echo $dil['baslik']; ?>" class="img-thumbnail">
							<?php echo $dil['baslik']; ?></a>
						</li>
					<?php endforeach; ?>	
				</ul>
			</div>

		</div>
		
										<div class="form-group">
									<label>Kategori Seç</label>
									<select class="custom-select" name="kat_id">									
										<option value="0" selected="selected">Seç</option>
											<?php foreach ($kategoriler as $kategori): ?>
												<option value="<?php echo $kategori['id']; ?>"><?php echo $kategori['baslik']; ?></option>
											<?php endforeach; ?> 						
									</select>
								</div>
								
							<div class="form-group">
								<label for="resim">Resim</label>
								<input type="file" name="resim"  data-resim-tablosu="kategori_resim" data-hedef-dizin="resimler/kategoriler" data-resim-en="100" data-resim-boy="100" data-resim-kalite="75" class="form-control" id="dosyaYukleInput" multiple>
							</div>								
		
		<div class="tab-content" id="custom-tabs-four-tabContent">
	  <?php foreach ($diller as $dil): ?>
	  	<div class="tab-pane fade <?php echo $dil['varsayilan'] == 1 ? 'active show' : ''; ?>" id="custom-tabs-<?php echo $dil['id']; ?>" role="tabpanel" aria-labelledby="custom-tabs-<?php echo $dil['id']; ?>-tab">

        <div class="row">
          <!-- left column -->
          <div class="col-md-6">
            <!-- jquery validation -->
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Kategori Ekle <?php echo $dil['baslik']; ?></small></h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
					
						<div class="card-body">
							<div class="form-group">
								<label for="baslik">Başlık</label>
								<input type="text" name="baslik-<?php echo $dil['kod']; ?>" class="form-control" data-sifrele="" id="baslik" placeholder="Başlık">
								
							</div>

							<div class="form-group">
								<label for="aciklama">Açıklama</label>
								<input type="text" name="aciklama-<?php echo $dil['kod']; ?>" class="form-control" data-sifrele="" id="aciklama" placeholder="Açıklama">
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
					<h3 class="card-title">Arama Motoru Bilgileri</small></h3>
				  </div>
				  <!-- /.card-header -->
				  <!-- form start -->
						
							<div class="card-body">
								<div class="form-group">
									<label for="meta_baslik">Meta Başlık</label>
									<input type="text" name="meta_baslik-<?php echo $dil['kod']; ?>" class="form-control" data-sifrele="" id="meta_baslik" placeholder="Meta Başlık">
								</div>

								<div class="form-group">
									<label for="meta_aciklama">Meta Açıklama</label>
									<input type="text" name="meta_aciklama-<?php echo $dil['kod']; ?>" class="form-control" data-sifrele="" id="meta_aciklama" placeholder="Meta Açıklama">
								</div>
								<div class="form-group">
									<label for="link">Seo Link</label>
									<input type="text" name="link-<?php echo $dil['kod']; ?>" class="form-control" id="link" data-sifrele="" placeholder="Seo Link">
								</div>
								
							<div class="form-group">
									<label for="etiketler">Etiketler</label>
									<input type="text" name="etiketler-<?php echo $dil['kod']; ?>" class="form-control" data-sifrele="" id="etiketler" placeholder="Etiketler (Ör. Kalem, şapka, saat">
								</div>								

						
							</div> 
							<!-- /.card-body -->

				</div>
          
            </div>
          <!--/.col (left) -->

        </div>
        </div>
		<?php endforeach; ?>
		</div>
		
		<div class="row">
					
			<div class="col-md-12">

				<div class="card card-primary">
					<div class="card-body">
						<div class="row">
							<div class="col-8">	
								<input type="submit" class="btn btn-primary float-left" value="Kaydet" data-sifrele="" data-form-turu="veri_ekle" data-tablo-adi="urunler">	
							</div> 					
							<div class="col-4">								
								
								<div class="custom-control custom-switch custom-switch-on-success float-right">
									<input name="yayin_durumu" type="checkbox" class="custom-control-input" id="yayin_durumu" value="1">
									 <label class="custom-control-label" for="yayin_durumu">Yayın Durumu</label>
								</div>								
							</div>	
						</div>						
					</div>
				</div>
			</div>	
			

		</div>
		
		</form>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  
