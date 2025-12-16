<?php
// tour-sitemap.php (düzeltilmiş - standartlara uygun)
require_once 'config.php';

header("Content-Type: application/xml; charset=utf-8");

echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

// Güvenli segment encode (sadece path parçaları için)
function enc_segment($s) {
    return rawurlencode((string)$s);
}

// Güvenli tarih (Y-m-d) üret
function safe_lastmod($iso = null) {
    if (!$iso) return date('Y-m-d');
    $ts = strtotime($iso);
    return $ts ? date('Y-m-d', $ts) : date('Y-m-d');
}

$sql = "SELECT id, veri FROM paketler WHERE yayin_durumu = 1 ORDER BY id DESC";
$stmt = $pdo->query($sql);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data = json_decode($row['veri'], true);
    if (!$data || !isset($data[0]['data'])) {
        error_log("JSON decode/structure error for paketler.id={$row['id']}");
        continue;
    }

    $turData = $data[0]['data'] ?? [];
    $trData  = $turData['diller']['tr'] ?? [];
    $ortak   = $turData['ortak_alanlar'] ?? [];

    // Zorunlu alanlar
    if (empty($trData['link'])) {
        error_log("Missing link for paketler.id={$row['id']}");
        continue;
    }

    // URL: https://site/tur-detay/{id}/{link}
    $loc = rtrim($sirket_url, '/')
         . '/tur-detay/'
         . enc_segment($row['id'])
         . '/'
         . enc_segment($trData['link']);

    // lastmod: varsa veri içindeki bir tarih alanından, yoksa bugün
    $lastmod_source = $turData['guncellenme_tarihi'] ?? ($turData['updated_at'] ?? null);
    $lastmod = safe_lastmod($lastmod_source);

    echo "  <url>\n";
    echo '    <loc>' . htmlspecialchars($loc, ENT_XML1, 'UTF-8') . "</loc>\n";
    echo '    <lastmod>' . htmlspecialchars($lastmod, ENT_XML1, 'UTF-8') . "</lastmod>\n";
    // changefreq/priority eklemek istersen aşağıyı aç:
    // echo "    <changefreq>weekly</changefreq>\n";
    // echo "    <priority>0.9</priority>\n";
    echo "  </url>\n";
}

echo '</urlset>';