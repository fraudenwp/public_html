<?php
$query = $pdo->prepare("
    SELECT u.id, u.urun_kodu, u.baslik, u.yayin_durumu, u.kat_id, u.grup, COALESCE(NULLIF(r.resim, ''), 'resimler/resim-yok.jpg') AS resim
    FROM urunler u
    LEFT JOIN urun_resim r ON u.id = r.sayfa_id
    WHERE 
        u.dil = :user_dil OR
        u.grup NOT IN (
            SELECT grup
            FROM urunler
            WHERE dil = :user_dil
        )
");

$query->execute(['user_dil' => $user_dil]);
$urunler = $query->fetchAll(PDO::FETCH_ASSOC);


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
        <div class="row">
          <div class="col-12">	  
	  
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">DataTable with default features</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">


				<table id="example1" class="table table-bordered table-striped">
				  <thead>
					<tr>
					  <th>Resim</th>
					  <th>Başlık</th>
					  <th>Ürün Kodu</th>
					  <th>Durum</th>
					</tr>
				  </thead>
				  <tbody>
					<?php foreach ($urunler as $urun): 

					?>

					  <tr>
						<td class="text-center"><img src="<?php echo resizeImage($urun['resim'], 'resimler/onbellek/urunler', 40, 40); ?>" alt="<?php echo $urun['baslik']; ?>" class="img-thumbnail"></td>
						<td class="text-start"><a href="urun-duzenle?list=urunler&id=<?php echo $urun['grup']; ?>"><?php echo $urun['baslik']; ?></a><br><small class="text-success">Kategori: </small><small><?php echo $urun['kat_id']; ?></small></td>
						<td class="text-start"><?php echo $urun['urun_kodu']; ?></td>
						
						<td class="text-start">
							<div class="custom-control custom-switch custom-switch-on-success float-right">
								<input name="yayin_durumu" type="checkbox" class="custom-control-input" data-veri-tablosu="urunler"  id="yayin_durumu-<?php echo $urun['grup']; ?>" value="1" <?php echo ($urun['yayin_durumu'] == 1) ? 'checked' : ''; ?>>
								<label class="custom-control-label" for="yayin_durumu-<?php echo $urun['grup']; ?>"><?php echo ($urun['yayin_durumu'] == 1) ? '' : ''; ?></label>
							</div>
						</td>
					  </tr>
					<?php endforeach; ?>
				  </tbody>
				</table>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->	  
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->	  

      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  
