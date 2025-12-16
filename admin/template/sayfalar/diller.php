<?php
// Ürünler tablosundan verileri alın
$query = $pdo->query("SELECT id, kod, baslik, COALESCE(NULLIF(resim, ''), 'resimler/resim-yok.jpg') AS resim FROM diller");
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
					  <th>Kod</th>
					</tr>
				  </thead>
				  <tbody>
					<?php foreach ($diller as $dil): ?>
					  <tr>
						<td><img src="<?php echo resizeImage($dil['resim'], 'resimler/onbellek/bayrak', 40, 40); ?>" alt="<?php echo $dil['baslik']; ?>" class="img-thumbnail"></td>
						<td><a href="dil-duzenle?list=diller&id=<?php echo $dil['id']; ?>"><?php echo $dil['baslik']; ?></a></td>
						<td><?php echo $dil['kod']; ?></td>
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
  
  


