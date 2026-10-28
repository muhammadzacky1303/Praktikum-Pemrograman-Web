<?php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "db5d";

// Koneksi ke database
$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// --- Pastikan kolom foto ada di tabel mahasiswa ---
$cek_kolom = mysqli_query($conn, "SHOW COLUMNS FROM mahasiswa LIKE 'foto'");
if (mysqli_num_rows($cek_kolom) == 0) {
    mysqli_query($conn, "ALTER TABLE mahasiswa ADD COLUMN foto VARCHAR(255) DEFAULT NULL");
}

// --- Fungsi konversi nilai ---
function konversiNilai($nilai) {
    if ($nilai >= 80.51)      return ["A", "Sangat Memuaskan"];
    elseif ($nilai >= 65.51)  return ["B", "Memuaskan"];
    elseif ($nilai >= 50.51)  return ["C", "Cukup Memuaskan"];
    elseif ($nilai >= 34.51)  return ["D", "Kurang"];
    else                      return ["E", "Sangat Kurang"];
}

$pesan      = "";
$pesan_type = "success";

// --- PROSES TAMBAH (dengan foto) ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['_action']) && $_POST['_action'] === 'tambah') {
    $nim        = trim($_POST["nim"] ?? "");
    $nama       = trim($_POST["nama_lengkap"] ?? "");
    $nilai      = floatval($_POST["nilai_akhir"] ?? 0);

    if ($nim === "" || $nama === "" || $nilai < 0 || $nilai > 100) {
        $pesan      = "Data tidak valid. Pastikan semua field terisi dengan benar.";
        $pesan_type = "danger";
    } else {
        // Upload foto jika ada
        $foto_path = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = "uploads/foto/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($ext, $allowed)) {
                $foto_name = $nim . '_' . time() . '.' . $ext;
                $foto_path = $upload_dir . $foto_name;
                move_uploaded_file($_FILES['foto']['tmp_name'], $foto_path);
            }
        }

        [$huruf, $predikat] = konversiNilai($nilai);
        $stmt = mysqli_prepare($conn, "INSERT INTO mahasiswa (nim, nama_lengkap, nilai_akhir, nilai_huruf, predikat, foto) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssdsss", $nim, $nama, $nilai, $huruf, $predikat, $foto_path);
            if (mysqli_stmt_execute($stmt)) {
                $pesan      = "Data mahasiswa <strong>{$nama}</strong> berhasil ditambahkan!";
                $pesan_type = "success";
            } else {
                $pesan      = "Gagal menyimpan data: " . mysqli_stmt_error($stmt);
                $pesan_type = "danger";
            }
            mysqli_stmt_close($stmt);
        } else {
            $pesan      = "Gagal menyiapkan query.";
            $pesan_type = "danger";
        }
    }
}

// --- PROSES EDIT (dengan foto) ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['_action']) && $_POST['_action'] === 'edit') {
    $nim   = trim($_POST["nim"] ?? "");
    $nama  = trim($_POST["nama_lengkap"] ?? "");
    $nilai = floatval($_POST["nilai_akhir"] ?? 0);

    if ($nim === "" || $nama === "" || $nilai < 0 || $nilai > 100) {
        $pesan      = "Data tidak valid.";
        $pesan_type = "danger";
    } else {
        // Upload foto baru jika ada
        $foto_path = null;
        $update_foto = false;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = "uploads/foto/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($ext, $allowed)) {
                $foto_name = $nim . '_' . time() . '.' . $ext;
                $foto_path = $upload_dir . $foto_name;
                move_uploaded_file($_FILES['foto']['tmp_name'], $foto_path);
                $update_foto = true;
            }
        }

        [$huruf, $predikat] = konversiNilai($nilai);

        if ($update_foto) {
            $stmt = mysqli_prepare($conn, "UPDATE mahasiswa SET nama_lengkap=?, nilai_akhir=?, nilai_huruf=?, predikat=?, foto=? WHERE nim=?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sdssss", $nama, $nilai, $huruf, $predikat, $foto_path, $nim);
                if (mysqli_stmt_execute($stmt)) {
                    $pesan      = "Data mahasiswa <strong>{$nama}</strong> berhasil diperbarui!";
                    $pesan_type = "success";
                } else {
                    $pesan      = "Gagal memperbarui data: " . mysqli_stmt_error($stmt);
                    $pesan_type = "danger";
                }
                mysqli_stmt_close($stmt);
            }
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE mahasiswa SET nama_lengkap=?, nilai_akhir=?, nilai_huruf=?, predikat=? WHERE nim=?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sdsss", $nama, $nilai, $huruf, $predikat, $nim);
                if (mysqli_stmt_execute($stmt)) {
                    $pesan      = "Data mahasiswa <strong>{$nama}</strong> berhasil diperbarui!";
                    $pesan_type = "success";
                } else {
                    $pesan      = "Gagal memperbarui data: " . mysqli_stmt_error($stmt);
                    $pesan_type = "danger";
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// --- PROSES HAPUS ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['_action']) && $_POST['_action'] === 'hapus') {
    $nim = trim($_POST["nim"] ?? "");
    if ($nim !== "") {
        $stmt = mysqli_prepare($conn, "DELETE FROM mahasiswa WHERE nim=?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $nim);
            if (mysqli_stmt_execute($stmt)) {
                $pesan      = "Data mahasiswa berhasil dihapus.";
                $pesan_type = "success";
            } else {
                $pesan      = "Gagal menghapus data.";
                $pesan_type = "danger";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// =====================================================
// FITUR UTS: SEARCHING, SORTING, PAGING (SERVER-SIDE)
// =====================================================

// --- 1. SEARCHING: berdasarkan nama (WHERE LIKE) dan predikat (WHERE =) ---
$search_nama     = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_predikat = isset($_GET['predikat']) ? trim($_GET['predikat']) : 'all';

// --- 2. SORTING: berdasarkan nilai_akhir (ORDER BY) ---
$sort_order = isset($_GET['sort']) ? strtoupper(trim($_GET['sort'])) : '';
if ($sort_order !== 'ASC' && $sort_order !== 'DESC') {
    $sort_order = ''; // default: tidak ada sorting khusus
}

// --- 2b. SORTING: berdasarkan nama_lengkap (ORDER BY) ---
$sort_nama = isset($_GET['sort_nama']) ? strtoupper(trim($_GET['sort_nama'])) : '';
if ($sort_nama !== 'ASC' && $sort_nama !== 'DESC') {
    $sort_nama = ''; // default: tidak ada sorting nama
}

// --- 3. PAGING: kelipatan 5 (5, 10, 15) ---
$per_page_options = [5, 10, 15];
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 5;
if (!in_array($per_page, $per_page_options)) {
    $per_page = 5;
}
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// --- Build WHERE clause ---
$where_conditions = [];
$where_params     = [];
$where_types      = "";

if ($search_nama !== '') {
    $where_conditions[] = "nama_lengkap LIKE ?";
    $search_param       = "%" . $search_nama . "%";
    $where_params[]     = &$search_param;
    $where_types       .= "s";
}

if ($filter_predikat !== 'all' && $filter_predikat !== '') {
    // Opsi khusus: "Lulus" = A/B/C, "Tidak Lulus" = D/E
    if ($filter_predikat === 'Lulus') {
        $where_conditions[] = "predikat IN ('Sangat Memuaskan', 'Memuaskan', 'Cukup Memuaskan')";
    } elseif ($filter_predikat === 'Tidak Lulus') {
        $where_conditions[] = "predikat IN ('Kurang', 'Sangat Kurang')";
    } else {
        $where_conditions[] = "predikat = ?";
        $where_params[]     = &$filter_predikat;
        $where_types       .= "s";
    }
}

$where_sql = "";
if (count($where_conditions) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_conditions);
}

// --- Build ORDER BY clause ---
$order_sql = "ORDER BY nim ASC"; // default
if ($sort_nama !== '') {
    // Sorting berdasarkan Nama Lengkap, jika nama sama bedakan berdasarkan nilai_akhir
    $order_sql = "ORDER BY nama_lengkap {$sort_nama}, nilai_akhir DESC";
} elseif ($sort_order !== '') {
    $order_sql = "ORDER BY nilai_akhir {$sort_order}";
}

// --- Total data (dengan filter) ---
$count_sql = "SELECT COUNT(*) AS total FROM mahasiswa {$where_sql}";
$stmt_count = mysqli_prepare($conn, $count_sql);
if ($stmt_count) {
    if (count($where_params) > 0) {
        mysqli_stmt_bind_param($stmt_count, $where_types, ...$where_params);
    }
    mysqli_stmt_execute($stmt_count);
    $count_result = mysqli_stmt_get_result($stmt_count);
    $total = (int)mysqli_fetch_assoc($count_result)['total'];
    mysqli_stmt_close($stmt_count);
} else {
    $total = 0;
}

$total_pages = max(1, ceil($total / $per_page));
if ($page > $total_pages) $page = $total_pages;
$offset    = ($page - 1) * $per_page;
$start_num = $total > 0 ? $offset + 1 : 0;
$end_num   = min($offset + $per_page, $total);

// --- AMBIL DATA (dengan WHERE, ORDER BY, LIMIT OFFSET) ---
$data_sql = "SELECT * FROM mahasiswa {$where_sql} {$order_sql} LIMIT {$per_page} OFFSET {$offset}";
$stmt_data = mysqli_prepare($conn, $data_sql);
if ($stmt_data) {
    if (count($where_params) > 0) {
        mysqli_stmt_bind_param($stmt_data, $where_types, ...$where_params);
    }
    mysqli_stmt_execute($stmt_data);
    $result = mysqli_stmt_get_result($stmt_data);
    mysqli_stmt_close($stmt_data);
} else {
    $result = false;
}

// --- Helper: build query string untuk link paging/sort ---
function buildQuery($overrides = []) {
    $params = [
        'search'    => $_GET['search'] ?? '',
        'predikat'  => $_GET['predikat'] ?? 'all',
        'sort'      => $_GET['sort'] ?? '',
        'sort_nama' => $_GET['sort_nama'] ?? '',
        'per_page'  => $_GET['per_page'] ?? 5,
        'page'      => $_GET['page'] ?? 1,
    ];
    $params = array_merge($params, $overrides);
    return http_build_query($params);
}

// Data untuk modal edit (jika ada nim di GET)
$edit_data = null;
if (isset($_GET['edit_nim']) && $_GET['edit_nim'] !== '') {
    $edit_nim = trim($_GET['edit_nim']);
    $stmt_e   = mysqli_prepare($conn, "SELECT nim, nama_lengkap, nilai_akhir, foto FROM mahasiswa WHERE nim=?");
    if ($stmt_e) {
        mysqli_stmt_bind_param($stmt_e, "s", $edit_nim);
        mysqli_stmt_execute($stmt_e);
        $edit_result = mysqli_stmt_get_result($stmt_e);
        $edit_data   = mysqli_fetch_assoc($edit_result);
        mysqli_stmt_close($stmt_e);
    }
}

// Toggle sort Nilai Akhir
$next_sort = 'ASC';
$sort_icon  = 'bi-arrow-down-up';
$sort_label = 'Urutkan';
if ($sort_order === 'ASC') {
    $next_sort = 'DESC';
    $sort_icon  = 'bi-sort-numeric-up';
    $sort_label = 'Rendah → Tinggi';
} elseif ($sort_order === 'DESC') {
    $next_sort = '';
    $sort_icon  = 'bi-sort-numeric-down-alt';
    $sort_label = 'Tinggi → Rendah';
}

// Toggle sort Nama Lengkap
$next_sort_nama = 'ASC';
$sort_nama_icon  = 'bi-arrow-down-up';
$sort_nama_label = 'Urutkan';
if ($sort_nama === 'ASC') {
    $next_sort_nama = 'DESC';
    $sort_nama_icon  = 'bi-sort-alpha-up';
    $sort_nama_label = 'A → Z';
} elseif ($sort_nama === 'DESC') {
    $next_sort_nama = '';
    $sort_nama_icon  = 'bi-sort-alpha-down-alt';
    $sort_nama_label = 'Z → A';
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Rekap Nilai Mahasiswa — UTS Web</title>

    <!--begin::Theme Init-->
    <script>
        (() => {
            'use strict';
            const root = document.documentElement;
            if (root.getAttribute('data-lte-color-mode') === 'off') return;
            const STORAGE_KEY = 'lte-theme';
            let stored = null;
            try { stored = localStorage.getItem(STORAGE_KEY); } catch {}
            const authored = root.getAttribute('data-bs-theme');
            let resolved = 'light';
            if (stored === 'dark' || stored === 'light') resolved = stored;
            else if (authored === 'dark' || authored === 'light') resolved = authored;
            else if (globalThis.matchMedia('(prefers-color-scheme: dark)').matches) resolved = 'dark';
            root.setAttribute('data-bs-theme', resolved);
            root.style.colorScheme = resolved;
            if (resolved !== authored) root.setAttribute('data-lte-theme-resolved', '');
        })();
    </script>
    <!--end::Theme Init-->

    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="description" content="Rekap Nilai Mahasiswa - UTS Pemrograman Web" />

    <!--begin::Fonts-->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
        integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous"
        media="print" onload="this.media='all'" />
    <!--end::Fonts-->

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
        crossorigin="anonymous" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
        crossorigin="anonymous" />
    <link rel="preload" href="./css/css-admin.css" as="style" />
    <link rel="stylesheet" href="./css/css-admin.css" />

    <style>
        /* Foto profil di tabel */
        .foto-profil {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(102, 126, 234, 0.3);
            flex-shrink: 0;
        }
        .avatar-placeholder {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            flex-shrink: 0;
        }
        /* Sortable header */
        .sortable-header {
            cursor: pointer;
            user-select: none;
            transition: background-color 0.15s;
        }
        .sortable-header:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.08);
        }
        .sort-icon {
            font-size: 0.75rem;
            opacity: 0.5;
            margin-left: 4px;
            transition: opacity 0.15s;
        }
        .sortable-header:hover .sort-icon,
        .sort-active .sort-icon {
            opacity: 1;
        }
        .sort-active {
            color: var(--bs-primary);
        }
        /* Badge predikat */
        .badge-predikat {
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 20px;
        }
        /* Preview foto di modal */
        .foto-preview-container {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px dashed var(--bs-border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            transition: border-color 0.2s;
        }
        .foto-preview-container:hover {
            border-color: var(--bs-primary);
        }
        .foto-preview-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        /* Per page selector active */
        .per-page-btn.active {
            background-color: var(--bs-primary);
            color: #fff;
            border-color: var(--bs-primary);
        }
    </style>
</head>

<!-- Tanpa sidebar: hapus class layout-fixed sidebar-expand-lg, pakai sidebar-collapse -->
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary sidebar-collapse">
    <div class="app-wrapper">


        <!--begin::App Main (tanpa sidebar)-->
        <main class="app-main">
            <!--begin::App Content Header-->
            <div class="app-content-header">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <h1 class="mb-0 fs-3">Rekap Nilai Mahasiswa</h1>
                        </div>
                        <div class="col-sm-6">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb float-sm-end">
                                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Rekap Nilai Mahasiswa</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::App Content Header-->

            <!--begin::App Content-->
            <div class="app-content">
                <div class="container-fluid">

                    <?php if ($pesan !== ""): ?>
                    <!--begin::Alert-->
                    <div class="alert alert-<?= $pesan_type ?> alert-dismissible fade show" role="alert">
                        <i class="bi bi-<?= $pesan_type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?> me-2"></i>
                        <?= $pesan ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <!--end::Alert-->
                    <?php endif; ?>



                    <!--begin::Row-->
                    <div class="row">
                        <div class="col-12">
                            <!--begin::Card-->
                            <div class="card mb-4">
                                <!--begin::Card Header-->
                                <div class="card-header">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-12 col-md-3">
                                            <h3 class="card-title">
                                                <i class="bi bi-journal-text me-2"></i>
                                                Rekap Nilai Mahasiswa
                                            </h3>
                                        </div>
                                        <div class="col-12 col-md-9">
                                            <form method="GET" action="rekap_nilai.php" id="form-filter">
                                                <!-- Simpan per_page dan sort saat search -->
                                                <input type="hidden" name="per_page" value="<?= $per_page ?>">
                                                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort_order) ?>">
                                                <input type="hidden" name="sort_nama" value="<?= htmlspecialchars($sort_nama) ?>">

                                                <div class="d-flex flex-wrap justify-content-md-end gap-2">
                                                    <!-- FITUR 1: Searching berdasarkan Nama (input text, WHERE LIKE) -->
                                                    <div class="input-group input-group-sm w-auto">
                                                        <span class="input-group-text">
                                                            <i class="bi bi-search" aria-hidden="true"></i>
                                                        </span>
                                                        <input type="search" name="search" id="nilai-search" class="form-control"
                                                            placeholder="Cari berdasarkan nama…" aria-label="Cari berdasarkan nama"
                                                            value="<?= htmlspecialchars($search_nama) ?>"
                                                            style="width: 200px" />
                                                    </div>

                                                    <!-- FITUR 2: Searching berdasarkan Predikat (dropdown pilihan, WHERE =) -->
                                                    <select name="predikat" id="nilai-predikat-filter"
                                                        class="form-select form-select-sm w-auto"
                                                        aria-label="Filter berdasarkan predikat"
                                                        onchange="document.getElementById('form-filter').submit()">
                                                        <option value="all" <?= $filter_predikat === 'all' ? 'selected' : '' ?>>Semua Predikat</option>
                                                        <optgroup label="── Status Kelulusan ──">
                                                            <option value="Lulus" <?= $filter_predikat === 'Lulus' ? 'selected' : '' ?>>✅ Lulus (A/B/C)</option>
                                                            <option value="Tidak Lulus" <?= $filter_predikat === 'Tidak Lulus' ? 'selected' : '' ?>>❌ Tidak Lulus (D/E)</option>
                                                        </optgroup>
                                                        <optgroup label="── Detail Predikat ──">
                                                            <option value="Sangat Memuaskan" <?= $filter_predikat === 'Sangat Memuaskan' ? 'selected' : '' ?>>Sangat Memuaskan (A)</option>
                                                            <option value="Memuaskan" <?= $filter_predikat === 'Memuaskan' ? 'selected' : '' ?>>Memuaskan (B)</option>
                                                            <option value="Cukup Memuaskan" <?= $filter_predikat === 'Cukup Memuaskan' ? 'selected' : '' ?>>Cukup Memuaskan (C)</option>
                                                            <option value="Kurang" <?= $filter_predikat === 'Kurang' ? 'selected' : '' ?>>Kurang (D)</option>
                                                            <option value="Sangat Kurang" <?= $filter_predikat === 'Sangat Kurang' ? 'selected' : '' ?>>Sangat Kurang (E)</option>
                                                        </optgroup>
                                                    </select>

                                                    <!-- Tombol Search -->
                                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-funnel me-1"></i> Cari
                                                    </button>

                                                    <!-- Reset Filter -->
                                                    <?php if ($search_nama !== '' || $filter_predikat !== 'all'): ?>
                                                    <a href="rekap_nilai.php?per_page=<?= $per_page ?>" class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi bi-x-circle me-1"></i> Reset
                                                    </a>
                                                    <?php endif; ?>

                                                    <!--begin::Tombol Tambah (buka modal)-->
                                                    <button type="button" class="btn btn-sm btn-primary"
                                                        data-bs-toggle="modal" data-bs-target="#modal-tambah">
                                                        <i class="bi bi-person-plus-fill me-1" aria-hidden="true"></i>
                                                        Tambah Data
                                                    </button>
                                                    <!--end::Tombol Tambah-->
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Card Header-->

                                <!--begin::Card Body-->
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle m-0" id="tabel-nilai">
                                            <thead>
                                                <tr>
                                                    <th>NIM</th>
                                                    <!-- FITUR 3b: SORTING berdasarkan Nama Lengkap (ORDER BY) -->
                                                    <th class="sortable-header <?= $sort_nama !== '' ? 'sort-active' : '' ?>"
                                                        onclick="window.location.href='rekap_nilai.php?<?= buildQuery(['sort_nama' => ($next_sort_nama !== '' ? $next_sort_nama : ''), 'sort' => '', 'page' => 1]) ?>'"
                                                        title="Klik untuk mengurutkan berdasarkan Nama Lengkap">
                                                        Nama Lengkap
                                                        <i class="bi <?= $sort_nama_icon ?> sort-icon"></i>
                                                        <?php if ($sort_nama !== ''): ?>
                                                        <br><small class="text-muted fw-normal" style="font-size:0.65rem">(<?= $sort_nama_label ?>)</small>
                                                        <?php endif; ?>
                                                    </th>
                                                    <!-- FITUR 3: SORTING berdasarkan Nilai Akhir (ORDER BY) -->
                                                    <th class="text-center sortable-header <?= $sort_order !== '' ? 'sort-active' : '' ?>"
                                                        onclick="window.location.href='rekap_nilai.php?<?= buildQuery(['sort' => ($next_sort !== '' ? $next_sort : ''), 'sort_nama' => '', 'page' => 1]) ?>'"
                                                        title="Klik untuk mengurutkan berdasarkan Nilai Akhir">
                                                        Nilai Akhir
                                                        <i class="bi <?= $sort_icon ?> sort-icon"></i>
                                                        <?php if ($sort_order !== ''): ?>
                                                        <br><small class="text-muted fw-normal" style="font-size:0.65rem">(<?= $sort_label ?>)</small>
                                                        <?php endif; ?>
                                                    </th>
                                                    <th class="text-center">Nilai Huruf</th>
                                                    <th class="text-center">Predikat</th>
                                                    <th class="text-center">Tanggal Dibuat</th>
                                                    <th class="text-end">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tabel-body">
                                                <?php
                                                if ($result && mysqli_num_rows($result) > 0) {
                                                    while ($row = mysqli_fetch_assoc($result)) {
                                                        $nama        = $row['nama_lengkap'] ?? '';
                                                        $nilai_akhir = $row['nilai_akhir'] ?? '';
                                                        $nilai_huruf = $row['nilai_huruf'] ?? '';
                                                        $predikat    = $row['predikat'] ?? '';
                                                        $nim_raw     = $row['nim'] ?? '';
                                                        $nim_enc     = $nim_raw;
                                                        $initial     = strtoupper(substr($row['nama_lengkap'] ?? 'M', 0, 1));
                                                        $tanggal     = isset($row['created_at']) ? date('d M Y, H:i', strtotime($row['created_at'])) : '-';
                                                        $foto        = $row['foto'] ?? '';

                                                        // Badge color berdasarkan predikat
                                                        $badge_class = 'bg-secondary';
                                                        if ($predikat === 'Sangat Memuaskan') $badge_class = 'bg-success';
                                                        elseif ($predikat === 'Memuaskan') $badge_class = 'bg-primary';
                                                        elseif ($predikat === 'Cukup Memuaskan') $badge_class = 'bg-info';
                                                        elseif ($predikat === 'Kurang') $badge_class = 'bg-warning text-dark';
                                                        elseif ($predikat === 'Sangat Kurang') $badge_class = 'bg-danger';

                                                        // Foto HTML
                                                        $foto_html = "";
                                                        if ($foto && file_exists($foto)) {
                                                            $foto_html = "<img src=\"" . htmlspecialchars($foto) . "\" class=\"foto-profil\" alt=\"Foto {$nama}\">";
                                                        } else {
                                                            $foto_html = "<div class=\"avatar-placeholder\">{$initial}</div>";
                                                        }

                                                        echo "
                                                <tr>
                                                    <td class=\"fw-semibold\">{$nim_enc}</td>
                                                    <td>
                                                        <div class=\"d-flex align-items-center gap-2\">
                                                            {$foto_html}
                                                            <span class=\"fw-medium\">{$nama}</span>
                                                        </div>
                                                    </td>
                                                    <td class=\"text-center\">
                                                        <span class=\"fw-bold fs-6\">{$nilai_akhir}</span>
                                                    </td>
                                                    <td class=\"text-center\">{$nilai_huruf}</td>
                                                    <td class=\"text-center\">
                                                        <span class=\"badge badge-predikat {$badge_class}\">{$predikat}</span>
                                                    </td>
                                                    <td class=\"text-center text-body-secondary\">
                                                        <small>{$tanggal}</small>
                                                    </td>
                                                    <td class=\"text-end\">
                                                        <div class=\"btn-group btn-group-sm\">
                                                            <button type=\"button\"
                                                                class=\"btn btn-outline-secondary btn-edit\"
                                                                data-nim=\"{$nim_enc}\"
                                                                data-nama=\"{$nama}\"
                                                                data-nilai=\"{$nilai_akhir}\"
                                                                data-foto=\"" . htmlspecialchars($foto) . "\"
                                                                data-bs-toggle=\"modal\"
                                                                data-bs-target=\"#modal-edit\"
                                                                aria-label=\"Edit {$nama}\">
                                                                <i class=\"bi bi-pencil\" aria-hidden=\"true\"></i>
                                                            </button>
                                                            <button type=\"button\"
                                                                class=\"btn btn-outline-danger btn-hapus\"
                                                                data-nim=\"{$nim_enc}\"
                                                                data-nama=\"{$nama}\"
                                                                data-bs-toggle=\"modal\"
                                                                data-bs-target=\"#modal-hapus\"
                                                                aria-label=\"Hapus {$nama}\">
                                                                <i class=\"bi bi-trash\" aria-hidden=\"true\"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>";
                                                    }
                                                } else {
                                                    echo "
                                                <tr>
                                                    <td colspan=\"7\" class=\"text-center py-5 text-body-secondary\">
                                                        <i class=\"bi bi-inbox fs-1 d-block mb-2\"></i>
                                                        Belum ada data mahasiswa.
                                                    </td>
                                                </tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!--end::Card Body-->

                                <!--begin::Card Footer (PAGING) -->
                                <div class="card-footer">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">

                                        <!-- Kiri: Info jumlah data + pilihan per page -->
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="fs-7 text-body-secondary">
                                                Menampilkan <strong><?= $start_num ?></strong> sampai <strong><?= $end_num ?></strong> dari <strong><?= $total ?></strong> data
                                            </div>

                                            <!-- FITUR 4: PAGING - Pilihan jumlah data per halaman (5, 10, 15) -->
                                            <div class="d-flex align-items-center gap-1">
                                                <span class="text-body-secondary fs-7 me-1">Tampilkan:</span>
                                                <?php foreach ($per_page_options as $opt): ?>
                                                <a href="rekap_nilai.php?<?= buildQuery(['per_page' => $opt, 'page' => 1]) ?>"
                                                   class="btn btn-sm btn-outline-secondary per-page-btn <?= $per_page === $opt ? 'active' : '' ?>"
                                                   style="min-width:36px">
                                                    <?= $opt ?>
                                                </a>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>

                                        <!-- Kanan: Navigasi halaman -->
                                        <?php if ($total_pages > 1): ?>
                                        <nav aria-label="Navigasi halaman">
                                            <ul class="pagination pagination-sm m-0">
                                                <!-- Tombol First -->
                                                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                                    <a class="page-link" href="rekap_nilai.php?<?= buildQuery(['page' => 1]) ?>" aria-label="Pertama">
                                                        <i class="bi bi-chevron-double-left"></i>
                                                    </a>
                                                </li>
                                                <!-- Tombol Previous -->
                                                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                                    <a class="page-link" href="rekap_nilai.php?<?= buildQuery(['page' => $page - 1]) ?>" aria-label="Sebelumnya">
                                                        <i class="bi bi-chevron-left"></i>
                                                    </a>
                                                </li>
                                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                                    <a class="page-link" href="rekap_nilai.php?<?= buildQuery(['page' => $i]) ?>"><?= $i ?></a>
                                                </li>
                                                <?php endfor; ?>
                                                <!-- Tombol Next -->
                                                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                                    <a class="page-link" href="rekap_nilai.php?<?= buildQuery(['page' => $page + 1]) ?>" aria-label="Selanjutnya">
                                                        <i class="bi bi-chevron-right"></i>
                                                    </a>
                                                </li>
                                                <!-- Tombol Last -->
                                                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                                    <a class="page-link" href="rekap_nilai.php?<?= buildQuery(['page' => $total_pages]) ?>" aria-label="Terakhir">
                                                        <i class="bi bi-chevron-double-right"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </nav>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <!--end::Card Footer-->
                            </div>
                            <!--end::Card-->
                        </div>
                    </div>
                    <!--end::Row-->

                </div>
            </div>
            <!--end::App Content-->
        </main>
        <!--end::App Main-->

        <!--begin::Footer-->
        <footer class="app-footer">
            <div class="float-end d-none d-sm-inline">Pemrograman Web</div>
            <strong>
                Hak Cipta &copy; <?= date('Y') ?> pada Prodi TI.
            </strong>
            <a href="https://adminlte.io" target="_blank" rel="noopener noreferrer">AdminLTE.io</a>
            &mdash; Seluruh hak cipta dilindungi.
        </footer>
        <!--end::Footer-->
    </div>
    <!--end::App Wrapper-->

    <!-- ================================================================ -->
    <!--  MODAL: TAMBAH MAHASISWA (dengan upload foto)                    -->
    <!-- ================================================================ -->
    <div class="modal fade" id="modal-tambah" tabindex="-1" aria-labelledby="modal-tambah-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="rekap_nilai.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="_action" value="tambah">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-tambah-label">
                            <i class="bi bi-person-plus-fill me-2 text-primary"></i>Tambah Data Nilai
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Preview Foto -->
                        <div class="text-center mb-3">
                            <div class="foto-preview-container" id="tambah-foto-preview-container">
                                <i class="bi bi-camera fs-3 text-body-secondary" id="tambah-foto-icon"></i>
                                <img src="" id="tambah-foto-preview" style="display:none" alt="Preview foto">
                            </div>
                            <label for="tambah-foto" class="btn btn-sm btn-outline-secondary mt-1">
                                <i class="bi bi-upload me-1"></i> Upload Foto
                            </label>
                            <input type="file" class="d-none" id="tambah-foto" name="foto" accept="image/*" />
                        </div>

                        <div class="mb-3">
                            <label for="tambah-nim" class="form-label fw-semibold">NIM</label>
                            <input type="text" class="form-control" id="tambah-nim" name="nim"
                                placeholder="Masukkan NIM" required />
                        </div>
                        <div class="mb-3">
                            <label for="tambah-nama" class="form-label fw-semibold">Nama Lengkap</label>
                            <input type="text" class="form-control" id="tambah-nama" name="nama_lengkap"
                                placeholder="Masukkan nama lengkap" required />
                        </div>
                        <div class="mb-3">
                            <label for="tambah-nilai" class="form-label fw-semibold">Nilai Akhir</label>
                            <input type="number" class="form-control" id="tambah-nilai" name="nilai_akhir"
                                placeholder="0 – 100" step="0.01" min="0" max="100" required />
                            <div class="form-text">Nilai huruf &amp; predikat akan dihitung otomatis.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ================================================================ -->
    <!--  MODAL: EDIT MAHASISWA (dengan upload foto)                      -->
    <!-- ================================================================ -->
    <div class="modal fade" id="modal-edit" tabindex="-1" aria-labelledby="modal-edit-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="rekap_nilai.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="_action" value="edit">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-edit-label">
                            <i class="bi bi-pencil-square me-2 text-warning"></i>Edit Nilai Mahasiswa
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Preview Foto -->
                        <div class="text-center mb-3">
                            <div class="foto-preview-container" id="edit-foto-preview-container">
                                <i class="bi bi-camera fs-3 text-body-secondary" id="edit-foto-icon"></i>
                                <img src="" id="edit-foto-preview" style="display:none" alt="Preview foto">
                            </div>
                            <label for="edit-foto" class="btn btn-sm btn-outline-secondary mt-1">
                                <i class="bi bi-upload me-1"></i> Ganti Foto
                            </label>
                            <input type="file" class="d-none" id="edit-foto" name="foto" accept="image/*" />
                        </div>

                        <div class="mb-3">
                            <label for="edit-nim" class="form-label fw-semibold">NIM</label>
                            <input type="text" class="form-control bg-body-secondary" id="edit-nim" name="nim"
                                readonly />
                        </div>
                        <div class="mb-3">
                            <label for="edit-nama" class="form-label fw-semibold">Nama Lengkap</label>
                            <input type="text" class="form-control" id="edit-nama" name="nama_lengkap"
                                placeholder="Masukkan nama lengkap" required />
                        </div>
                        <div class="mb-3">
                            <label for="edit-nilai" class="form-label fw-semibold">Nilai Akhir</label>
                            <input type="number" class="form-control" id="edit-nilai" name="nilai_akhir"
                                placeholder="0 – 100" step="0.01" min="0" max="100" required />
                            <div class="form-text">Nilai huruf &amp; predikat akan dihitung ulang otomatis.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning text-white">
                            <i class="bi bi-arrow-repeat me-1"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ================================================================ -->
    <!--  MODAL: HAPUS MAHASISWA                                           -->
    <!-- ================================================================ -->
    <div class="modal fade" id="modal-hapus" tabindex="-1" aria-labelledby="modal-hapus-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="rekap_nilai.php" method="POST">
                    <input type="hidden" name="_action" value="hapus">
                    <input type="hidden" name="nim" id="hapus-nim" value="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-hapus-label">
                            <i class="bi bi-trash-fill me-2 text-danger"></i>Hapus Data Mahasiswa
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">
                            Apakah Anda yakin ingin menghapus data mahasiswa
                            <strong id="hapus-nama-label"></strong>?
                            <br><span class="text-danger small">Tindakan ini tidak dapat dibatalkan.</span>
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i> Ya, Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!--begin::Scripts-->
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"
        crossorigin="anonymous"></script>
    <script src="./js/js-admin.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // ── Preview foto upload (Tambah) ──────────────────────────
            const tambahFotoInput = document.getElementById('tambah-foto');
            const tambahFotoPreview = document.getElementById('tambah-foto-preview');
            const tambahFotoIcon = document.getElementById('tambah-foto-icon');

            if (tambahFotoInput) {
                tambahFotoInput.addEventListener('change', function () {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            tambahFotoPreview.src = e.target.result;
                            tambahFotoPreview.style.display = 'block';
                            if (tambahFotoIcon) tambahFotoIcon.style.display = 'none';
                        };
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }

            // ── Preview foto upload (Edit) ────────────────────────────
            const editFotoInput = document.getElementById('edit-foto');
            const editFotoPreview = document.getElementById('edit-foto-preview');
            const editFotoIcon = document.getElementById('edit-foto-icon');

            if (editFotoInput) {
                editFotoInput.addEventListener('change', function () {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            editFotoPreview.src = e.target.result;
                            editFotoPreview.style.display = 'block';
                            if (editFotoIcon) editFotoIcon.style.display = 'none';
                        };
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }

            // ── Modal Edit: isi field dari data-* tombol ──────────────────
            const modalEdit = document.getElementById('modal-edit');
            if (modalEdit) {
                modalEdit.addEventListener('show.bs.modal', function (event) {
                    const btn   = event.relatedTarget;
                    if (!btn) return;
                    document.getElementById('edit-nim').value   = btn.getAttribute('data-nim')   || '';
                    document.getElementById('edit-nama').value  = btn.getAttribute('data-nama')  || '';
                    document.getElementById('edit-nilai').value = btn.getAttribute('data-nilai') || '';

                    // Tampilkan foto yang sudah ada
                    const fotoSrc = btn.getAttribute('data-foto') || '';
                    if (fotoSrc && fotoSrc !== '') {
                        editFotoPreview.src = fotoSrc;
                        editFotoPreview.style.display = 'block';
                        if (editFotoIcon) editFotoIcon.style.display = 'none';
                    } else {
                        editFotoPreview.style.display = 'none';
                        editFotoPreview.src = '';
                        if (editFotoIcon) editFotoIcon.style.display = '';
                    }
                });
            }

            // ── Modal Hapus: isi nim & nama dari data-* tombol ───────────
            const modalHapus = document.getElementById('modal-hapus');
            if (modalHapus) {
                modalHapus.addEventListener('show.bs.modal', function (event) {
                    const btn = event.relatedTarget;
                    if (!btn) return;
                    document.getElementById('hapus-nim').value        = btn.getAttribute('data-nim')  || '';
                    document.getElementById('hapus-nama-label').textContent = btn.getAttribute('data-nama') || '';
                });
            }

            // ── Reset modal tambah saat ditutup ───────────────────────
            const modalTambah = document.getElementById('modal-tambah');
            if (modalTambah) {
                modalTambah.addEventListener('hidden.bs.modal', function () {
                    tambahFotoPreview.style.display = 'none';
                    tambahFotoPreview.src = '';
                    if (tambahFotoIcon) tambahFotoIcon.style.display = '';
                    tambahFotoInput.value = '';
                });
            }

        });
    </script>
    <!--end::Scripts-->

</body>
</html>
<?php
mysqli_close($conn);
?>
