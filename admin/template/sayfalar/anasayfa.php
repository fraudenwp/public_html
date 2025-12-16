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

		<section class="content">
			
			<div class="container-fluid">

				<div class="row">

				<?php
					

					// Fonksiyon: Belirli bir duruma göre paket sayısını hesapla
					function getPaketSayisi($pdo, $condition) {
						$sql = "SELECT COUNT(*) as sayi FROM paketler WHERE $condition";
						$stmt = $pdo->prepare($sql);
						$stmt->execute();
						return $stmt->fetch(PDO::FETCH_ASSOC)['sayi'];
					}

					// Aktif tur paketleri
					$aktif_tur_sayisi = getPaketSayisi($pdo, "yayin_durumu = 1 AND JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.arsivle') = '0'" );

					// Yaklaşan turlar (önümüzdeki 7 gün içinde başlayacak olanlar)		
					$yaklasan_tur_sayisi = $pdo->query("
						SELECT COUNT(*) as sayi FROM paketler 
						WHERE yayin_durumu = 1 AND JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.arsivle') = '0' 
						AND STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.tur_baslangic_tarihi')), '%Y-%m-%d')
							BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
					")->fetch(PDO::FETCH_ASSOC)['sayi'];

					// Süresi biten turlar (başlangıç tarihi geçmiş olanlar)
					$biten_tur_sayisi = $pdo->query("
						SELECT COUNT(*) as sayi FROM paketler 
						WHERE yayin_durumu = 1 AND JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.arsivle') = '0' 
						AND STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.tur_baslangic_tarihi')), '%Y-%m-%d') < CURDATE()
					")->fetch(PDO::FETCH_ASSOC)['sayi'];

					// Arşive alınmış tur paketleri
					$arsiv_tur_sayisi = $pdo->query("
						SELECT COUNT(*) as sayi FROM paketler 
						WHERE JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.arsivle') = '1'
					")->fetch(PDO::FETCH_ASSOC)['sayi'];
				
				
				?>

				<!-- HTML kısmı -->
				<div class="col-lg-3 col-6">
					<div class="small-box bg-info">
						<div class="inner">
							<h3><?php echo $aktif_tur_sayisi; ?></h3>
							<p>Toplam Aktif Tur Paketi</p>
						</div>
						<div class="icon">
							<i class="fas fa-suitcase"></i>
						</div>
					</div>
				</div>
				<div class="col-lg-3 col-6">
					<div class="small-box bg-success">
						<div class="inner">
							<h3><?php echo $yaklasan_tur_sayisi; ?></h3>
							<p>Süresi Yaklaşan Tur Paketleri</p>
						</div>
						<div class="icon">
							<i class="fas fa-hourglass-half"></i>
						</div>
					</div>
				</div>
				<div class="col-lg-3 col-6">
					<div class="small-box bg-warning">
						<div class="inner">
							<h3><?php echo $biten_tur_sayisi; ?></h3>
							<p>Süresi Biten Tur Paketleri</p>
						</div>
						<div class="icon">
							<i class="fas fa-calendar-times"></i>
						</div>
					</div>
				</div>
				<div class="col-lg-3 col-6">
					<div class="small-box bg-danger">
						<div class="inner">
							<h3><?php echo $arsiv_tur_sayisi; ?></h3>
							<p>Arşive Alınmış Tur Paketleri</p>
						</div>
						<div class="icon">
							<i class="fas fa-archive"></i>
						</div>
					</div>
				</div>
								
				</div>
				<div class="row">
				
<?php
// Veritabanı bağlantısı
require_once 'config.php';

// Son gelen okunmayan mesajlar
$mesajlar_sorgu = $pdo->query("
    SELECT id, veri, yayin_durumu, CAST(JSON_UNQUOTE(JSON_EXTRACT(veri, '$[0].data.diller.tr.mesaj_tarih')) AS DATE) as tarih
    FROM mesajlar
    WHERE JSON_EXTRACT(veri, '$[0].data.diller.tr.mesaj_turu') = 'İletişim'
    AND yayin_durumu = 0
    ORDER BY tarih DESC
    LIMIT 10
");
$mesajlar = $mesajlar_sorgu->fetchAll(PDO::FETCH_ASSOC);

// Okunmayan bilgi istekleri
$bilgi_istekleri_sorgu = $pdo->query("
    SELECT id, veri, yayin_durumu, CAST(JSON_UNQUOTE(JSON_EXTRACT(veri, '$[0].data.diller.tr.mesaj_tarih')) AS DATE) as tarih
    FROM mesajlar
    WHERE JSON_EXTRACT(veri, '$[0].data.diller.tr.mesaj_turu') = 'Bilgi Taleb'
    AND yayin_durumu = 0
    ORDER BY tarih DESC
    LIMIT 10
");
$bilgi_istekleri = $bilgi_istekleri_sorgu->fetchAll(PDO::FETCH_ASSOC);

// Son yapılan yorumlar
$yorumlar_sorgu = $pdo->query("
    SELECT id, veri, yayin_durumu, CAST(JSON_UNQUOTE(JSON_EXTRACT(veri, '$[0].data.diller.tr.mesaj_tarih')) AS DATE) as tarih
    FROM yorumlar WHERE yayin_durumu = 0
    ORDER BY tarih DESC
    LIMIT 10
");
$yorumlar = $yorumlar_sorgu->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- HTML kısmı -->
<div class="col-4">
    <div class="card">
        <div class="card-header border-transparent">
            <h3 class="card-title">Son Gelen Okunmayan Mesajlar</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table m-0">
                    <thead>
                        <tr>
                            <th>Tarih</th>
                            <th>Gönderen</th>
                            <th>Konu</th>
                            <th>Durum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mesajlar as $mesaj): 
                            $veri = json_decode($mesaj['veri'], true)[0]['data']['diller']['tr'];
                        ?>
                        <tr>
                            <td><?php echo $veri['mesaj_tarih']; ?></td>
                            <td><?php echo $veri['baslik']; ?></td>
                            <td><?php echo $veri['konu']; ?></td>
                            <td><span class="badge badge-danger">Okunmadı</span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer clearfix">
            <a href="<?php echo $baseurl; ?>mesajlar" class="btn btn-sm btn-info float-left">Tümünü Listele</a>
        </div>
    </div>
</div>

<div class="col-4">
    <div class="card">
        <div class="card-header border-transparent">
            <h3 class="card-title">Okunmayan Bilgi İstekleri</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table m-0">
                    <thead>
                        <tr>
                            <th>Tarih</th>
                            <th>Gönderen</th>
                            <th>Durum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bilgi_istekleri as $istek): 
                            $veri = json_decode($istek['veri'], true)[0]['data']['diller']['tr'];
                        ?>
                        <tr>
                            <td><?php echo $veri['mesaj_tarih']; ?></td>
                            <td><?php echo $veri['baslik']; ?></td>
                            <td><span class="badge badge-danger">Okunmadı</span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer clearfix">
            <a href="<?php echo $baseurl; ?>mesajlar" class="btn btn-sm btn-info float-left">Tüm Mesajları Listele</a>
        </div>
    </div>
</div>

<div class="col-4">
    <div class="card">
        <div class="card-header border-transparent">
            <h3 class="card-title">Son Yapılan Yorumlar</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table m-0">
                    <thead>
                        <tr>
                            <th>Tarih</th>
                            <th>Gönderen</th>
                            <th>Durum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($yorumlar as $yorum): 
                            $veri = json_decode($yorum['veri'], true)[0]['data']['diller']['tr'];
                        ?>
                        <tr>
                            <td><?php echo $veri['mesaj_tarih']; ?></td>
                            <td><?php echo $veri['baslik']; ?></td>
                            <td>
                                <?php if ($yorum['yayin_durumu'] == 1): ?>
                                    <span class="badge badge-success">Yayında</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Yayında Değil</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer clearfix">
            <a href="<?php echo $baseurl; ?>yorumlar" class="btn btn-sm btn-info float-left">Tüm Yorumları Listele</a>
        </div>
    </div>
</div>
						
								</div>


			</div>
			
		</section>


			<!-- Main content -->
    <div class="content">
	
      <div class="container-fluid">
		<div class="col-md-12" id="tema-genelAyarlar">  </div>
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  
