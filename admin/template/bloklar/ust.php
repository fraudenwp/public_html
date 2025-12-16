<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $sayfa['baslik']; ?></title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="template/plugins/fontawesome-free/css/all.min.css">
  <?php if (in_array($url, $datatables)) { ?>
  <!-- DataTables -->
  <link rel="stylesheet" href="template/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="template/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="template/plugins/datatables-buttons/css/buttons.bootstrap4.min.css"> 
    <?php } ?>
	
   <?php if (in_array($url, $uyarilar)) { ?>
  <link rel="stylesheet" href="template/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
<link rel="stylesheet" href="template/plugins/toastr/toastr.min.css">
  <?php } ?>
  
  <?php if (in_array($url, $summernote)) { ?>
    <!-- summernote -->
  <link rel="stylesheet" href="template/plugins/summernote/summernote-bs4.min.css">
   <?php } ?>
   
 	<?php if (in_array($url, $select2)) { ?>
	  <!-- Select2 -->
	  <link rel="stylesheet" href="template/plugins/select2/css/select2.min.css">
	  <link rel="stylesheet" href="template/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">	
	  <!-- Bootstrap4 Duallistbox -->
	  <link rel="stylesheet" href="template/plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css">	  
	<?php } ?> 
    <link rel="stylesheet" href="template/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css">
<!-- dropzonejs -->
<script src="template/plugins/dropzone/min/dropzone.min.js"></script>

 
  <!-- Theme style -->
  <link rel="stylesheet" href="template/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="template/dist/css/yonetim.css">

</head>
<body class="hold-transition sidebar-mini">
<div id="page-overlay" style="display:none;">
  <div class="overlay">
    <i class="fas fa-2x fa-sync fa-spin"></i>
  </div>
</div>
<div class="wrapper"> 

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="https://yakutturizm.com.tr/" target="_plank" class="nav-link">Siteyi Görüntüle</a>
      </li>

    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Navbar Search -->
      <li class="nav-item">
        <a class="nav-link" data-widget="navbar-search" href="#" role="button">
          <i class="fas fa-search"></i>
        </a>
        <div class="navbar-search-block">
          <form class="form-inline">
            <div class="input-group input-group-sm">
              <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
              <div class="input-group-append">
                <button class="btn btn-navbar" type="submit">
                  <i class="fas fa-search"></i>
                </button>
                <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
          </form>
        </div>
      </li>

      <!-- Messages Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-envelope"></i>
          <?php
          $mesajlar_sorgu = $pdo->query("
              SELECT COUNT(*) as sayi FROM mesajlar
              WHERE JSON_EXTRACT(veri, '$[0].data.diller.tr.mesaj_turu') = 'İletişim'
              AND yayin_durumu = 0
          ");
          $mesaj_sayisi = $mesajlar_sorgu->fetch(PDO::FETCH_ASSOC)['sayi'];
          ?>
          <span class="badge badge-danger navbar-badge"><?php echo $mesaj_sayisi; ?></span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-header"><?php echo $mesaj_sayisi; ?> Okunmamış Mesaj</span>
          
          <?php
          $mesajlar_sorgu = $pdo->query("
              SELECT id, veri, CAST(JSON_UNQUOTE(JSON_EXTRACT(veri, '$[0].data.diller.tr.mesaj_tarih')) AS DATE) as tarih
              FROM mesajlar
              WHERE JSON_EXTRACT(veri, '$[0].data.diller.tr.mesaj_turu') = 'İletişim'
              AND yayin_durumu = 0
              ORDER BY tarih DESC
              LIMIT 3
          ");
          $mesajlar = $mesajlar_sorgu->fetchAll(PDO::FETCH_ASSOC);
          
          foreach ($mesajlar as $mesaj):
              $veri = json_decode($mesaj['veri'], true)[0]['data']['diller']['tr'];
          ?>
          <div class="dropdown-divider"></div>
          <a href="mesajlar" class="dropdown-item">
            <div class="media">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  <?php echo $veri['baslik']; ?>
                  <span class="float-right text-sm text-danger"><i class="fas fa-envelope"></i></span>
                </h3>
                <p class="text-sm"><?php echo substr($veri['konu'], 0, 50); ?>...</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> <?php echo $veri['mesaj_tarih']; ?></p>
              </div>
            </div>
          </a>
          <?php endforeach; ?>
          
          <div class="dropdown-divider"></div>
          <a href="/admin/mesajlar" class="dropdown-item dropdown-footer">Tüm Mesajları Gör</a>
        </div>
      </li>

      <!-- Information Requests Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-bell"></i>
          <?php
          $bilgi_istekleri_sorgu = $pdo->query("
              SELECT COUNT(*) as sayi FROM mesajlar
              WHERE JSON_EXTRACT(veri, '$[0].data.diller.tr.mesaj_turu') = 'Bilgi Taleb'
              AND yayin_durumu = 0
          ");
          $bilgi_istek_sayisi = $bilgi_istekleri_sorgu->fetch(PDO::FETCH_ASSOC)['sayi'];
          ?>
          <span class="badge badge-warning navbar-badge"><?php echo $bilgi_istek_sayisi; ?></span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-header"><?php echo $bilgi_istek_sayisi; ?> Bilgi Talebi</span>
          
          <?php
          $bilgi_istekleri_sorgu = $pdo->query("
              SELECT id, veri, CAST(JSON_UNQUOTE(JSON_EXTRACT(veri, '$[0].data.diller.tr.mesaj_tarih')) AS DATE) as tarih
              FROM mesajlar
              WHERE JSON_EXTRACT(veri, '$[0].data.diller.tr.mesaj_turu') = 'Bilgi Taleb'
              AND yayin_durumu = 0
              ORDER BY tarih DESC
              LIMIT 3
          ");
          $bilgi_istekleri = $bilgi_istekleri_sorgu->fetchAll(PDO::FETCH_ASSOC);
          
          foreach ($bilgi_istekleri as $istek):
              $veri = json_decode($istek['veri'], true)[0]['data']['diller']['tr'];
          ?>
          <div class="dropdown-divider"></div>
          <div class="dropdown-item">
            <div class="media">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  <?php echo $veri['baslik']; ?>
                  <span class="float-right text-sm text-warning"><i class="fas fa-info-circle"></i></span>
                </h3>
                <p class="text-sm"><?php echo substr($veri['konu'], 0, 50); ?>...</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> <?php echo $veri['mesaj_tarih']; ?></p>
              </div>
            </div>
          </div>
          <?php endforeach; ?>

          <div class="dropdown-divider"></div>
          <a href="/admin/mesajlar" class="dropdown-item dropdown-footer">Tüm Bilgi Taleplerini Gör</a>
        </div>
      </li>

      <!-- Comments Dropdown Menu -->
      <li class="nav-item dropdown">
        <div class="nav-link" data-toggle="dropdown">
          <i class="far fa-comments"></i>
          <?php
          $yorum_sorgu = $pdo->query("
              SELECT COUNT(*) as sayi FROM yorumlar
              WHERE yayin_durumu = 0
          ");
          $yorum_sayisi = $yorum_sorgu->fetch(PDO::FETCH_ASSOC)['sayi'];
          ?>
          <span class="badge badge-info navbar-badge"><?php echo $yorum_sayisi; ?></span>
        </div>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-header"><?php echo $yorum_sayisi; ?> Yeni Yorum</span>

          <?php
          $yorumlar_sorgu = $pdo->query("
              SELECT id, veri, CAST(JSON_UNQUOTE(JSON_EXTRACT(veri, '$[0].data.diller.tr.mesaj_tarih')) AS DATE) as tarih
              FROM yorumlar
              WHERE yayin_durumu = 0
              ORDER BY tarih DESC
              LIMIT 3
          ");
          $yorumlar = $yorumlar_sorgu->fetchAll(PDO::FETCH_ASSOC);
          
          foreach ($yorumlar as $yorum):
              $veri = json_decode($yorum['veri'], true)[0]['data']['diller']['tr'];
          ?>
          <div class="dropdown-divider"></div>
          <a href="yorumlar" class="dropdown-item">
            <div class="media">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  <?php echo $veri['baslik']; ?>
                  <span class="float-right text-sm text-info"><i class="fas fa-comments"></i></span>
                </h3>
                <p class="text-sm"><?php echo substr($veri['konu'], 0, 50); ?>...</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> <?php echo $veri['mesaj_tarih']; ?></p>
              </div>
            </div>
          </a>
          <?php endforeach; ?>

          <div class="dropdown-divider"></div>
          <a href="/admin/yorumlar" class="dropdown-item dropdown-footer">Tüm Yorumları Gör</a>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
      <li class="nav-item">
        <a href="logout" class="nav-link">
          <i class="fa fa-power-off"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
          <i class="fas fa-th-large"></i>
        </a>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->
  
