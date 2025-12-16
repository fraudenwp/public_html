  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="/admin" class="brand-link">
     
      <span class="brand-text font-weight-light"><b>Yakut Turizm</b></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        
        <div class="info">
          <a href="#" class="d-block">Name : <?php echo $_SESSION['ad_soyad'];?></a>
          <a href="#" class="d-block">Language: <?php echo $user_dil;?></a>
          
        </div>
      </div>



<!-- Sidebar Menu -->
<nav class="mt-2">
  <?php
  // Önce grup adlarını al
  $grupQuery = $pdo->query("SELECT DISTINCT gurup_adi FROM sayfalar WHERE sol_menu != 0 AND gurup_adi IS NOT NULL ORDER BY sira ASC");
  $gruplar = $grupQuery->fetchAll(PDO::FETCH_ASSOC);
  ?>
  <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
    <?php
    // Önce grupları döngüye al
    foreach ($gruplar as $grup):
      // Her grup için o gruba ait sayfaları çek
      $sayfaQuery = $pdo->prepare("SELECT baslik, link FROM sayfalar WHERE sol_menu != 0 AND gurup_adi = ? ORDER BY sira ASC");
      $sayfaQuery->execute([$grup['gurup_adi']]);
      $sayfalar = $sayfaQuery->fetchAll(PDO::FETCH_ASSOC);
    ?>
      <li class="nav-item has-treeview">
        <a href="#" class="nav-link">
          <i class="nav-icon fas fa-folder"></i>
          <p>
            <?php echo $grup['gurup_adi']; ?>
            <i class="fas fa-angle-left right"></i>
          </p>
        </a>
        <ul class="nav nav-treeview">
          <?php foreach ($sayfalar as $sayfa): ?>
            <li class="nav-item">
              <a href="<?php echo $sayfa['link']; ?>" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p><?php echo $sayfa['baslik']; ?></p>
              </a>
            </li>
          <?php endforeach; ?>
        </ul> 
      </li>
    <?php endforeach; ?>

    <?php
    // Grubu olmayan sayfaları listele
    $tekSayfaQuery = $pdo->query("SELECT baslik, link FROM sayfalar WHERE sol_menu != 0 AND (gurup_adi IS NULL OR gurup_adi = '') ORDER BY sira ASC");
    $tekSayfalar = $tekSayfaQuery->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($tekSayfalar as $sayfa):
    ?>
      <li class="nav-item">
        <a href="<?php echo $sayfa['link']; ?>" class="nav-link">
          <i class="nav-icon fas fa-th"></i>
          <p><?php echo $sayfa['baslik']; ?></p>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</nav>
			  

		  
			  
			</ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>
