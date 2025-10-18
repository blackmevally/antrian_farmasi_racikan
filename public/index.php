<?php 
include("../config/db.php");

// Fungsi ambil nomor berikutnya per jenis
function nextNomor($conn, $jenis) {
    $today = date("Y-m-d");
    $result = $conn->query("SELECT no_antrian FROM antrian WHERE DATE(created_at)='$today' AND jenis='$jenis' ORDER BY id DESC LIMIT 1");
    $row = $result->fetch_assoc();
    $last = $row ? intval(substr($row['no_antrian'], 1)) : 0;
    $prefix = ($jenis == 'racikan') ? 'R' : 'O';
    return $prefix . str_pad($last + 1, 3, "0", STR_PAD_LEFT);
}

// Fungsi hitung antrian menunggu per jenis
function countWaiting($conn, $jenis) {
    $today = date("Y-m-d");
    $q = $conn->query("SELECT COUNT(*) AS jml FROM antrian WHERE status='waiting' AND jenis='$jenis' AND DATE(created_at)='$today'");
    $row = $q->fetch_assoc();
    return intval($row['jml']);
}

$nextObat = nextNomor($conn, 'obat');
$nextRacikan = nextNomor($conn, 'racikan');
$countObat = countWaiting($conn, 'obat');
$countRacikan = countWaiting($conn, 'racikan');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Ambil Nomor Antrian Farmasi</title>
<style>
body {
  background: linear-gradient(135deg, #27ae60, #1e8449);
  font-family: Arial, sans-serif;
  text-align: center;
  color: white;
  height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  margin: 0;
}
h1 {
  font-size: 42px;
  margin-bottom: 30px;
}
.container {
  display: flex;
  justify-content: center;
  gap: 60px;
  flex-wrap: wrap;
}
.card {
  background: white;
  color: #27ae60;
  border-radius: 20px;
  box-shadow: 0 8px 20px rgba(0,0,0,0.3);
  width: 320px;
  padding: 30px;
  transition: transform 0.3s ease;
}
.card:hover {
  transform: scale(1.02);
}
.card h2 {
  margin: 0;
  color: #2c3e50;
}
.nomor {
  font-size: 120px;
  font-weight: bold;
  margin: 20px 0;
  border: 6px solid #f1c40f;
  border-radius: 15px;
  color: #27ae60;
}
button {
  background: #e74c3c;
  color: white;
  border: none;
  padding: 20px 40px;
  font-size: 22px;
  font-weight: bold;
  border-radius: 12px;
  cursor: pointer;
  transition: 0.3s;
  width: 100%;
}
button:hover { background: #c0392b; transform: scale(1.05); }
.waiting {
  font-size: 18px;
  margin-top: 10px;
  color: #f39c12;
  transition: transform 0.3s ease;
}
.footer {
  position: absolute;
  bottom: 20px;
  font-size: 14px;
  opacity: 0.8;
}
</style>
</head>
<body>

<h1>Ambil Nomor Antrian Farmasi</h1>
<div class="container">

  <!-- KOTAK ANTRIAN OBAT -->
  <div class="card">
    <h2>ANTRIAN OBAT</h2>
    <div class="nomor"><?= $nextObat ?></div>
    <form action="cetak.php" method="POST">
      <input type="hidden" name="jenis" value="obat">
      <input type="hidden" name="next" value="<?= $nextObat ?>">
      <button type="submit">KLIK DISINI 1X AMBIL NOMOR OBAT</button>
    </form>
    <div class="waiting" id="waitObat">Menunggu: <?= $countObat ?> pasien</div>
  </div>

  <!-- KOTAK ANTRIAN RACIKAN -->
  <div class="card">
    <h2>ANTRIAN RACIKAN</h2>
    <div class="nomor"><?= $nextRacikan ?></div>
    <form action="cetak.php" method="POST">
      <input type="hidden" name="jenis" value="racikan">
      <input type="hidden" name="next" value="<?= $nextRacikan ?>">
      <button type="submit">KLIK DISINI 1X AMBIL NOMOR RACIKAN</button>
    </form>
    <div class="waiting" id="waitRacikan">Menunggu: <?= $countRacikan ?> pasien</div>
  </div>

</div>

<div class="footer">RSU Permata Medika Kebumen - Sistem Antrian Farmasi</div>

<!-- ========================= -->
<!--  REALTIME AUTO REFRESH   -->
<!-- ========================= -->
<script>
async function updateWaiting() {
  try {
    const res = await fetch("../config/get_waiting.php");
    const data = await res.json();

    // Update teks
    const elObat = document.getElementById("waitObat");
    const elRacikan = document.getElementById("waitRacikan");

    elObat.textContent = "Menunggu: " + data.obat + " pasien";
    elRacikan.textContent = "Menunggu: " + data.racikan + " pasien";

    // Efek animasi heartbeat halus setiap kali berubah
    elObat.style.transform = "scale(1.1)";
    elRacikan.style.transform = "scale(1.1)";
    setTimeout(() => {
      elObat.style.transform = "scale(1)";
      elRacikan.style.transform = "scale(1)";
    }, 400);
  } catch (err) {
    console.error("Gagal update jumlah antrian:", err);
  }
}

// Jalankan pertama kali & update tiap 5 detik
updateWaiting();
setInterval(updateWaiting, 5000);
</script>

</body>
</html>
