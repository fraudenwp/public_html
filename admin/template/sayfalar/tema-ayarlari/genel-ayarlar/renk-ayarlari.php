<!-- veri-listele.php -->
<?php
// Include config.php to connect to the database
include '../../../../config.php';

// Gelen JSON verisini al ve decode et
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

// Verileri değişkenlere ata ve güvenlik kontrolü yap
$tablo_adi = filter_var($data['veri_cek_tablo_adi'] ?? '', FILTER_SANITIZE_STRING);
$sutun_adi = filter_var($data['veri_cek_sutun_adi'] ?? '', FILTER_SANITIZE_STRING);
$satir_adi = filter_var($data['veri_cek_satir_adi'] ?? '', FILTER_SANITIZE_STRING);
$icerik_cek = $data['veri_cek_icerik_cek'] ?? '';

// Tablo ve sütun adlarını güvenli hale getir
$allowed_tables = ['tema']; // İzin verilen tablo adlarını buraya ekleyin
$allowed_columns = ['tur']; // İzin verilen sütun adlarını buraya ekleyin

if (!in_array($tablo_adi, $allowed_tables) || !in_array($sutun_adi, $allowed_columns)) {
    die("Geçersiz tablo veya sütun adı.");
}

// SQL sorgusunu hazırla (prepared statement kullanarak)
$sql = "SELECT veri, id FROM $tablo_adi WHERE $sutun_adi = :satir_adi";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':satir_adi', $satir_adi, PDO::PARAM_STR);
$stmt->execute();

$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
    // Decode the JSON data into a PHP array
    $veri_data = json_decode($result['veri'], true);
    $veri_id = $result['id'];

    // Verileri sırala
    usort($veri_data, function($a, $b) {
        $sira_a = isset($a['Sıra No']) && $a['Sıra No'] !== '' ? intval($a['Sıra No']) : -1;
        $sira_b = isset($b['Sıra No']) && $b['Sıra No'] !== '' ? intval($b['Sıra No']) : -1;
        
        if ($sira_a === $sira_b) {
            return 0;
        }
        return ($sira_a < $sira_b) ? -1 : 1;
    });

    // İçerik çek verilerini diziye dönüştür
    $icerik_cek_array = explode(', ', $icerik_cek);

    // Hücre içeriğini oluşturan yardımcı fonksiyon
function getCellContent($key, $value, $id, $tablo_adi, $sutun_adi, $satir_adi) {
    switch ($key) {
        case 'Sıra No':
            return "<td>" . (isset($value) && $value !== '' ? htmlspecialchars($value) : '0') . "</td>";			
        case 'Resim':
            return "<td><img src='" . htmlspecialchars('../' . $value) . "' alt='Resim' style='max-width:50px; max-height:50px;'></td>";
        case 'Başlık':
            return "<td><a href='#' data-toggle='modal'  data-formturu='veri-guncelle' data-tabloadi='{$tablo_adi}' data-sutun-adi='{$sutun_adi}' data-satir-adi='{$satir_adi}' data-target='#GuncelleModalForm' data-modalduzenleid='" . htmlspecialchars($id) . "'>" . htmlspecialchars($value) . "</a></td>";
        case 'Link':
            return "<td>" . htmlspecialchars($value) . "</td>";
        case 'Yayın Durumu':
            $checked = $value == 1 ? 'checked' : '';
            $switch_id = "yayin_durumu_" . $id;
            return "
                <td style='width: 30px;'>
                    <div class='custom-control custom-switch custom-switch-on-success float-right'>
                        <input type='checkbox' class='custom-control-input yayin-durumu-switch' 
                               id='{$switch_id}' 
                               name='{$switch_id}' 
                               {$checked}
                               data-id='" . htmlspecialchars($id) . "'
                               data-tabloadi='" . htmlspecialchars($tablo_adi) . "'
                               data-sutunadi='" . htmlspecialchars($sutun_adi) . "'
                               data-satiradi='" . htmlspecialchars($satir_adi) . "'>
                        <label class='custom-control-label' for='{$switch_id}'></label>
                    </div>
                </td>";
        default:
            return "<td>" . htmlspecialchars($value) . "</td>";
    }
}
?>
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">#<?php echo $veri_id; ?> Renk Listesi</h3>
        <div class="card-tools">
            <div class="input-group input-group-sm" style="<?php if( $gizle == 1 ) { echo 'display:none;'; } ?>">
                <button type="button" class="btn btn-success btn-sm" data-toggle="modal"  data-target="#YeniEkleModalForm" style="margin-right: 10px;">Yeni Ekle</button>
                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#SilModalForm">Sil</button>
            </div>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
					<th style="width: 30px;">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" id="selectAll">
							<label class="custom-control-label" for="selectAll"></label>
						</div>
					</th>
                    <?php foreach ($icerik_cek_array as $header): ?>
                        <th><?php echo htmlspecialchars($header); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
			<tbody>
			<?php foreach ($veri_data as $veri): ?>
				<tr id="tr<?php echo htmlspecialchars($veri['id']); ?>">
					<td>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input row-checkbox" id="select_<?php echo htmlspecialchars($veri['id']); ?>">
							<label class="custom-control-label" for="select_<?php echo htmlspecialchars($veri['id']); ?>"></label>
						</div>
					</td>
					<?php foreach ($icerik_cek_array as $key): ?>
						<?php echo getCellContent($key, $veri[$key] ?? '', $veri['id'], $tablo_adi, $sutun_adi, $satir_adi); ?>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
			
			</tbody>
        </table>
    </div>
</div>


<!-- Yeni Ekle Modal-->
<div class="modal fade" id="YeniEkleModalForm" tabindex="-1" role="dialog" aria-labelledby="YeniEkleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="YeniEkleModalLabel">Ekle</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
		<div class="modal-body">
		  <form id="ekleForm" enctype="multipart/form-data">
			<input type="hidden" data-name="Yayın Durumu" name="input_1" value="1">
			<div class="form-group">
			  <label for="input_5">Sıra</label>
			  <input type="text" class="form-control" data-name="Sıra No" name="input_5">
			</div>
			<div class="form-group">
			  <label for="input_2">Başlık</label>
			  <input type="text" class="form-control" data-name="Başlık" name="input_2">
			</div>
			<div class="form-group">
			  <label for="resim">Resim</label>
			  <input type="file" data-resim_en="500" data-name="Resim" data-resim_boy="500" data-resim_doldur="false" data-resim_kirp="true" data-resim_turu="png" data-resim_yolu="../../resimler/Renk-Ayar/" class="form-control" name="resim">
			</div>
			<div class="form-group">
			  <label for="input_4">Link</label>
			  <input type="text" class="form-control" data-name="Link" name="input_4">
			</div>
		  </form>
		</div>
		<div class="modal-footer">
		  <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
		  <button type="button" id="KaydetButon" data-formturu="veri-ekle" data-tabloadi="tema" data-sutun-adi="tur" data-satir-adi="renkayar" class="btn btn-primary">Kaydet</button>
		</div>
    </div>
  </div>
</div>


<!-- Sil Modal-->
<div class="modal fade" id="SilModalForm" tabindex="-1" role="dialog" aria-labelledby="SilModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="SilModalLabel">Sil</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="sil-uyari">Seçilen verileri silmek istediğinize emin misiniz?</p>
      </div>
      <div class="modal-footer">
        <button type="button" id="iptalButon" class="btn btn-secondary" data-dismiss="modal">İptal</button>
        <button type="button" id="SilButon" data-formturu="veri-sil" data-tabloadi="tema" data-sutun-adi="tur" data-satir-adi="renkayar"  class="btn btn-primary">Evet</button>
      </div>
    </div>
  </div>
</div>


<!-- Güncelle Modal-->
<div class="modal fade" id="GuncelleModalForm" tabindex="-1" role="dialog" aria-labelledby="GuncelleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="GuncelleModalLabel">Güncelle</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div> 
		<div class="modal-body">
		<div style="display:none;">
		<img id="modal-resim" src="" alt="Resim" style="margin-bottom: 10px; max-width: 100%; background: #ededed;">
		</div>
		  <form id="guncelleForm" enctype="multipart/form-data">
			<input type="hidden" data-name="id" name="duzenle-id">
			<input type="hidden" data-name="Eski-Resim-Yolu" name="eski-resim-yolu">
			<div class="form-group" style="<?php if( $gizle == 1 ) { echo 'display:none;'; } ?>">
			  <label for="input_5">Sıra</label>
			  <input type="text" class="form-control" data-name="Sıra No" name="input_5">
			</div>


			<div class="form-group" style="<?php if( $gizle == 1 ) { echo 'display:none;'; } ?>">
			  <label for="input_2">Başlık</label>
			  <input type="text" class="form-control" data-name="Başlık" name="input_2">
			</div>
			<div class="form-group" style="<?php if( $gizle == 1 ) { echo 'display:none;'; } ?>">
			  <label for="resim">Resim</label>
			  <input type="file" data-resim_en="500" data-name="Resim" data-resim_boy="500" data-resim_doldur="false" data-resim_kirp="true" data-resim_turu="png" data-resim_yolu="../../resimler/Renk-Ayar/" class="form-control" name="resim">
			</div>
			<div class="form-group" style="<?php if( $gizle == 1 ) { echo 'display:none;'; } ?>">
			  <label for="input_4">Link</label>
			  <input type="text" class="form-control" data-name="Link" name="input_4">
			</div>
			<div class="form-group" style="<?php if( $gizle == 1 ) { echo 'display:none;'; } ?>">
			  <label for="tur">Tür</label>
			  <input type="text" class="form-control" data-name="tur" name="tur">
			</div>
				

                <div class="form-group">
                 <label for="ust_bar_renk">Üst Bar Arka Plan Renk Kodu</label>

                  <div class="input-group my-colorpicker2">
                     <input type="text" class="form-control" data-name="ust_bar_renk" name="ust_bar_renk" data-varsayilan-veri="#5c5e65">

                    <div class="input-group-append">
                      <span class="input-group-text"><i class="fas fa-square ust_bar_renk" style=""></i></span>
					  <div type="button" class="input-group-text" data="varsayilan-veri"><i class="fas fa-sync-alt"></i></div>
                    </div>
										
                  </div> 
                  <!-- /.input group -->
                </div>	




                <div class="form-group">
                 <label for="search_bar_renk">Tur Arama Arka Plan Rengi</label>

                  <div class="input-group my-colorpicker2">
                     <input type="text" class="form-control" data-name="search_bar_renk" name="search_bar_renk" data-varsayilan-veri="#41444c">

                    <div class="input-group-append">
                      <span class="input-group-text"><i class="fas fa-square search_bar_renk" style=""></i></span>
					  <div type="button" class="input-group-text" data="varsayilan-veri"><i class="fas fa-sync-alt"></i></div>
                    </div>
                  </div> 
                  <!-- /.input group -->
                </div>				
                <div class="form-group">
                 <label for="footer_bar_renk">En Alt Arka Plan Rengi</label>

                  <div class="input-group my-colorpicker2">
                     <input type="text" class="form-control" data-name="footer_bar_renk" name="footer_bar_renk" data-varsayilan-veri="#121111">

                    <div class="input-group-append">
                      <span class="input-group-text"><i class="fas fa-square footer_bar_renk" style=""></i></span>
					  <div type="button" class="input-group-text" data="varsayilan-veri"><i class="fas fa-sync-alt"></i></div>
                    </div>
                  </div> 
                  <!-- /.input group -->
                </div>	

                <div class="form-group">
                 <label for="genel_buton_renk">Genel Buton Rengi</label>
                  <div class="input-group my-colorpicker2">
                     <input type="text" class="form-control" data-name="genel_buton_renk" name="genel_buton_renk" data-varsayilan-veri="#ffb900">

                    <div class="input-group-append">
                      <span class="input-group-text"><i class="fas fa-square genel_buton_renk" style=""></i></span>
					  <div type="button" class="input-group-text" data="varsayilan-veri"><i class="fas fa-sync-alt"></i></div>
                    </div>
                  </div> 
                  <!-- /.input group -->
                </div>	
                <div class="form-group">
                 <label for="genel_buton_yazi_renk">Genel Buton Yazı Rengi</label>

                  <div class="input-group my-colorpicker2">
                     <input type="text" class="form-control" data-name="genel_buton_yazi_renk" name="genel_buton_yazi_renk" data-varsayilan-veri="#000000">
 
                    <div class="input-group-append">
                      <span class="input-group-text"><i class="fas fa-square genel_buton_yazi_renk" style=""></i></span>
					  <div type="button" class="input-group-text" data="varsayilan-veri"><i class="fas fa-sync-alt"></i></div>
                    </div>
                  </div> 
                  <!-- /.input group -->
                </div>				

                <div class="form-group"> 
                 <label for="liste_sayfa_renk_bir">Liste Sayfası Tur Paketi 1. Renk</label>

                  <div class="input-group my-colorpicker2">
                     <input type="text" class="form-control" data-name="liste_sayfa_renk_bir" name="liste_sayfa_renk_bir" data-varsayilan-veri="#ffb900">
 
                    <div class="input-group-append">
                      <span class="input-group-text"><i class="fas fa-square liste_sayfa_renk_bir" style=""></i></span>
					  <div type="button" class="input-group-text" data="varsayilan-veri"><i class="fas fa-sync-alt"></i></div>
                    </div>
                  </div> 
                  <!-- /.input group -->
                </div>
				
 
                <div class="form-group"> 
                 <label for="liste_sayfa_renk_bir_yazi">Liste Sayfası Tur Paketi 1. Yazı Rengi</label>

                  <div class="input-group my-colorpicker2">
                     <input type="text" class="form-control" data-name="liste_sayfa_renk_bir_yazi" name="liste_sayfa_renk_bir_yazi" data-varsayilan-veri="#000000">
 
                    <div class="input-group-append">
                      <span class="input-group-text"><i class="fas fa-square liste_sayfa_renk_bir_yazi" style=""></i></span>
					  <div type="button" class="input-group-text" data="varsayilan-veri"><i class="fas fa-sync-alt"></i></div>
                    </div>
                  </div> 
                  <!-- /.input group -->
                </div>				
				
                <div class="form-group">
                 <label for="liste_sayfa_renk_iki">Liste Sayfası Tur Paketi 2. Renk</label>

                  <div class="input-group my-colorpicker2">
                     <input type="text" class="form-control" data-name="liste_sayfa_renk_iki" name="liste_sayfa_renk_iki" data-varsayilan-veri="#2b2727">
 
                    <div class="input-group-append">
                      <span class="input-group-text"><i class="fas fa-square liste_sayfa_renk_iki" style=""></i></span>
					  <div type="button" class="input-group-text" data="varsayilan-veri"><i class="fas fa-sync-alt"></i></div>
                    </div>
                  </div> 
                  <!-- /.input group -->
                </div>	

                <div class="form-group"> 
                 <label for="liste_sayfa_renk_iki_yazi">Liste Sayfası Tur Paketi 2. Yazı Rengi</label>
                  <div class="input-group my-colorpicker2">
                     <input type="text" class="form-control" data-name="liste_sayfa_renk_iki_yazi" name="liste_sayfa_renk_iki_yazi" data-varsayilan-veri="#ffffff">
                    <div class="input-group-append">
                      <span class="input-group-text"><i class="fas fa-square liste_sayfa_renk_iki_yazi" style=""></i></span>
					  <div type="button" class="input-group-text" data="varsayilan-veri"><i class="fas fa-sync-alt"></i></div>
                    </div>
                  </div> 
                  <!-- /.input group -->
                </div>	
				
                <div class="form-group"> 
                 <label for="liste_sayfa_renk_filtre_baslik">Liste Sayfası Filtre Başlık</label>
                  <div class="input-group my-colorpicker2">
                     <input type="text" class="form-control" data-name="liste_sayfa_renk_filtre_baslik" name="liste_sayfa_renk_filtre_baslik" data-varsayilan-veri="#ffb900">
                    <div class="input-group-append">
                      <span class="input-group-text"><i class="fas fa-square liste_sayfa_renk_filtre_baslik" style=""></i></span>
					  <div type="button" class="input-group-text" data="varsayilan-veri"><i class="fas fa-sync-alt"></i></div>
                    </div>
                  </div> 
                  <!-- /.input group -->
                </div>	
				
                <div class="form-group"> 
                 <label for="liste_sayfa_renk_filtre_secim">Liste Sayfası Filtre Seçim</label>
                  <div class="input-group my-colorpicker2">
                     <input type="text" class="form-control" data-name="liste_sayfa_renk_filtre_secim" name="liste_sayfa_renk_filtre_secim" data-varsayilan-veri="#ffb900">
                    <div class="input-group-append">
                      <span class="input-group-text"><i class="fas fa-square liste_sayfa_renk_filtre_secim" style=""></i></span>
					  <div type="button" class="input-group-text" data="varsayilan-veri"><i class="fas fa-sync-alt"></i></div>
                    </div>
                  </div> 
                  <!-- /.input group -->
                </div>			
				
                <div class="form-group"> 
                 <label for="liste_sayfa_renk_filtre_buton">Liste Sayfası Filtre Buton</label>
                  <div class="input-group my-colorpicker2">
                     <input type="text" class="form-control" data-name="liste_sayfa_renk_filtre_buton" name="liste_sayfa_renk_filtre_buton" data-varsayilan-veri="#ffb900">
                    <div class="input-group-append">
                      <span class="input-group-text"><i class="fas fa-square liste_sayfa_renk_filtre_buton" style=""></i></span>
					  <div type="button" class="input-group-text" data="varsayilan-veri"><i class="fas fa-sync-alt"></i></div>
                    </div>
                  </div> 
                  <!-- /.input group -->
                </div>			
                <div class="form-group"> 
                 <label for="liste_sayfa_renk_filtre_buton_yazi">Liste Sayfası Filtre Buton Yazı</label>
                  <div class="input-group my-colorpicker2">
                     <input type="text" class="form-control" data-name="liste_sayfa_renk_filtre_buton_yazi" name="liste_sayfa_renk_filtre_buton_yazi" data-varsayilan-veri="#ffffff">
                    <div class="input-group-append">
                      <span class="input-group-text"><i class="fas fa-square liste_sayfa_renk_filtre_buton_yazi" style=""></i></span>
					  <div type="button" class="input-group-text" data="varsayilan-veri"><i class="fas fa-sync-alt"></i></div>
                    </div>
                  </div> 
                  <!-- /.input group -->
                </div>	
				
                <div class="form-group"> 
                 <label for="detay_sayfa_baslik_yazı_rengi">Detay Sayfası Başlık Yazı Rengi</label>
                  <div class="input-group my-colorpicker2">
                     <input type="text" class="form-control" data-name="detay_sayfa_baslik_yazı_rengi" name="detay_sayfa_baslik_yazı_rengi" data-varsayilan-veri="#ffb900">
                    <div class="input-group-append">
                      <span class="input-group-text"><i class="fas fa-square detay_sayfa_baslik_yazı_rengi" style=""></i></span>
					  <div type="button" class="input-group-text" data="varsayilan-veri"><i class="fas fa-sync-alt"></i></div>
                    </div>
                  </div> 
                  <!-- /.input group -->
                </div>	

				
                <div class="form-group"> 
                 <label for="detay_sayfa_bilgi_arkaplan_rengi">Detay Sayfası Bilgi Sayfaları Başlık Arka Plan</label>
                  <div class="input-group my-colorpicker2">
                     <input type="text" class="form-control" data-name="detay_sayfa_bilgi_arkaplan_rengi" name="detay_sayfa_bilgi_arkaplan_rengi" data-varsayilan-veri="#ffb900">
                    <div class="input-group-append">
                      <span class="input-group-text"><i class="fas fa-square detay_sayfa_bilgi_arkaplan_rengi" style=""></i></span>
					  <div type="button" class="input-group-text" data="varsayilan-veri"><i class="fas fa-sync-alt"></i></div>
                    </div> 
                  </div> 
                  <!-- /.input group -->
                </div>
				
                <div class="form-group"> 
                 <label for="detay_sayfa_bilgi_baslik_yazi_rengi">Detay Sayfası Bilgi Sayfaları Başlık Arka Plan</label>
                  <div class="input-group my-colorpicker2">
                     <input type="text" class="form-control" data-name="detay_sayfa_bilgi_baslik_yazi_rengi" name="detay_sayfa_bilgi_baslik_yazi_rengi" data-varsayilan-veri="#ffffff">
                    <div class="input-group-append">
                      <span class="input-group-text"><i class="fas fa-square detay_sayfa_bilgi_baslik_yazi_rengi" style=""></i></span>
					  <div type="button" class="input-group-text" data="varsayilan-veri"><i class="fas fa-sync-alt"></i></div>
                    </div> 
                  </div> 
                  <!-- /.input group -->
                </div>					

			
		  </form>
		</div>
		<div class="modal-footer">
		  <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
		  <button type="button" id="GuncelleButon" data-formturu="veri-guncelle" data-tabloadi="tema" data-sutun-adi="tur" data-satir-adi="renkayar" class="btn btn-primary">Güncelle</button>
		</div>
    </div>
  </div>
</div>


<?php
} else {
    echo "Veri bulunamadı.";
}
?>