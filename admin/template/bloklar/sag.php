<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
  <!-- Control sidebar content -->
  <div class="p-3">
    <h5 class="text-light">Sistem Bildirimleri</h5>
    <?php
    $bugun = date('Y-m-d');
    
    // Son arşivleme bilgilerini veritabanından al
    $kontrol_query = "SELECT * FROM ayarlar WHERE anahtar IN ('son_arsiv_kontrol', 'son_arsiv_sayisi')";
    $stmt = $pdo->prepare($kontrol_query);
    $stmt->execute();
    $sonuclar = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    if (isset($sonuclar['son_arsiv_kontrol']) && $sonuclar['son_arsiv_kontrol'] == $bugun) {
        echo '<div class="alert alert-info">';
        echo '<i class="fas fa-archive mr-2"></i>';
        echo 'Bugün yapılan arşiv kontrolünde ';
        if (isset($sonuclar['son_arsiv_sayisi']) && $sonuclar['son_arsiv_sayisi'] > 0) {
            echo '<strong>' . $sonuclar['son_arsiv_sayisi'] . '</strong> tur arşivlendi.';
        } else {
            echo 'arşivlenecek tur bulunamadı.';
        }
        echo '</div>';
    } else {
        echo '<div class="alert alert-warning">';
        echo '<i class="fas fa-exclamation-triangle mr-2"></i>';
        echo 'Bugün henüz arşiv kontrolü yapılmadı.';
        echo '</div>';
    }
    ?>
  </div>
</aside>
<!-- /.control-sidebar -->