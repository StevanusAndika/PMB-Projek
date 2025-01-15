<?php 
session_start();
include '../../koneksi.php'; // Menghubungkan ke database

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user'])) {
    header("Location: ../../index.php");
    exit;
}

$user = $_SESSION['user'];
// Query untuk mendapatkan role dari tabel users
$query = "SELECT role FROM users WHERE user_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$user['user_id']]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

// Periksa apakah role adalah 
if ($result['role'] !== 'pendaftar') {
    echo "<h1>Anda tidak memiliki akses ke menu ini</h1>";
    exit;
}


// Query untuk mendapatkan data mahasiswa dan status pendaftaran
$query = "SELECT m.*, 
                 b.jenis_berkas, 
                 b.file_path, 
                 p.nama_program_studi, 
                 k.nama_kelas, 
                 d.status AS status_pendaftaran, 
                 d.keterangan 
          FROM mahasiswa m
          LEFT JOIN berkas b ON m.mahasiswa_id = b.mahasiswa_id
          LEFT JOIN program_studi p ON m.program_studi_id = p.program_studi_id
          LEFT JOIN kelas k ON m.kelas_id = k.kelas_id
          LEFT JOIN pendaftaran d ON m.mahasiswa_id = d.mahasiswa_id
          WHERE m.user_id = ?";

$stmt = $pdo->prepare($query);
$stmt->execute([$user['user_id']]);
$data_mahasiswa = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Dashboard - Tampil Data.</title>
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

    </style>
  </head>
  <body >
    <script src="./dist/js/demo-theme.min.js?1692870487"></script>
    <div class="page">
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
                <span class="avatar avatar-sm" style="background-image: url(../static/avatars/000m.jpg)"></span>
                <div class="d-none d-xl-block ps-2">
                  <div><?php echo htmlspecialchars($user['username']); ?></div>
                  <div class="mt-1 small text-secondary"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
              </a>
              <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <a href="http://localhost/PMB-Projek/dashboard/menu/profile_user.php" class="dropdown-item">Profile</a>
                
                
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
                  <a class="nav-link" href="http://localhost/PMB-Projek/dashboard/user.php" >
                    <span class="nav-link-icon d-md-none d-lg-inline-block"><!-- Download SVG icon from http://tabler-icons.io/i/home -->
                      <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" /></svg>
                    </span>
                    <span class="nav-link-title">
                      Home
                    </span>
                  </a>
                </li>
               
                <li class="nav-item">
                  <a class="nav-link" href="http://localhost/PMB-Projek/dashboard/menu/status_pendaftaran_user.php" >
                    <span class="nav-link-icon d-md-none d-lg-inline-block"><!-- Download SVG icon from http://tabler-icons.io/i/checkbox -->
                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-checkbox"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 11l3 3l8 -8" /><path d="M20 12v6a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h9" /></svg>
                </span>
                    <span class="nav-link-title">
                      Status Pendaftaran
                    </span>
                  </a>
                </li>
                <li class="nav-item ">
                  <a class="nav-link" href="http://localhost/PMB-Projek/dashboard/menu/tanggal_daftar.php" >
                    <span class="nav-link-icon d-md-none d-lg-inline-block"><!-- Download SVG icon from http://tabler-icons.io/i/checkbox -->
                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-week"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M8 14v4" /><path d="M12 14v4" /><path d="M16 14v4" /></svg>
                </span>
                    <span class="nav-link-title">
                     Jadwal Pendaftaran
                    </span>
                  </a>
                </li>

                
                <li class="nav-item ">
                  <a class="nav-link" href="http://localhost/PMB-Projek/dashboard/menu/program_studi.php" >
                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-school"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M22 9l-10 -4l-10 4l10 4l10 -4v6" /><path d="M6 10.6v5.4a6 3 0 0 0 12 0v-5.4" /></svg>
                </span>
                    <span class="nav-link-title">
                      Program Studi
                    </span>
                  </a>
                </li>

                <li class="nav-item ">
                  <a class="nav-link" href="langkah_bayar.php" >
                    <span class="nav-link-icon d-md-none d-lg-inline-block">

                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-info-circle"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 9h.01" /><path d="M11 12h1v4h1" /></svg>
                </span>
                    <span class="nav-link-title">
                    Informasi Pembayaran
                    </span>
                  </a>
                </li>

                <li class="nav-item">
                  <a class="nav-link" href="http://localhost/PMB-Projek/dashboard/menu/penerimaan.php" >
                    <span class="nav-link-icon d-md-none d-lg-inline-block">

                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-check"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M11.5 21h-5.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v6" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M15 19l2 2l4 -4" /></svg>

                </span>
                    <span class="nav-link-title">
                    Pengumuman Penerimaan
                    </span>
                  </a>
                </li>

                <li class="nav-item dropdown">
  <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
    <span class="nav-link-icon d-md-none d-lg-inline-block">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-file-check">
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M14 3v4a1 1 0 0 0 1 1h4"/>
        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/>
        <path d="M9 15l2 2l4 -4"/>
      </svg>
    </span>
    <span class="nav-link-title">
      Biodata
    </span>
  </a>
  <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
    <!-- Menu Isi Data -->
    <li>
      <a class="dropdown-item" href="http://localhost/PMB-Projek/dashboard/menu/isi_biodata.php">
        <span class="nav-link-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M12 8v4l2 2l4 -4l-4 -4l-2 2z"/>
            <path d="M4 12h6l2 -2h6"/>
          </svg>
        </span>
        Isi Data
      </a>
    </li>
    <!-- Menu Tampil Data -->
    <li >
      <a class="dropdown-item  active" href="http://localhost/PMB-Projek/dashboard/menu/tampil_data.php">
        <span class="nav-link-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M14 3v4a1 1 0 0 0 1 1h4"/>
            <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/>
            <path d="M9 15l2 2l4 -4"/>
          </svg>
        </span>
        Tampil Data
      </a>
    </li>
  </ul>
</li>

                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false" >
                    <span class="nav-link-icon d-md-none d-lg-inline-block"><!-- Download SVG icon from http://tabler-icons.io/i/star -->
                      <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" /></svg>
                    </span>
                    <span class="nav-link-title">
                      Extra
                    </span>
                  </a>
                  <div class="dropdown-menu">
                    <div class="dropdown-menu-columns">
                      <div class="dropdown-menu-column">
                        <a class="dropdown-item" href="https://api.whatsapp.com/send/?phone=087788789741&text=Saya+tanya+terkait+pendaftaran&type=phone_number&app_absent=0">
                          WhatsApp PMB
                        </a>
                        <a class="dropdown-item" href="http://localhost/PMB-Projek/dashboard/menu/informasi_rekening.php">
                         Rekening Pembayaran
                        </a>
                        <a class="dropdown-item" href="https://pdf.hana-ci.com/compress"target="_blank">
                          PDF Compress
                          <span class="badge badge-sm bg-green-lt text-uppercase ms-auto">New</span>
                        </a>
                        <a class="dropdown-item" href="https://docs.google.com/forms/d/e/1FAIpQLSdAAPNpGYhFLmWgozP6g9ek50Bz8eSpsLUIEejRJSUKyFY0pA/viewform" target="_blank">
                         Soal CBT
                          <span class="badge badge-sm bg-green-lt text-uppercase ms-auto">New</span>
                        </a>
                        
                        
                       
                      </div>
                      <div class="dropdown-menu-column">
                       
                          
                      </div>
                    </div>
                  </div>
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
                 Biodata <?php echo htmlspecialchars($user['username']); ?>
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
  <!-- Skeleton Loading -->
  <!-- Skeleton Loading -->

  <div id="skeleton-loader" class="skeleton-container">
    <div class="skeleton skeleton-text skeleton-loading"></div>
    <div class="skeleton skeleton-text skeleton-loading"></div>
    <div class="skeleton skeleton-text skeleton-loading"></div>
</div>

<div class="table-responsive">
    <table id="data-table" class="table table-bordered">
        <thead>
            <tr>
                <th>Nomor</th>
                <th>Nama Mahasiswa</th>
                <th>NIK</th>
                <th>Alamat</th>
                <th>Sekolah Asal</th>
                <th>Tahun Lulus</th>
                <th>Biaya Pendaftaran</th>
                <th>Program Studi Pilihan</th>
                <th>Kelas Pilihan</th>
                <th>Berkas Pendaftaran</th>
                <th>Status Pendaftaran</th>
                <th>Keterangan</th>
                <th>Tanggal Pendaftaran</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data_mahasiswa as $index => $row): ?>
                <tr>
                    <td><?= $index + 1; ?></td>
                    <td><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                    <td><?= htmlspecialchars($row['nik']); ?></td>
                    <td><?= htmlspecialchars($row['alamat']); ?></td>
                    <td><?= htmlspecialchars($row['sekolah_asal']); ?></td>
                    <td><?= htmlspecialchars($row['tahun_lulus']); ?></td>
                    <td>Rp. <?= number_format($row['biaya_pendaftaran'], 0, ',', '.'); ?></td>
                    <td><?= htmlspecialchars($row['nama_program_studi']); ?></td>
                    <td><?= htmlspecialchars($row['nama_kelas']); ?></td>
                    <td>
                        <a href="<?= htmlspecialchars($row['file_path']); ?>" class="text-danger" target="_blank">Lihat Berkas</a>
                    </td>
                    <td><?= htmlspecialchars($row['status_pendaftaran']); ?></td>
                    <td><?= htmlspecialchars($row['keterangan']); ?></td>
                    <td><?= htmlspecialchars($row['waktu_pendaftaran']); ?></td>
                    <td>
                        <?php if ($row['status_pendaftaran'] === 'disetujui'): ?>
                            <button class="btn btn-secondary btn-sm" title="Tidak Bisa Diedit" disabled>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-secondary btn-sm" title="Tidak Bisa Dihapus" disabled>
                                <i class="fas fa-trash"></i>
                            </button>
                            <div class="mt-2">
                                <small class="text-danger">
                                    Silahkan hubungi admin untuk melakukan perubahan data.
                                </small>
                                <a href="https://api.whatsapp.com/send/?phone=087788789741&text=Saya+tanya+terkait+pendaftaran&type=phone_number&app_absent=0" 
                                   class="btn btn-success btn-sm mt-1" 
                                   target="_blank">
                                    Hubungi Admin
                                </a>
                            </div>
                            <script>
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Aksi Dibatasi',
                                    text: 'Anda tidak dapat menghapus atau mengedit data karena sudah disetujui oleh admin.',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            </script>
                        <?php else: ?>
                            <a href="update_data.php" class="btn btn-info btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="cetak_pdf.php?id=<?= $row['mahasiswa_id']; ?>" class="btn btn-success btn-sm" title="Cetak PDF">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                            <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $row['mahasiswa_id']; ?>)" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
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
    <div class="modal modal-blur fade" id="modal-report" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Biodata</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" enctype="multipart/form-data">
          <div class="mb-3">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" class="form-control" name="nama_lengkap" value="<?= htmlspecialchars($row['nama_lengkap']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">NIK</label>
            <input type="number" class="form-control" name="nik" value='<?= htmlspecialchars($row['nik']) ?>' required>
          </div>
          <div class="mb-3">
            <label class="form-label">Asal Sekolah</label>
            <input type="text" class="form-control" name="sekolah_asal" <?= htmlspecialchars($row['sekolah_asal']) ?> required>
          </div>
          <div class="mb-3">
            <label class="form-label">Nilai Ujian </label>
            <input type="number" class="form-control" name="nilai_Ujian" value='<?= htmlspecialchars($row['nik']) ?>' required>
          </div>


          <div class="row">
            <div class="col-lg-8">
              <div class="mb-3">
                <label class="form-label">Nomor Telp</label>
                <div class="input-group input-group-flat">
                  <span class="input-group-text"></span>
                  <input type="number" class="form-control ps-0" name="no_telp" required>
                </div>
              </div>
            </div>
            <div class="col-lg-4">
              <div class="mb-3">
                <label class="form-label">Jurusan Pilihan</label>
                <select class="form-select" name="program_studi" required>
                  <option value="sekolah_asal" selected>Pilih Jurusan</option>
                  <?php foreach ($program_studi as $program) : ?>
                    <option value="<?= $program['program_studi_id']; ?>"><?= $program['nama_program_studi']; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-lg-6">
              <div class="mb-3">
                <label class="form-label">Biaya Pendaftaran</label>
                <input type="text" class="form-control" value="1,500,000" readonly>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="mb-3">
                <label class="form-label">Pilihan Kelas</label>
                <select class="form-select" name="kelas" required>
                  <option value="" selected>Pilih Kelas</option>
                  <?php foreach ($kelas as $kelas_option) : ?>
                    <option value="<?= $kelas_option['kelas_id']; ?>"><?= $kelas_option['nama_kelas']; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>

          <hr>

          <div class="row">
            <div class="col-lg-6">
              <div class="mb-3">
                <label class="form-label" for="year" required>Tahun Lulus</label>
                <select class="form-control" name="tahun_lulus" required>
                  <option value="" disabled selected>Select Year</option>
                  <?php
                  for ($year = 2000; $year <= 2050; $year++) {
                    echo "<option value='$year'>$year</option>";
                  }
                  ?>
                </select>
              </div>
            </div>

            <div class="col-lg-6">
              <div class="mb-3">
                <label class="form-label" for="file">Upload File</label>
                <input type="file" class="form-control" name="file" accept="application/pdf" required>
                <div class="form-text text-danger">
                  Berkas yang diupload: hanya file PDF (ijazah/SKL, kartu keluarga, foto, dan bukti pembayaran) dengan ukuran maksimal 3MB.
                </div>
              </div>
            </div>
            <div class="mb-3">
            <label class="form-label">Format Berkas</label>
            <select class="form-select" name="jenis_berkas" required>
                <option value="" selected>Pilih Format File Berkas</option>
                <option value="PDF">PDF</option>
               
            </select>
        </div>
          </div>

          <hr>

          <div class="mb-3">
            <label class="form-label">Alamat</label>
            <textarea class="form-control" name="alamat" placeholder="Masukkan Alamat Anda" rows="3" required></textarea>
          </div>

          <div class="text-end">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Close</button>
            <button type="submit" class="btn btn-primary">Update</button>
          </div>
        </form>
      </div>
    </div>
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

    function confirmDelete(mahasiswaId) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: "Apakah Anda yakin ingin menghapus data ini?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirect to delete action
            window.location.href = `delete_data.php?id=${mahasiswaId}`;
        }
    });
}
</script>

  </body>
</html>