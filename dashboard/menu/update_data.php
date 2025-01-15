<?php
session_start();
include '../../koneksi.php';

// Pastikan user sudah login
if (!isset($_SESSION['user'])) {
    header('Location: ../../login.php');
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['user_id'];

// Query untuk mendapatkan role pengguna
$query = "SELECT role FROM users WHERE user_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$user['user_id']]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

// Cek jika role bukan admin
if ($result['role'] !== 'pendaftar') {
    echo "<h1>Anda tidak memiliki akses ke menu admin</h1>";
    exit;
}

// Ambil data mahasiswa berdasarkan user_id
$stmt_mahasiswa = $pdo->prepare("SELECT * FROM mahasiswa WHERE user_id = :user_id LIMIT 1");
$stmt_mahasiswa->execute([':user_id' => $user_id]);
$mahasiswa = $stmt_mahasiswa->fetch(PDO::FETCH_ASSOC);

// Ambil data program studi dan kelas
$stmt_program_studi = $pdo->query("SELECT * FROM program_studi");
$program_studi = $stmt_program_studi->fetchAll(PDO::FETCH_ASSOC);

$stmt_kelas = $pdo->query("SELECT * FROM kelas");
$kelas = $stmt_kelas->fetchAll(PDO::FETCH_ASSOC);

// Ambil data berkas jika ada
$stmt_berkas = $pdo->prepare("SELECT * FROM berkas WHERE mahasiswa_id = :mahasiswa_id");
$stmt_berkas->execute([':mahasiswa_id' => $mahasiswa['mahasiswa_id']]);
$berkas = $stmt_berkas->fetch(PDO::FETCH_ASSOC);

// Cek apakah form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = $_POST['nama_lengkap'];
    $nik = $_POST['nik'];
    $alamat = $_POST['alamat'];
    $sekolah_asal = $_POST['sekolah_asal'];
    $tahun_lulus = $_POST['tahun_lulus'];
    $no_telp = $_POST['no_telp'];
    $program_studi_id = $_POST['program_studi'];
    $kelas_id = $_POST['kelas'];
    $gelombang = $_POST['gelombang'];

    // Validasi dan unggah file
    if (isset($_FILES['file']) && $_FILES['file']['size'] > 0) {
        $file = $_FILES['file'];
        if ($file['size'] > 3145728) { // 3MB
            echo "<script>alert('File size exceeds 3MB!');</script>";
            exit;
        }
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        if ($fileExtension !== 'pdf') {
            echo "<script>alert('Only PDF files are allowed!');</script>";
            exit;
        }
        $randomFileName = uniqid('file_', true) . '.' . $fileExtension;
        $filePath = "../../uploads/" . $randomFileName;
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            echo "<script>alert('Failed to upload the file!');</script>";
            exit;
        }
        // Update atau simpan data file ke tabel berkas
        $stmt_file = $pdo->prepare("REPLACE INTO berkas (mahasiswa_id, jenis_berkas, file_path, upload_time) 
                                    VALUES (:mahasiswa_id, :jenis_berkas, :file_path, NOW())");
        $stmt_file->execute([
            ':mahasiswa_id' => $mahasiswa['mahasiswa_id'],
            ':jenis_berkas' => $gelombang,
            ':file_path' => $filePath
        ]);
    }

    
    
      // Kalkulasi biaya pendaftaran berdasarkan program studi
      $biaya_pendaftaran = 0;
      $biaya_gedung = 0;
      $biaya_spp = 0;
  
      switch ($program_studi_id) {
          case 1: case 2: case 3: case 5: case 7: // S1 Program Studi
              $biaya_pendaftaran = 600000;
              $biaya_gedung = 6000000;
              $biaya_spp = 900000;
              break;
          case 4: // D3 Kebidanan
              $biaya_pendaftaran = 2100000;
              $biaya_gedung = 6000000;
              $biaya_spp = 1100000;
              break;
          case 6: // S2 Manajemen
              $biaya_pendaftaran = 1100000;
              $biaya_gedung = 7500000;
              $biaya_spp = 1250000;
              break;
          default:
              break;
      }
  
      $total_biaya = $biaya_pendaftaran + $biaya_gedung + $biaya_spp;
    
    
    // Update data mahasiswa
    try {
        $stmt_update = $pdo->prepare("UPDATE mahasiswa SET 
                                        nama_lengkap = :nama_lengkap, 
                                        nik = :nik, 
                                        alamat = :alamat, 
                                        sekolah_asal = :sekolah_asal, 
                                        tahun_lulus = :tahun_lulus, 
                                        no_telp = :no_telp, 
                                        program_studi_id = :program_studi_id, 
                                        kelas_id = :kelas_id,
                                        Gelombang = :gelombang,
                                        biaya_pendaftaran = :biaya_pendaftaran
                                      WHERE user_id = :user_id");
        $stmt_update->execute([
            ':nama_lengkap' => $nama_lengkap,
            ':nik' => $nik,
            ':alamat' => $alamat,
            ':sekolah_asal' => $sekolah_asal,
            ':tahun_lulus' => $tahun_lulus,
            ':no_telp' => $no_telp, // Menambahkan no_telp ke query
            ':program_studi_id' => $program_studi_id,
            ':kelas_id' => $kelas_id,
            ':gelombang' => $gelombang,
            ':biaya_pendaftaran' => $total_biaya,
            ':user_id' => $user_id
        ]);

        echo "<script>alert('Data berhasil diperbarui!');</script>";
        header('Location: tampil_data.php');
        exit;
    } catch (PDOException $e) {
        echo "<script>alert('Terjadi kesalahan: " . $e->getMessage() . "');</script>";
    }
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
    <title>Dashboard - Update Data.</title>
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
                <li class="nav-item">
                  <a class="nav-link" href="http://localhost/PMB-Projek/dashboard/menu/tanggal_daftar.php" >
                    <span class="nav-link-icon d-md-none d-lg-inline-block"><!-- Download SVG icon from http://tabler-icons.io/i/checkbox -->
                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-week"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M8 14v4" /><path d="M12 14v4" /><path d="M16 14v4" /></svg>
                </span>
                    <span class="nav-link-title">
                     Jadwal Pendaftaran
                    </span>
                  </a>
                </li>

                
                  <a class="nav-link" href="program_studi.php" >
                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-school"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M22 9l-10 -4l-10 4l10 4l10 -4v6" /><path d="M6 10.6v5.4a6 3 0 0 0 12 0v-5.4" /></svg>
                </span>
                    <span class="nav-link-title">
                      Program Studi
                    </span>
                  </a>
                </li>

                <li class="nav-item active">
               
                  <a class="nav-link " href="./form-elements.html" >
                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-file-check"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 15l2 2l4 -4" /></svg>
                </span>
                    <span class="nav-link-title">
                     Update Biodata
                    </span>
                  </a>
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
                        <a class="dropdown-item" href="https://pdf.hana-ci.com/compress">
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
                 Update Biodata
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
  <a href="#" class="btn btn-primary d-inline-block d-sm-inline-block ms-auto" data-bs-toggle="modal" data-bs-target="#modal-report">
    <!-- Download SVG icon from http://tabler-icons.io/i/plus -->
    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <path d="M12 5l0 14" />
        <path d="M5 12l14 0" />
    </svg>
    Update Biodata
</a>


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
        <h5 class="modal-title">Update Data <?= $mahasiswa['nama_lengkap'] ?? ''; ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <form method="POST" enctype="multipart/form-data">
    <!-- Nama Lengkap -->
    <div class="mb-3">
        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
               value="<?= $mahasiswa['nama_lengkap'] ?? ''; ?>" required>
    </div>

    <!-- NIK -->
    <div class="mb-3">
        <label for="nik" class="form-label">NIK</label>
        <input type="text" class="form-control" id="nik" name="nik" 
               value="<?= $mahasiswa['nik'] ?? ''; ?>" required>
    </div>

    <!-- Alamat -->
    <div class="mb-3">
        <label for="alamat" class="form-label">Alamat</label>
        <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?= $mahasiswa['alamat'] ?? ''; ?></textarea>
    </div>

    <!-- Sekolah Asal -->
    <div class="mb-3">
        <label for="sekolah_asal" class="form-label">Sekolah Asal</label>
        <input type="text" class="form-control" id="sekolah_asal" name="sekolah_asal" 
               value="<?= $mahasiswa['sekolah_asal'] ?? ''; ?>" required>
    </div>

    <!-- Tahun Lulus -->
    <div class="mb-3">
        <label for="tahun_lulus" class="form-label">Tahun Lulus</label>
        <select class="form-control" id="tahun_lulus" name="tahun_lulus" required>
            <option value="">Pilih Tahun Lulus</option>
            <?php for ($year = 2000; $year <= 2050; $year++): ?>
                <option value="<?= $year; ?>" 
                        <?= isset($mahasiswa['tahun_lulus']) && $mahasiswa['tahun_lulus'] == $year ? 'selected' : ''; ?>>
                    <?= $year; ?>
                </option>
            <?php endfor; ?>
        </select>
    </div>

   <!-- Program Studi -->
<div class="mb-3">
    <label for="program_studi" class="form-label">Program Studi</label>
    <select class="form-control" id="program_studi" name="program_studi" required onchange="updateBiaya()">
        <option value="">Pilih Program Studi</option>
        <?php
        // Daftar program studi
        $program_studi = [
            1 => "S1 Rekayasa Perangkat Lunak",
            2 => " S1 Informatika",
            3 => "S1 Sistem Informasi",
            4 => "D3 Kebidanan",
            5 => "S1 Kewirausahaan",
            6 => "S2 Manajemen",
            7 => "S1 Manajemen",
            
        ];

        // Generate opsi dropdown
        foreach ($program_studi as $id => $nama) {
            $selected = isset($mahasiswa['program_studi_id']) && $mahasiswa['program_studi_id'] == $id ? 'selected' : '';
            echo "<option value=\"$id\" $selected>$nama</option>";
        }
        ?>
    </select>
</div>


    <!-- Kelas -->
    <div class="mb-3">
        <label for="kelas" class="form-label">Kelas</label>
        <select class="form-control" id="kelas" name="kelas" required>
            <option value="">Pilih Kelas</option>
            <?php foreach ($kelas as $kls): ?>
                <option value="<?= $kls['kelas_id']; ?>" 
                        <?= isset($mahasiswa['kelas_id']) && $mahasiswa['kelas_id'] == $kls['kelas_id'] ? 'selected' : ''; ?>>
                    <?= $kls['nama_kelas']; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Gelombang Pendaftaran -->
    <div class="mb-3">
        <label for="gelombang" class="form-label">Gelombang Pendaftaran</label>
        <select class="form-control" id="gelombang" name="gelombang" required>
            <option value="1" <?= isset($mahasiswa['gelombang']) && $mahasiswa['gelombang'] == '1' ? 'selected' : ''; ?>>Gelombang 1</option>
            <option value="2" <?= isset($mahasiswa['gelombang']) && $mahasiswa['gelombang'] == '2' ? 'selected' : ''; ?>>Gelombang 2</option>
            <option value="3" <?= isset($mahasiswa['gelombang']) && $mahasiswa['gelombang'] == '3' ? 'selected' : ''; ?>>Gelombang 3</option>
        </select>
    </div>

    <!-- Biaya Pendaftaran -->
    <div class="mb-3">
        <label for="biaya_pendaftaran" class="form-label">Biaya Pendaftaran</label>
        <input type="text" class="form-control" id="biaya_pendaftaran" name="biaya_pendaftaran" 
               value="<?= number_format($mahasiswa['biaya_pendaftaran'] ?? 0, 0, ',', '.'); ?>" readonly>
    </div>

    <!-- File Sebelumnya -->
    <div class="mb-3">
        <label for="file_sebelumnya" class="form-label">File Sebelumnya</label>
        <?php if (!empty($berkas)): ?>
            <a href="<?= $berkas['file_path']; ?>" target="_blank">Lihat File</a>
        <?php else: ?>
            <p>Belum ada file yang diunggah.</p>
        <?php endif; ?>
    </div>

    <!-- Upload File Baru -->
    <div class="mb-3">
        <label for="file" class="form-label">Upload File Baru</label>
        <input type="file" class="form-control" id="file" name="file" accept=".pdf">
        <small class="form-text text-muted">Hanya file PDF dengan ukuran maksimum 3MB yang diizinkan.</small>
    </div>
    <!-- Nomor Telepon -->
<!-- Nomor Telepon -->
<div class="mb-3">
                <label for="no_telp" class="form-label">Nomor Telepon</label>
                <input type="text" class="form-control" id="no_telp" name="no_telp" value="<?= $mahasiswa['no_telp'] ?? ''; ?>" required>
            </div>


    <!-- Tombol Simpan -->
    <button type="submit" class="btn btn-primary" id="submitButton">Simpan</button>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Close</button>
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

function updateBiaya() {
    var programStudi = document.getElementById("program_studi").value;
    var biayaPendaftaran = 0;
    var biayaGedung = 0;
    var biayaSPP = 0;

    // Kondisi untuk setiap program studi
    if (programStudi == 1) { //S1 Rekayasa Perangkat Lunak
        biayaPendaftaran = 600000;
        biayaGedung = 6000000;
        biayaSPP = 900000;
    } else if (programStudi == 2) { //S1 INFORMATIKA
        biayaPendaftaran = 600000;
        biayaGedung = 6000000;
        biayaSPP = 900000;
    } else if (programStudi == 3) { // S1 SISTEM INFORMASI
        biayaPendaftaran = 600000;
        biayaGedung = 6000000;
        biayaSPP = 900000;
    } else if (programStudi == 4) { //D3 KEBIDANAN
        biayaPendaftaran = 2100000;
        biayaGedung = 6000000;
        biayaSPP = 1100000;
    } else if (programStudi == 5) { // S1 KEWIRAUSAHAAN
        biayaPendaftaran = 600000;
        biayaGedung = 6000000;
        biayaSPP = 900000;
    } else if (programStudi == 6) { // S2 MANAJEMEN
      biayaPendaftaran = 1100000;
        biayaGedung = 7500000;
        biayaSPP = 1250000;
    } else if (programStudi == 7) { // S1  MANAJEMEN
        biayaPendaftaran = 600000;
        biayaGedung = 6000000;
        biayaSPP = 900000;
    } else { // Jika tidak ada jurusan yang dipilih
        biayaPendaftaran = 0;
        biayaGedung = 0;
        biayaSPP = 0;
    }

    // Kalkulasi total biaya
    var totalBiaya = biayaPendaftaran + biayaGedung + biayaSPP;
    document.getElementById("biaya_pendaftaran").value = totalBiaya.toLocaleString();
}


    document.addEventListener("DOMContentLoaded", function() {
    // Ambil tombol submit form
    const submitButton = document.querySelector("button[type='submit']");

    // Event listener ketika tombol submit diklik
    submitButton.addEventListener("click", function(event) {
        event.preventDefault(); // Mencegah form agar tidak langsung disubmit

        // Tampilkan pesan SweetAlert
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah Anda yakin ingin mengirimkan data?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Kirim!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Jika pengguna mengklik 'Ya, Kirim!', form akan disubmit
                Swal.fire('Data Terkirim!', 'Data Anda berhasil dikirim!', 'success').then(() => {
                  // Redirect ke tampil_data.php setelah submit
                 
                    document.querySelector('form').submit(); 
                    
                    
                });
            } else {
                // Jika pengguna mengklik 'Batal', tampilkan pesan batal
                Swal.fire('Batal', 'Pengiriman data dibatalkan', 'error');
            }
        });
    });
});
    


   
    </script>
    </body>
    </html>