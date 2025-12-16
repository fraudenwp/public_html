<?php
session_start();
require_once '../config.php'; // Veritabanı bağlantısı için config dosyası

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = md5($_POST['password']); // MD5 ile şifreleme

    try {
        $stmt = $pdo->prepare("SELECT * FROM kullanicilar WHERE k_adi = :username AND sifre = :password");
        $stmt->execute(['username' => $username, 'password' => $password]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['ad_soyad'] = $user['ad_soyad'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['dil'] = $user['dil']; // Kullanıcının dil tercihini oturuma kaydet
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Kullanıcı adı veya şifre hatalı!']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek metodu.']);
}
