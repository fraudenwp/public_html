  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Bir Hata Var</h1>
          </div>
          <div class="col-sm-6">
		
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?php echo $baseurl; ?>">Gösterge Paneli</a></li>
              <li class="breadcrumb-item active">Hata!</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="error-page">
       

        <div class="error-content">
          <h3><i class="fas fa-exclamation-triangle text-danger"></i> Oops!  <?php echo $hata_mesaji; ?></h3>

          <p>
            <?php echo $aciklama; ?>
          </p>

        </div>
      </div>
      <!-- /.error-page -->

    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  