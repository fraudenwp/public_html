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
			  <?php
				// Veritabanındaki kullanıcılar tablosundan verileri alın
				$query = $pdo->query("SELECT id, ad_soyad, mail, telefon, yetki, yayin_durumu FROM kullanicilar");
				$kullanicilar = $query->fetchAll(PDO::FETCH_ASSOC);
				?>

					<table id="example1" class="table table-bordered table-striped">
					  <thead>
						<tr>
						  <th style="width: 1px;"><input type="checkbox" id="selectAll"></th>
						  <th data-orderable="true">İsim</th>
						  <th>Mail</th>
						  <th>Telefon</th>
						  <th>Yetki</th>
						  <th>Durum</th> 
						</tr>
					  </thead>
					  <tbody>
						<?php foreach ($kullanicilar as $kullanici): ?>
						  <tr>
							<td><input type="checkbox" class="selectSingle"></td>
							<td><a href="kullanici-duzenle?list=kullanicilar&id=<?php echo $kullanici['id']; ?>"><?php echo $kullanici['ad_soyad']; ?></a></td>
							<td><?php echo aes_coz(base64_decode($kullanici['mail'])); ?></td>
							<td><?php echo aes_coz(base64_decode($kullanici['telefon'])); ?></td>
							<td><?php echo $kullanici['yetki']; ?></td>
							<td class="text-start">
							<div class="custom-control custom-switch custom-switch-on-success float-right">
								<input name="yayin_durumu" type="checkbox" class="custom-control-input" data-veri-tablosu="kullanicilar"  id="yayin_durumu-<?php echo $kullanici['id']; ?>" value="1" <?php echo ($kullanici['yayin_durumu'] == 1) ? 'checked' : ''; ?>>
								<label class="custom-control-label" for="yayin_durumu-<?php echo $kullanici['id']; ?>"><?php echo ($kullanici['yayin_durumu'] == 1) ? '' : ''; ?></label>
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
  
