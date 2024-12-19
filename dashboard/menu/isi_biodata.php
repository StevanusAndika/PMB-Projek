<?php
// Menghubungkan ke database menggunakan PDO
session_start();
include '../../koneksi.php';

// Pastikan user sudah login
if (!isset($_SESSION['user'])) {
    echo "User not logged in.";
    exit;
}

$user = $_SESSION['user'];  // Mengambil data user dari session

// Ambil data program studi dan kelas dari database
$stmt_program_studi = $pdo->query("SELECT * FROM program_studi");
$program_studi = $stmt_program_studi->fetchAll(PDO::FETCH_ASSOC);

$stmt_kelas = $pdo->query("SELECT * FROM kelas");
$kelas = $stmt_kelas->fetchAll(PDO::FETCH_ASSOC);

// Cek apakah form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $nama_lengkap = $_POST['nama_lengkap'];
    $nik = $_POST['nik'];
    $sekolah_asal = $_POST['sekolah_asal'];
    $no_telp = $_POST['no_telp'];
    $program_studi_id = $_POST['program_studi'];
    $kelas_id = $_POST['kelas'];
    $tahun_lulus = $_POST['tahun_lulus'];
    $alamat = $_POST['alamat'];
    $jenis_berkas = $_POST['jenis_berkas'];

    // Validasi file yang diupload
    if (isset($_FILES['file'])) {
        $file = $_FILES['file'];

        // Cek ukuran file (maksimal 3MB)
        if ($file['size'] > 3145728) {
            echo "<script>Swal.fire('Error!', 'File size exceeds 3MB!', 'error');</script>";
            exit;
        }

        // Cek tipe file (hanya PDF yang diizinkan)
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        if ($fileExtension !== 'pdf') {
            echo "<script>Swal.fire('Error!', 'Only PDF files are allowed!', 'error');</script>";
            exit;
        }

        // Mengacak nama file
        $randomFileName = uniqid('file_', true) . '.' . $fileExtension;
        $filePath = "../../uploads/" . $randomFileName;

        // Pastikan folder uploads ada
        if (!is_dir("../../uploads")) {
            mkdir("../../uploads", 0777, true);  // Membuat folder uploads jika belum ada
        }

        // Pindahkan file ke folder tujuan
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            echo "<script>Swal.fire('Error!', 'Failed to upload the file!', 'error');</script>";
            exit;
        }
    } else {
        echo "<script>Swal.fire('Error!', 'File upload failed!', 'error');</script>";
        exit;
    }

    // Masukkan data mahasiswa ke database
    try {
        // Query untuk memasukkan data mahasiswa
        $stmt = $pdo->prepare("INSERT INTO mahasiswa (user_id, nama_lengkap, nik, sekolah_asal, no_telp, program_studi_id, kelas_id, tahun_lulus, biaya_pendaftaran, alamat) 
                               VALUES (:user_id, :nama_lengkap, :nik, :sekolah_asal, :no_telp, :program_studi_id, :kelas_id, :tahun_lulus, :biaya_pendaftaran, :alamat)");

        // Eksekusi query untuk mahasiswa
        $stmt->execute([
            ':user_id' => $user['user_id'],
            ':nama_lengkap' => $nama_lengkap,
            ':nik' => $nik,
            ':sekolah_asal' => $sekolah_asal,
            ':no_telp' => $no_telp,
            ':program_studi_id' => $program_studi_id,
            ':kelas_id' => $kelas_id,
            ':tahun_lulus' => $tahun_lulus,
            ':biaya_pendaftaran' => 1500000,  // Biaya pendaftaran otomatis
            ':alamat' => $alamat
        ]);

        // Ambil ID mahasiswa yang baru saja dimasukkan
        $mahasiswa_id = $pdo->lastInsertId();

        // Memastikan ID mahasiswa yang benar
        if ($mahasiswa_id) {
            // Query untuk memasukkan data berkas
            $stmt_berkas = $pdo->prepare("INSERT INTO berkas (mahasiswa_id, jenis_berkas, file_path, upload_time) 
                                          VALUES (:mahasiswa_id, :jenis_berkas, :file_path, NOW())");

            // Eksekusi query untuk berkas
            $stmt_berkas->execute([
                ':mahasiswa_id' => $mahasiswa_id,
                ':jenis_berkas' => $jenis_berkas,
                ':file_path' => $filePath
            ]);

            // Notifikasi sukses dengan SweetAlert
            echo "<script>
                    Swal.fire({
                        title: 'Success!',
                        text: 'Data and file successfully submitted!',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(function() {
                        window.location = 'tampil_data.php';  // Redirect ke halaman isi_biodata.php
                    });
                  </script>";
        } else {
            // Error jika ID mahasiswa tidak berhasil didapat
            echo "<script>Swal.fire('Error!', 'Failed to save student data!', 'error');</script>";
        }

    } catch (PDOException $e) {
        // Tangani error jika terjadi kesalahan pada query
        echo "<script>
                Swal.fire({
                    title: 'Error!',
                    text: 'Error inserting data: " . $e->getMessage() . "',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
              </script>";
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
    <title>Dashboard - User Menu.</title>
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
            <a href="#">
            <img src="../../assets/img/logo.ico" width="150" height="50" alt="Tabler" class="navbar-brand-image">

            </a>
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
                  <a class="nav-link" href="#" >
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
                     Isi Biodata
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
                        <a class="dropdown-item" href="#">
                         Rekening Pembayaran
                        </a>
                        <a class="dropdown-item" href="https://pdf.hana-ci.com/compress">
                          PDF Compress
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
                  Isi Biodata
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
    isi Biodata
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
        <h5 class="modal-title">Isi Biodata</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" enctype="multipart/form-data">
          <div class="mb-3">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" class="form-control" name="nama_lengkap" placeholder="Masukkan Nama Lengkap Anda" required>
          </div>
          <div class="mb-3">
            <label class="form-label">NIK</label>
            <input type="number" class="form-control" name="nik" placeholder="Masukkan NIK Anda" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Asal Sekolah</label>
            <input type="text" class="form-control" name="sekolah_asal" placeholder="Masukkan Asal Sekolah Anda" required>
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
                  <option value="" selected>Pilih Jurusan</option>
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
                <label class="form-label" for="year">Tahun Lulus</label>
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
            <button type="submit" class="btn btn-primary">Submit</button>
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
                    document.querySelector('form').submit(); // Melanjutkan proses submit form
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