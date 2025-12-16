<!doctype html>
<html lang="tr">
<head>
<!-- Required meta tags -->

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo htmlspecialchars(PageData::get('title')); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars(PageData::get('metaDescription')); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(PageData::get('metaKeywords')); ?>">
	<meta name="author" content="<?php echo htmlspecialchars($author); ?>">
	<meta name="publisher" content="<?php echo htmlspecialchars($publisher); ?>">
    <link rel="icon" type="image/png" sizes="40x40" href="/resimler/fawicon.png">
    <?php foreach(PageData::getAllMeta() as $property => $content): ?>
        <?php if (strpos($property, 'og:') === 0 || strpos($property, 'twitter:') === 0): ?>
            <meta property="<?php echo htmlspecialchars($property); ?>" content="<?php echo htmlspecialchars($content); ?>">
        <?php endif; ?>
    <?php endforeach; ?>


<!-- Bootstrap CSS -->
<link rel="stylesheet" href="<?php echo $baseurl_onyuz; ?>template/assets/css/bootstrap.min.css">
<link href="<?php echo $baseurl_onyuz; ?>template/assets/css/all.css" rel="stylesheet">
<!-- Owl Carousel CSS -->
<link href="<?php echo $baseurl_onyuz; ?>template/assets/css/owl.carousel.css" rel="stylesheet">
<link href="https:/fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Poppins:100,200,300,400,500,600,700,800,900&display=swap" rel="stylesheet">
<link href="<?php echo $baseurl_onyuz; ?>template/assets/css/animate.css" rel="stylesheet">
<link rel="stylesheet" href="<?php echo $baseurl_onyuz; ?>template/assets/css/nice-select.css">
<link rel="stylesheet" href="<?php echo $baseurl_onyuz; ?>template/assets/css/flexslider.css">
<link rel="stylesheet" href="<?php echo $baseurl_onyuz; ?>template/assets/rs-plugin/css/settings.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<link href="<?php echo $baseurl_onyuz; ?>template/assets/css/style.css" rel="stylesheet">
<meta name="google-site-verification" content="QADbyD0JQ-V83dAS8-Q7Vqdpta_XXWMJMah6jcQYDxg" />
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-7E3WYH5116"></script>
<!-- LightSlider CSS ve JS dosyalarını sayfanızın head kısmına ekleyin -->
<link rel="stylesheet" href="<?php echo $baseurl_onyuz; ?>template/assets/css/lightslider.css"/>
<link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css" rel="stylesheet">

<link rel="canonical" href="https://<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" />


<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-W9WWKKNJ');</script>
<!-- End Google Tag Manager -->
</head>
<body> 
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-W9WWKKNJ"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

  <?php
 try {
    // Tema tablosundan verileri çek
    $stmt = $pdo->query("SELECT veri FROM tema where tur = 'renkayar' ");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        // JSON verisini decode et
        $tema_verileri = json_decode($row['veri'], true);
        
        // JSON dizisinin ilk elemanını al (çünkü veriler bir dizi içinde)
        $tema = $tema_verileri[0];
        
        // Her bir değeri ayrı değişkenlere ata
        $ust_bar_renk = $tema['ust_bar_renk'];
        $search_bar_renk = $tema['search_bar_renk'];
        $footer_bar_renk = $tema['footer_bar_renk'];
        $genel_buton_renk = $tema['genel_buton_renk'];
        $genel_buton_yazi_renk = $tema['genel_buton_yazi_renk'];
        $liste_sayfa_renk_bir = $tema['liste_sayfa_renk_bir'];
        $liste_sayfa_renk_bir_yazi = $tema['liste_sayfa_renk_bir_yazi'];
        $liste_sayfa_renk_iki = $tema['liste_sayfa_renk_iki'];
        $liste_sayfa_renk_iki_yazi = $tema['liste_sayfa_renk_iki_yazi'];
		
        $liste_sayfa_renk_filtre_baslik = $tema['liste_sayfa_renk_filtre_baslik'];
        $liste_sayfa_renk_filtre_secim = $tema['liste_sayfa_renk_filtre_secim'];
        $detay_sayfa_baslik_yazı_rengi = $tema['detay_sayfa_baslik_yazı_rengi'];
		
        $detay_sayfa_bilgi_arkaplan_rengi = $tema['detay_sayfa_bilgi_arkaplan_rengi'];
        $detay_sayfa_bilgi_baslik_yazi_rengi = $tema['detay_sayfa_bilgi_baslik_yazi_rengi'];
        
		
		        // Değişkenleri global olarak tanımla
       // global $search_bar_renk, $footer_bar_renk, $ust_bar_renk, $genel_buton_renk;
		
        // Artık bu değişkenleri istediğiniz gibi kullanabilirsiniz 
        // Örnek kullanım:
        //echo "Üst Bar Renk: " . $ust_bar_renk;
?>
<style>
.topbar-wrap {
    background: <?php echo $ust_bar_renk; ?>;
}

.form_sec {
    background: <?php echo $search_bar_renk; ?>;
}

.footer {
    background: <?php echo $footer_bar_renk; ?>;
}

.sbutn {
    background: <?php echo $genel_buton_renk; ?>;
	color: <?php echo $genel_buton_yazi_renk; ?>;
}
.start_btn a {
    background: <?php echo $genel_buton_renk; ?>;
	color: <?php echo $genel_buton_yazi_renk; ?>;
}

.readmore a {
    background: <?php echo $genel_buton_renk; ?>;
}

.submit {
    background: <?php echo $genel_buton_renk; ?>;
	color: <?php echo $genel_buton_yazi_renk; ?>;
} 
.apart { 
    background: <?php echo $liste_sayfa_renk_bir; ?>;
	color: <?php echo $liste_sayfa_renk_bir_yazi; ?>;
}
.apart:after { 
    background: <?php echo $liste_sayfa_renk_bir; ?>;
}

.sale { 
    background: <?php echo $liste_sayfa_renk_iki; ?>;
	color: <?php echo $liste_sayfa_renk_iki_yazi; ?>;	
}

.advanceWrp h4 a.collapsed, .advanceWrp h4 a { 
    color: <?php echo $liste_sayfa_renk_filtre_baslik; ?> !important;
}

.custom-checkboxx + label:before {
    border: 1px solid <?php echo $liste_sayfa_renk_filtre_secim; ?>;
    background: #ffffff;
}

.blog-pagination a:hover, .blog-pagination a.active {

    background:  <?php echo $genel_buton_renk; ?>;
}
 
.blog-pagination a {
    border: 1px solid <?php echo $genel_buton_renk; ?>;
}

blockquote {
    background: <?php echo $genel_buton_renk; ?>40;
    border-left: 8px solid <?php echo $genel_buton_renk; ?>;
}


.property_price {
    color: <?php echo $detay_sayfa_baslik_yazı_rengi; ?>; 
}

.desc_head {

    color: <?php echo $detay_sayfa_baslik_yazı_rengi; ?>;

}

.faqs h4 a {

    background: <?php echo $detay_sayfa_bilgi_arkaplan_rengi; ?>;
	color: <?php echo $detay_sayfa_bilgi_baslik_yazi_rengi; ?>;   
}


</style> 

<?php		
		
		
        
    }
    
} catch(PDOException $e) {
    echo "Hata: " . $e->getMessage();
} 
  ?>