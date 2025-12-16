$(document).ready(function(){
    // Sayfa yüklendiğinde çalışacak fonksiyon
    $('#veriForm').submit(function(e){
        e.preventDefault(); // Form submit işlemini engelle
        var formData = {}; // Boş bir obje oluştur

        // Form elemanlarındaki isimleri ve girilen verileri formData objesine ekle
        $(this).find('input[name], select[name]').filter(function() {
            return $(this).attr('type') !== 'file'; // Type="file" olanları hariç tut
        }).each(function(){
            var name = $(this).attr('name'); // Input elemanının ismini al
            var value;
            // Checkbox durumunu kontrol et
            if ($(this).attr('type') === 'checkbox') {
                value = $(this).is(':checked') ? $(this).val() : ''; // Checkbox seçiliyse değeri, değilse boş bir değer al
            } else {
                value = $(this).val(); // Diğer input türlerinden değeri al
            }
            // data-sifrele değerini al
            var sifrelemeTuru = $(this).data('sifrele'); // data-sifrele özelliğini al
            // Şifreleme türünü formData objesine ekle
            formData[name] = {value: value, sifrelemeAlgoritmasi: sifrelemeTuru};
        });

        var tabloAdi = $(this).find('[data-tablo-adi]').data('tablo-adi'); // Tablo adını al
        var formTuru = $(this).find('[data-form-turu]').data('form-turu'); // Form Türünü al

        // Form türüne göre konsola uyarı yazdır
        if (formTuru === 'veri_ekle') {
            console.log('Form Türü: Veri Ekle');
        } else if (formTuru === 'veri-duzenle') {
            console.log('Form Türü: Veri Güncelle');
        }		
		
        $.ajax({
            type: 'POST',
            url: 'islemler/' + formTuru + '.php', // PHP dosyasının yolunu belirt
            data: {formData: formData, tabloAdi: tabloAdi}, // Form verilerini ve tablo adını gönder
            beforeSend: function(){
                console.log("Ajax Post isteği başlatılıyor..."); // Ajax isteği başlatıldığında konsola yazdır
                console.log(formData);
				startLoading(); // Loading Başlat
            },
            success: function(response){
                console.log("Sonuç Alınıyor...");
				stopLoading(); /// Loading Kapat
                try {
                    var parsedResponse = JSON.parse(response); // JSON formatına dönüştür
                    if (Array.isArray(parsedResponse)) {
                        let allSuccessful = parsedResponse.every(item => item.success); // Tüm işlemlerin başarılı olup olmadığını kontrol et
                        if (allSuccessful) {
                            console.log("Tüm Kayıtlar Başarılı.");
                            uyariFunctions.uyarisuccess(); // Başarılı uyarı göster
                            parsedResponse.forEach(item => {
                                console.log("Kayıt ID'si:", item.kayitId);
                                console.log("Grup ID'si:", item.grupId);
                            });
                            var fileInputExists = $('#dosyaYukleInput').length > 0; // Dosya yükleme inputunun varlığını kontrol et
                            if (fileInputExists) {
                                var fileInput = $('#dosyaYukleInput')[0].files[0];
                                if (!fileInput || fileInput.size === 0) {
                                    console.log("Resim Seçilmediğinden Resim Ekleme fonksiyonu Çağırılmadı.");
                                } else {
                                    console.log("Resim Ekleme Fonksiyonu Çağırıldı.");
                                    resimEkle(parsedResponse[0].grupId); // Resim ekleme fonksiyonunu çağır
                                }
                            } else {
                                console.log("Dosya yükleme inputu bulunamadı. İşlem iptal edildi.");
                            }
                            // $('#veriForm')[0].reset(); // Formu sıfırla
                        } else {
                            console.log("Kayıt Başarısız! Response:", response);
                            uyariFunctions.uyarierror(); // Hata uyarısı göster
                        }
                    } else {
                        console.log("Beklenmeyen yanıt formatı. Response:", response);
                    }
                } catch (e) {
                    console.error("JSON parse hatası:", e);
                    console.log("Response:", response);
                }
				
				
            },
            error: function(xhr, status, error){
                console.error("AJAX Hatası:", xhr.status, xhr.statusText);
                console.error("Hata Detayı:", error);
				stopLoading(); /// Loading Kapat
                uyariFunctions.uyarierror(); // Hata uyarısı göster
				
            }
        });
    });
});

// Dosya yükleme işlemi
function resimEkle(grupId) {
    var fileInput = $('#dosyaYukleInput')[0].files[0]; // Dosya yükleme input alanından dosyayı al
    var formData = new FormData(); // Boş bir FormData nesnesi oluştur
    formData.append('dosya', fileInput); // Dosyayı FormData nesnesine ekle
    formData.append('grupId', grupId); // Grup ID'sini de ekle
    formData.append('data-resim-tablosu', $('#dosyaYukleInput').data('resim-tablosu')); // Özel veriyi ekle
    formData.append('data-hedef-dizin', $('#dosyaYukleInput').data('hedef-dizin')); // Özel veriyi ekle
    formData.append('data-resim-en', $('#dosyaYukleInput').data('resim-en')); // Özel veriyi ekle
    formData.append('data-resim-boy', $('#dosyaYukleInput').data('resim-boy')); // Özel veriyi ekle
    formData.append('data-resim-kalite', $('#dosyaYukleInput').data('resim-kalite')); // Özel veriyi ekle

    // Verileri konsolda göster
    console.log('Kayıt ID:', grupId);
    console.log('Data Resim Tablosu:', $('#dosyaYukleInput').data('resim-tablosu'));
    console.log('Data Hedef Dizin:', $('#dosyaYukleInput').data('hedef-dizin'));
    console.log("Resim yükleme işlemi başlatılıyor...");

    $.ajax({
        type: 'POST',
        url: 'islemler/resim_ekle.php', // Resim ekleme PHP dosyasının yolunu belirt
        data: formData,
        contentType: false, // İçerik tipini otomatik olarak ayarlamasını engelle
        processData: false, // Veriyi dönüştürmesini engelle
        beforeSend: function(){
            console.log("Resim yükleniyor..."); // Ajax isteği başlatıldığında konsola yazdır
        },
        success: function(response){
            console.log(response); // Başarılı yanıtı konsola yazdır
        },
        error: function(xhr, status, error){
            console.error("Resim ekleme hatası:", xhr.status, xhr.statusText); // Hata mesajını konsola yazdır
            console.error("Hata Detayı:", error); // Hata detayını konsola yazdır
        }
    });
}
