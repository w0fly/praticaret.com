<?php
// Oturumu başlat
session_start();

// Oturum kontrolü yap
if (!isset($_SESSION['giris']) || $_SESSION['giris'] !== true) {
    // Oturum yoksa veya hatalıysa, kullanıcıyı login sayfasına yönlendir
    header("Location: login.php");
    exit;
}

// Oturumu olan kullanıcıyı karşıla
echo "Hoş geldiniz, " .$_SESSION['user']. "!";

// Çıkış linki
echo '<br><a href="logout.php">Çıkış Yap</a>';
?>
