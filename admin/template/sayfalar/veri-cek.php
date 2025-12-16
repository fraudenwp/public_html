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
<?php
try {
    // Kategoriler tablosundaki grup sütununda farklı verilere sahip olanların sayısını bul
    $stmt = $pdo->query("SELECT COUNT(DISTINCT grup) AS kategori_grup_sayisi FROM kategoriler");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $kategori_grup_sayisi = $row['kategori_grup_sayisi'];
      
    } else {
        //echo "Kayıt bulunamadı.";
    }
} catch (PDOException $e) {
   // echo "Veritabanı hatası: " . $e->getMessage();
}
?>

<?php
try {
    // Kategoriler tablosundaki grup sütununda farklı verilere sahip olanların sayısını bul
    $stmt = $pdo->query("SELECT COUNT(DISTINCT grup) AS urun_grup_sayisi FROM urunler");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $urunler_grup_sayisi = $row['urun_grup_sayisi'];
      
    } else {
        //echo "Kayıt bulunamadı.";
    }
} catch (PDOException $e) {
   // echo "Veritabanı hatası: " . $e->getMessage();
}
?>
    <!-- Main content -->
    <div class="content">
	
		<div class="container-fluid">
			<div class="row">  
<!-- KATEGORİLER APİ İSTEĞİ-->
<div class="col-12">    
    <div id="kategoriler-card" class="card card-success">
        <div class="card-header">
            <h3 class="card-title">Kategorileri Çek</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-10">
                    <div class="progress" style="height: 38px;border-radius: 9px;">
                        <div class="progress-bar bg-primary progress-bar-striped kontrol-et-bar" role="progressbar"
                             aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                            <span class="sr-only">0% Tamamlandı</span>
                            <span class="progress-bar-text">0% Tamamlandı</span>
                        </div>
                    </div>                    
                </div>
                <div class="col-2">
                    <button class="btn btn-block btn-primary float-left kategori-kontrol" data-veritabani="kategoriler" data-veritabani-resim="kategori_resim" data-toplamsatir="<?php echo $kategori_grup_sayisi;?>"> Bağlan </button>                   
                </div>
                <div class="col-12" id="veriCek-kategorilerDiv" style="display: none;">
                    <div class="callout callout-danger" style="margin-bottom: 0px; margin-top: 15px;">
                        <table class="table table-sm">
                            <thead>
                                <tr>                                    
                                    <th style="width: 25%">İşlem Adı</th>
                                    <th style="width: 10%">Toplam Veri</th>
                                    <th>İlerleme</th>
                                    <th style="width: 200px">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tüm Kategorileri Kaydet</td>
                                    <td id="tum-verisayisi">0 Adet</td>
                                    <td>
                                        <div class="progress" style="height: 23px;">
                                          <div class="progress-bar bg-success progress-bar-striped tumkategori-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                                            <span class="sr-only">0% Tamamlandı</span>
                                            <span class="progress-bar-tumkategorilertext">0% Tamamlandı</span>
                                          </div>
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-block btn-success btn-xs" data-veri="tum-veriler" data-table-veri="kategoriler" data-table-resim="kategori-resim">Kayıt Et</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Yeni Kategorileri Kaydet</td>
                                    <td id="yeni-verisayisi">0 Adet</td>
                                    <td>
                                        <div class="progress" style="height: 23px;">
                                          <div class="progress-bar bg-warning progress-bar-striped yenikategori-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                                            <span class="sr-only">0% Tamamlandı</span>
                                            <span class="progress-bar-yenikategorilertext">0% Tamamlandı</span>
                                          </div>
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-block btn-warning btn-xs" data-veri="yeni-veriler" data-table-veri="kategoriler" data-table-resim="kategori-resim">Kayıt Et</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>                   
                    </div>                        
                </div>
            </div>
        </div>
    </div>
</div>   

<!-- ÜRÜNLER APİ İSTEĞİ-->
<div class="col-12">    
    <div id="urunler-card" class="card card-success">
        <div class="card-header">
            <h3 class="card-title">Ürünleri Çek</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-10">
                    <div class="progress" style="height: 38px;border-radius: 9px;">
                        <div class="progress-bar bg-primary progress-bar-striped kontrol-et-bar" role="progressbar"
                             aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                            <span class="sr-only">0% Tamamlandı</span>
                            <span class="progress-bar-text">0% Tamamlandı</span>
                        </div>
                    </div>                    
                </div>
                <div class="col-2">
                    <button class="btn btn-block btn-primary float-left urun-kontrol" data-veritabani="urunler" data-veritabani-resim="urun_resim" data-toplamsatir="<?php echo $urunler_grup_sayisi;?>"> Bağlan </button>                   
                </div>
                <div class="col-12" id="veriCek-urunlerDiv" style="display: none;">
                    <div class="callout callout-danger" style="margin-bottom: 0px; margin-top: 15px;">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th style="width: 25%">İşlem Adı</th>
                                    <th style="width: 10%">Toplam Veri</th>
                                    <th>İlerleme</th>
                                    <th style="width: 200px">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Tüm Ürünleri Kaydet</td>
                                    <td id="tum-verisayisi">0 Adet</td>
                                    <td>
                                        <div class="progress" style="height: 23px;">
                                          <div class="progress-bar bg-success progress-bar-striped tumurun-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                                            <span class="sr-only">0% Tamamlandı</span>
                                            <span class="progress-bar-tumuruntext">0% Tamamlandı</span>
                                          </div>
                                        </div> 
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-block btn-success btn-xs" data-veri="tum-veriler" data-table-veri="urunler" data-table-resim="urun-resim">Kayıt Et</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Yeni Ürünleri Kaydet</td>
                                    <td id="yeni-verisayisi">0 Adet</td>
                                    <td>
                                        <div class="progress" style="height: 23px;">
                                          <div class="progress-bar bg-warning progress-bar-striped yeniurun-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                                            <span class="progress-bar-yeniuruntext">0% Tamamlandı</span>
                                            <span class="sr-only">0% Tamamlandı</span>
                                          </div>
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-block btn-warning btn-xs" data-veri="yeni-veriler" data-table-veri="urunler" data-table-resim="urun-resim">Kayıt Et</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>                   
                    </div>                        
                </div>
            </div>
        </div>
    </div>
</div>
			
			
			
			
			</div>
		</div>
          <!-- /.col -->
    </div>
        <!-- /.row -->	  

</div><!-- /.container-fluid -->

