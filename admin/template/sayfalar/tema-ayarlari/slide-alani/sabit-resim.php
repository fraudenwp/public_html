<!-- veri-listele.php -->
<?php
// Include config.php to connect to the database
include '../../../../config.php';
$user_dil = $_SESSION['dil'] ?? 'tr';
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
        $sira_a = isset($a['Sıra_No']) && $a['Sıra_No'] !== '' ? intval($a['Sıra_No']) : -1;
        $sira_b = isset($b['Sıra_No']) && $b['Sıra_No'] !== '' ? intval($b['Sıra_No']) : -1;
        
        if ($sira_a === $sira_b) {
            return 0;
        }
        return ($sira_a < $sira_b) ? -1 : 1;
    });

    // İçerik çek verilerini diziye dönüştür
    $icerik_cek_array = explode(', ', $icerik_cek);

    // Hücre içeriğini oluşturan yardımcı fonksiyon
function getCellContent($key, $value, $id, $tablo_adi, $sutun_adi, $satir_adi, $user_dil) {
    switch ($key) {
        case 'Sıra_No':
            return "<td>" . (isset($value) && $value !== '' ? htmlspecialchars($value) : '0') . "</td>";			
        case 'Resim':
            return "<td><img src='" . htmlspecialchars('../' . $value) . "' alt='Resim' style='max-width:50px; max-height:50px;'></td>";
        case 'Başlık':
            $baslik = is_array($value) ? ($value[$user_dil] ?? $value['tr'] ?? reset($value)) : $value;
            return "<td><a href='#' data-toggle='modal' data-formturu='veri-guncelle' data-tabloadi='{$tablo_adi}' data-sutun-adi='{$sutun_adi}' data-satir-adi='{$satir_adi}' data-target='#GuncelleModalForm' data-modalduzenleid='" . htmlspecialchars($id) . "'>" . htmlspecialchars($baslik) . "</a></td>"; 
			case 'Link':
			$link = is_array($value) ? ($value[$user_dil] ?? $value['tr'] ?? reset($value)) : $value;
            return "<td>" . htmlspecialchars($link) . "</td>";
        case 'Yayın_Durumu':
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

$query = $pdo->query("SELECT id, kod, baslik, varsayilan, COALESCE(NULLIF(resim, ''), 'resimler/resim-yok.jpg') AS resim FROM diller"); $diller = $query->fetchAll(PDO::FETCH_ASSOC);

?>
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">#<?php echo $veri_id; ?> Slaytlar </h3>
        <div class="card-tools">
            <div class="input-group input-group-sm">
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
							<?php echo getCellContent($key, $veri[$key] ?? '', $veri['id'], $tablo_adi, $sutun_adi, $satir_adi, $user_dil); ?>
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
			<input type="hidden" data-name="Yayın Durumu" name="yayin_durumu" value="1">
			<div class="form-group">
			  <label for="sira_no">Sıra</label>
			  <input type="text" class="form-control" data-name="Sıra_No" name="sira_no" value="0">
			</div>
			<?php foreach ($diller as $dil): ?>
			<div class="form-group">
			  <label for="baslik_<?php echo $dil['kod']; ?>">Başlık (<?php echo $dil['kod']; ?>)</label>
			  <input type="text" class="form-control" data-name="Başlık" data-dil="<?php echo $dil['kod']; ?>" name="baslik_<?php echo $dil['kod']; ?>">
			</div>

			<div class="form-group">
			  <label for="aciklama_<?php echo $dil['kod']; ?>">Açıklama (<?php echo $dil['kod']; ?>)</label>
			  <input type="text" class="form-control" data-name="Açıklama" data-dil="<?php echo $dil['kod']; ?>" name="aciklama_<?php echo $dil['kod']; ?>">
			</div>
			
			<div class="form-group">
			  <label for="link_<?php echo $dil['kod']; ?>">Link</label>
			  <input type="text" class="form-control" data-name="Link" data-dil="<?php echo $dil['kod']; ?>" name="link_<?php echo $dil['kod']; ?>">
			</div>			
			<?php endforeach; ?>
			<div class="form-group">
			  <label for="resim">Resim</label>
			  <input type="file" data-resim_en="350" data-name="Resim" data-resim_boy="200" data-resim_doldur="false" data-resim_kirp="true" data-resim_turu="png" data-resim_yolu="../../resimler/slide/" class="form-control" name="resim">
			</div>

		  </form> 
		</div>
		<div class="modal-footer">
		  <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
		  <button type="button" id="KaydetButon" data-formturu="veri-ekle" data-tabloadi="tema" data-sutun-adi="tur" data-satir-adi="Slayt-Sabit-Resimler" class="btn btn-primary">Kaydet</button>
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
        <button type="button" id="SilButon" data-formturu="veri-sil" data-tabloadi="tema" data-sutun-adi="tur" data-satir-adi="Slayt-Sabit-Resimler"  class="btn btn-primary">Evet</button>
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
		<img id="modal-resim" src="" alt="Resim" style="margin-bottom: 10px; height: 100px;">
		  <form id="guncelleForm" enctype="multipart/form-data">
			<input type="hidden" data-name="id" name="duzenle-id">
			<input type="hidden" data-name="Eski-Resim-Yolu" name="eski-resim-yolu">
			<div class="form-group">
			  <label for="sira_no">Sıra</label>
			  <input type="text" class="form-control" data-name="Sıra_No" name="sira_no">
			</div>
			<?php foreach ($diller as $dil): ?>
				<div class="form-group">
				  <label for="baslik_<?php echo $dil['kod']; ?>">Başlık (<?php echo $dil['kod']; ?>)</label>
				  <input type="text" class="form-control" data-name="Başlık" data-dil="<?php echo $dil['kod']; ?>" name="baslik_<?php echo $dil['kod']; ?>">
				</div>
				
				<div class="form-group">
				  <label for="aciklama_<?php echo $dil['kod']; ?>">Açıklama (<?php echo $dil['kod']; ?>)</label>
				  <input type="text" class="form-control" data-name="Açıklama" data-dil="<?php echo $dil['kod']; ?>" name="aciklama_<?php echo $dil['kod']; ?>">
				</div>

			<div class="form-group">
			  <label for="link_<?php echo $dil['kod']; ?>">Link (<?php echo $dil['kod']; ?>)</label>
			  <input type="text" class="form-control" data-name="Link" data-dil="<?php echo $dil['kod']; ?>" name="link_<?php echo $dil['kod']; ?>">
			</div>				
				
				<hr>			
			<?php endforeach; ?>
			 
			<div class="form-group">
			  <label for="resim">Resim</label>
			  <input type="file" data-resim_en="350" data-name="Resim" data-resim_boy="200" data-resim_doldur="false" data-resim_kirp="true" data-resim_turu="png" data-resim_yolu="../../resimler/slide/" class="form-control" name="resim">
			</div>

		  </form>
		</div>
		<div class="modal-footer">
		  <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
		  <button type="button" id="GuncelleButon" data-formturu="veri-guncelle" data-tabloadi="tema" data-sutun-adi="tur" data-satir-adi="Slayt-Sabit-Resimler" class="btn btn-primary">Güncelle</button>
		</div>
    </div>
  </div>
</div>


<?php
} else {
    echo "Veri bulunamadı.";
}
?>