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
            <h1 class="m-0">Ana Sayfa</h1>
          </div><!-- /.col -->
          
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
              <div class="inner">
                <h3>1</h3>

                <p>Aktif Müşteri</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
                <h3>1</h3>

                <p>Aktif PazarYeri</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
              <div class="inner">
                <h3>0</h3>

                <p>Bekleyen Ticket</p>
              </div>
              <div class="icon">
                <i class="ion ion-person-add"></i>
              </div>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
                <h3>0</h3>

                <p>Sistem Sorunu</p>
              </div>
              <div class="icon">
                <i class="ion ion-pie-graph"></i>
              </div>
            </div>
          </div>
          <!-- ./col -->
        </div>
        
    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
           

            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Aktif Müşteri / Lisans Listesi</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>Alan Adı</th>
                    <th>OpenCart Admin Dizini</th>
                    <th>Admin Kullanıcı Adı</th>
                    <th>Admin Şifre</th>
                    <th>Aktif Pazaryeri Sayısı</th>
                    <th>Kalan Süre</th>
                  </tr>
                  </thead>
                  <tbody>
                  <tr>
                    <td>demo.emersoft.com.tr</td>
                    <td>/admin </td>
                    <td>admin</td>
                    <td>root123</td>
                    <td>3</td>                    
                    <td>17 gün</td>
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
        <!-- /.row (main row) -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <?php include "core/footer.php"; ?>