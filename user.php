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
            <h1 class="m-0">Kullanıcı Yönetimi</h1>
          </div><!-- /.col -->
          
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
           

            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Kullanıcı Yönetimi</h3>              
              </div>
             <a href="#"><button type="button" class="btn btn-primary btn-block"><i class="fa fa-plus"></i> Yeni Kullanıcı Ekle</button></a>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>Kullanıcı Adı</th>
                    <th>E-Posta</th>
                    <th>Şifre Kurtarma Anahtarı</th>
                    <th>İşlem</th>
                  </tr>
                  </thead>
                  <tbody>
                  <tr>
                    <td>Sistem Yöneticisi</td>
                    <td>root@praticaret.com </td>
                    <td>praticaret123</td>
                    <td>root123</td>
                    <td><button type="button" class="btn btn-block btn-danger">Sil</button> <button type="button" class="btn btn-block btn-success">Düzenle</button></td>     
                  </tr>
                  </tbody>
                 
                </table>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div>
      <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <?php include "core/footer.php"; ?>