<?php
// Oturumu başlat
session_start();

// Oturumu sonlandır (logout)
session_unset(); // Oturum değişkenlerini temizle
session_destroy(); // Oturumu sonlandır

// Kullanıcıyı başka bir sayfaya yönlendir (isteğe bağlı)
header("Location: login.php");
exit; // Yönlendirmeden sonra kodun devam etmemesi için exit komutu kullanılır
?>