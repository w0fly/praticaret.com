<?php
// core/db.php dosyasını include et
include 'db.php';

// AJAX isteği var mı kontrol et
if (isset($_POST['email']) && isset($_POST['password'])) {
    $username = $_POST['email'];
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
            echo json_encode(array('status' => 'success', 'message' => 'Giriş başarılı.'));
        } else {
            // Kullanıcı yanlış bilgileri girdi, giriş başarısız
            echo json_encode(array('status' => 'error', 'message' => 'Kullanıcı adı veya şifre yanlış.'));
        }
    } catch (PDOException $e) {
        // Veritabanı hatası oluştu
        echo json_encode(array('status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()));
    }
} else {
    // POST isteği alınmadı
    echo json_encode(array('status' => 'error', 'message' => 'Bilgiler eksik.'));
}
?>
