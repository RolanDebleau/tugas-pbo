<?php
session_start();

require_once 'classes/Tanaman.php';
require_once 'classes/TanamanSayur.php';
require_once 'classes/TanamanBuah.php';
require_once 'classes/TanamanPremium.php';
require_once 'classes/Lahan.php';
require_once 'classes/Petani.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$token = $_POST['form_token'] ?? '';
$sessionToken = $_SESSION['form_token'] ?? '';

error_log("Token dari form: " . substr($token, 0, 10) . "...");
error_log("Token dari session: " . substr($sessionToken, 0, 10) . "...");

if ($token !== $sessionToken) {
    $_SESSION['pesan'] = "Token tidak valid! Silahkan refresh halaman.";
    $_SESSION['tipepesan'] = "error";
    header('Location: index.php');
    exit;
}

if (!isset($_SESSION['petani'])) {
    $_SESSION['petani'] = serialize(new Petani("Farmer"));
    $_SESSION['pesan'] = "Session baru dibuat. Mulai bermain!";
    $_SESSION['tipepesan'] = "info";
}

$petani = unserialize($_SESSION['petani']);

if (!$petani instanceof Petani) {
    $petani = new Petani("Farmer");
    $_SESSION['petani'] = serialize($petani);
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'beli':
        $jenis = $_POST['jenis'] ?? '';
        $jumlah = intval($_POST['jumlah'] ?? 1);
        
        if ($jumlah < 1 || $jumlah > 10) {
            $_SESSION['pesan'] = "Jumlah tidak valid (1-10)!";
            $_SESSION['tipepesan'] = "error";
            break;
        }
        
        $allKatalog = $petani->getAllKatalogTanaman();
        if (!isset($allKatalog[$jenis])) {
            $_SESSION['pesan'] = "Tanaman tidak ditemukan!";
            $_SESSION['tipepesan'] = "error";
            break;
        }
        
        $item = $allKatalog[$jenis];
        
        //Cek level
        if ($petani->getLevel() < $item['levelRequired']) {
            $_SESSION['pesan'] = "Level tidak cukup! Butuh level {$item['levelRequired']}";
            $_SESSION['tipepesan'] = "error";
            break;
        }
        
        //Hitung total harga
        $totalHarga = $item['hargaBeli'] * $jumlah;
        
        //Cek uang
        if ($petani->getUang() < $totalHarga) {
            $_SESSION['pesan'] = "Uang tidak cukup! Butuh Rp" . number_format($totalHarga) . 
                                ", kamu punya Rp" . number_format($petani->getUang());
            $_SESSION['tipepesan'] = "error";
            break;
        }
        
        //Cek lahan tersedia
        $lahanTersedia = $petani->getLahan()->getKapasitasTersedia();
        if ($lahanTersedia <= 0) {
            $_SESSION['pesan'] = "Lahan penuh! Upgrade lahan terlebih dahulu.";
            $_SESSION['tipepesan'] = "error";
            break;
        }
        
        //Hitung berapa yang benar-benar bisa ditanam
        $bisaTanam = min($jumlah, $lahanTersedia);
        $berhasilTanam = 0;
        
        //Proses pembelian
        for ($i = 0; $i < $bisaTanam; $i++) {
            $tanaman = $petani->beliTanaman($jenis, $petani->getLevel());
            if ($tanaman && $petani->getLahan()->tanam($tanaman)) {
                $berhasilTanam++;
            } else {
                break;
            }
        }
        
        if ($berhasilTanam > 0) {
            $uangTerpakai = $item['hargaBeli'] * $berhasilTanam;
            
            if ($berhasilTanam < $jumlah) {
                $_SESSION['pesan'] = "Berhasil menanam $berhasilTanam dari $jumlah {$item['nama']}. " . 
                                    "Hanya Rp" . number_format($uangTerpakai) . " yang terpakai. " .
                                    "(Lahan terbatas: $lahanTersedia slot)";
                $_SESSION['tipepesan'] = "warning";
            } else {
                $_SESSION['pesan'] = "Berhasil membeli dan menanam $berhasilTanam {$item['nama']}!";
                $_SESSION['tipepesan'] = "success";
            }
        } else {
            $_SESSION['pesan'] = "Gagal menanam tanaman!";
            $_SESSION['tipepesan'] = "error";
        }
        break;
        
    case 'siram':
        $jumlah = $petani->getLahan()->siramSemua();
        $_SESSION['pesan'] = "Berhasil menyiram $jumlah tanaman!";
        $_SESSION['tipepesan'] = "success";
        break;
        
    case 'panen':
        $index = intval($_POST['index'] ?? -1);
        $hasil = $petani->getLahan()->panenTanaman($index);
        
        if ($hasil > 0) {
            $hasilBulat = round($hasil);
            $petani->tambahUang($hasilBulat);
            $levelUp = $petani->tambahExp(20);
            
            if ($levelUp) {
                $_SESSION['pesan'] = "LEVEL UP! Level {$petani->getLevel()}! Dapat Rp" . number_format($hasilBulat) . "!";
                $_SESSION['tipepesan'] = "levelup";
            } else {
                $_SESSION['pesan'] = "Panen berhasil! Dapat Rp" . number_format($hasilBulat) . "!";
                $_SESSION['tipepesan'] = "success";
            }
        } else {
            $_SESSION['pesan'] = "Tanaman belum siap dipanen!";
            $_SESSION['tipepesan'] = "error";
        }
        break;

    case 'panen_semua':
        $hasilPanen = $petani->getLahan()->panenSemua();
        
        if ($hasilPanen['jumlah'] > 0) {
            $hasilBulat = round($hasilPanen['total']);
            $petani->tambahUang($hasilBulat);
            $levelUp = $petani->tambahExp($hasilPanen['jumlah'] * 20);
            
            if ($levelUp) {
                $_SESSION['pesan'] = "LEVEL UP! Level {$petani->getLevel()}! " .
                                    "Berhasil memanen {$hasilPanen['jumlah']} tanaman, " .
                                    "Dapat Rp" . number_format($hasilBulat) . "!";
                $_SESSION['tipepesan'] = "levelup";
            } else {
                $_SESSION['pesan'] = "Berhasil memanen {$hasilPanen['jumlah']} tanaman! " .
                                    "Dapat Rp" . number_format($hasilBulat) . "!";
                $_SESSION['tipepesan'] = "success";
            }
        } else {
            $_SESSION['pesan'] = "Tidak ada tanaman yang siap dipanen!";
            $_SESSION['tipepesan'] = "error";
        }
        break;
        
    case 'upgrade':
        $hargaUpgrade = $petani->getLahan()->getKapasitas() * 200;
        
        if ($petani->getUang() >= $hargaUpgrade) {
            $petani->tambahUang(-$hargaUpgrade); // Kurangi uang
            $kapasitasLama = $petani->getLahan()->getKapasitas();
            $petani->getLahan()->setKapasitas($kapasitasLama + 3);
            
            $_SESSION['pesan'] = "Berhasil upgrade lahan dari $kapasitasLama menjadi " . 
                                ($kapasitasLama + 3) . " slot!";
            $_SESSION['tipepesan'] = "success";
        } else {
            $_SESSION['pesan'] = "Uang tidak cukup! Butuh Rp" . number_format($hargaUpgrade) . 
                                ", kamu punya Rp" . number_format($petani->getUang());
            $_SESSION['tipepesan'] = "error";
        }
        break;
        
    case 'reset':
        error_log("RESET ACTION CALLED - Destroying session");
        
        $_SESSION = array();
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        
        session_start();
        
        $_SESSION['petani'] = serialize(new Petani("Farmer"));
        $_SESSION['form_token'] = bin2hex(random_bytes(16));
        $_SESSION['pesan'] = "Game telah direset! Mulai dari awal.";
        $_SESSION['tipepesan'] = "info";
        $_SESSION['scroll_pos'] = 0;
        
        header('Location: index.php');
        exit;
        break;
        
    default:
        $_SESSION['pesan'] = "Action tidak dikenali!";
        $_SESSION['tipepesan'] = "error";
}

$_SESSION['petani'] = serialize($petani);

if (isset($_POST['scroll_pos'])) {
    $_SESSION['scroll_pos'] = intval($_POST['scroll_pos']);
}

$_SESSION['form_token'] = bin2hex(random_bytes(16));

header('Location: index.php');
exit;
?>