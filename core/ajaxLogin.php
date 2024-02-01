<?php
// core/db.php dosyasını include et
include 'db.php';

// Oturumu başlat
session_start();

// AJAX isteği var mı kontrol et
if (isset($_POST['email'], $_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $pass2db = md5($password);
    try {
        // Kullanıcıyı veritabanında kontrol et
        $stmt = $con->prepare("SELECT * FROM users WHERE email = :email AND password = :password");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $pass2db);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Kullanıcı doğru bilgileri girdi, giriş başarılı
            $_SESSION['giris'] = true;
            $_SESSION['user'] = $user; // Kullanıcı bilgilerini oturumda sakla
            echo json_encode(array('status' => 'success', 'message' => 'Giriş başarılı.'));
            exit;
        } else {
            // Kullanıcı yanlış bilgileri girdi, giriş başarısız
            echo json_encode(array('status' => 'error', 'message' => 'Kullanıcı adı veya şifre yanlış.'));
            exit;
        }
    } catch (PDOException $e) {
        // Veritabanı hatası oluştu
        echo json_encode(array('status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()));
        exit;
    }
} else {
    // POST isteği alınmadı
    echo json_encode(array('status' => 'error', 'message' => 'Bilgiler eksik.'));
    exit;
}
?>

