<?php
// sitemap.php
require_once 'config.php';
header("Content-Type: application/xml; charset=utf-8");

echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . PHP_EOL;
echo '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . PHP_EOL;

function addUrl($loc, $lastmod = null, $changefreq = null, $priority = null, $images = []) {
    echo "<url>\n";
    echo '  <loc>' . htmlspecialchars($loc, ENT_XML1, 'UTF-8') . "</loc>\n";
    if ($lastmod)   echo '  <lastmod>' . htmlspecialchars($lastmod, ENT_XML1, 'UTF-8') . "</lastmod>\n";
    if ($changefreq) echo '  <changefreq>' . htmlspecialchars($changefreq, ENT_XML1, 'UTF-8') . "</changefreq>\n";
    if ($priority)   echo '  <priority>' . htmlspecialchars($priority, ENT_XML1, 'UTF-8') . "</priority>\n";

    foreach ($images as $image) {
        echo "  <image:image>\n";
        echo '    <image:loc>' . htmlspecialchars($image['url'], ENT_XML1, 'UTF-8') . "</image:loc>\n";
        if (!empty($image['caption'])) {
            echo '    <image:caption>' . htmlspecialchars($image['caption'], ENT_XML1, 'UTF-8') . "</image:caption>\n";
        }
        echo "  </image:image>\n";
    }
    echo "</url>\n";
}

function enc_segment($s) {
    // Sadece path segmentini encode et
    return rawurlencode($s);
}

function processJsonData($row, $urlPrefix, $priority = '0.6', $includeImages = false) {
    global $sirket_url;
    $veri = json_decode($row['veri'], true);
    if ($veri === null) {
        error_log("JSON decode error for ID {$row['id']}: " . json_last_error_msg());
        return;
    }

    if (isset($veri[0]['data']['diller']['tr']['link'])) {
        $link = $veri[0]['data']['diller']['tr']['link']; // örn: 'kappadokya-turu'
        $id   = (string)$row['id'];
        // URL'yi segment bazlı kur
        $loc = rtrim($sirket_url, '/') . $urlPrefix . enc_segment($id) . '/' . enc_segment($link);

        // lastmod (DB alan adına göre uyarlayın)
        $lastmod = null;
        if (!empty($veri[0]['data']['guncellenme_tarihi'])) {
            $ts = strtotime($veri[0]['data']['guncellenme_tarihi']);
            if ($ts) $lastmod = date('Y-m-d', $ts);
        }

        $images = [];
        if ($includeImages && !empty($veri[0]['data']['resimler']) && is_array($veri[0]['data']['resimler'])) {
            foreach ($veri[0]['data']['resimler'] as $resim) {
                if (!empty($resim['dosya_adi'])) {
                    $resim_url = rtrim($sirket_url, '/') . '/' . ltrim($resim['dosya_adi'], '/');
                    $images[] = [
                        'url' => $resim_url,
                        'caption' => $resim['aciklama'] ?? ''
                    ];
                }
            }
        }

        // changefreq/priority opsiyonel; istersen null bırak
        addUrl($loc, $lastmod, null, $priority, $images);
    } else {
        error_log("Link not found in JSON data for ID {$row['id']}");
    }
}

// Ana sayfa ve sabit sayfalar
addUrl(rtrim($sirket_url, '/').'/', date('Y-m-d'), null, '1.0');

// Statik sayfalar (segment bazlı birleştir, tüm yolu encode etme)
$staticPages = [
    ['kurumsal-detay', '2', 'hakkimizda'],
    ['kurumsal-detay', '3', 'iletisim'],
    ['turlar', 'umre-turlari'],
    ['turlar', 'hac-programlari'],
    ['turlar'],
    ['bilgi-sayfalari'],
    ['blok-haber']
];
foreach ($staticPages as $parts) {
    $parts = (array)$parts;
    $path = implode('/', array_map('enc_segment', $parts));
    $loc  = rtrim($sirket_url, '/') . '/' . $path;
    addUrl($loc, null, null, '0.8');
}

// Dinamik sayfalar
function addDynamicPages($tableName, $urlPrefix, $priority = '0.9', $includeImages = false) {
    global $pdo;
    try {
        $sql = "SELECT id, veri FROM $tableName WHERE yayin_durumu = 1";
        $stmt = $pdo->query($sql);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            processJsonData($row, $urlPrefix, $priority, $includeImages);
        }
    } catch (PDOException $e) {
        error_log("Database error for table $tableName: " . $e->getMessage());
    }
}

addDynamicPages('paketler', '/tur-detay/', '0.9', true);
addDynamicPages('bilgi_sayfalari', '/bilgi-sayfalari-detay/');
addDynamicPages('kurumsal', '/kurumsal-detay/');
addDynamicPages('blok_haber', '/blok-haber-detay/');

echo '</urlset>';