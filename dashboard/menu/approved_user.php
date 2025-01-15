<?php
session_start();
include '../../koneksi.php'; // Koneksi ke database

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user'])) {
    header("Location: ../../index.php");
    exit;
}

$user = $_SESSION['user'];

// Query untuk mendapatkan role pengguna
$query = "SELECT role FROM users WHERE user_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$user['user_id']]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

// Cek jika role bukan admin
if ($result['role'] !== 'admin') {
    echo "<h1>Anda tidak memiliki akses ke menu admin</h1>";
    exit;
}

// Ambil data mahasiswa untuk admin
$query = "SELECT m.nama_lengkap, 
                 d.status AS status_pendaftaran,
                 d.pendaftaran_id,
                 d.keterangan,
                 m.mahasiswa_id
          FROM mahasiswa m
          LEFT JOIN pendaftaran d ON m.mahasiswa_id = d.mahasiswa_id";
$stmt = $pdo->prepare($query);
$stmt->execute();
$data_mahasiswa = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Proses form untuk insert/update data pendaftaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status_pendaftaran'], $_POST['mahasiswa_id'], $_POST['keterangan'])) {
    $status_pendaftaran = $_POST['status_pendaftaran'];
    $mahasiswa_id = $_POST['mahasiswa_id'];
    $keterangan = $_POST['keterangan'];

    // Query untuk memeriksa apakah mahasiswa_id sudah ada di tabel pendaftaran
    $checkQuery = "SELECT * FROM pendaftaran WHERE mahasiswa_id = ?";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->execute([$mahasiswa_id]);
    $pendaftaranData = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($pendaftaranData) {
        // Update status dan keterangan jika data sudah ada
        $updateQuery = "UPDATE pendaftaran SET status = ?, keterangan = ? WHERE mahasiswa_id = ?";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute([$status_pendaftaran, $keterangan, $mahasiswa_id]);
    } else {
        // Insert data baru jika belum ada
        $insertQuery = "INSERT INTO pendaftaran (mahasiswa_id, status, keterangan) VALUES (?, ?, ?)";
        $insertStmt = $pdo->prepare($insertQuery);
        $insertStmt->execute([$mahasiswa_id, $status_pendaftaran, $keterangan]);
    }

    // Redirect untuk mencegah resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Menangani jika modal dibuka untuk mendapatkan data mahasiswa berdasarkan mahasiswa_id
if (isset($_GET['mahasiswa_id'])) {
    $mahasiswa_id = $_GET['mahasiswa_id'];

    // Query untuk mengambil data mahasiswa berdasarkan mahasiswa_id
    $query = "SELECT m.mahasiswa_id, m.nama_lengkap, d.status AS status_pendaftaran, d.keterangan
              FROM mahasiswa m
              LEFT JOIN pendaftaran d ON m.mahasiswa_id = d.mahasiswa_id
              WHERE m.mahasiswa_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$mahasiswa_id]);
    $data_mahasiswa = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!doctype html>
<!--
* Tabler - Premium and Open Source dashboard template with responsive and high quality UI.
* @version 1.0.0-beta20
* @link https://tabler.io
* Copyright 2018-2023 The Tabler Authors
* Copyright 2018-2023 codecalm.net Paweł Kuna
* Licensed under MIT (https://github.com/tabler/tabler/blob/master/LICENSE)
-->
<html lang="en">
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>Dashboard - Status Pendaftaran.</title>
    <!-- CSS files -->
    <link href="../../assets/css/tabler.min.css?1692870487" rel="stylesheet" />
    <link href="../../assets/css/tabler-flags.min.css?1692870487" rel="stylesheet" />
    <link href="../../assets/css/tabler-payments.min.css?1692870487" rel="stylesheet" />
    <link href="../../assets/css/tabler-vendors.min.css?1692870487" rel="stylesheet" />
    <link href="../../assets/css/demo.min.css?1692870487" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
      @import url('https://rsms.me/inter/inter.css');
      :root {
      	--tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
      }
      body {
      	font-feature-settings: "cv03", "cv04", "cv11";
      }
          .hover-icon {
        transition: transform 0.2s, color 0.2s; /* Efek transisi */
    }

    .hover-icon:hover {
        transform: scale(1.2); /* Membesarkan ikon */
        color: #0056b3; /* Mengubah warna saat hover */
    }

      table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      display: none; /* Sembunyikan tabel sampai data dimuat */
    }

    table, th, td {
      border: 1px solid #ddd;
    }

    th, td {
      padding: 10px;
      text-align: left;
    }

    th {
      background-color: #f4f4f4;
      position: relative;
    }

    .filter-icon {
      font-size: 14px;
      color: #007BFF;
      cursor: pointer;
      margin-left: 5px;
    }

    .filter-icon:hover {
      color: #0056b3;
    }

    /* CSS untuk Skeleton Loading */
    .skeleton {
      background-color: #e0e0e0;
      height: 20px;
      margin: 10px 0;
      border-radius: 4px;
    }

    .skeleton-text {
      background-color: #e0e0e0;
      height: 15px;
      margin: 10px 0;
      border-radius: 4px;
    }

    .skeleton-loading {
      display: block;
      animation: loading 2s infinite ease-in-out;
    }

    @keyframes loading {
      0% {
        background-color: #e0e0e0;
      }
      50% {
        background-color: #f4f4f4;
      }
      100% {
        background-color: #e0e0e0;
      }
    }

    .skeleton-container {
      display: flex;
      flex-direction: column;
    }

    #search-input::placeholder {
    font-size: 0.8rem; /* Adjust this value as needed */
  }

    </style>
  </head>
  <body >
    <script src="./dist/js/demo-theme.min.js?1692870487"></script>
    <div class="page">
      <!-- Navbar -->
       <!-- Navbar -->
       <header class="navbar navbar-expand-md d-print-none" >
        <div class="container-xl">
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
           <img src="../../assets/img/logo.ico" width="150" height="50" alt="Tabler" class="navbar-brand-image">
            <span>Universitas IPWIJA</span>
          </h1>
          <div class="navbar-nav flex-row order-md-last">
            <div class="nav-item d-none d-md-flex me-3">
              <div class="btn-list">
                
                
              </div>
            </div>
            <div class="d-none d-md-flex">
            
              <a href="?theme=light" class="nav-link px-0 hide-theme-light" title="Enable light mode" data-bs-toggle="tooltip"
		   data-bs-placement="bottom">
                <!-- Download SVG icon from http://tabler-icons.io/i/sun -->
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7" /></svg>
              </a>
              <div class="nav-item dropdown d-none d-md-flex me-3">
                
                <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end dropdown-menu-card">
                  <div class="card">
                    <div class="card-header">
                      <h3 class="card-title">Last updates</h3>
                    </div>
                    <div class="list-group list-group-flush list-group-hoverable">
                      <div class="list-group-item">
                        <div class="row align-items-center">
                          <div class="col-auto"><span class="status-dot status-dot-animated bg-red d-block"></span></div>
                          <div class="col text-truncate">
                            <a href="#" class="text-body d-block">Example 1</a>
                            <div class="d-block text-secondary text-truncate mt-n1">
                              Change deprecated html tags to text decoration classes (#29604)
                            </div>
                          </div>
                          <div class="col-auto">
                            <a href="#" class="list-group-item-actions">
                              <!-- Download SVG icon from http://tabler-icons.io/i/star -->
                              <svg xmlns="http://www.w3.org/2000/svg" class="icon text-muted" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" /></svg>
                            </a>
                          </div>
                        </div>
                      </div>
                      <div class="list-group-item">
                        <div class="row align-items-center">
                          <div class="col-auto"><span class="status-dot d-block"></span></div>
                          <div class="col text-truncate">
                            <a href="#" class="text-body d-block">Example 2</a>
                            <div class="d-block text-secondary text-truncate mt-n1">
                              justify-content:between ⇒ justify-content:space-between (#29734)
                            </div>
                          </div>
                          <div class="col-auto">
                            <a href="#" class="list-group-item-actions show">
                              <!-- Download SVG icon from http://tabler-icons.io/i/star -->
                              <svg xmlns="http://www.w3.org/2000/svg" class="icon text-yellow" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" /></svg>
                            </a>
                          </div>
                        </div>
                      </div>
                      <div class="list-group-item">
                        <div class="row align-items-center">
                          <div class="col-auto"><span class="status-dot d-block"></span></div>
                          <div class="col text-truncate">
                            <a href="#" class="text-body d-block">Example 3</a>
                            <div class="d-block text-secondary text-truncate mt-n1">
                              Update change-version.js (#29736)
                            </div>
                          </div>
                          <div class="col-auto">
                            <a href="#" class="list-group-item-actions">
                              <!-- Download SVG icon from http://tabler-icons.io/i/star -->
                              <svg xmlns="http://www.w3.org/2000/svg" class="icon text-muted" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" /></svg>
                            </a>
                          </div>
                        </div>
                      </div>
                      <div class="list-group-item">
                        <div class="row align-items-center">
                          <div class="col-auto"><span class="status-dot status-dot-animated bg-green d-block"></span></div>
                          <div class="col text-truncate">
                            <a href="#" class="text-body d-block">Example 4</a>
                            <div class="d-block text-secondary text-truncate mt-n1">
                              Regenerate package-lock.json (#29730)
                            </div>
                          </div>
                          <div class="col-auto">
                            <a href="#" class="list-group-item-actions">
                              <!-- Download SVG icon from http://tabler-icons.io/i/star -->
                              <svg xmlns="http://www.w3.org/2000/svg" class="icon text-muted" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" /></svg>
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="nav-item dropdown">
              <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
              <span class="avatar avatar-sm" style="background-image: url('dashboard/assets/avatars/000f.jpg')"></span>

              <div class="d-none d-xl-block ps-2">
                  <div><?php echo htmlspecialchars($user['username']); ?></div>
                  <div class="mt-1 small text-secondary"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
              </a>
              <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
              <a href="http://localhost/PMB-Projek/dashboard/menu/profile.php" class="dropdown-item">Profile</a>

                
                <div class="dropdown-divider"></div>
                
                <a href="#" id="logoutLink" class="dropdown-item">Logout</a>


              </div>
            </div>
          </div>
        </div>
      </header>
      <header class="navbar-expand-md">
        <div class="collapse navbar-collapse" id="navbar-menu">
          <div class="navbar">
            <div class="container-xl">
              <ul class="navbar-nav">
                <li class="nav-item ">
                  <a class="nav-link" href="http://localhost/PMB-Projek/dashboard/admin.php#" >
                    <span class="nav-link-icon d-md-none d-lg-inline-block"><!-- Download SVG icon from http://tabler-icons.io/i/home -->
                      <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" /></svg>
                    </span>
                    <span class="nav-link-title">
                      Home
                    </span>
                  </a>
                </li>
               
                <li class="nav-item active">
                  <a class="nav-link" href="http://localhost/PMB-Projek/dashboard/menu/approved_user.php" >
                    <span class="nav-link-icon d-md-none d-lg-inline-block"><!-- Download SVG icon from http://tabler-icons.io/i/checkbox -->
                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-checkbox"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 11l3 3l8 -8" /><path d="M20 12v6a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h9" /></svg>
                </span>
                    <span class="nav-link-title">
                      Setujui Data User
                    </span>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="http://localhost/PMB-Projek/dashboard/menu/admin_liatdata.php" >
                    <span class="nav-link-icon d-md-none d-lg-inline-block"><!-- Download SVG icon from http://tabler-icons.io/i/checkbox -->
                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-eye"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                </span>
                    <span class="nav-link-title">
                     Lihat Data User
                    </span>
                  </a>
                </li>

                
                <li class="nav-item">
                  <a class="nav-link" href="menu/profile.php" >
                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-users"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
                </span>
                    <span class="nav-link-title">
                     Profil
                    </span>
                  </a>
                </li>

                


              
                
               
               
               
              </ul>

            </div>
          </div>
        </div>
      </header>
      <div class="page-wrapper">
        <!-- Page header -->
        <div class="page-header d-print-none">
          <div class="container-xl">
            <div class="row g-2 align-items-center">
              <div class="col">
                <!-- Page pre-title -->
               
                <h2 class="page-title">
                 Status Pendaftaran Mahasiswa
                </h2>
              </div>
              <!-- Page title actions -->
              <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                 
                  
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Page body -->
        <div class="page-body">
        <div class="container-xl">
    <div class="d-flex justify-content-between mb-3">
        <!-- Filter Jumlah Data -->
        <div>
            <label for="data-length" class="me-2">Tampilkan:</label>
            <select id="data-length" class="form-select" style="width: auto; display: inline-block;">
                <option value="10">10</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <!-- Input Pencarian -->
        <div>
        <input type="text" id="search-input" class="form-control form-control-md w-100" placeholder="Cari Data Berdasarkan Nama...">
        </div>
    </div>
    <!-- Skeleton Loading -->
    <div id="skeleton-loader" class="skeleton-container">
        <div class="skeleton skeleton-text skeleton-loading"></div>
        <div class="skeleton skeleton-text skeleton-loading"></div>
        <div class="skeleton skeleton-text skeleton-loading"></div>
    </div>
    <!-- Table Data -->
    <div class="table-responsive">
        <table id="data-table" class="table table-bordered">
            <thead>
                <tr>
                    <th>Nomor</th>
                    <th>Nama Mahasiswa</th>
                    <th>Status Pendaftaran</th>
                    <th>Catatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="data-table-body">
                <!-- Data diisi dengan PHP -->
                <?php foreach ($data_mahasiswa as $index => $row): ?>
                    <tr>
                        <td><?= $index + 1; ?></td>
                        <td><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                        <td><?= htmlspecialchars($row['status_pendaftaran'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($row['keterangan'] ?? '-'); ?></td>
                        <td>
                            <button class="btn btn-primary" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modal-report" 
                                    data-pendaftaran-id="<?= htmlspecialchars($row['pendaftaran_id']); ?>" 
                                    data-mahasiswa-id="<?= htmlspecialchars($row['mahasiswa_id']); ?>"
                                    data-status-pendaftaran="<?= htmlspecialchars($row['status_pendaftaran']); ?>"
                                    data-keterangan="<?= htmlspecialchars($row['keterangan'] ?? ''); ?>">
                                Ubah Status
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


<div class="modal modal-blur fade" id="modal-report" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ubah Status Pendaftar</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" enctype="multipart/form-data">
          <!-- Hidden field for mahasiswa_id -->
          <input type="hidden" name="mahasiswa_id" id="mahasiswa_id">

          <div class="mb-3">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" class="form-control" name="nama_lengkap" id="nama_lengkap" readonly>
          </div>

          <div class="mb-3">
        <label for="status_pendaftaran" class="form-label">Status Pendaftaran</label>
        <select class="form-select" id="status_pendaftaran" name="status_pendaftaran" required>
            <option value="Pilih Status Pendaftaran" selected>Pilih Status Pendaftaran</option>
            <option value="menunggu disetujui">Menunggu Disetujui</option>
            <option value="disetujui">Disetujui</option>
            <option value="pending">Pending</option>
            <option value="ditolak">Ditolak</option>
        </select>
    </div>


          <div class="mb-3">
            <label class="form-label">Catatan</label>
            <textarea class="form-control" name="keterangan" id="keterangan" rows="4" placeholder="Masukkan catatan..."></textarea>
          </div>

          <div class="text-end">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Update</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


        <footer class="footer footer-transparent d-print-none">
          <div class="container-xl">
            <div class="row text-center align-items-center flex-row-reverse">
              <div class="col-lg-auto ms-lg-auto">
                <ul class="list-inline list-inline-dots mb-0">
                  <li class="list-inline-item"><a href="https://tabler.io/docs" target="_blank" class="link-secondary" rel="noopener">Documentation</a></li>
                  <li class="list-inline-item"><a href="./license.html" class="link-secondary">License</a></li>
                  <li class="list-inline-item"><a href="https://github.com/tabler/tabler" target="_blank" class="link-secondary" rel="noopener">Source code</a></li>
                  <li class="list-inline-item">
                    <a href="https://github.com/sponsors/codecalm" target="_blank" class="link-secondary" rel="noopener">
                      <!-- Download SVG icon from http://tabler-icons.io/i/heart -->
                      <svg xmlns="http://www.w3.org/2000/svg" class="icon text-pink icon-filled icon-inline" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M19.5 12.572l-7.5 7.428l-7.5 -7.428a5 5 0 1 1 7.5 -6.566a5 5 0 1 1 7.5 6.572" /></svg>
                      Sponsor
                    </a>
                  </li>
                </ul>
              </div>
              <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                <ul class="list-inline list-inline-dots mb-0">
                  <li class="list-inline-item">
                    Copyright &copy; 2024
                    <a href="." class="link-secondary">Student Software Engineering</a>.
                    All rights reserved.
                  </li>
                 
                </ul>
              </div>
            </div>
          </div>
        </footer>
      </div>
    </div>
   
    <!-- Libs JS -->
    <script src="../../assets/libs/apexcharts/dist/apexcharts.min.js?1692870487" defer></script>
    <script src="../../assets/libs/jsvectormap/dist/js/jsvectormap.min.js?1692870487" defer></script>
    <script src="../../assets/libs/jsvectormap/dist/maps/world.js?1692870487" defer></script>
    <script src="../../assets/libs/jsvectormap/dist/maps/world-merc.js?1692870487" defer></script>
    <!-- Tabler Core -->
    <script src="../../assets/js/tabler.min.js?1692870487" defer></script>
    <script src="../../assets/js/demo.min.js?1692870487" defer></script>

    <script>
    document.getElementById('logoutLink').addEventListener('click', function (event) {
        event.preventDefault(); // Mencegah aksi default tautan
        Swal.fire({
            title: 'Are you sure?',
            text: "You will be logged out!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, logout!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect ke index.php
                window.location.href = '../../index.php';
            }
        });
    });

    let lastSortedColumn = -1; // Variabel untuk melacak kolom yang terakhir diurutkan
    let sortOrder = 'desc'; // Urutan default adalah descending (terbesar ke terkecil)

    // Fungsi untuk menampilkan data dan menghilangkan skeleton
    window.onload = function () {
      const skeletonLoader = document.getElementById('skeleton-loader');
      const table = document.getElementById('data-table');

      // Sembunyikan skeleton dan tampilkan tabel
      skeletonLoader.style.display = 'none';
      table.style.display = 'table';
    };

    function sortTable(columnIndex) {
      const table = document.getElementById("data-table");
      const tbody = table.tBodies[0];
      const rows = Array.from(tbody.rows);

      // Cek apakah kolom yang sama yang diklik sebelumnya
      if (lastSortedColumn === columnIndex) {
        sortOrder = (sortOrder === 'desc') ? 'asc' : 'desc'; // Toggle urutan
      } else {
        sortOrder = 'desc'; // Jika kolom berbeda, defaultkan ke descending
      }

      // Mengurutkan berdasarkan kolom yang dipilih dari besar ke kecil atau kecil ke besar
      rows.sort((a, b) => {
        const aValue = a.cells[columnIndex].textContent.trim().toLowerCase();
        const bValue = b.cells[columnIndex].textContent.trim().toLowerCase();

        // Jika kolom angka, gunakan parseInt untuk konversi
        if (!isNaN(aValue) && !isNaN(bValue)) {
          return sortOrder === 'desc' 
            ? parseInt(bValue, 10) - parseInt(aValue, 10) 
            : parseInt(aValue, 10) - parseInt(bValue, 10);
        }

        // Jika kolom teks, gunakan string comparison
        return sortOrder === 'desc' 
          ? bValue.localeCompare(aValue) 
          : aValue.localeCompare(bValue);
      });

      // Reorder rows dalam tabel
      rows.forEach(row => tbody.appendChild(row));

      // Melacak kolom yang terakhir diurutkan
      lastSortedColumn = columnIndex;
    }

    document.querySelectorAll("[data-bs-toggle='modal']").forEach(button => {
    button.addEventListener("click", function() {
        // Ambil data dari atribut tombol
        const mahasiswaId = this.getAttribute("data-mahasiswa-id");
        const namaLengkap = this.closest("tr").querySelector("td:nth-child(2)").textContent.trim();
        const statusPendaftaran = this.getAttribute("data-status-pendaftaran");
        const keterangan = this.getAttribute("data-keterangan");

        // Isi data ke dalam form di modal
        document.getElementById("mahasiswa_id").value = mahasiswaId;
        document.getElementById("nama_lengkap").value = namaLengkap;
        document.querySelector("select[name='status_pendaftaran']").value = statusPendaftaran || 'Pilih Status Pendaftaran';
        document.getElementById("keterangan").value = keterangan || '';
    });
});

// Tangani klik tombol "Update"
document.querySelector("button[type='submit']").addEventListener("click", function(event) {
    event.preventDefault(); // Mencegah form dikirimkan langsung

    // Menampilkan konfirmasi SweetAlert2
    Swal.fire({
      title: 'Apakah Anda yakin?',
      text: 'Status pendaftaran akan diubah!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, perbarui!',
      cancelButtonText: 'Batal',
    }).then((result) => {
      if (result.isConfirmed) {
        // Jika dikonfirmasi, kirimkan form
        const form = event.target.closest("form");
        form.submit(); // Submit form

        // Menampilkan pesan sukses setelah form disubmit
        Swal.fire({
          title: 'Sukses!',
          text: 'Status pendaftaran berhasil diperbarui.',
          icon: 'success',
         
          showConfirmButton: false,
        }).then(() => {
          // Mengalihkan ke halaman status_pendaftaran_user.php setelah pesan sukses
          window.location.href = 'approved_user.php';
        });
      }
    });
  });

  document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('data-table-body');
    const searchInput = document.getElementById('search-input');
    const dataLengthSelect = document.getElementById('data-length');

    const rows = Array.from(tableBody.querySelectorAll('tr'));

    function filterData() {
        const searchValue = searchInput.value.toLowerCase();
        const maxRows = parseInt(dataLengthSelect.value, 10);

        let visibleCount = 0;
        rows.forEach(row => {
            const namaLengkap = row.children[1].textContent.toLowerCase();
            const matchesSearch = namaLengkap.includes(searchValue);

            if (matchesSearch && visibleCount < maxRows) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('input', filterData);
    dataLengthSelect.addEventListener('change', filterData);

    filterData(); // Inisialisasi filter
});

</script>

  </body>
</html>