<?php
// Sayfalar tablosundan veri çekme
$stmt = $pdo->prepare("SELECT veri FROM sayfalar WHERE id = 34");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    $sayfa_veri = json_decode($row['veri'], true);
 
    // Meta verilerini ayarla
    $sayfa_baslik = $sayfa_veri[0]['data']['diller']['tr']['baslik'] ?? 'Kurumsal Bilgiler';
    $sayfa_meta_baslik = $sayfa_veri[0]['data']['diller']['tr']['meta_baslik'] ?? $sayfa_baslik;
    $sayfa_meta_aciklama = $sayfa_veri[0]['data']['diller']['tr']['meta_aciklama'] ?? '';
    $sayfa_etiketler = $sayfa_veri[0]['data']['diller']['tr']['etiketler'] ?? [];
    $sayfa_link = $sayfa_veri[0]['data']['diller']['tr']['link'] ?? '';

    if (empty($sayfa_meta_aciklama)) {
        $sayfa_meta_aciklama = substr(strip_tags($sayfa_veri[0]['data']['diller']['tr']['aciklama'] ?? ''), 0, 160);
    }

    $sayfa_etiketler_string = implode(', ', array_merge($sayfa_etiketler, ['blok', 'haber', 'makale']));
    $tam_url = $sirket_url . '/blok-haber';

    // Resim yolunu bul
    $sayfa_resim_yolu = '';
    if (isset($sayfa_veri[0]['data']['resimler']) && is_array($sayfa_veri[0]['data']['resimler'])) {
        foreach ($sayfa_veri[0]['data']['resimler'] as $resim) {
            if ($resim['kapak_resim'] == 'evet') {
                $sayfa_resim_yolu = $resim['dosya_adi'];
                break;
            }
        }
    }

    // PageData'yı ayarla
    PageData::set(
        $sayfa_meta_baslik . ' - ' . $sirket_adi,
        $sayfa_meta_aciklama,
        $sayfa_etiketler_string,
        [
            'og:title' => $sayfa_meta_baslik,
            'og:description' => $sayfa_meta_aciklama,
            'og:type' => 'website',
            'og:url' => $tam_url,
            'og:image' => $sirket_url . $sayfa_resim_yolu,
            'og:site_name' => $sirket_adi,
            'twitter:card' => 'summary_large_image',
            'twitter:title' => $sayfa_meta_baslik,
            'twitter:description' => $sayfa_meta_aciklama,
            'twitter:image' => $sirket_url . $sayfa_resim_yolu
        ]
    );
} else {
    // Eğer veri bulunamazsa varsayılan değerleri kullan
    PageData::set(
        'Bilgi Sayfaları - ' . $sirket_adi,
        'Seyahatlarinizde Faydalana Bileceğiniz Önemli Bilgilerin Yer Aldığı hakkında bilgiler',
        'Bilgi sayfalari',
        [
            'og:title' => 'Bilgi Sayfaları',
            'og:description' => 'Seyahatlarinizde Faydalana Bileceğiniz Önemli Bilgilerin Yer Aldığı hakkında bilgiler',
            'og:type' => 'website',
            'og:url' => $sirket_url . '/bilgi-sayfalari',
            'og:site_name' => $sirket_adi,
            'twitter:card' => 'summary_large_image',
            'twitter:title' => 'Bilgi Sayfaları',
            'twitter:description' => 'Seyahatlarinizde Faydalana Bileceğiniz Önemli Bilgilerin Yer Aldığı hakkında bilgiler'
        ]
    );
}
?>
