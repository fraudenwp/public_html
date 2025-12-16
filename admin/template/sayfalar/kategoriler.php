<?php

// Ana kategorileri çekmek için sorgu
$query = $pdo->prepare("
    SELECT k.id, k.yayin_durumu, k.baslik, k.sira, k.grup, k.kat_id
    FROM kategoriler k
    LEFT JOIN (
        SELECT grup, MIN(CASE WHEN dil = :user_dil THEN id END) AS user_dil_id
        FROM kategoriler
        WHERE kat_id IS NULL
        GROUP BY grup
    ) grouped_k ON k.grup = grouped_k.grup
    WHERE (k.id = grouped_k.user_dil_id OR grouped_k.user_dil_id IS NULL)
    AND k.kat_id IS NULL
    ORDER BY k.sira
");

$query->execute(['user_dil' => $user_dil]);
$ana_kategoriler = $query->fetchAll(PDO::FETCH_ASSOC);

// Tüm kategorileri çekmek için yardımcı fonksiyon
function getAllCategories($pdo, $user_dil) {
    $query = $pdo->prepare("
        SELECT k.id, k.yayin_durumu, k.baslik, k.sira, k.grup, k.kat_id
        FROM kategoriler k
        LEFT JOIN (
            SELECT grup, MIN(CASE WHEN dil = :user_dil THEN id END) AS user_dil_id
            FROM kategoriler
            GROUP BY grup
        ) grouped_k ON k.grup = grouped_k.grup
        WHERE k.id = grouped_k.user_dil_id OR grouped_k.user_dil_id IS NULL
        ORDER BY k.sira
    ");
    $query->execute(['user_dil' => $user_dil]);
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

// Kategori ağacını oluşturmak için yardımcı fonksiyon
function buildCategoryTree($categories, $parentId = null) {
    $branch = [];
    foreach ($categories as $category) {
        if ($category['kat_id'] == $parentId) {
            $children = buildCategoryTree($categories, $category['id']);
            if ($children) {
                $category['children'] = $children;
            }
            $branch[] = $category;
        }
    }
    return $branch;
}

// Kategori yolunu oluşturmak için yardımcı fonksiyon
function getCategoryPath($category, $categories) {
    $path = [$category['baslik']];
    $parentId = $category['kat_id'];
    
    while ($parentId !== null) {
        foreach ($categories as $cat) {
            if ($cat['id'] == $parentId) {
                array_unshift($path, $cat['baslik']);
                $parentId = $cat['kat_id'];
                break;
            }
        }
    }
    
    return implode(' > ', $path);
}

// Kategori ve tüm alt kategorileri yazdırmak için recursive bir işlev
function printCategoryRow($category, $allCategories, $depth = 0) {
    $indent = str_repeat('', $depth);
    $categoryPath = getCategoryPath($category, $allCategories);
    
    echo '<tr>';
    echo '<td>' . $indent . '<a href="kategori-duzenle?list=kategoriler&id=' . $category['grup'] . '">' . $categoryPath . '</a></td>';
    echo '<td>' . $category['sira'] . '</td>';
    echo '<td>';
    echo '<div class="custom-control custom-switch custom-switch-on-success float-right">';
    echo '<input name="yayin_durumu" type="checkbox" class="custom-control-input" data-veri-tablosu="kategoriler"  id="yayin_durumu-' . $category['grup'] . '" value="1" ' . ($category['yayin_durumu'] == 1 ? 'checked' : '') . '>';
    echo '<label class="custom-control-label" for="yayin_durumu-' . $category['grup'] . '">' . ($category['yayin_durumu'] == 1 ? 'Aktif' : 'Pasif') . '</label>';
    echo '</div>';
    echo '</td>';
    echo '</tr>';

    if (isset($category['children'])) {
        foreach ($category['children'] as $child) {
            printCategoryRow($child, $allCategories, $depth + 1);
        }
    }
}

// Tüm kategorileri al ve ağacı oluştur
$allCategories = getAllCategories($pdo, $user_dil);
$categoryTree = buildCategoryTree($allCategories);
?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0"> <?php echo $sayfa['baslik']; ?></h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?php echo $baseurl; ?>">Gösterge Paneli</a></li>
              <li class="breadcrumb-item active"><?php echo $sayfa['baslik']; ?></li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Kategoriler</h3>
                        </div>
                        <div class="card-body">
                            <table id="example1" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Başlık</th>
                                        <th>Sıralama Düzeni</th>
                                        <th>Durum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    foreach ($categoryTree as $category) {
                                        printCategoryRow($category, $allCategories);
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
  
