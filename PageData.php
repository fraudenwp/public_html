<?php

// PageData.php
class PageData {
    private static $data = [
        'title' => 'Varsayılan Başlık',
        'metaDescription' => 'Varsayılan Açıklama',
        'metaKeywords' => 'varsayılan, etiketler'
    ];
    private static $isSet = false;

    public static function set($title, $description, $keywords, $additionalMeta = []) {
        self::$data['title'] = $title;
        self::$data['metaDescription'] = $description;
        self::$data['metaKeywords'] = $keywords;
        self::$data = array_merge(self::$data, $additionalMeta);
        self::$isSet = true;
    }

    public static function get($key) {
        return self::$data[$key] ?? '';
    }

    public static function setMeta($key, $value) {
        self::$data[$key] = $value;
    }

    public static function getAllMeta() {
        return self::$data;
    }
}

?>