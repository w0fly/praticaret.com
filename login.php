<?php
ob_start();
session_start();

include "config.php";

if (isset($_SESSION['giris'])) {
    header("Location:home.php");
    exit; // Kodun devamını çalıştırmamak için çıkış yap
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PraTicaret - Entegratör Yönetim Paneli  | Giriş Ekranı</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <!-- /.login-logo -->
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <a href="#" class="h1"><b>Pra</b>Ticaret</a>
    </div>
   <!-- İlgili içeriği güncelleyin -->
<div class="card-body">
    <!-- İhtiyacınıza göre hata mesajını buraya ekleyebilirsiniz -->
   <script>
    alert('Sunucuda SQL bazlı eklemeler yapılıyor. Kodlarda ilerlemeler gözükmeyebilir veya anlık olarak hatalar yansıyabilir.')
   </script>

    <p class="login-box-msg">Sistem Yönetim İçin Lütfen Kullanıcı Bilgilerinizi Girin</p>

    <form id="loginForm" method="post">
        <div class="input-group mb-3">
            <input type="email" class="form-control" placeholder="E-Posta Adresi" name="email">
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope"></span>
                </div>
            </div>
        </div>
        <div class="input-group mb-3">
            <input type="password" class="form-control" placeholder="Şifre" name="password">
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-8">
                <div class="icheck-primary">
                    <input type="checkbox" id="remember">
                    <label for="remember">
                        Oturumu Açık Tut
                    </label>
                </div>
            </div>
            <!-- /.col -->
            <div class="col-4">
                <button type="submit" class="btn btn-primary btn-block">Giriş Yap</button>
            </div>
            <!-- /.col -->
        </div>
        
    </form>
    <p class="mb-1">
        <a href="forgot-password.html">Şifremi Sıfırla</a>
    </p>
</div>
<div class="modal fade" id="modal-danger" style="display: none;" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content bg-danger">
            <div class="modal-header">
              <h4 class="modal-title">Doğrulama Hatası !</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
              </button>
            </div>
            <div class="modal-body">
              <p>Kullanıcı bilgileriniz veri tabanında bulunamadı. Lütfen kullanıcı bilgilerinizi kontrol edip tekrar deneyin !</p>
            </div>
            
          </div>
          <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
      </div>
  <!-- /.card -->
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>
<script>
$(document).ready(function() {
    $('#loginForm').submit(function(e) {
        e.preventDefault(); // Formun normal submit olmasını engelle
        $.ajax({
            type: "POST",
            url: "core/ajaxLogin.php",
            data: $(this).serialize(),
            success: function(response) {
                try {
                    var jsonData = JSON.parse(response);
                    if (jsonData.status === "success") {
                        window.location.href = "home.php";
                    } else {
                        $('#errorModalBody').html(jsonData.message);
                        $('#modal-danger').modal('show');
                    }
                } catch (error) {
                    console.error('JSON parse hatası:', error);
                    $('#errorMessage').html('Sunucu hatası. Lütfen tekrar deneyin.');
                    $('#errorToast').toast('show');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX hatası:', error);
                $('#errorMessage').html('Sunucu hatası. Lütfen tekrar deneyin.');
                $('#errorToast').toast('show');
            }
        });
    });
});
</script>

</body>
</html>
