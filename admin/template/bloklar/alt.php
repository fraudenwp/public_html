  <!-- Main Footer -->
  <footer class="main-footer">
    <!-- To the right -->
    <div class="float-right d-none d-sm-inline">
      <b>User Language:</b> <?php echo $user_dil; ?> | <b>License status:</b> www.yakutturizm.com.tr (active)
    </div>
    <!-- Default to the left -->
    <strong>Copyright &copy; 2024 <a href="https://webdoksandokuz.com">GF-KILIC</a></strong> Tüm hakları saklıdır.
  </footer>


</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->

<!-- jQuery -->
<script src="template/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="template/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
 	<script src="template/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
<?php if (in_array($url, $summernote)) { ?>
<!-- Summernote -->
<script src="template/plugins/summernote/summernote-bs4.min.js"></script>
<?php } ?>
<?php if (in_array($url, $select2)) { ?>
<!-- Select2 -->
<script src="template/plugins/select2/js/select2.full.min.js"></script>
<!-- Bootstrap4 Duallistbox -->
<script src="template/plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js"></script>
<?php } ?>
<!-- AdminLTE App -->
<script src="template/dist/js/adminlte.min.js"></script>
<script src="template/dist/js/yonetim.js"></script>


 <?php if (in_array($url, $uyarilar)) { ?>

<script src="template/plugins/sweetalert2/sweetalert2.min.js"></script>
<script src="template/plugins/toastr/toastr.min.js"></script>


<script>

var uyariFunctions; // Global alanda uyariFunctions değişkenini tanımla
var Toast; // Global alanda Toast değişkenini tanımla

$(function() {
    Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000
    });

    // Fonksiyonları dizi içinde tanımla
    uyariFunctions = {
      uyarisuccess: function() {
		  console.log("Uyarı Çalıştırıldı. (success)");
        Toast.fire({
          icon: 'success',
          title: 'İşlem başarıyla tamamlandı.'
        });
      },
      uyarierror: function() {
		console.log("Uyarı Çalıştırıldı. (error)");
        Toast.fire({
          icon: 'error',
          title: 'İşlem sırasında hata oluştu.'
        });
      }
      // Diğer uyari fonksiyonlarını buraya ekleyebilirsiniz
    };
});



</script>
<?php } ?>

<?php 

// $data_tables değişkeninin listede bulunup bulunmadığını kontrol etme
if (in_array($url, $datatables)) { ?>
    <!-- DataTables & Plugins -->
    <script src="template/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="template/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="template/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="template/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
    <script src="template/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
    <script src="template/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
    <script src="template/plugins/jszip/jszip.min.js"></script>
    <script src="template/plugins/pdfmake/pdfmake.min.js"></script>
    <script src="template/plugins/pdfmake/vfs_fonts.js"></script>
    <script src="template/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
    <script src="template/plugins/datatables-buttons/js/buttons.print.min.js"></script>
    <script src="template/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
	<script src="template/ajax/yayin-durumu.js"></script>

	
	<script>
    var baseurl_onyuz = "<?php echo $baseurl_onyuz; ?>";
	
</script>


	<script src="template/ajax/veri-list.js"></script>

	
<script>
  $(function () {
    $('#example1').DataTable({
      "paging": true,
      "lengthChange": true,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": true,
      "responsive": true,
      "language": { // Dil ayarlarını burada belirtiyoruz
        "decimal": ",",
        "thousands": ".",
        "lengthMenu": "Sayfa başına _MENU_ kayıt göster",
        "zeroRecords": "Eşleşen kayıt bulunamadı",
        "info": "_TOTAL_ kayıttan _START_ - _END_ arası gösteriliyor",
        "infoEmpty": "Gösterilecek kayıt yok",
        "infoFiltered": "(toplam _MAX_ kayıt)",
        "search": "Ara:",
        "paginate": {
          "first": "İlk",
          "previous": "Önceki",
          "next": "Sonraki",
          "last": "Son"
        },
        "buttons": {
          "copy": "Kopyala",
          "excel": "Excel'e Aktar",
          "pdf": "PDF'e Aktar",
          "print": "Yazdır",
        }
      },
      "buttons": ["copy", "excel", "pdf", "print"]	  
    }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
	
	
  });
  
 
</script>	
<?php } ?>

<?php if (in_array($url, $toplu_secim)) { // $data_tables değişkeninin listede bulunup bulunmadığını kontrol etme ?>

<script>
 $(document).ready(function(){
    // Tüm seçim kutularını seçmek veya seçimi kaldırmak için ana seçim kutusunu dinleyin
    $('#selectAll').click(function() {
        $('.selectSingle').prop('checked', $(this).prop('checked'));
    });
    
    // Herhangi bir seçim kutusu tıklandığında, ana seçim kutusunu güncelleyin
    $('.selectSingle').click(function() {
        if ($(this).prop('checked') == false) {
            $('#selectAll').prop('checked', false);
        } else {
            if ($('.selectSingle:checked').length == $('.selectSingle').length) {
                $('#selectAll').prop('checked', true);
            }
        }
    });
});
  
</script>
	
<?php } ?>

<?php if (in_array($url, $veri_kayit)) { ?>


	<!-- jquery-validation -->
	<script src="template/plugins/jquery-validation/jquery.validate.min.js"></script>
	<script src="template/plugins/jquery-validation/additional-methods.min.js"></script>
	
	<!-- Bootstrap Switch -->
	<script src="template/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>	
	<script src="template/ajax/veri-gonder.js"></script>

	

<script>

    $("input[data-bootstrap-switch]").each(function(){
      $(this).bootstrapSwitch('state', $(this).prop('checked'));
    })
	
 
</script>

<?php } ?>	
<?php if (in_array($url, $api_yeni_kod)) { ?>
 

<script src="template/ajax/api-kod-uret.js"></script>
<?php } ?>	
<?php if (in_array($url, $api_istek)) { ?>

	<script>
		document.addEventListener('DOMContentLoaded', function() {
			let receivedData = {}; // Global değişken

			// Tüm kontrol butonlarını seç
			const kontrolButtons = document.querySelectorAll('.kategori-kontrol, .urun-kontrol');
			
			kontrolButtons.forEach(button => {
				button.addEventListener('click', function() {
					const veritabani = this.getAttribute('data-veritabani');
					const veritabaniResim = this.getAttribute('data-veritabani-resim');
					const mevcutToplamSatir = parseInt(this.getAttribute('data-toplamsatir'), 10);
					
					// Bu butonun bulunduğu card'ı bul
					const card = this.closest('.card');
					const progress = card.querySelector('.progress-bar');
					const progressBarText = progress.querySelector('.progress-bar-text');
					const veriCekDiv = card.querySelector('[id^="veriCek-"]');
					const totalElement = veriCekDiv.querySelector('#tum-verisayisi');
					const newElement = veriCekDiv.querySelector('#yeni-verisayisi');

					const headers = {
						'Content-Type': 'application/json',
						'X-Requested-With': 'XMLHttpRequest'
					};
					let progressWidth = 0;
					progress.style.width = progressWidth + '%';

					// veriCekDiv'i gizle
					veriCekDiv.style.display = 'none';

					fetch('api/eminonline/config.php', {
						method: 'POST',
						headers: headers,
						body: JSON.stringify({
							veritabani: veritabani,
							veritabani_resim: veritabaniResim
						})
					})
					.then(response => response.json())
					.then(data => {
						console.log('Config verileri:', data);
						updateProgressBar(40);
						return fetch('api/eminonline/istek-gonder.php', {
							method: 'POST',
							headers: headers,
							body: JSON.stringify(data)
						});
					})
					.then(response => response.json())
					.then(result => {
						console.log('API yanıtı:', result);
						updateProgressBar(80);
						if (result.success) {
							console.log('Doğrulama: Başarılı');
							updateProgressBar(100);
							processReceivedData(result.data, mevcutToplamSatir);
							// Tüm aşamalar başarıyla tamamlandığında veriCekDiv'i göster
							veriCekDiv.style.display = 'block';

							// Alınan veriyi global değişkende sakla
							receivedData[veritabani] = result.data;
						} else {
							console.log('Doğrulama: Başarısız');
							throw new Error('API doğrulama başarısız: ' + result.message);
						}
					})
					.catch(error => {
						console.error('Hata:', error);
						alert('Bir hata oluştu: ' + error.message);
						updateProgressBar(0);
						// Hata durumunda veriCekDiv'i gizli tutuyoruz
					});

					function updateProgressBar(percent) {
						progressWidth = percent;
						progress.style.width = progressWidth + '%';
						if (progressBarText) {
							progressBarText.textContent = progressWidth + '% Tamamlandı';
						}
					}

					function processReceivedData(data, mevcutToplamSatir) {
						if (!Array.isArray(data)) {
							console.error('Geçersiz veri formatı:', data);
							return;
						}

						const uniqueGroups = new Set();
						data.forEach(item => {
							if (item.grup) {
								uniqueGroups.add(item.grup);
							}
						});

						const totalCount = uniqueGroups.size;
						totalElement.textContent = totalCount + ' Adet';

						// Yeni veri sayısını hesapla
						const newCount = Math.max(0, totalCount - mevcutToplamSatir);
						newElement.textContent = newCount + ' Adet';
					}
				});
			});

			// Kayıt et butonları için event listener ekleyelim
			const kayitButtons = document.querySelectorAll('button[data-veri]');

			kayitButtons.forEach(button => {
				button.addEventListener('click', function() {
					const veri = this.getAttribute('data-veri');
					const tableVeri = this.getAttribute('data-table-veri');
					const tableResim = this.getAttribute('data-table-resim');

					// Alınan verileri kayit-et.php dosyasına gönder
					const postData = {
						veri: veri,
						table_veri: tableVeri,
						table_resim: tableResim,
						data: receivedData[tableVeri] || []
					};

					let progressBarClass;
					if (veri === 'tum-veriler') {
						progressBarClass = tableVeri === 'kategoriler' ? 'tumkategori-bar' : 'tumurun-bar';
					} else if (veri === 'yeni-veriler') {
						progressBarClass = tableVeri === 'kategoriler' ? 'yenikategori-bar' : 'yeniurun-bar';
					}

					processData(postData, progressBarClass);
				});
			});

				function processData(data, progressBarClass) {
					return fetch('api/eminonline/kayit-et.php', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json',
							'X-Requested-With': 'XMLHttpRequest'
						},
						body: JSON.stringify(data)
					})
					.then(response => response.text())
					.then(text => {
						console.log('Ham yanıt:', text);  // Ham yanıtı konsola yazdır
						
						try {
							// Önce tüm metni tek bir JSON olarak ayrıştırmayı dene
							const result = JSON.parse(text);
							handleResult(result, progressBarClass);
						} catch (error) {
							// Eğer tek JSON olarak ayrıştırılamazsa, JSON nesnelerini ayırmayı dene
							const jsonObjects = text.match(/{[^}]+}/g);
							if (jsonObjects) {
								jsonObjects.forEach(jsonString => {
									try {
										const result = JSON.parse(jsonString);
										handleResult(result, progressBarClass);
									} catch (error) {
										console.error('JSON parse hatası:', error, 'Ham veri:', jsonString);
									}
								});
							} else {
								console.error('Geçerli JSON nesnesi bulunamadı:', text);
							}
						}
					})
					.catch(error => {
						console.error('Fetch hatası:', error);
					});
				}

				function handleResult(result, progressBarClass) {
					if (result.status === 'progress') {
						updateProgressBar(progressBarClass, result.current, result.total);
					} else if (result.status === 'complete') {
						console.log(result.message);
						updateProgressBar(progressBarClass, 100, 100);
					} else {
						console.error('Beklenmeyen sonuç:', result);
					}
				}

			function updateProgressBar(elementClass, current, total) {
				const progressBar = document.querySelector(`.${elementClass}`);
				const progressText = progressBar.querySelector('span:not(.sr-only)');
				const percentage = Math.round((current / total) * 100);
				
				progressBar.style.width = `${percentage}%`;
				progressBar.setAttribute('aria-valuenow', percentage);
				progressText.textContent = `${current}/${total} Tamamlandı (${percentage}%)`;
			}
		});
 
	</script>

<?php } ?>



<?php if (in_array($url, $tema_ayarlari)) { ?>

<script> var baseurl_onyuz = <?php echo json_encode(BASEURL_ONYUZ); ?>; 

console.log(baseurl_onyuz);

</script>
<script src="template/ajax/tema-ayarlari-sayfa-cagir.js"></script>


<?php } ?>


<script>
function showOverlay() {
    document.getElementById('page-overlay').style.display = 'block';
}

function hideOverlay() {
    document.getElementById('page-overlay').style.display = 'none';
}





</script>
</body>
</html>