// tema-ayarlari.js
// Sayfa yüklendiğinde çalışacak ana fonksiyon
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM yüklendi, ana script çalışıyor.');
    initializeTemaMenuLinks();
    initializeCheckboxes();
    initializeModalHandlers();
    initializeUpdateModal();
    initializeYayinDurumuSwitches();
    initializeAnasayfaMetaAyarlari(); // Yeni eklenen fonksiyon

});

// Tema menü linklerini initialize eden fonksiyon
function initializeTemaMenuLinks() {
    var links = document.querySelectorAll('.tema-menu-link');
    console.log('Bulunan link sayısı:', links.length);
    links.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Tema menü linkine tıklandı:', this.getAttribute('data-veri_cek_dizin_yolu'));
			
			
			 
            var phpUrl = this.getAttribute('data-veri_cek_dizin_yolu');
            
            // Tüm data özelliklerini topla
            var dataAttributes = {};
            for (let attr of this.attributes) {
                if (attr.name.startsWith('data-')) {
                    dataAttributes[attr.name.slice(5)] = attr.value;
                }
            }
            
            loadContent(phpUrl, dataAttributes);
        });
    });
}

// İçeriği yükleyen fonksiyon
function loadContent(phpUrl, dataAttributes) {
    console.log('PHP içeriği yükleniyor:', phpUrl);
    console.log('Gönderilecek veri:', dataAttributes);
   
    fetch(phpUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(dataAttributes)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.statusText);
        }
        return response.text();
    })
    .then(html => {
        console.log('Veriler Alındı');
        document.getElementById('tema-genelAyarlar').innerHTML = html;
        console.log('İçerik yüklendi ve eklendi.');
        initializeCheckboxes();
        initializeModalHandlers();
        reinitializeSelect2();
        // Aktif linki işaretle
        document.querySelectorAll('.tema-menu-link').forEach(link => link.classList.remove('active'));
        const activeLink = document.querySelector(`.tema-menu-link[data-veri_cek_dizin_yolu="${phpUrl}"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }
    })
    .catch(error => {
        console.error('İçerik yüklenirken hata oluştu:', error);
        document.getElementById('tema-genelAyarlar').innerHTML = 'İçerik yüklenemedi. Hata: ' + error.message;
    });
}

// Checkbox işlevselliğini initialize eden fonksiyon
function initializeCheckboxes() {
    console.log('Checkbox işlevselliği başlatılıyor.');
    const selectAllCheckbox = document.getElementById('selectAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            console.log('Tümünü Seç checkbox değişti:', isChecked);
            rowCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });
        });
    }

    rowCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            console.log('Satır checkbox değişti:', this.id, this.checked);
            const allChecked = Array.from(rowCheckboxes).every(cb => cb.checked);
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = !allChecked && Array.from(rowCheckboxes).some(cb => cb.checked);
            }
        });
    });
}

// Ekle Modal ve form işlemlerini yöneten fonksiyon
function initializeModalHandlers() {
    console.log('Modal işleyicileri başlatılıyor.');
    initializeUpdateModal();
    initializeYayinDurumuSwitches();
    
    // Kaydet butonuna tıklama olayını dinle
    const kaydetButon = document.getElementById('KaydetButon');
	
    if (kaydetButon) {
        kaydetButon.addEventListener('click', function() {
            console.log('Kaydet butonuna tıklandı.');
            const form = document.getElementById('ekleForm');
            const formData = new FormData(form);
            const cleanedFormData = new FormData();
            const multiLangFields = {};
            const excludedProperties = ['resim_en', 'resim_boy', 'resim_yolu', 'resim_turu', 'resim_doldur', 'resim_kirp'];

            // Form elemanlarını döngüyle kontrol et
            form.querySelectorAll('[data-name]').forEach(element => {
                const dataName = element.getAttribute('data-name');
                const dataDil = element.getAttribute('data-dil');
                const value = element.value;

                // Hariç tutulan özellikleri kontrol et
                if (excludedProperties.some(prop => dataName.toLowerCase().includes(prop))) {
                    return; // Bu özelliği atla
                }

                if (dataDil) {
                    // Çoklu dil desteği olan alan
                    if (!multiLangFields[dataName]) {
                        multiLangFields[dataName] = {};
                    }
                    multiLangFields[dataName][dataDil] = value;
                } else {
                    // Tekil alan
                    cleanedFormData.append(dataName, value);
                }
            });

            // Çoklu dil objelerini JSON'a çevir ve FormData'ya ekle
            for (const [key, value] of Object.entries(multiLangFields)) {
                cleanedFormData.append(key, JSON.stringify(value));
            }

            // Resim dosyasını ekle
            const resimInput = form.querySelector('input[type="file"][name="resim"]');
            if (resimInput && resimInput.files.length > 0) {
                cleanedFormData.append('resim', resimInput.files[0]);
            }

            // Butonun data özelliklerini FormData'ya ekle
            for (let attr of this.attributes) {
                if (attr.name.startsWith('data-') && !excludedProperties.some(prop => attr.name.toLowerCase().includes(prop))) {
                    cleanedFormData.append(attr.name.slice(5), attr.value);
                }
            }

            // AJAX isteği ile verileri gönder
            fetch('islemler/tema-ayarlari.php', {
                method: 'POST',
                body: cleanedFormData
            })
            .then(response => response.text())
            .then(text => {
                console.log('Raw response:', text);
                let data;
                try {
                    data = JSON.parse(text);
                } catch (error) {
                    throw new Error('JSON parse error: ' + text);
                }
                console.log('Parsed data:', data);
                if (data.status === 'success') {
                    console.log('İşlem başarılı:', data.message);
                    $('#YeniEkleModalForm').modal('hide');
                    const activeLink = document.querySelector('.tema-menu-link.active');
                    if (activeLink) {
                        const phpUrl = activeLink.getAttribute('data-veri_cek_dizin_yolu');
                        const dataAttributes = {};
                        for (let attr of activeLink.attributes) {
                            if (attr.name.startsWith('data-')) {
                                dataAttributes[attr.name.slice(5)] = attr.value;
                            }
                        }
                        loadContent(phpUrl, dataAttributes);
                    }
                } else {
                    console.error('Hata:', data.message);
                    alert('Veri eklenirken bir hata oluştu: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('Bir hata oluştu: ' + error.message);
            });
        });
    } else {
        console.error('KaydetButon bulunamadı.');
    }

    // Sil butonuna tıklama olayını dinle
    const silButon = document.getElementById('SilButon');
    if (silButon) {
        silButon.addEventListener('click', function() {
            const selectedCheckboxes = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.id.split('_')[1]);
            if (selectedCheckboxes.length === 0) {
                alert('Lütfen silinecek öğeleri seçin.');
                return;
            }

            const formData = new FormData();
            formData.append('selectedCheckboxes', JSON.stringify(selectedCheckboxes));
            
            // Butonun data özelliklerini ekle
            for (let attr of this.attributes) {
                if (attr.name.startsWith('data-')) {
                    formData.append(attr.name.slice(5), attr.value);
                }
            }

            fetch('islemler/tema-ayarlari.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    $('#SilModalForm').modal('hide');
                    
                    // Aktif olan linki bul ve sayfayı yeniden yükle
                    const activeLink = document.querySelector('.tema-menu-link.active');
                    if (activeLink) {
                        const phpUrl = activeLink.getAttribute('data-veri_cek_dizin_yolu');
                        const dataAttributes = {};
                        for (let attr of activeLink.attributes) {
                            if (attr.name.startsWith('data-')) {
                                dataAttributes[attr.name.slice(5)] = attr.value;
                            }
                        }
                        loadContent(phpUrl, dataAttributes);
                    } else {
                        console.error('Active link bulunamadı.');
                    }
                    uyariFunctions.uyarisuccess(); // Başarılı uyarı göster
                } else {
                    alert('Hata: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('Bir hata oluştu.');
            });
        });
    }

// GuncelleButon'a tıklama olayını dinle
const guncelleButon = document.getElementById('GuncelleButon');

if (guncelleButon) {
    guncelleButon.addEventListener('click', function() {
        const form = document.getElementById('guncelleForm');
        const formData = new FormData(form);
        const cleanedFormData = new FormData();
        const multiLangFields = {};
        const excludedProperties = ['resim_en', 'resim_boy', 'resim_yolu', 'resim_turu', 'resim_doldur', 'resim_kirp'];

        // Form elemanlarını döngüyle kontrol et
        form.querySelectorAll('[data-name]').forEach(element => {
            const dataName = element.getAttribute('data-name');
            const dataDil = element.getAttribute('data-dil');
            const value = element.value;

            // Hariç tutulan özellikleri kontrol et
            if (excludedProperties.some(prop => dataName.toLowerCase().includes(prop))) {
                return; // Bu özelliği atla
            }

            if (dataDil) {
                // Çoklu dil desteği olan alan
                if (!multiLangFields[dataName]) {
                    multiLangFields[dataName] = {};
                }
                multiLangFields[dataName][dataDil] = value;
            } else {
                // Tekil alan
                cleanedFormData.append(dataName, value);
            }
        });

        // Çoklu dil objelerini JSON'a çevir ve FormData'ya ekle
        for (const [key, value] of Object.entries(multiLangFields)) {
            cleanedFormData.append(key, JSON.stringify(value));
        }

        // Resim dosyasını ekle
        const resimInput = form.querySelector('input[type="file"][name="resim"]');
        if (resimInput && resimInput.files.length > 0) {
            cleanedFormData.append('resim', resimInput.files[0]);
        }

        // Resim özelliklerini ekle
        if (resimInput) {
            cleanedFormData.append('data-resim_en', resimInput.getAttribute('data-resim_en'));
            cleanedFormData.append('data-resim_boy', resimInput.getAttribute('data-resim_boy'));
            cleanedFormData.append('data-resim_yolu', resimInput.getAttribute('data-resim_yolu'));
            cleanedFormData.append('data-resim_turu', resimInput.getAttribute('data-resim_turu'));
            cleanedFormData.append('data-resim_doldur', resimInput.getAttribute('data-resim_doldur'));
            cleanedFormData.append('data-resim_kirp', resimInput.getAttribute('data-resim_kirp'));
        }

        // Butonun data özelliklerini FormData'ya ekle
        for (let attr of this.attributes) {
            if (attr.name.startsWith('data-')) {
                cleanedFormData.append(attr.name.slice(5), attr.value);
            }
        }

        // FormData içeriğini kontrol et (debug için)
        for (let [key, value] of cleanedFormData.entries()) {
            console.log(key, value);
        }

        // AJAX isteği ile verileri gönder
        fetch('islemler/tema-ayarlari.php', {
            method: 'POST',
            body: cleanedFormData
        })
        .then(response => response.json())
        .then(data => {
            console.log('Güncelleme sonucu:', data);
            if (data.status === 'success') {
                $('#GuncelleModalForm').modal('hide');
                // Sayfayı yeniden yükle
                const activeLink = document.querySelector('.tema-menu-link.active');
                if (activeLink) {
                    const phpUrl = activeLink.getAttribute('data-veri_cek_dizin_yolu');
                    const dataAttributes = {};
                    for (let attr of activeLink.attributes) {
                        if (attr.name.startsWith('data-')) {
                            dataAttributes[attr.name.slice(5)] = attr.value;
                        }
                    }
                    loadContent(phpUrl, dataAttributes);
                    uyariFunctions.uyarisuccess(); // Başarılı uyarı göster
                }
            } else {
                alert('Hata: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Bir hata oluştu.');
        });
    });
	
	
}

}

// Modal güncelleme işlemlerini başlatan fonksiyon
function initializeUpdateModal() {
    $(document).on('click', '[data-toggle="modal"][data-target="#GuncelleModalForm"]', function(e) {
        initializeColorPicker();
        e.preventDefault();
        
        var $this = $(this);
        var id = $this.attr('data-modalduzenleid');
        var formturu = $this.attr('data-formturu');
        var tabloadi = $this.attr('data-tabloadi');
        var sutunadi = $this.attr('data-sutun-adi');
        var satiradi = $this.attr('data-satir-adi');
        
        // Resim özelliklerini al
        var $resimInput = $('#GuncelleModalForm input[type="file"][name="resim"]');
        var resimEn = $resimInput.attr('data-resim_en');
        var resimBoy = $resimInput.attr('data-resim_boy');
        var resimTuru = $resimInput.attr('data-resim_turu');
        var resimYolu = $resimInput.attr('data-resim_yolu');
        var resimDoldur = $resimInput.attr('data-resim_doldur');
        var resimKirp = $resimInput.attr('data-resim_kirp');
        
        $.ajax({
            url: 'islemler/tema-ayarlari.php',
            method: 'POST',
            data: {
                formturu: 'veri-cek',
                id: id,
                tabloadi: tabloadi,
                'sutun-adi': sutunadi,
                'satir-adi': satiradi,
                'data-resim_en': resimEn,
                'data-resim_boy': resimBoy, 
                'data-resim_turu': resimTuru,
                'data-resim_yolu': resimYolu,
                'data-resim_doldur': resimDoldur,
                'data-resim_kirp': resimKirp,
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    var data = response.data;
                    
                    // Form elemanlarını döngüye al ve doldur
                    $('#GuncelleModalForm [data-name]').each(function() {
                        var $element = $(this);
                        var dataName = $element.attr('data-name');
                        var dataDil = $element.attr('data-dil');
                        var varsayilanDeger = $element.attr('data-varsayilan-veri');
                        
                        if (data.hasOwnProperty(dataName)) {
                            // Dosya input kontrolü
                            if ($element.is(':file')) {
                                $('#modal-resim').attr('src', '../' + data[dataName]).show();
                            } 
                            // Select kontrolü
                            else if ($element.is('select')) {
                                $element.val(data[dataName]).trigger('change');
                            } 
                            // Çoklu dil desteği kontrolü
                            else if (typeof data[dataName] === 'object' && dataDil) {
                                $element.val(data[dataName][dataDil] || '');
                            } 
                            // Normal input kontrolü
                            else {
                                // Veri boş ve varsayılan değer varsa varsayılan değeri kullan
                                var value = data[dataName];
                                if ((!value || value.trim() === '') && varsayilanDeger) {
                                    value = varsayilanDeger;
                                }
                                
                                // Input değerini güncelle
                                $element.val(value);
                                
                                // Renk göstergesini güncelle (eğer varsa)
                                if (dataName) {
                                    $('.' + dataName).css('color', value);
                                    
                                    // ColorPicker'ı güncelle (eğer varsa)
                                    var $colorPicker = $element.closest('.my-colorpicker2');
                                    if ($colorPicker.length) {
                                        $colorPicker.colorpicker('setValue', value);
                                    }
                                }
                            }
                        } 
                        // Veri yoksa ve varsayılan değer varsa varsayılan değeri kullan
                        else if (varsayilanDeger) {
                            $element.val(varsayilanDeger);
                            
                            if (dataName) {
                                $('.' + dataName).css('color', varsayilanDeger);
                                
                                var $colorPicker = $element.closest('.my-colorpicker2');
                                if ($colorPicker.length) {
                                    $colorPicker.colorpicker('setValue', varsayilanDeger);
                                }
                            }
                        }
                    });
                    
                    // ID değerini güncelle
                    $('[data-name="id"]').val(id);
                    
                    // Varsayılan değere döndürme butonlarını aktifleştir
                    $('.input-group-text[data="varsayilan-veri"]').off('click').on('click', function() {
                        var $inputGroup = $(this).closest('.input-group');
                        var $input = $inputGroup.find('input');
                        var varsayilanDeger = $input.attr('data-varsayilan-veri');
                        
                        if (varsayilanDeger) {
                            $input.val(varsayilanDeger);
                            
                            var dataName = $input.attr('data-name');
                            if (dataName) {
                                $('.' + dataName).css('color', varsayilanDeger);
                            }
                            
                            var $colorPicker = $inputGroup.closest('.my-colorpicker2');
                            if ($colorPicker.length) {
                                $colorPicker.colorpicker('setValue', varsayilanDeger);
                            }
                        }
                    });
                    
                    // Modalı göster
                    $('#GuncelleModalForm').modal('show');
                } else {
                    alert('Veri alınamadı: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Ajax hatası:', error);
                alert('Veri alınırken bir hata oluştu.');
            }
        });
    });
    
    // Form gönderilmeden önce boş alanları kontrol et
    $('#guncelleForm').on('submit', function(e) {
        $(this).find('input[data-varsayilan-veri]').each(function() {
            var $input = $(this);
            if (!$input.val().trim()) {
                $input.val($input.attr('data-varsayilan-veri'));
            }
        });
    });
}


function initializeYayinDurumuSwitches() {
    console.log('Yayın durumu switchleri başlatılıyor');
    const switches = document.querySelectorAll('.yayin-durumu-switch');
    console.log('Bulunan switch sayısı:', switches.length);
    
    switches.forEach(switchEl => {
        switchEl.addEventListener('change', function() {
            console.log('Switch değişti:', this.id, 'Yeni durum:', this.checked);
            
            const formData = new FormData();
            formData.append('formturu', 'yayin-durumu');
            formData.append('id', this.dataset.id);
            formData.append('tabloadi', this.dataset.tabloadi);
            formData.append('sutun-adi', this.dataset.sutunadi);
            formData.append('satir-adi', this.dataset.satiradi);
            formData.append('yayin_durumu', this.checked ? 1 : 0);

            fetch('islemler/tema-ayarlari.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Yayın durumu güncelleme sonucu:', data);
                if (data.status === 'success') {
                    console.log('Yayın durumu başarıyla güncellendi.');
                    uyariFunctions.uyarisuccess(); // Başarılı uyarı göster
                } else {
                    console.error('Yayın durumu güncellenirken hata oluştu:', data.message);
                    this.checked = !this.checked; // Hata durumunda switch'i eski haline getir
                    uyariFunctions.uyarierror(); // Hata uyarısı göster
                }
            })
            .catch(error => {
                console.error('Fetch hatası:', error);
                this.checked = !this.checked; // Hata durumunda switch'i eski haline getir
            });
        });
    });
}

// Yeni Ekle butonuna tıklama olayını dinle
document.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('btn-success') && e.target.getAttribute('data-toggle') === 'modal') {
        console.log('Yeni Ekle butonuna tıklandı.');
		
    }
});


function reinitializeSelect2() {
    $('#YeniEkleModalForm').on('shown.bs.modal', function () {
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#YeniEkleModalForm'),
            width: '100%' // Genişlik sorunlarını önlemek için
        });
		    //Bootstrap Duallistbox
    $('.duallistbox').bootstrapDualListbox()
    });

    // Modal kapandığında Select2'yi temizle
    $('#YeniEkleModalForm').on('hidden.bs.modal', function () {
        $('.select2bs4').select2('destroy');
		    //Bootstrap Duallistbox
    $('.duallistbox').bootstrapDualListbox()
    });     
	
	$('#GuncelleModalForm').on('shown.bs.modal', function () {
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#GuncelleModalForm'),
            width: '100%' // Genişlik sorunlarını önlemek için
        });
		    //Bootstrap Duallistbox
    $('.duallistbox').bootstrapDualListbox()
    });

    // Modal kapandığında Select2'yi temizle
    $('#GuncelleModalForm').on('hidden.bs.modal', function () {
        $('.select2bs4').select2('destroy');
		    //Bootstrap Duallistbox
		$('.duallistbox').bootstrapDualListbox()
    });
}

function initializeAnasayfaMetaAyarlari() {
    $('#sayfa_EkleButon').on('click', function(e) {
        e.preventDefault();

        var formData = new FormData($('#SayfaAyarlarForm')[0]);
        
        // Veri yapısını oluştur
        var veriYapisi = {
            diller: {
                tr: {
                    baslik: formData.get('sayfa_baslik_tr'),
                    aciklama: formData.get('sayfa_aciklama_tr'),
                    meta_baslik: formData.get('sayfa_meta_baslik_tr'),
                    meta_aciklama: formData.get('sayfa_meta_aciklama_tr'),
                    link: formData.get('sayfa_link_tr'),
                    etiketler: formData.get('sayfa_etiketler_tr').split(',').map(item => item.trim())
                }
            },
            ortak_alanlar: {
                sayfa_kisa_aciklama: formData.get('sayfa_kisa-aciklama')
            }
        };

        // Veri yapısını JSON'a çevir ve formData'ya ekle
        formData.set('veri', JSON.stringify([veriYapisi]));

        formData.append('action', 'update_sayfa_ayarlar');
        formData.append('tablo_adi', $('#SayfaAyarModal').data('sayfa_ayar_tablo_adi'));
        formData.append('satir_id', $('#SayfaAyarModal').data('sayfa_ayar_tablo_satir_id'));

        $.ajax({
            url: 'islemler/veri-islem.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showToast('Başarılı', response.message, 'success');
                    $('#SayfaAyarModal').modal('hide');
                    if (typeof loadTableData === 'function') {
                        loadTableData();
                    }
                } else {
                    showToast('Hata', response.message, 'error');
                }
            },
            error: function() {
                showToast('Hata', 'İşlem sırasında bir hata oluştu', 'error');
            }
        });
    });
}

// Toast mesajlarını göstermek için yardımcı fonksiyon
function showToast(title, message, type) {
    // Eğer toastr kütüphanesi kullanılıyorsa:
    if (typeof toastr !== 'undefined') {
        toastr[type](message, title);
    } else {
        // Basit bir alert kullan
        alert(title + ': ' + message);
    }
}


// color picker
function initializeColorPicker() {
    $('.my-colorpicker2').colorpicker();
    
    // Renk değişikliğinde güncelleme
    $('.my-colorpicker2').on('colorpickerChange', function(event) {
        var $input = $(this).find('input');
        var dataName = $input.attr('data-name');
        if (dataName) {
            $('.' + dataName).css('color', event.color.toString());
        }
    });

    // Varsayılan değere döndürme butonlarını initialize et
    $('.input-group-text[data="varsayilan-veri"]').on('click', function() {
        var $inputGroup = $(this).closest('.input-group');
        var $input = $inputGroup.find('input');
        
        // Eğer input'ta data-varsayilan-veri attribute'u varsa işlem yap
        var varsayilanDeger = $input.attr('data-varsayilan-veri');
        if (varsayilanDeger) {
            // Input değerini güncelle
            $input.val(varsayilanDeger);
            
            // Renk göstergesini güncelle
            var dataName = $input.attr('data-name');
            if (dataName) {
                $('.' + dataName).css('color', varsayilanDeger);
            }
            
            // ColorPicker'ı güncelle
            $inputGroup.colorpicker('setValue', varsayilanDeger);
        }
    });

    // Form gönderilmeden önce boş alanları kontrol et
    $('#guncelleForm, #ekleForm').on('submit', function(e) {
        $(this).find('input[data-varsayilan-veri]').each(function() {
            var $input = $(this);
            if (!$input.val().trim()) {
                $input.val($input.attr('data-varsayilan-veri'));
            }
        });
    });

    // Sayfa yüklendiğinde boş olan ve varsayılan değeri olan inputları doldur
    $('input[data-varsayilan-veri]').each(function() {
        var $input = $(this);
        if (!$input.val().trim()) {
            var varsayilanDeger = $input.attr('data-varsayilan-veri');
            $input.val(varsayilanDeger);
            
            var dataName = $input.attr('data-name');
            if (dataName) {
                $('.' + dataName).css('color', varsayilanDeger);
            }
        }
    });

    $("input[data-bootstrap-switch]").each(function(){
        $(this).bootstrapSwitch('state', $(this).prop('checked'));
    });
}