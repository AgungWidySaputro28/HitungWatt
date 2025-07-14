<?php
// Sertakan file koneksi
require_once "koneksi.php";

// Inisialisasi variabel untuk hasil
$hasil_biaya = null;

// Cek jika form telah disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Ambil data dari form (input pengguna)
    $nama_alat = $_POST['nama_alat'];
    $daya_watt = (int)$_POST['daya_watt'];
    $lama_pakai = (float)$_POST['lama_pakai'];
    $jumlah_hari = (int)$_POST['jumlah_hari'];
    $tarif = (float)$_POST['tarif'];

    // 2. Lakukan Perhitungan
    $daya_kw = $daya_watt / 1000;
    $energi_harian_kwh = $daya_kw * $lama_pakai;
    $energi_bulanan_kwh = $energi_harian_kwh * $jumlah_hari;
    $hasil_biaya = $energi_bulanan_kwh * $tarif;

    // 3. Simpan data ke Database menggunakan Prepared Statement (lebih aman)
    $sql = "INSERT INTO tb_perhitungan (nama_alat, daya_watt, lama_pakai_jam, jumlah_hari, tarif_per_kwh, total_biaya) VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "siiddd", $nama_alat, $daya_watt, $lama_pakai, $jumlah_hari, $tarif, $hasil_biaya);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// 4. Ambil data riwayat dari database untuk ditampilkan
$riwayat_sql = "SELECT * FROM tb_perhitungan ORDER BY tanggal_hitung DESC LIMIT 10";
$result = mysqli_query($koneksi, $riwayat_sql);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HitungWatt</title>
    <style>
        h1 span.blinking {
    animation: blinker 1s linear infinite;
}
@keyframes blinker {
    10% { opacity: 0.7; }
}
        body {
    font-family: sans-serif;
    background-image: url('lamp.jpg');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    margin: 0;
    padding: 20px;
}
       .container {
    background-color: rgba(226, 223, 163, 0.4);
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
}
        h1, h2 { text-align: center; color: #333; }
        form div { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="number"] { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #218838; }
        .hasil { background-color: #e2f0e6; text-align: center; padding: 15px; margin-top: 20px; border-left: 5px solid #28a745; font-size: 1.2em; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>

    <div class="container">
        <h1>HitungWatt<span class="blinking">⚡</span></h1>
        <h2>Prediksi Tagihan Listrik Anda</h2>

        <form action="index.php" method="post">
            <div>
                <label for="nama_alat">Nama Alat</label>
                <input type="text" id="nama_alat" name="nama_alat" placeholder="Contoh: AC, Kulkas, TV" required>
            </div>
            <div>
                <label for="daya_watt">Daya Alat (Watt)</label>
                <input type="number" id="daya_watt" name="daya_watt" placeholder="Contoh: 750" required>
            </div>
            <div>
                <label for="lama_pakai">Lama Pemakaian (Jam per Hari)</label>
                <input type="number" step="0.1" id="lama_pakai" name="lama_pakai" placeholder="Contoh: 8" required>
            </div>
            <div>
                <label for="jumlah_hari">Jumlah Hari Pemakaian</label>
                <input type="number" id="jumlah_hari" name="jumlah_hari" value="30" required>
            </div>
            <div>
                <label for="tarif">Tarif Listrik per kWh (Rp)</label>
                <input type="number" step="0.01" id="tarif" name="tarif" value="1444.70" required>
            </div>
            <button type="submit">Hitung Biaya</button>
        </form>

        <?php if ($hasil_biaya !== null): ?>
            <div class="hasil">
                Perkiraan Biaya untuk <strong><?= htmlspecialchars($nama_alat); ?></strong> selama <strong><?= $jumlah_hari; ?> hari</strong> adalah:
                <h2>Rp <?= number_format($hasil_biaya, 2, ',', '.'); ?></h2>
            </div>
        <?php endif; ?>

    </div>

    <div class="container" style="margin-top: 20px;">
        <h2>Riwayat Perhitungan Terakhir</h2>
        <table>
            <thead>
                <tr>
                    <th>Alat</th>
                    <th>Daya</th>
                    <th>Pemakaian</th>
                    <th>Total Biaya</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nama_alat']); ?></td>
                            <td><?= $row['daya_watt']; ?> Watt</td>
                            <td><?= $row['lama_pakai_jam']; ?> jam/hari</td>
                            <td>Rp <?= number_format($row['total_biaya'], 2, ',', '.'); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center;">Belum ada riwayat perhitungan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>

<?php
// Tutup koneksi database
mysqli_close($koneksi);
?>