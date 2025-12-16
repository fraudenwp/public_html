<?php
// Sayfalar tablosundan veri çekme
$stmt = $pdo->prepare("SELECT veri FROM sayfalar WHERE id = 1");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    $sayfa_veri = json_decode($row['veri'], true);

    if (isset($sayfa_veri[0]['diller']['tr'])) {
        $tr_data = $sayfa_veri[0]['diller']['tr'];

        // Meta verilerini ayarla
        $sayfa_baslik = $tr_data['baslik'] ?? 'Seyahat, Seyahat Bilgileri, Önemli Bilgiler';
        $sayfa_meta_baslik = $tr_data['meta_baslik'] ?? $sayfa_baslik;
        $sayfa_meta_aciklama = $tr_data['meta_aciklama'] ?? '';
        $sayfa_etiketler = $tr_data['etiketler'] ?? [];
        $sayfa_link = $tr_data['link'] ?? ''; 

        if (empty($sayfa_meta_aciklama)) {
            $sayfa_meta_aciklama = substr(strip_tags($tr_data['aciklama'] ?? ''), 0, 160);
        }

        $sayfa_etiketler_string = implode(', ', $sayfa_etiketler);
        $tam_url = $sirket_url . '/' . $sayfa_link;

        // Kısa açıklama için ortak alanları kontrol et
        $sayfa_kisa_aciklama = $sayfa_veri[0]['ortak_alanlar']['sayfa_kisa-aciklama'] ?? '';

        // PageData'yı ayarla
        PageData::set(
            $sayfa_meta_baslik . ' - ' . $sirket_adi,
            $sayfa_meta_aciklama,
            $sayfa_etiketler_string,
            [
                'og:title' => $sayfa_meta_baslik,
                'og:description' => $sayfa_meta_aciklama,
                'og:type' => 'website',
                'og:url' => 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
                'og:site_name' => $sirket_adi,
                'twitter:card' => 'summary_large_image',
                'twitter:title' => $sayfa_meta_baslik,
                'twitter:description' => $sayfa_meta_aciklama
            ]
        );

        // Eğer kısa açıklama varsa, onu da meta verilerine ekle
        if (!empty($sayfa_kisa_aciklama)) {
            PageData::setMeta('og:description', $sayfa_kisa_aciklama);
            PageData::setMeta('twitter:description', $sayfa_kisa_aciklama);
        }
    } else {
        // Veri yapısı beklenen formatta değilse varsayılan değerleri kullan
        setDefaultMetaData();
    }
} else {
    // Eğer veri bulunamazsa varsayılan değerleri kullan
    setDefaultMetaData();
}

function setDefaultMetaData() {
    PageData::set(
        'Blok, Haber, Makale - ' . $sirket_adi,
        'Blok, Haber ve Makaleler hakkında bilgiler',
        'Blok, Haber, Makale',
        [
            'og:title' => 'Blok, Haber, Makale',
            'og:description' => 'Blok, Haber ve Makaleler hakkında bilgiler',
            'og:type' => 'website',
            'og:url' => $sirket_url . '/blok-haber',
            'og:site_name' => $sirket_adi,
            'twitter:card' => 'summary_large_image',
            'twitter:title' => 'Blok, Haber, Makale',
            'twitter:description' => 'Blok, Haber ve Makaleler hakkında bilgiler'
        ]
    );
}
?>
