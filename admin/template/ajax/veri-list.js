// yeni js

$(document).ready(function() {
    const sayfaCek = document.getElementById('sayfa-cek');
    const dizinYolu = sayfaCek.getAttribute('data-dizin_yolu');
    const tabloAdi = sayfaCek.getAttribute('data-tablo_adi');
    const sutunAdi = sayfaCek.getAttribute('data-sutun_adi');
    const jsonSutunAdi = sayfaCek.getAttribute('data-json_sutun_adi');
    const tabloBasliklari = sayfaCek.getAttribute('data-tablo_basliklari');
    const userDil = sayfaCek.getAttribute('data-user_dil');
   
    let dataTable;
    // Global değişken olarak yeni resimleri saklayacağımız bir dizi oluşturalım
	var newSelectedFiles = [];
    loadTableData(); 
    

$('.summernote').summernote({height: 300}); 

	function loadTableData() {
		startLoading();
		// Filtreleme özelliklerini al
		const filtreleBaslik = sayfaCek.getAttribute('data-veri-filtrele-baslik');
		const filtreleVeri = sayfaCek.getAttribute('data-veri-filtrele-veri');

		$.ajax({
			url: dizinYolu,
			method: 'POST',
			data: {
				tablo_adi: tabloAdi,
				sutun_adi: sutunAdi,
				json_sutun_adi: jsonSutunAdi,
				tablo_basliklari: tabloBasliklari,
				user_dil: userDil,
				veri_filtrele_baslik: filtreleBaslik, // Yeni eklenen
				veri_filtrele_veri: filtreleVeri      // Yeni eklenen
			},
			dataType: 'json',
			success: function(response) {
				stopLoading();
				if (response.success) {
					sayfaCek.innerHTML = response.html;
					initializeDataTable();
				} else {
					sayfaCek.innerHTML = `<div class="alert alert-danger">Veriler yüklenirken bir hata oluştu: ${response.error}</div>`;
				}
			},
			error: function(xhr, status, error) {
				stopLoading();
				sayfaCek.innerHTML = '<div class="alert alert-danger">Ağ hatası: Veriler yüklenemedi.</div>';
			}
		});
	}

    function initializeDataTable() {
        if (dataTable) {
            dataTable.destroy();
        }

        dataTable = $('#example1').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": true,
            "responsive": true,
            "language": {
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
			"buttons": [
				"excel", "print",
                {
                    text: 'Sayfa Genel Ayarlar',
                    className: 'btn-primary gizle',
                    action: function (e, dt, node, config) {
                        $('#SayfaAyarModal').modal('show');
                    }
                },				
				{
					text: 'Yeni Ekle',
					className: 'btn-success gizle', // 'gizle' sınıfını ekledik
					action: function (e, dt, node, config) {
						openVeriIslemModal('add');
					}
				},
				{
					text: 'Kopyala',
					className: 'btn-primary gizle', // 'gizle' sınıfını ekledik
					action: function (e, dt, node, config) {
						var selectedIds = $('.row-select:checked').map(function() {
							return this.value;
						}).get();
						if (selectedIds.length > 0) {
							copyRecords(selectedIds);
						} else {
							showToast('Uyarı', 'Lütfen kopyalanacak satırları seçin.', 'warning');
						}
					}
				},
                {
                    text: 'Sil',
                    className: 'btn-danger',
                    action: function (e, dt, node, config) {
                        $('#SilModal').modal('show');
                    }
                },

            ],
            'columnDefs': [{
                'targets': 0,
                'checkboxes': {
                   'selectRow': true
                }
            }],
            'select': {
                'style': 'multi'
            },
            "order": [[3, 'asc']],
            "columnDefs": [
                { "type": "num", "targets": 3 }
            ],
        });

        dataTable.buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');

        bindEventListeners();
    }
	
	function copyRecords(ids) {
		startLoading();
		$.ajax({
			url: 'islemler/veri-islem.php',
			method: 'POST',
			data: {
				action: 'copy',
				ids: ids,
				tablo_adi: tabloAdi
			},
			dataType: 'json',
			success: function(response) {
				stopLoading();
				if (response.success) {
					loadTableData();
					showToast('Başarılı', response.message, 'success');
				} else {
					showToast('Hata', response.message, 'error');
				}
			},
			error: function(xhr, status, error) {
				stopLoading();
				showToast('Hata', 'Kopyalama işlemi sırasında bir hata oluştu.', 'error');
			}
		});
	}	

    function bindEventListeners() {
        $('#example1').on('click', '.update-link', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            openVeriIslemModal('update', id);
        });

        $('#select-all').on('click', function() {
            $('.row-select').prop('checked', this.checked);
        });

        $('.row-select').on('click', function() {
            $('#select-all').prop('checked', $('.row-select:checked').length === $('.row-select').length);
        });

        $('#SilButon').off('click').on('click', function() {
            var selectedIds = $('.row-select:checked').map(function() {
                return this.value;
            }).get();

            if (selectedIds.length > 0) {
                deleteRecords(selectedIds);
            } else {
                showToast('Uyarı', 'Lütfen silinecek satırları seçin.', 'warning');
            }
        });

        $('#example1').on('change', '.yayin-durumu-switch', function() {
            var id = $(this).data('id');
            var yayinDurumu = $(this).prop('checked') ? 1 : 0;
            updateYayinDurumu(id, yayinDurumu);
        });
    }

function openVeriIslemModal(action, id = null) {
    var modal = $('#VeriIslemModal');
    modal.find('.modal-title').text(action === 'add' ? 'Yeni Veri Ekle' : 'Veri Güncelle');
    $('#EkleButon').text(action === 'add' ? 'Ekle' : 'Güncelle').data('action', action);
    $('#VeriEkleForm').data('id', id);
    
    // Her modal açılışında ek form container'ını temizle
    $('#ek-form-container').empty();
    
    if (action === 'add') {
        modal.find('form')[0].reset();
        $('#yayin_durumu').prop('checked', true);
        loadCategories();
        $('select[data-select_tablo_adi]').each(function() {
            loadSelectOptions($(this));
        });

        $('#resim-preview-container').empty();
        $('input[type="file"]').val('');
        $('#image-actions').hide();
        $('.summernote').summernote('code', '');
        setupImageUpload();

        modal.modal('show');
    } else if (action === 'update') {
        startLoading();
        $.ajax({
            url: 'islemler/veri-islem.php',
            method: 'GET',
            data: { 
                action: 'get', 
                id: id,
                tablo_adi: tabloAdi
            },
            dataType: 'json',
            success: function(response) {
                stopLoading();
                if (response.success) {
                    console.log("AJAX yanıtı:", response);
                    
                    if (response.data.ek_form) {
                        var ekFormlar = response.data.ek_form.split(',');
                        loadEkFormlar(ekFormlar, response.data, function() {
                            fillFormWithData(response.data).then(() => {
                                loadCategories(response.data.ust_kategori_id);
                                setupImageUpload();
                                modal.modal('show');
                            });
                        });
                    } else {
                        fillFormWithData(response.data).then(() => {
                            loadCategories(response.data.ust_kategori_id);
                            setupImageUpload();
                            modal.modal('show');
                        });
                    }
                } else {
                    showToast('Hata', 'Veri getirme hatası: ' + response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                stopLoading();
                showToast('Hata', 'Veri getirme sırasında bir hata oluştu.', 'error');
            }
        });
    }
}

function loadEkFormlar(ekFormlar, veriJson, callback) {
    var ekFormContainer = $('#ek-form-container');
    ekFormContainer.empty();
    
    var loadedForms = 0;
    var totalForms = ekFormlar.length;
    
    ekFormlar.forEach(function(formAdi) {
        if (formAdi.trim() !== '') {
            $.ajax({
                url: 'template/sayfalar/ek-formlar/' + formAdi.trim() + '.php',
                method: 'GET',
                data: { veri: JSON.stringify(veriJson) },
                success: function(formIcerigi) {
                    ekFormContainer.append(formIcerigi);
                    loadedForms++;
                    
                    if (loadedForms === totalForms) {
                        setupFormFieldListeners();
                        initializeSelect2Elements();
                        if (typeof callback === 'function') {
                            callback();
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error(formAdi + ' yüklenirken bir hata oluştu:', error);
                    loadedForms++;
                    
                    if (loadedForms === totalForms) {
                        setupFormFieldListeners();
                        initializeSelect2Elements();
                        if (typeof callback === 'function') {
                            callback();
                        }
                    }
                }
            });
        } else {
            loadedForms++;
        }
    });
    
    if (totalForms === 0) {
        if (typeof callback === 'function') {
            callback();
        }
    }
}

function fillFormWithData(data) {
    console.log("fillFormWithData'ya gelen veri:", data);
    var jsonData;
    try {
        jsonData = typeof data.veri === 'string' ? JSON.parse(data.veri)[0].data : data.veri[0].data;
    } catch (error) {
        console.error('JSON parse hatası:', error);
        jsonData = {};
    } 

    var promises = [];

    if (jsonData.diller) {
        $.each(jsonData.diller, function(lang, langData) {
            $.each(langData, function(field, value) {
                var element = $('[name="' + field + '_' + lang + '"]');
                setElementValue(element, value);
            });
        });
    }

    if (jsonData.ortak_alanlar) {
        $.each(jsonData.ortak_alanlar, function(field, value) {
            var element = $('[name="' + field + '"][data-json_ortak_alan="evet"]');
            if (element.is('select') && element.attr('data-select_tablo_adi')) {
                promises.push(loadSelectOptions(element, value));
            } else {
                setElementValue(element, value);
            }
        });
    }

    $('#sira').val(data.sira);
    $('#yayin_durumu').prop('checked', data.yayin_durumu == 1);

    if (jsonData.resimler) {
        updateImagePreviews(jsonData.resimler);
    }

    if (data.ust_kategori_id) {
        var ustKategoriIds = data.ust_kategori_id.split(',');
        $('#ust_kategori_id').val(ustKategoriIds).trigger('change');
    }

    return Promise.all(promises).then(() => {
        initializeSelect2Elements();
    });
}

    function setElementValue(element, value) {
        if (element.length) {
            if (element.is('select')) {
                if (element.prop('multiple')) {
                    element.val(Array.isArray(value) ? value : [value]).trigger('change');
                } else {
                    element.val(value).trigger('change');
                }
                
                if (element.hasClass('select2')) {
                    if ($.fn.select2) {
                        if (element.data('select2')) {
                            element.select2('destroy');
                        }
                        element.select2();
                        element.trigger('change.select2');
                    }
                }
            } else if (element.is(':checkbox')) {
                element.prop('checked', value === true || value === '1' || value === 1);
            } else if (element.hasClass("summernote")) {
                element.summernote('code', value);
            } else if (Array.isArray(value)) {
                element.val(value.join(','));
            } else {
                element.val(value);
            }
        }
    }



function setupImageUpload() {
    $('input[type="file"]').off('change').on('change', function(e) {
        var files = e.target.files;
        var existingImages = $('.existing-image').map(function() {
            return {
                dosya_adi: $(this).attr('src').replace(baseurl_onyuz, ''),
                kapak_resim: $(this).closest('.image-wrapper').find('.fa-star').length > 0 ? 'evet' : 'hayir'
            };
        }).get();
        
        newImages = []; // Her yeni yükleme işleminde newImages'ı sıfırla
        var filesProcessed = 0;
        
        function checkAllFilesProcessed() {
            if (filesProcessed === files.length) {
                updateImagePreviews(existingImages.concat(newImages));
            }
        }
        
        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            var reader = new FileReader();
            
            reader.onload = (function(file, index) {
                return function(e) {
                    newImages.push({
                        dosya_adi: e.target.result,
                        id: 'new-' + index,
                        file: file
                    });
                    filesProcessed++;
                    checkAllFilesProcessed();
                };
            })(file, i);
            
            reader.readAsDataURL(file);
        }
    });
}

function updateImagePreviews(images) {
    var resimPreviewContainer = $('#resim-preview-container');
    resimPreviewContainer.empty();
    if (images && Array.isArray(images) && images.length > 0) {
        images.forEach(function(resim, index) {
            var imgSrc, isNewImage, imgId, isKapak;
            if (resim.dosya_adi && resim.dosya_adi.startsWith('data:')) {
                isNewImage = true;
                imgSrc = resim.dosya_adi;
                imgId = resim.id || ('new-' + index);
                isKapak = false;
            } else if (resim && typeof resim === 'object' && resim.dosya_adi) {
                isNewImage = false;
                imgSrc = baseurl_onyuz + resim.dosya_adi;
                imgId = 'existing-' + $('#VeriEkleForm').data('id') + '-' + index;
                isKapak = resim.kapak_resim === 'evet';
            } else {
                console.error('Geçersiz resim formatı:', resim);
                return;
            }
            
            var imgWrapper = $('<div>')
                .addClass('image-wrapper')
                .css({
                    'display': 'inline-block',
                    'position': 'relative',
                    'margin': '5px'
                });
            var imgElement = $('<img>')
                .attr('src', imgSrc)
                .attr('alt', 'Resim ' + (index + 1))
                .attr('id', imgId)
                .addClass('img-thumbnail')
                .addClass(isNewImage ? 'new-image' : 'existing-image')
                .css('max-width', '100px');
            
            if (!isNewImage) {
                var checkboxElement = $('<input>')
                    .attr('type', 'checkbox')
                    .attr('id', 'check-' + imgId)
                    .addClass('image-checkbox')
                    .css({
                        'position': 'absolute',
                        'top': '5px',
                        'left': '5px'
                    });
                imgWrapper.append(checkboxElement);
            }
            
            if (isKapak) {
                imgWrapper.append($('<i class="fas fa-star text-warning" style="position: absolute; top: 5px; right: 5px;"></i>'));
            }
            if (isNewImage) {
                imgWrapper.append($('<i class="fas fa-upload text-success" style="position: absolute; top: 5px; right: 5px; font-size: 12px;"></i>'));
            }
            imgWrapper.append(imgElement);
            resimPreviewContainer.append(imgWrapper);
        });
    } else {
        resimPreviewContainer.append('<p>Resim eklenmemiş</p>');
    }
    
    bindImageActions();
}

function bindImageActions() {
    $('.image-wrapper').off('click').on('click', function(e) {
        if ($(this).find('img').hasClass('existing-image')) {
            if (!$(e.target).is('input:checkbox')) {
                var checkbox = $(this).find('input:checkbox');
                checkbox.prop('checked', !checkbox.prop('checked'));
                updateImageSelection($(this));
            }
        }
    });

    $('.image-checkbox').off('change').on('change', function() {
        updateImageSelection($(this).closest('.image-wrapper'));
    });

$('#delete-images').off('click').on('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    var checkedImages = $('.image-checkbox:checked');
    if (checkedImages.length === 0) {
        showToast('Uyarı', 'Lütfen silinecek resimleri seçin', 'warning');
        return;
    }
    if (confirm('Seçili resimleri silmek istediğinizden emin misiniz?')) {
        var imagesToDelete = [];
        var wrappersToRemove = [];

        checkedImages.each(function() {
            var wrapper = $(this).closest('.image-wrapper');
            var imgElement = wrapper.find('img');
            var imgId = imgElement.attr('id');
            var parts = imgId.split('-');
            
            imagesToDelete.push({
                id: parts[1],
                index: parseInt(parts[2])
            });
            wrappersToRemove.push(wrapper);
        }); 

        deleteMultipleImages(imagesToDelete, wrappersToRemove);
    }
});

	$('#make-cover').off('click').on('click', function(e) {
			e.preventDefault();
			e.stopPropagation();
			var checkedImage = $('.image-checkbox:checked');
			if (checkedImage.length !== 1) {
				showToast('Uyarı', 'Lütfen kapak resmi olarak ayarlamak için bir resim seçin', 'warning');
				return;
			}
			var wrapper = checkedImage.closest('.image-wrapper');
			var imgElement = wrapper.find('img');
			var imgId = imgElement.attr('id');
			var parts = imgId.split('-');

			updateCoverImage(parts[1], parseInt(parts[2]));
		});
    }

function updateCoverImage(id, imageIndex) {
    $.ajax({
        url: 'islemler/veri-islem.php',
        method: 'POST',
        data: {
            action: 'update_cover_image',
            id: id,
            image_index: imageIndex,
            tablo_adi: tabloAdi
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Tüm resim çerçevelerini sıfırla
                $('.image-wrapper img').css('border', '');
                // Tüm yıldız ikonlarını kaldır
                $('.image-wrapper .fa-star').remove();
                // Seçili resme yıldız ikonu ekle
                $('.image-wrapper').eq(imageIndex).find('img').css('border', '').after('<i class="fas fa-star text-warning" style="position: absolute; top: 5px; right: 5px;"></i>');
                
                showToast('Başarılı', 'Kapak resmi başarıyla güncellendi', 'success');
                
                // Checkbox'ları temizle ve butonları gizle
                $('.image-checkbox').prop('checked', false);
                updateImageActionButtons();
                
                // Veri listesini güncelle
                loadTableData();
            } else {
                showToast('Hata', response.message, 'error');
            }
        },
        error: function(xhr, status, error) {
            showToast('Hata', 'Kapak resmi güncellenirken bir hata oluştu', 'error');
            console.error('AJAX hatası:', status, error);
        }
    });
}

function deleteMultipleImages(imagesToDelete, wrappersToRemove) {
    // Resimleri indekse göre sırala (büyükten küçüğe)
    imagesToDelete.sort((a, b) => b.index - a.index);

    $.ajax({
        url: 'islemler/veri-islem.php',
        method: 'POST',
        data: {
            action: 'delete_multiple_images',
            images: JSON.stringify(imagesToDelete),
            tablo_adi: tabloAdi,
            baseurl_onyuz: baseurl_onyuz
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                wrappersToRemove.forEach(function(wrapper) {
                    wrapper.remove();
                });
                showToast('Başarılı', response.message, 'success');
            } else {
                showToast('Uyarı', response.message, 'warning');
                console.error('Resim silme hatası:', response.message);
            }
            updateImageActionButtons();
            loadTableData(); // Her durumda tabloyu yenile
        },
        error: function(xhr, status, error) {
            showToast('Hata', 'Resimler silinirken bir hata oluştu', 'error');
            console.error('AJAX hatası:', status, error);
            loadTableData(); // Hata durumunda da tabloyu yenile
        }
    });
}


    function deleteImage(imgId, wrapper) {
        var parts = imgId.split('-');
        var id = parts[1]; // Ana kaydın ID'si
        var imageIndex = parts[2]; // Resmin indeksi
        
        $.ajax({
            url: 'islemler/veri-islem.php',
            method: 'POST',
            data: {
                action: 'delete_image',
                id: id,
                image_index: imageIndex,
                tablo_adi: tabloAdi
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    wrapper.remove();
                    showToast('Başarılı', 'Resim başarıyla silindi', 'success');
                } else {
                    showToast('Hata', response.message, 'error');
                    console.error('Resim silme hatası:', response.message);
                }
            },
            error: function(xhr, status, error) {
                showToast('Hata', 'Resim silinirken bir hata oluştu', 'error');
                console.error('AJAX hatası:', status, error);
            }
        });
    }

function updateImageSelection(imageWrapper) {
    var checkbox = imageWrapper.find('input:checkbox');
    var img = imageWrapper.find('img');
    if (checkbox.prop('checked')) {
        img.css('border', '2px solid red');
    } else {
        img.css('border', '');
    }
    updateImageActionButtons();
}

function clearImageSelections() {
    $('.image-checkbox').prop('checked', false);
    $('.image-wrapper img').css('border', '');
    updateImageActionButtons();
}

function updateImageActionButtons() {
    var checkedCount = $('.image-checkbox:checked').length;
    if (checkedCount > 0) {
        $('#image-actions').show();
        $('#delete-images').show();
        $('#make-cover').toggle(checkedCount === 1);
    } else {
        $('#image-actions').hide();
    }
}


    // Form gönderme işlemi
$('#EkleButon').on('click', function(e) {
    e.preventDefault();
    clearValidationErrors();

    if (!validateForm()) {
        console.log('Form doğrulama başarısız');
        return;
    }

    console.log('Form doğrulama başarılı, gönderiliyor');

    var formData = new FormData();
    
    // Tüm form elemanlarını, dinamik olarak eklenenleri de dahil ederek topla
    $('#VeriEkleForm, #ek-form-container').find('input, select, textarea').each(function() {
        var $input = $(this);
        if (!$input.hasClass('image-checkbox') && $input.attr('type') !== 'file') {
            if ($input.attr('type') === 'checkbox') {
                formData.append($input.attr('name'), $input.prop('checked') ? '1' : '0');
            } else {
                formData.append($input.attr('name'), $input.val());
            }
        }
    });

    var action = $(this).data('action');
    formData.append('action', action);
    
    // Mevcut resimleri formData'ya ekle
    var existingImages = [];
    $('.existing-image').each(function() {
        var imgWrapper = $(this).closest('.image-wrapper');
        var isKapak = imgWrapper.find('.fa-star').length > 0;
        existingImages.push({
            dosya_adi: $(this).attr('src').replace(baseurl_onyuz, ''),
            kapak_resim: isKapak ? 'evet' : 'hayir'
        });
    });
    formData.append('existing_images', JSON.stringify(existingImages));

    // Yeni seçilen dosyaları formData'ya ekle
    if (typeof newImages !== 'undefined' && newImages.length > 0) {
        newImages.forEach(function(img, index) {
            if (img.file) {
                formData.append('dosya_adi[]', img.file);
            }
        });
    }

    // Resim kayıt dizinini ve diğer özellikleri ekle
    var fileInput = $('input[type="file"][name="dosya_adi[]"]')[0];
    var requiredAttributes = ['resim_eni', 'resim_boyu', 'resim_kayit_dizini', 'resmi_kirp', 'resmi_doldur'];
    requiredAttributes.forEach(function(attr) {
        var value = $(fileInput).data(attr);
        if (value !== undefined) {
            formData.append(attr, value);
        }
    });

    var jsonOrtakAlanlar = getJsonOrtakAlanlar();
    formData.append('json_ortak_alanlar', JSON.stringify(jsonOrtakAlanlar));

    if (action === 'update') {
        var id = $('#VeriEkleForm').data('id');
        if (!id) {
            showToast('Hata', 'Güncelleme için gerekli bilgiler eksik.', 'error');
            return;
        }
        formData.append('id', id);
    }
    
    formData.append('yayin_durumu', $('#yayin_durumu').is(':checked') ? '1' : '0');
    formData.append('tablo_adi', tabloAdi);
    
    var ustKategoriId = $('#ust_kategori_id').val();
    if (ustKategoriId) {
        if (Array.isArray(ustKategoriId)) {
            ustKategoriId.forEach(function(id) {
                formData.append('ust_kategori_id[]', id);
            });
        } else {
            formData.append('ust_kategori_id', ustKategoriId);
        }
    }

    startLoading();
    $.ajax({
        url: 'islemler/veri-islem.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            stopLoading();
            if (response.success) {
                $('#VeriIslemModal').modal('hide');
                loadTableData();
                showToast('Başarılı', response.message, 'success');
                // Form gönderildikten sonra newImages'ı sıfırla
                newImages = [];
            } else {
                showToast('Hata', response.message, 'error');
            }
        },
        error: function(xhr, status, error) {
            stopLoading();
            showToast('Hata', 'İşlem sırasında bir hata oluştu.', 'error');
        }
    });
});

$('#VeriIslemModal').on('show.bs.modal', function () {
    $('#image-actions').hide();
	
});

$('#VeriIslemModal').on('hidden.bs.modal', function () {
    // Mevcut temizleme işlemleri
    $('#VeriEkleForm')[0].reset();
    $('select.select2').val(null).trigger('change');
    clearValidationErrors();

    // Resim önizleme alanını temizle
    $('#resim-preview-container').empty();

    // Dosya yükleme inputunu sıfırla
    $('input[type="file"]').val('');

    // Resim işlem butonlarını gizle
    $('#image-actions').hide();

    // Summernote editorlerini temizle
    $('.summernote').summernote('code', '');

    // Diğer özel alanları sıfırla
    // Örneğin:
    // $('#ozel-alan').val('');
});

    function getElementValue(element) {
        if (element.is(':checkbox')) {
            return element.prop('checked') ? '1' : '0';
        } else if (element.is('select')) {
            return element.val() || '';
        } else if (element.attr('type') === 'number' || element.hasClass('number-input')) {
            return element.val() ? element.val() : '';
        } else {
            return element.val() || '';
        }
    }

    function deleteRecords(ids) {
        startLoading();
        $.ajax({
            url: 'islemler/veri-islem.php',
            method: 'POST',
            data: {
                action: 'delete',
                ids: ids,
                tablo_adi: tabloAdi
            },
            dataType: 'json',
            success: function(response) {
                stopLoading();
                if (response.success) {
                    $('#SilModal').modal('hide');
                    loadTableData();
                    showToast('Başarılı', response.message, 'success');
                } else {
                    showToast('Hata', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                stopLoading();
                showToast('Hata', 'Silme işlemi sırasında bir hata oluştu.', 'error');
            }
        });
    }

function updateYayinDurumu(id, yayinDurumu) {
    startLoading();
    $.ajax({
        url: 'islemler/veri-islem.php',
        method: 'POST',
        data: {
            action: 'update_yayin_durumu',
            id: id,
            yayin_durumu: yayinDurumu,
            tablo_adi: tabloAdi
        },
        dataType: 'json',
        success: function(response) {
            stopLoading();
            if (response.success) {
                showToast('Başarılı', 'Yayın durumu güncellendi', 'success');
            } else {
                $('#yayin_durumu_' + id).prop('checked', !yayinDurumu);
                showToast('Hata', response.message, 'error');
            }
        },
        error: function(xhr, status, error) {
            stopLoading();
            $('#yayin_durumu_' + id).prop('checked', !yayinDurumu);
            showToast('Hata', 'Yayın durumu güncellenirken bir hata oluştu.', 'error');
        }
    });
}

function loadCategories(selectedCategories = null) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: 'islemler/veri-islem.php',
            method: 'POST',
            data: {
                action: 'get_categories',
                user_lang: userDil
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var select = $('#ust_kategori_id');
                    select.empty().append('<option value="">Seçiniz</option>');
                    $.each(response.categories, function(index, category) {
                        select.append($('<option></option>').attr('value', category.id).text(category.title));
                    });
                    select.select2({
                        theme: 'bootstrap4',
                        placeholder: "Üst kategori seçin",
                        allowClear: true,
                        //dropdownParent: $('#VeriIslemModal')
                    });
                    if (selectedCategories) {
                        select.val(selectedCategories.split(',')).trigger('change');
                    }
                    resolve();
                } else {
                    reject('Kategoriler yüklenemedi: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                reject('Kategoriler yüklenirken hata oluştu: ' + error);
            }
        });
    });
}

function loadSelectOptions(selectElement, selectedValue) {
    var $selectElement = $(selectElement);
    var tableNameAttr = $selectElement.attr('data-select_tablo_adi');
    if (tableNameAttr) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: 'islemler/veri-islem.php',
                method: 'POST',
                data: {
                    action: 'get_select_options',
                    table_name: tableNameAttr,
                    user_lang: userDil
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var options = '<option value="">Seç</option>';
                        $.each(response.data, function(index, item) {
                            var selected = (selectedValue && selectedValue.toString() === item.id.toString()) ? 'selected' : '';
                            options += '<option value="' + item.id + '" ' + selected + '>' + item.name + '</option>';
                        });
                        $selectElement.html(options);
                        
                        if (selectedValue) {
                            $selectElement.val(selectedValue).trigger('change');
                        }
                        
                        // Select2 için güncelleme
                        if ($selectElement.hasClass('select2')) {
                            $selectElement.select2({
                                theme: 'bootstrap4',
                                width: '100%'
                            });
                        }
                        resolve();
                    } else {
                        console.error('Select options yüklenemedi:', response.message);
                        reject(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX hatası:', status, error);
                    reject(error);
                }
            });
        });
    } else {
        return Promise.resolve();
    }
}

function getJsonOrtakAlanlar() {
    var jsonOrtakAlanlar = {};
    $('#VeriEkleForm, #ek-form-container').find('[data-json_ortak_alan="evet"]').each(function() {
        var $element = $(this);
        var fieldName = $element.attr('name');
        var fieldValue;

        if ($element.is(':checkbox')) {
            fieldValue = $element.prop('checked') ? '1' : '0';
        } else if ($element.is('select')) {
            fieldValue = $element.val() || null;
        } else if ($element.is('textarea')) {
            fieldValue = $element.val().trim() || null;
        } else {
            fieldValue = $element.val() || null;
        }

        jsonOrtakAlanlar[fieldName] = fieldValue;
    });
    return jsonOrtakAlanlar;
}

function initializeSelect2($element) {
    if ($element.hasClass('select2') && $.fn.select2) {
        if ($element.data('select2')) {
            $element.select2('destroy');
        }
        $element.select2({
            theme: 'bootstrap4',
            // dropdownParent: $('#VeriIslemModal')
        });
    }
}

function initializeSelect2Elements() {
    $('select.select2').each(function() {
        initializeSelect2($(this));
    });
}

function setupFormFieldListeners() {
    $('#VeriEkleForm [data-form_bos_kontrol="evet"]').on('input', function() {
        var field = $(this);
        if (field.val().trim() !== '') {
            field.removeClass('is-invalid');
            field.next('.invalid-feedback').remove();
        }
    });
}

function validateForm() {
    var isValid = true;
    var emptyFields = [];

    clearValidationErrors();

    $('#VeriEkleForm [data-form_bos_kontrol="evet"]').each(function() {
        var field = $(this);
        var fieldId = field.attr('id') || '';
        var fieldName = field.attr('name') || '';
        var fieldValue = field.val() ? field.val().trim() : '';
        var fieldLabel = $('label[for="' + fieldId + '"]').text().trim().replace(' *', '') || fieldName || fieldId || 'Bilinmeyen Alan';
        
        if (fieldValue === '' || (field.is('select') && !fieldValue)) {
            isValid = false;
            emptyFields.push(fieldLabel);
            
            field.addClass('is-invalid');
            if (!field.next('.invalid-feedback').length) {
                field.after('<div class="invalid-feedback">Bu alan zorunludur</div>');
            }
        }
    });

    if (!isValid) {
        var message = "Aşağıdaki alanlar boş bırakılamaz:\n\n" + emptyFields.join('\n');
        showToast('Uyarı', message, 'warning');
        $('#VeriEkleForm [data-form_bos_kontrol="evet"].is-invalid').first().focus();
    }

    return isValid;
}

function clearValidationErrors() {
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
}

function showToast(title, message, type) {
    toastr[type](message, title);
}

function startLoading() {
    $('.overlay').show();
}

function stopLoading() {
    $('.overlay').hide();
}



    // Resim önizleme fonksiyonu
$('input[type="file"]').on('change', function(e) {
    var files = e.target.files;
    var existingImages = $('.existing-image').map(function() {
        return $(this).attr('src').replace(baseurl_onyuz, '');
    }).get();
    
    var newImages = [];
    var filesProcessed = 0;
    
    function checkAllFilesProcessed() {
        if (filesProcessed === files.length) {
            updateImagePreviews(existingImages.concat(newImages));
        }
    }
    
    for (var i = 0; i < files.length; i++) {
        var file = files[i];
        var reader = new FileReader();
        
        reader.onload = function(e) {
            newImages.push(e.target.result);
            filesProcessed++;
            checkAllFilesProcessed();
        };
        
        reader.readAsDataURL(file);
    }
});

function displayExistingImages(images) {
    var resimPreviewContainer = $('#resim-preview-container');
    images.forEach(function(imagePath) {
        var imgElement = $('<img>')
            .attr('src', baseurl_onyuz + imagePath)
            .attr('alt', 'Mevcut Resim')
            .addClass('img-thumbnail existing-image')
            .css('max-width', '100px')
            .css('margin', '5px');
        resimPreviewContainer.append(imgElement);
    });
}

// Initialize toastr
toastr.options = {
    "closeButton": true,
    "debug": false,
    "newestOnTop": false,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
};

    // Tüm input elemanlarını dinle
    $('input[type="text"]').on('input', function() {
        var $this = $(this);
        var inputName = $this.attr('name');
        
        // data-text_cek_input özelliğine sahip tüm elemanları bul
        $('input[data-text_cek_input]').each(function() {
            var $target = $(this);
            var sourceInputName = $target.data('text_cek_input');
            
            // Eğer kaynak input adı, değişen input adıyla eşleşiyorsa
            if (sourceInputName === inputName) {
                var newValue = $this.val();
                
                // Eğer SEO dönüşümü gerekiyorsa
                if ($target.data('text_cek_input_seo_donustur') === 'evet') {
                    newValue = seoFriendlyUrl(newValue);
                }
                
                $target.val(newValue);
            }
        });
    });
    
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
                loadTableData();
            } else {
                showToast('Hata', response.message, 'error');
            }
        },
        error: function() {
            showToast('Hata', 'İşlem sırasında bir hata oluştu', 'error');
        }
    });
});
	
	// SEO dostu URL oluşturma fonksiyonu

		function seoFriendlyUrl(text) {
		  text = text.toLowerCase();
		  text = text.replace(/ğ/g, 'g');
		  text = text.replace(/ü/g, 'u');
		  text = text.replace(/ş/g, 's');
		  text = text.replace(/ı/g, 'i');
		  text = text.replace(/ö/g, 'o');
		  text = text.replace(/ç/g, 'c');
		  text = text.replace(/[^a-z0-9]/g, '-');
		  text = text.replace(/-+/g, '-');
		  text = text.replace(/^-|-$/g, '');
		  return text;
		}	

});



