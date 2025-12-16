			  <?php
				// Veritabanındaki kurumlar tablosundan verileri alın
				$query = $pdo->query("SELECT id, yayin_durumu, yetkili_adi, yetkili_telefon, alan_adi, yayin_durumu FROM kurumlar");
				$kurumlar = $query->fetchAll(PDO::FETCH_ASSOC);
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
					  <th>Alan Adı</th>
					  <th>Yetkili</th>
					  <th>Telefon</th>

					  <th>Durum</th>

					</tr>
				  </thead>
				  <tbody>
					<?php foreach ($kurumlar as $kurum): ?>
					  <tr>
					  	<td><a href="kurum-duzenle?list=kurumlar&id=<?php echo $kurum['id']; ?>"><?php echo $kurum['alan_adi']; ?></td>
						<td><?php echo $kurum['yetkili_adi']; ?></td>
						<td><?php echo $kurum['yetkili_telefon']; ?></td>

						<td>
							<div class="custom-control custom-switch custom-switch-on-success float-right">
								<input name="yayin_durumu" type="checkbox" class="custom-control-input" data-veri-tablosu="kurumlar"  id="yayin_durumu-<?php echo $kurum['id']; ?>" value="1" <?php echo ($kurum['yayin_durumu'] == 1) ? 'checked' : ''; ?>>
								<label class="custom-control-label" for="yayin_durumu-<?php echo $kurum['id']; ?>"><?php echo ($kurum['yayin_durumu'] == 1) ? '' : ''; ?></label>
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
  
