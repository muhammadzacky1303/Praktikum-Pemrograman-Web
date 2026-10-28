<?php

// ======================================================
// 1. VARIABEL AWAL
// ======================================================

// Menyimpan nama mahasiswa
$nama_mhsw = "";

// Menyimpan nilai akhir
$nilai_akhir = "";

// Menyimpan pesan error untuk nama
$errName = "";

// Menyimpan pesan error untuk nilai
$errNilai = "";


// ======================================================
// 2. CEK APAKAH FORM SUDAH DIKIRIM
// ======================================================

// isset() digunakan untuk mengecek apakah data tersebut
// sudah dikirim melalui form.

// Jika nama_mhsw ATAU nilai_akhir sudah ada,
// berarti tombol Kirim sudah ditekan.
$submitted = isset($_POST["nama_mhsw"]) || isset($_POST["nilai_akhir"]);


// ======================================================
// 3. PROSES DATA SETELAH FORM DIKIRIM
// ======================================================

if ($submitted) {

    // --------------------------------------------------
    // Mengambil data dari form
    // --------------------------------------------------

    // Mengambil nilai dari input nama_mhsw
    // ?? "" artinya jika datanya tidak ada,
    // maka gunakan string kosong.
    $nama_mhsw = $_POST["nama_mhsw"] ?? "";

    // Mengambil nilai dari input nilai_akhir
    $nilai_akhir = $_POST["nilai_akhir"] ?? "";


    // --------------------------------------------------
    // 3.1 VALIDASI NAMA MAHASISWA
    // --------------------------------------------------

    // trim() digunakan untuk menghilangkan spasi
    // di awal dan akhir teks.

    // Jika nama kosong...
    if (trim($nama_mhsw) == "") {

        // Simpan pesan error
        $errName = "Nama Mahasiswa tidak boleh kosong";

    } else {

        // Jika nama tidak kosong,
        // lakukan sanitasi terhadap data.
        $nama_mhsw = sanitasi($nama_mhsw);

    }


    // --------------------------------------------------
    // 3.2 VALIDASI NILAI AKHIR
    // --------------------------------------------------

    // Jika nilai kosong...
    if (trim($nilai_akhir) == "") {

        // Simpan pesan error
        $errNilai = "Nilai Akhir tidak boleh kosong";

    } else {

        // Jika nilai tidak kosong,
        // lakukan sanitasi terhadap data.
        $nilai_akhir = sanitasi($nilai_akhir);

    }


    // ==================================================
    // 4. VALIDASI FORMAT NAMA
    // ==================================================

    // preg_match() digunakan untuk mencari pola tertentu.

    // Pola:
    // [^a-zA-Z\s]
    //
    // Artinya mencari karakter yang BUKAN:
    // a-z     = huruf kecil
    // A-Z     = huruf besar
    // \s      = spasi

    // Jadi kalau nama mengandung angka atau simbol,
    // maka akan dianggap salah.

    // $errName == ""
    // artinya sebelumnya belum ada error nama.

    if ($errName == "" && preg_match("/[^a-zA-Z\s]/", $nama_mhsw)) {

        // Jika ditemukan angka atau simbol,
        // tampilkan pesan error.
        $errName = "Nama Mahasiswa hanya boleh berisi huruf dan spasi";

    }


    // ==================================================
    // 5. VALIDASI FORMAT NILAI
    // ==================================================

    // Nilai boleh:
    // 90
    // 85
    // 90.5
    // 75.25
    //
    // Nilai TIDAK boleh:
    // 90,5
    // 90abc
    // abc
    //
    // Pola:
    // ^              = mulai dari awal
    // [0-9]+         = satu atau lebih angka
    // (\.[0-9]+)?    = boleh ada titik lalu angka
    // $              = sampai akhir

    if (
        $errNilai == "" &&
        !preg_match("/^[0-9]+(\.[0-9]+)?$/", $nilai_akhir)
    ) {

        // Jika format nilai salah,
        // tampilkan pesan error.
        $errNilai = "Nilai Akhir harus berupa angka";

    }


    // ==================================================
    // 6. VALIDASI RANGE NILAI
    // ==================================================

    // Nilai harus berada di antara 0 sampai 100.

    // Jika nilai kurang dari 0
    // ATAU nilai lebih dari 100,
    // maka dianggap salah.

    if (
        $errNilai == "" &&
        ($nilai_akhir < 0 || $nilai_akhir > 100)
    ) {

        // Tampilkan pesan error
        $errNilai = "Nilai Akhir harus antara 0 sampai 100";

    }

}


// ======================================================
// 7. FUNGSI SANITASI DATA
// ======================================================

// Fungsi ini digunakan untuk membersihkan data
// sebelum digunakan atau ditampilkan.

function sanitasi(string $data)
{

    // Menghapus spasi di awal dan akhir
    $data = trim($data);

    // Menghapus backslash (\)
    $data = stripslashes($data);

    // Mengubah karakter khusus HTML
    // agar lebih aman ketika ditampilkan.
    $data = htmlspecialchars($data);

    // Mengembalikan data yang sudah dibersihkan
    return $data;
}


// ======================================================
// 8. FUNGSI KONVERSI NILAI KE HURUF
// ======================================================

// Fungsi ini menerima nilai berupa float
// dan mengembalikan hasil berupa string.

function konversi_huruf(float $nilai): string
{

    // Jika nilai kurang dari 0
    // atau lebih dari 100,
    // maka nilai dianggap tidak valid.
    if ($nilai < 0 || $nilai > 100) {

        return "Invalid";

    }

    // Nilai 0 sampai 34.50 = E
    elseif ($nilai <= 34.50) {

        return "E";

    }

    // Nilai 34.51 sampai 50.50 = D
    elseif ($nilai <= 50.50) {

        return "D";

    }

    // Nilai 50.51 sampai 65.50 = C
    elseif ($nilai <= 65.50) {

        return "C";

    }

    // Nilai 65.51 sampai 80.50 = B
    elseif ($nilai <= 80.50) {

        return "B";

    }

    // Nilai di atas 80.50 sampai 100 = A
    else {

        return "A";

    }
}


// ======================================================
// 9. FUNGSI KONVERSI NILAI KE PREDIKAT
// ======================================================

// Fungsi ini mengubah nilai menjadi predikat.

function konversi_predikat(float $nilai): string
{

    // Mengecek apakah nilai berada di luar 0-100.
    if ($nilai < 0 || $nilai > 100) {

        return "Invalid Value";

    }

    // Nilai 0 sampai 34.50
    elseif ($nilai <= 34.50) {

        return "Tidak Lulus";

    }

    // Nilai 34.51 sampai 50.50
    elseif ($nilai <= 50.50) {

        return "Kurang Memuaskan";

    }

    // Nilai 50.51 sampai 65.50
    elseif ($nilai <= 65.50) {

        return "Cukup Memuaskan";

    }

    // Nilai 65.51 sampai 80.50
    elseif ($nilai <= 80.50) {

        return "Memuaskan";

    }

    // Nilai di atas 80.50
    else {

        return "Sangat Memuaskan";

    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <!-- Mengatur karakter yang digunakan -->
    <meta charset="UTF-8">

    <!-- Agar tampilan responsive di HP/laptop -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Judul halaman -->
    <title>Form Nilai Baru</title>


    <style>

        /* Warna untuk pesan error */
        .error {
            color: red;
        }

        /* Warna untuk tanda wajib (*) */
        .wajib {
            color: red;
        }

    </style>

</head>


<body>


<!-- ================================================== -->
<!-- 10. FORM INPUT DATA -->
<!-- ================================================== -->

<form
    action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
    method="post"
>


    <!-- ============================================== -->
    <!-- INPUT NAMA MAHASISWA -->
    <!-- ============================================== -->

    <label for="nama">

        Nama Mahasiswa:

        <!-- Tanda bahwa field wajib diisi -->
        <span class="wajib">*</span>

    </label>

    <br>


    <!-- Input nama mahasiswa -->
    <input
        type="text"
        name="nama_mhsw"
        value="<?php echo htmlspecialchars($nama_mhsw); ?>"
    >


    <?php

    // Error hanya ditampilkan jika form sudah dikirim
    // dan terdapat error pada nama.

    if ($submitted && $errName != "") {

        echo '<span class="error">';

        // Menampilkan pesan error nama
        echo $errName;

        echo '</span>';

    }

    ?>


    <br><br>


    <!-- ============================================== -->
    <!-- INPUT NILAI AKHIR -->
    <!-- ============================================== -->

    <label for="nilai_akhir">

        Nilai Akhir:

        <!-- Tanda bahwa field wajib diisi -->
        <span class="wajib">*</span>

    </label>

    <br>


    <!-- Input nilai akhir -->
    <input
        type="text"
        name="nilai_akhir"
        value="<?php echo htmlspecialchars($nilai_akhir); ?>"
    >


    <?php

    // Error hanya ditampilkan jika form sudah dikirim
    // dan terdapat error pada nilai.

    if ($submitted && $errNilai != "") {

        echo '<span class="error">';

        // Menampilkan pesan error nilai
        echo $errNilai;

        echo '</span>';

    }

    ?>


    <br>


    <!-- Keterangan field wajib diisi -->
    <span class="wajib">

        * Wajib diisi

    </span>


    <br><br>


    <!-- Tombol untuk mengirim form -->
    <input
        type="submit"
        value="Kirim"
    >


</form>


<?php

// ======================================================
// 11. MENAMPILKAN HASIL
// ======================================================

// Hasil hanya ditampilkan jika:
//
// $submitted       = form sudah dikirim
// $errName == ""   = tidak ada error nama
// $errNilai == ""  = tidak ada error nilai

if (
    $submitted &&
    $errName == "" &&
    $errNilai == ""
) {

    // Judul hasil
    echo "<h3>Data Nilai Mahasiswa</h3>";

    // Menampilkan nama
    echo "Nama Mahasiswa: " . $nama_mhsw . "<br>";

    // Menampilkan nilai
    echo "Nilai Akhir: " . $nilai_akhir . "<br>";

    // Mengubah nilai menjadi huruf
    // (float) digunakan untuk mengubah nilai menjadi angka desimal.
    echo "Nilai Huruf: " . konversi_huruf((float)$nilai_akhir) . "<br>";

    // Mengubah nilai menjadi predikat
    echo "Predikat: " . konversi_predikat((float)$nilai_akhir);

}

?>


</body>

</html>