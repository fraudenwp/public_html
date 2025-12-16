$(document).ready(function(){
    $('input[name="yayin_durumuu"]').change(function(){
        var id = $(this).attr('id').split('-').pop(); // Checkbox'un ID'sinden kategori ID'sini al
        var durum = $(this).is(':checked') ? $(this).val() : '0'; // Checkbox durumunu kontrol et (1: seçili, 0: seçili değil)
        var veriTablosu = $(this).data('veri-tablosu'); // Veri tablosunu al
        var formData = { id: id, veri: durum, tablo: veriTablosu }; // Gönderilecek veri
        $.ajax({
            type: 'POST',
            url: 'islemler/yayin-durumu.php', // Ajax işleminin yapılacağı PHP dosyasının yolu
            data: formData, // Gönderilecek veriyi belirt
            beforeSend: function(){
                console.log("Ajax Post isteği başlatılıyor..."); // Ajax isteği başlatıldığında konsola yazdır
            },
success: function(response){
    console.log("Ajax işlemi başarıyla tamamlandı.");
    console.log(response);

    // JSON yanıtını parse ederek objeye dönüştür
    var responseData = response; // JSON.parse gerekli değil, çünkü zaten JSON formatında alınıyor

    // Yanıtta 'success' anahtarını kontrol et
    if(responseData['success']) { // responseData.success yerine responseData['success'] kullanıldı
        uyariFunctions.uyarisuccess(); // Başarılı işlem uyarısı göster
    } else {
        // Başarısızlık durumunda, hata mesajını al ve uyarı göster
        var errorMessage = responseData.error;
        uyariFunctions.uyarierror(errorMessage);
    }
}, 
            error: function(xhr, status, error){
                console.error("AJAX Hatası:", xhr.status, xhr.statusText);
                console.error("Hata Detayı:", error);
                uyariFunctions.uyarierror(); // Hata durumunda uyarı göster
            }
        });
    });
});
