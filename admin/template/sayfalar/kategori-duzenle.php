<?php

$query = $pdo->query("SELECT id, yayin_durumu, baslik, grup FROM kategoriler"); $kategori_list = $query->fetchAll(PDO::FETCH_ASSOC);

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
	  
	  			  <?php
				  
				// GET parametrelerini al
				$table = $_GET['list'] ?? null;
				$id = $_GET['id'] ?? null;
				// GET parametrelerini al
			if ($table && $id) {
				function getOrtakVeriler($grup) {
					global $pdo;
					$query = $pdo->prepare("SELECT kat_id, yayin_durumu FROM kategoriler WHERE grup = :grup LIMIT 1");
					$query->execute(['grup' => $grup]);
					return $query->fetch(PDO::FETCH_ASSOC);
				}

				$ortakVeriler = getOrtakVeriler($id);
				$veri = getVeri($table, $id);

				if ($veri) {
						

				?>

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
    <label>Alt Kategori Olacaksa Seç</label>
    <select class="custom-select" name="kat_id">                                    
        <option value="">Seç</option>
        <?php foreach ($kategori_list as $kategori): ?>
            <option value="<?php echo $kategori['grup']; ?>" <?php echo ($ortakVeriler['kat_id'] == $kategori['id']) ? 'selected' : ''; ?>>
                <?php echo $kategori['baslik']; ?>
            </option>
        <?php endforeach; ?>                        
    </select>
</div>
		
				<input type="hidden" name="grup" class="form-control" data-sifrele="" id="grup" placeholder="grup" value="<?php echo htmlspecialchars($veri['grup'] ?? ''); ?>">

								
		<div class="form-group">
			<label for="resim">Resim</label>
				<input type="file" name="resim"  data-resim-tablosu="kategori_resim" data-hedef-dizin="resimler/kategoriler" data-resim-en="100" data-resim-boy="100" data-resim-kalite="75" class="form-control" id="dosyaYukleInput" multiple>
		</div>								
		
		<div class="tab-content" id="custom-tabs-four-tabContent">
			<?php
			// Tüm diller için verileri bir kerede çekelim
			$query = $pdo->prepare("SELECT * FROM $table WHERE grup = :id");
			$query->execute(['id' => $id]);
			$tum_veriler = $query->fetchAll(PDO::FETCH_ASSOC);

			// Verileri dillere göre gruplayalım
			$dil_verileri = [];
			foreach ($tum_veriler as $veri) {
				$dil_verileri[$veri['dil']] = $veri;
			}

			// Dil sekmelerini oluşturalım
			foreach ($diller as $dil): 
				$veri = $dil_verileri[$dil['kod']] ?? [];
			?>
				<div class="tab-pane fade <?php echo $dil['varsayilan'] == 1 ? 'active show' : ''; ?>" id="custom-tabs-<?php echo $dil['id']; ?>" role="tabpanel" aria-labelledby="custom-tabs-<?php echo $dil['id']; ?>-tab">
					<div class="row">
						<div class="col-md-6">
							<div class="card card-primary">
								<div class="card-header">
									<h3 class="card-title">Kategori Ekle <?php echo $dil['baslik']; ?></h3>
								</div>
								<div class="card-body">
									<div class="form-group">
										<label for="baslik-<?php echo $dil['kod']; ?>">Başlık</label>
										<input type="text" name="baslik-<?php echo $dil['kod']; ?>" class="form-control" data-sifrele="" id="baslik-<?php echo $dil['kod']; ?>" placeholder="Başlık" value="<?php echo htmlspecialchars($veri['baslik'] ?? ''); ?>">
										<input type="hidden" name="id-<?php echo $dil['kod']; ?>" class="form-control" data-sifrele="" id="id-<?php echo $dil['kod']; ?>" placeholder="Başlık" value="<?php echo htmlspecialchars($veri['id'] ?? ''); ?>">
									</div>
									<div class="form-group">
										<label for="aciklama-<?php echo $dil['kod']; ?>">Açıklama</label>
										<input type="text" name="aciklama-<?php echo $dil['kod']; ?>" class="form-control" data-sifrele="" id="aciklama-<?php echo $dil['kod']; ?>" placeholder="Açıklama" value="<?php echo htmlspecialchars($veri['aciklama'] ?? ''); ?>">
									</div>
								</div>
							</div>
						</div> 
						<div class="col-md-6">
							<div class="card card-primary">
								<div class="card-header">
									<h3 class="card-title">Arama Motoru Bilgileri</h3>
								</div>
								<div class="card-body">
									<div class="form-group">
										<label for="meta_baslik-<?php echo $dil['kod']; ?>">Meta Başlık</label>
										<input type="text" name="meta_baslik-<?php echo $dil['kod']; ?>" class="form-control" data-sifrele="" id="meta_baslik-<?php echo $dil['kod']; ?>" placeholder="Meta Başlık" value="<?php echo htmlspecialchars($veri['meta_baslik'] ?? ''); ?>">
									</div>
									<div class="form-group">
										<label for="meta_aciklama-<?php echo $dil['kod']; ?>">Meta Açıklama</label>
										<input type="text" name="meta_aciklama-<?php echo $dil['kod']; ?>" class="form-control" data-sifrele="" id="meta_aciklama-<?php echo $dil['kod']; ?>" placeholder="Meta Açıklama" value="<?php echo htmlspecialchars($veri['meta_aciklama'] ?? ''); ?>">
									</div>
									<div class="form-group">
										<label for="link-<?php echo $dil['kod']; ?>">Seo Link</label>
										<input type="text" name="link-<?php echo $dil['kod']; ?>" class="form-control" id="link-<?php echo $dil['kod']; ?>" data-sifrele="" placeholder="Seo Link" value="<?php echo htmlspecialchars($veri['link'] ?? ''); ?>">
									</div>
									<div class="form-group">
										<label for="etiketler-<?php echo $dil['kod']; ?>">Etiketler</label>
										<input type="text" name="etiketler-<?php echo $dil['kod']; ?>" class="form-control" data-sifrele="" id="etiketler-<?php echo $dil['kod']; ?>" placeholder="Etiketler (Ör. Kalem, şapka, saat)" value="<?php echo htmlspecialchars($veri['etiketler'] ?? ''); ?>">
									</div>
								</div>
							</div>
						</div>
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
									<input type="submit" class="btn btn-primary float-left" value="Düzenle" data-sifrele="" data-form-turu="veri-duzenle" data-tablo-adi="kategoriler" data-sifrele-aes="aes" data-sifrele-md5="md5">	
							</div> 					
							<div class="col-4">								
								
					<div class="custom-control custom-switch custom-switch-on-success float-right">
						<input name="yayin_durumu" type="checkbox" class="custom-control-input" id="yayin_durumu" value="1" <?php echo ($ortakVeriler['yayin_durumu'] == 1) ? 'checked' : ''; ?>>
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
  
