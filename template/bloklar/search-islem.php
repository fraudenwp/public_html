<?php
// Veritabanı bağlantısı ve diğer gerekli işlemler burada...

// Tüm paketleri çek
$query = $pdo->prepare("
    SELECT veri
    FROM paketler 
    WHERE yayin_durumu = 1 
    AND JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.tukendi') = '0'
    AND JSON_EXTRACT(veri, '$[0].data.ortak_alanlar.arsivle') = '0'
");
$query->execute();
$paketler = $query->fetchAll(PDO::FETCH_ASSOC);

$turSureleri = [];
$turDonemleri = [];
$oteller = [];

foreach ($paketler as $paket) {
    $veri = json_decode($paket['veri'], true)[0]['data'];
    $ortak_alanlar = $veri['ortak_alanlar'];
    
    // Manuel gün değeri varsa onu kullan, yoksa hesapla
    if (isset($ortak_alanlar['kac_gun']) && !empty($ortak_alanlar['kac_gun'])) {
        $turSuresi = $ortak_alanlar['kac_gun'];
    } else {
        $baslangic = new DateTime($ortak_alanlar['tur_baslangic_tarihi']);
        $bitis = new DateTime($ortak_alanlar['tur_bitis_tarihi']);
        $sureFark = $baslangic->diff($bitis);
        $turSuresi = $sureFark->days + 1;
    }
    
    $turSureleri[$turSuresi] = $turSuresi . ' Günlük Umre Turu';
    $turDonemleri = array_merge($turDonemleri, $ortak_alanlar['donem'] ?? []);
    $oteller[] = $ortak_alanlar['otel_bir'];
    $oteller[] = $ortak_alanlar['otel_iki'];
}

$turSureleri = array_unique($turSureleri);
$turDonemleri = array_unique($turDonemleri);
$oteller = array_unique(array_filter($oteller));

// Dönem ve otel isimlerini al
$donemQuery = $pdo->prepare("SELECT id, veri FROM donem WHERE id IN (" . implode(',', $turDonemleri) . ")");
$donemQuery->execute();
$donemler = $donemQuery->fetchAll(PDO::FETCH_ASSOC);

$otelQuery = $pdo->prepare("SELECT id, veri FROM oteller WHERE id IN (" . implode(',', $oteller) . ")");
$otelQuery->execute();
$otellerData = $otelQuery->fetchAll(PDO::FETCH_ASSOC);

$donemlerFormatted = [];
$otellerFormatted = [];

foreach ($donemler as $donem) {
    $veri = json_decode($donem['veri'], true)[0]['data'];
    $donemlerFormatted[$donem['id']] = $veri['diller'][$user_dil]['baslik'];
}

foreach ($otellerData as $otel) {
    $veri = json_decode($otel['veri'], true)[0]['data'];
    $otellerFormatted[$otel['id']] = $veri['diller'][$user_dil]['baslik'];
}

$jsonData = json_encode([
    'paketler' => $paketler,
    'turSureleri' => $turSureleri,
    'turDonemleri' => $donemlerFormatted,
    'oteller' => $otellerFormatted
]);

echo "<script>var turData = " . $jsonData . ";</script>";
?>