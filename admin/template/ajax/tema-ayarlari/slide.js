// slide.js

function initializeSlideList() {
    console.log('Slide listesi başlatılıyor...');
    initializeYayinDurumu();
    initializeEditLinks();
}

function initializeYayinDurumu() {
    document.querySelectorAll('input[name="yayin_durumu"]').forEach(input => {
        input.removeEventListener('change', handleYayinDurumuChange);
        input.addEventListener('change', handleYayinDurumuChange);
    });
}

function handleYayinDurumuChange() {
    console.log('Yayın durumu değiştirildi');
    updateYayinDurumu(this);
}

function updateYayinDurumu(element) {
    var isChecked = element.checked ? 1 : 0;
    var tableId = element.getAttribute('data-temaayartableid');
    var tableIsmi = element.getAttribute('data-temaayartableismi');
    var formTuru = element.getAttribute('data-temaformturu');

    console.log('Yayın durumu güncelleniyor:', isChecked, tableId, tableIsmi, formTuru);

    var formData = new FormData();
    formData.append('yayin_durumu', isChecked);
    formData.append('table_id', tableId);
    formData.append('table_ismi', tableIsmi);
    formData.append('form_turu', formTuru);

    sendAjaxRequest(formData, function(result) {
        console.log('Yayın durumu güncellendi:', result);
        uyariFunctions.uyarisuccess('Yayın durumu güncellendi');
    });
}

function initializeEditLinks() {
    document.querySelectorAll('a[data-temaformturu="slide_guncelle"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            editSlide(this);
        });
    });
}

function editSlide(element) {
    var tableId = element.getAttribute('data-temaAyarTableId');
    var tableIsmi = element.getAttribute('data-temaAyarTableIsmi');
    var formTuru = element.getAttribute('data-temaformturu');

    console.log('Edit slide çağrıldı. Veri:', { tableId, tableIsmi, formTuru });

    var url = 'islemler/tema/slide-alani/slide.php?' + new URLSearchParams({
        table_id: tableId,
        table_ismi: tableIsmi,
        form_turu: formTuru
    });

    console.log('İstek gönderiliyor:', url);

    fetch(url)
    .then(response => {
        console.log('Sunucu yanıtı alındı. Status:', response.status);
        return response.text();
    })
    .then(text => {
        console.log('Sunucu yanıtı (ham):', text);
        return JSON.parse(text);
    })
    .then(data => {
        if (data.error) {
            console.error('Sunucu hatası:', data.error);
            return;
        }
        console.log('İşlenmiş veri:', data);
        populateEditModal(data);
        $('#editModal').modal('show');
    })
    .catch(error => console.error('Hata oluştu:', error));
}

function populateEditModal(data) {
    console.log('Modal dolduruluyor:', data);
    var modal = document.getElementById('editModal');
    modal.querySelector('#table_id').value = data['data-temaAyarTableId'];
    modal.querySelector('#table_ismi').value = data['data-temaAyarTableIsmi'];
    modal.querySelector('#form_turu').value = 'slide_guncelle';
    modal.querySelector('#baslik').value = data['Başlık'];
    modal.querySelector('#link').value = data['Link'];
    modal.querySelector('#src_resim').src = baseurl_onyuz + data['Resim'];
    modal.querySelector('#resim_yolu').value = data['Resim'];

    // Modal kaydet butonu için event listener
    modal.querySelector('#saveButton').addEventListener('click', function() {
        saveEditedSlide(modal);
    });
}

function saveEditedSlide(modal) {
    var formData = new FormData(modal.querySelector('#editForm'));
    formData.append('form_turu', 'slide_guncelle');

    console.log('Gönderilen form verileri:', Object.fromEntries(formData));

    sendAjaxRequest(formData, function(result) {
        $('#editModal').modal('hide');
        result.table_id = formData.get('table_id');
        result.Başlık = formData.get('baslik');
        result.Link = formData.get('link');
        console.log('Sunucudan gelen yanıt (ek bilgilerle):', result);
        updateSlideUI(result);
        uyariFunctions.uyarisuccess('Slide başarıyla güncellendi.');
    });
}

function initializeAddButton() {
    console.log('Yeni ekle butonu başlatılıyor...');
    document.getElementById('addButton').addEventListener('click', function() {
        resetAddModal();
        $('#addModal').modal('show');
    });

    document.getElementById('addModal').querySelector('#addsaveButton').addEventListener('click', function() {
        saveNewSlide();
    });
}

function resetAddModal() {
    console.log('Add modal sıfırlanıyor...');
    var modal = document.getElementById('addModal');
    modal.querySelector('#editForm').reset();
    modal.querySelector('#addresim').value = '';
}

function saveNewSlide() {
    var formData = new FormData(document.getElementById('addModal').querySelector('#editForm'));
    formData.append('form_turu', 'veriekle');
    formData.append('table_ismi', 'slide');

    sendAjaxRequest(formData, function(result) {
        $('#addModal').modal('hide');
        result.Başlık = formData.get('baslik');
        result.Link = formData.get('link');
        addNewSlideToUI(result);
        uyariFunctions.uyarisuccess('Yeni slide başarıyla eklendi.');
        resetAddModal();
    });
}

function sendAjaxRequest(formData, successCallback) {
    $.ajax({
        url: 'islemler/tema/slide-alani/slide.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            console.log('AJAX yanıtı:', response);
            try {
                var result = JSON.parse(response);
                if (result.success) {
                    successCallback(result);
                } else {
                    console.error('Hata:', result.error);
                    uyariFunctions.uyarierror('İşlem sırasında bir hata oluştu.');
                }
            } catch (e) {
                console.error('JSON ayrıştırma hatası:', e);
                console.log('Ham yanıt:', response);
                uyariFunctions.uyarierror('Beklenmeyen bir hata oluştu.');
            }
        },
        error: function(xhr, status, error) {
            console.error('Hata oluştu:', error);
            uyariFunctions.uyarierror('Sunucu ile iletişim sırasında bir hata oluştu.');
        }
    });
}

function updateSlideUI(data) {
    console.log('updateSlideUI çağrıldı. Gelen veri:', data);
    
    var row;
    if (data.table_id) {
        row = document.querySelector('tr[data-temaayartableid="' + data.table_id + '"]');
        console.log('table_id ile aranan satır:', row);
    }
    if (!row && data.new_id) {
        row = document.querySelector('tr[data-temaayartableid="' + data.new_id + '"]');
        console.log('new_id ile aranan satır:', row);
    }
    
    if (row) {
        console.log('Güncellenecek satır bulundu:', row);
        
        if (data.Resim || data.new_image_path) {
            var imgSrc = baseurl_onyuz + (data.Resim || data.new_image_path);
            row.querySelector('td:nth-child(1) img').src = imgSrc;
            console.log('Resim güncellendi:', imgSrc);
        }
        if (data.Başlık) {
            row.querySelector('td:nth-child(2) a').textContent = data.Başlık;
            console.log('Başlık güncellendi:', data.Başlık);
        }
        if (data.Link) {
            row.querySelector('td:nth-child(3)').textContent = data.Link;
            console.log('Link güncellendi:', data.Link);
        }
        
        console.log('Satır güncellendi');
    } else {
        console.error('Güncellenecek satır bulunamadı. Arama kriterleri:', { table_id: data.table_id, new_id: data.new_id });
        document.querySelectorAll('tr[data-temaayartableid]').forEach(tr => {
            console.log('Mevcut satır:', tr.getAttribute('data-temaayartableid'), tr.outerHTML);
        });
    }
}

function addNewSlideToUI(result) {
    console.log('Yeni slide UI\'a ekleniyor:', result);
    var baslik = document.getElementById('addModal').querySelector('#eddbaslik').value;
    var link = document.getElementById('addModal').querySelector('#eddlink').value;
    var newRow = `
        <tr data-temaayartableid="${result.new_id}">
            <td><img src="${baseurl_onyuz}${result.new_image_path}" alt="Resim" style="height: 35px;"></td>
            <td><a href="#" onclick="editSlide(this); return false;" data-temaformturu="slide_guncelle" data-temaAyarTableId="${result.new_id}" data-temaAyarTableIsmi="slide">${baslik}</a></td>
            <td>${link}</td>
            <td>
                <div class="custom-control custom-switch custom-switch-on-success float-right">
                    <input name="yayin_durumu" type="checkbox" class="custom-control-input" id="yayin_durumu_${result.new_id}" value="1" checked data-temaformturu="yayin_durumu_guncelle" data-temaayartableismi="slide" data-temaayartableid="${result.new_id}">
                    <label class="custom-control-label" for="yayin_durumu_${result.new_id}">Yayın Durumu</label>
                </div>
            </td>
        </tr>
    `;
    document.querySelector('table tbody').insertAdjacentHTML('beforeend', newRow);
    initializeYayinDurumu();
    initializeEditLinks();
}

function getDataAttributes(element) {
    var data = {};
    Object.keys(element.dataset).forEach(key => {
        data[key] = element.dataset[key];
    });
    return data;
}

function bindModalEvents() {
    $('#editModal').off('click', '#saveButton').on('click', '#saveButton', function() {
        saveEditedSlide(document.getElementById('editModal'));
    });

    $('#editModal').off('click', '#deleteButton').on('click', '#deleteButton', function() {
        var tableId = $('#editModal #table_id').val();
        var tableIsmi = $('#editModal #table_ismi').val();

        if (confirm('Bu slide\'ı silmek istediğinizden emin misiniz?')) {
            deleteSlide(tableId, tableIsmi);
        }
    });
}

function deleteSlide(tableId, tableIsmi) {
    var formData = new FormData();
    formData.append('form_turu', 'verisil');
    formData.append('table_id', tableId);
    formData.append('table_ismi', tableIsmi);

    sendAjaxRequest(formData, function(result) {
        if (result.success) {
            $('#editModal').modal('hide');
            removeSlideFromUI(tableId);
            uyariFunctions.uyarisuccess('Slide başarıyla silindi.');
        } else {
            uyariFunctions.uyarierror('Silme işlemi sırasında bir hata oluştu.');
        }
    });
}

function removeSlideFromUI(tableId) {
    var row = document.querySelector('tr[data-temaayartableid="' + tableId + '"]');
    if (row) {
        row.remove();
        console.log('Slide UI\'dan kaldırıldı:', tableId);
    } else {
        console.error('Silinecek slide bulunamadı. table_id:', tableId);
    }
}

// Sayfa yüklendiğinde veya içerik dinamik olarak yüklendiğinde çağrılacak
function onSlideContentLoaded() {
    console.log('Slide içeriği yüklendi, modül başlatılıyor...');
    initializeSlideList();
    initializeAddButton();
    bindModalEvents();
}

// Bu satır, dosya yüklendiğinde otomatik olarak çalışacak
onSlideContentLoaded();