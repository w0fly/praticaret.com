<?php
// Oturumu başlat
session_start();

// Oturum kontrolü yap
if (!isset($_SESSION['giris']) || $_SESSION['giris'] !== true) {
    // Oturum yoksa veya hatalıysa, kullanıcıyı login sayfasına yönlendir
    header("Location: login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<?php include "core/header.php"; ?>


    <?php include "core/menu.php"; ?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Dökümantasyon Listesi</h1>
          </div><!-- /.col -->
          
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">

          <!-- ./col -->
        <!-- /.row -->
        <!-- Main row -->
        <div class="row">
         
        </div>
        <!-- /.row (main row) -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <?php include "core/footer.php"; ?>