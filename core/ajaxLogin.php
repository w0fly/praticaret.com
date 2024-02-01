<?php
// core/db.php dosyasını include et
include 'db.php';

// AJAX isteği var mı kontrol et
if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // Kullanıcıyı veritabanında kontrol et
        $stmt = $con->prepare("SELECT * FROM users WHERE username = :username AND password = :password");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
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
