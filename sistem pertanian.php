<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); //matikan error display di production

//penerapan encapsulation
abstract class Tanaman {
    protected $nama;
    protected $waktuTanam;
    protected $waktuPanen;
    protected $hargaBeli;
    protected $hargaJual;
    protected $statusAir;
    protected $sudahDipanen;
    protected $kualitas;
    protected $multiplier;
    protected $waktuSiramTerakhir;
    
    public function __construct($nama, $waktuPanen, $hargaBeli, $hargaJual) {
        $this->nama = $nama;
        $this->waktuTanam = time();
        $this->waktuPanen = $waktuPanen;
        $this->hargaBeli = $hargaBeli;
        $this->hargaJual = $hargaJual;
        $this->statusAir = 100;
        $this->sudahDipanen = false;
        $this->waktuSiramTerakhir = time();
        $this->tentukanKualitas();
    }
    
    private function tentukanKualitas() {
        $random = rand(1, 100);
        if ($random <= 5) {
            $this->kualitas = "rainbow";
            $this->multiplier = 10;
        } elseif ($random <= 20) {
            $this->kualitas = "golden";
            $this->multiplier = 5;
        } else {
            $this->kualitas = "normal";
            $this->multiplier = 1;
        }
    }
    public function getNama() {
        return $this->nama;
    }
    public function getKualitas() {
        return $this->kualitas;
    }
    public function getMultiplier() {
        return $this->multiplier;
    }
    public function siram() {
        if ($this->statusAir < 100 && !$this->sudahDipanen) {
            $this->statusAir = 100;
            $this->waktuSiramTerakhir = time();
            return true;
        }
        return false;
    }
    public function updateStatusAir() {
        if ($this->sudahDipanen) {
            return;
        }
        $waktuSekarang = time();
        $detikBerlalu = $waktuSekarang - $this->waktuSiramTerakhir;
        //tempat untuk mengurangi status air itu sendiri
        $interval30Detik = floor($detikBerlalu / 30);
        $penurunan = $interval30Detik * 5; //dia akan berkurang 5% setiap 30 detik
        $this->statusAir = max(0, 100 - $penurunan);
    }
    
    public function bisaDipanen() {
        $this->updateStatusAir();
        $waktuSekarang = time();
        $waktuTumbuh = $waktuSekarang - $this->waktuTanam;
        return ($waktuTumbuh >= $this->waktuPanen && $this->statusAir > 10 && !$this->sudahDipanen);
    }
    
    public function panen() {
        if ($this->bisaDipanen()) {
            $this->sudahDipanen = true;
            return $this->hargaJual * $this->multiplier;
        }
        return 0;
    }
    
    public function getStatus() {
        $this->updateStatusAir();
        $waktuSekarang = time();
        $waktuTumbuh = $waktuSekarang - $this->waktuTanam;
        $persenTumbuh = min(100, ($waktuTumbuh / $this->waktuPanen) * 100);
        
        return [
            'nama' => $this->nama,
            'pertumbuhan' => round($persenTumbuh, 1),
            'air' => round($this->statusAir, 1),
            'siapPanen' => $this->bisaDipanen(),
            'sudahDipanen' => $this->sudahDipanen,
            'kualitas' => $this->kualitas,
            'multiplier' => $this->multiplier,
            'hargaJual' => $this->hargaJual * $this->multiplier
        ];
    }
    
    abstract public function getJenis();
    abstract public function getEmoji();
}

//penrapan inheritance
class TanamanSayur extends Tanaman {
    private $vitaminContent;
    private $emoji;
    
    public function __construct($nama, $waktuPanen, $hargaBeli, $hargaJual, $vitaminContent, $emoji) {
        parent::__construct($nama, $waktuPanen, $hargaBeli, $hargaJual);
        $this->vitaminContent = $vitaminContent;
        $this->emoji = $emoji;
    }
    public function getJenis() {
        return "Sayuran";
    }
    public function getEmoji() {
        return $this->emoji;
    }
}

class TanamanBuah extends Tanaman {
    private $rasa;
    private $emoji;
    
    public function __construct($nama, $waktuPanen, $hargaBeli, $hargaJual, $rasa, $emoji) {
        parent::__construct($nama, $waktuPanen, $hargaBeli, $hargaJual);
        $this->rasa = $rasa;
        $this->emoji = $emoji;
    }
    public function getJenis() {
        return "Buah-buahan";
    }
    public function getEmoji() {
        return $this->emoji;
    }
}

class TanamanPremium extends Tanaman {
    private $emoji;
    private $levelRequired;
    
    public function __construct($nama, $waktuPanen, $hargaBeli, $hargaJual, $emoji, $levelRequired) {
        parent::__construct($nama, $waktuPanen, $hargaBeli, $hargaJual);
        $this->emoji = $emoji;
        $this->levelRequired = $levelRequired;
    }
    public function getJenis() {
        return "Premium";
    }
    public function getEmoji() {
        return $this->emoji;
    }
}

//penerapan polymorphism
class Lahan {
    private $tanaman = [];
    private $kapasitas;
    
    public function __construct($kapasitas = 6) {
        $this->kapasitas = $kapasitas;
    }
    public function tanam($tanaman) {
        if (count($this->tanaman) < $this->kapasitas) {
            $this->tanaman[] = $tanaman;
            return true;
        }
        return false;
    }
    public function getTanaman() {
        return $this->tanaman;
    }
    public function siramSemua() {
        $jumlahDisiram = 0;
        foreach ($this->tanaman as $tanaman) {
            if ($tanaman->siram()) {
                $jumlahDisiram++;
            }
        }
        return $jumlahDisiram;
    }
    
    public function panenTanaman($index) {
        if (isset($this->tanaman[$index])) {
            $hasil = $this->tanaman[$index]->panen();
            if ($hasil > 0) {
                unset($this->tanaman[$index]);
                $this->tanaman = array_values($this->tanaman);
                return $hasil;
            }
        }
        return 0;
    }
    
    public function getKapasitas() {
        return $this->kapasitas;
    }
    public function setKapasitas($kapasitas) {
        $this->kapasitas = $kapasitas;
    }
    public function getKapasitasTersedia() {
        return $this->kapasitas - count($this->tanaman);
    }
}

class Petani {
    private $nama;
    private $uang;
    private $level;
    private $exp;
    private $lahan;
    
    public function __construct($nama) {
        $this->nama = $nama;
        $this->uang = 500;
        $this->level = 1;
        $this->exp = 0;
        $this->lahan = new Lahan(6);
    }
    public function getNama() {
        return $this->nama;
    }
    public function getUang() {
        return $this->uang;
    }
    public function getLevel() {
        return $this->level;
    }
    public function getExp() {
        return $this->exp;
    }
    public function getLahan() {
        return $this->lahan;
    }
    public function beliTanaman($jenis, $level) {
        $katalog = $this->getKatalogTanaman($level);
        if (!isset($katalog[$jenis])) {
            return null;
        }
        
        $data = $katalog[$jenis];
        //ini untuk mengecek apakah level sudah mencukupi
        if ($level < $data['levelRequired']) {
            return null;
        }
        //untuk mengecek uang cukup atau tidak
        if ($this->uang < $data['hargaBeli']) {
            return null;
        }
        //untuk mengecek berapa kapasitas lahan yang tersedia
        if ($this->lahan->getKapasitasTersedia() <= 0) {
            return null;
        }
        //jika semua pengecekan lolos, baru potong uang yang ada untuk membeli tanaman
        $this->uang -= $data['hargaBeli'];
        
        //buat tanaman dengan beberapa spesialis tipe yang ada
        switch($data['tipe']) {
            case 'sayur':
                return new TanamanSayur($data['nama'], $data['waktuPanen'], $data['hargaBeli'], $data['hargaJual'], $data['vitamin'], $data['emoji']);
            case 'buah':
                return new TanamanBuah($data['nama'], $data['waktuPanen'], $data['hargaBeli'], $data['hargaJual'], $data['rasa'], $data['emoji']);
            case 'premium':
                return new TanamanPremium($data['nama'], $data['waktuPanen'], $data['hargaBeli'], $data['hargaJual'], $data['emoji'], $data['levelRequired']);
        }
        return null;
    }
    
    public function getKatalogTanaman($level) {
        $katalog = [
            'wortel' => [
                'nama' => 'Wortel',
                'tipe' => 'sayur',
                'hargaBeli' => 50,
                'hargaJual' => 120,
                'waktuPanen' => 30,
                'vitamin' => 'Vitamin A',
                'emoji' => '🥕',
                'levelRequired' => 1
            ],
            'tomat' => [
                'nama' => 'Tomat',
                'tipe' => 'sayur',
                'hargaBeli' => 100,
                'hargaJual' => 250,
                'waktuPanen' => 45,
                'vitamin' => 'Vitamin C',
                'emoji' => '🍅',
                'levelRequired' => 1
            ],
            'strawberry' => [
                'nama' => 'Strawberry',
                'tipe' => 'buah',
                'hargaBeli' => 200,
                'hargaJual' => 500,
                'waktuPanen' => 60,
                'rasa' => 'Manis',
                'emoji' => '🍓',
                'levelRequired' => 1
            ]
        ];
        if ($level >= 3) {
            $katalog['jagung'] = [
                'nama' => 'Jagung',
                'tipe' => 'sayur',
                'hargaBeli' => 150,
                'hargaJual' => 400,
                'waktuPanen' => 50,
                'vitamin' => 'Vitamin B',
                'emoji' => '🌽',
                'levelRequired' => 3
            ];
        }
        if ($level >= 5) {
            $katalog['semangka'] = [
                'nama' => 'Semangka',
                'tipe' => 'buah',
                'hargaBeli' => 300,
                'hargaJual' => 800,
                'waktuPanen' => 80,
                'rasa' => 'Segar',
                'emoji' => '🍉',
                'levelRequired' => 5
            ];
        }
        if ($level >= 7) {
            $katalog['nanas'] = [
                'nama' => 'Nanas',
                'tipe' => 'premium',
                'hargaBeli' => 500,
                'hargaJual' => 1500,
                'waktuPanen' => 100,
                'emoji' => '🍍',
                'levelRequired' => 7
            ];
        }
        if ($level >= 10) {
            $katalog['durian'] = [
                'nama' => 'Durian',
                'tipe' => 'premium',
                'hargaBeli' => 800,
                'hargaJual' => 2500,
                'waktuPanen' => 120,
                'emoji' => '🍈',
                'levelRequired' => 10
            ];
        }
        return $katalog;
    }
    public function tambahUang($jumlah) {
        $this->uang += $jumlah;
    }
    public function tambahExp($jumlah) {
        $this->exp += $jumlah;
        $levelBaru = false;
        while ($this->exp >= 100) {
            $this->exp -= 100;
            $this->level++;
            $levelBaru = true;
        }
        return $levelBaru;
    }
    
    public function upgradeLahan() {
        $hargaUpgrade = $this->lahan->getKapasitas() * 200;
        if ($this->uang >= $hargaUpgrade) {
            $this->uang -= $hargaUpgrade;
            $this->lahan->setKapasitas($this->lahan->getKapasitas() + 3);
            return true;
        }
        return false;
    }
}
//program utama untuk dapat berjalan
session_start();

//anti spam dengan token
if (!isset($_SESSION['form_token'])) {
    $_SESSION['form_token'] = bin2hex(random_bytes(16));
}

//inisialisasi petani
if (!isset($_SESSION['petani'])) {
    $_SESSION['petani'] = serialize(new Petani("Roland"));
}

$petani = unserialize($_SESSION['petani']);

//handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $token = $_POST['form_token'] ?? '';
    
    //validasi token
    if ($token !== $_SESSION['form_token']) {
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    switch($action) {
        case 'beli':
            $jenis = $_POST['jenis'] ?? '';
            $tanaman = $petani->beliTanaman($jenis, $petani->getLevel());
            
            if ($tanaman) {
                if ($petani->getLahan()->tanam($tanaman)) {
                    $_SESSION['pesan'] = "✅ Berhasil membeli dan menanam {$tanaman->getNama()}!";
                    $_SESSION['tipepesan'] = "success";
                } else {
                    $_SESSION['pesan'] = "❌ Gagal menanam!";
                    $_SESSION['tipepesan'] = "error";
                }
            } else {
                if ($petani->getLahan()->getKapasitasTersedia() <= 0) {
                    $_SESSION['pesan'] = "❌ Lahan penuh! Panen atau upgrade lahan dulu.";
                } else {
                    $_SESSION['pesan'] = "❌ Uang tidak cukup atau bibit belum unlock!";
                }
                $_SESSION['tipepesan'] = "error";
            }
            break;
            
        case 'siram':
            $jumlah = $petani->getLahan()->siramSemua();
            if ($jumlah > 0) {
                $_SESSION['pesan'] = "💧 Berhasil menyiram $jumlah tanaman!";
                $_SESSION['tipepesan'] = "success";
            } else {
                $_SESSION['pesan'] = "ℹ️ Semua tanaman sudah cukup air!";
                $_SESSION['tipepesan'] = "info";
            }
            break;
            
        case 'panen':
            $index = intval($_POST['index'] ?? -1);
            $hasil = $petani->getLahan()->panenTanaman($index);
            
            if ($hasil > 0) {
                $petani->tambahUang($hasil);
                $levelUp = $petani->tambahExp(20);
                
                if ($levelUp) {
                    $_SESSION['pesan'] = "🎉 LEVEL UP! Level {$petani->getLevel()}! Dapat Rp" . number_format($hasil) . "!";
                    $_SESSION['tipepesan'] = "levelup";
                } else {
                    $_SESSION['pesan'] = "🌾 Panen berhasil! Dapat Rp" . number_format($hasil) . "!";
                    $_SESSION['tipepesan'] = "success";
                }
            } else {
                $_SESSION['pesan'] = "❌ Tanaman belum siap dipanen!";
                $_SESSION['tipepesan'] = "error";
            }
            break;
            
        case 'upgrade':
            if ($petani->upgradeLahan()) {
                $_SESSION['pesan'] = "⬆️ Berhasil upgrade lahan! Kapasitas +3 slot!";
                $_SESSION['tipepesan'] = "success";
            } else {
                $harga = $petani->getLahan()->getKapasitas() * 200;
                $_SESSION['pesan'] = "❌ Uang tidak cukup! Butuh Rp" . number_format($harga);
                $_SESSION['tipepesan'] = "error";
            }
            break;
            
        case 'reset':
            unset($_SESSION['petani']);
            unset($_SESSION['pesan']);
            unset($_SESSION['tipepesan']);
            $_SESSION['form_token'] = bin2hex(random_bytes(16));
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
    }
    
    $_SESSION['petani'] = serialize($petani);
    $_SESSION['form_token'] = bin2hex(random_bytes(16));
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$pesan = $_SESSION['pesan'] ?? "";
$tipepesan = $_SESSION['tipepesan'] ?? "info";
unset($_SESSION['pesan']);
unset($_SESSION['tipepesan']);

$katalog = $petani->getKatalogTanaman($petani->getLevel());
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌱 Grow a Garden</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #00ffccff 0%, #000093ff 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        h1 {
            text-align: center;
            color: #2d3748;
            margin-bottom: 10px;
            font-size: 2.5em;
        }
        
        .subtitle {
            text-align: center;
            color: #718096;
            margin-bottom: 20px;
            font-size: 1.1em;
        }
        
        .konsep-box {
            background: linear-gradient(135deg, #fef5e7 0%, #fdebd0 100%);
            border-left: 5px solid #f39c12;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
        }
        
        .konsep-box h3 {
            color: #e67e22;
            margin-bottom: 10px;
        }
        
        .konsep-box ul {
            margin-left: 20px;
        }
        
        .pesan {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
            font-size: 1.1em;
            animation: slideIn 0.5s ease;
        }
        
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .pesan.success {
            background: #d4edda;
            color: #155724;
            border: 2px solid #28a745;
        }
        
        .pesan.error {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #dc3545;
        }
        
        .pesan.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 2px solid #17a2b8;
        }
        
        .pesan.levelup {
            background: #fff3cd;
            color: #856404;
            border: 2px solid #ffc107;
            animation: pulse 1s ease infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #2c835aff 0%, #438a29ff 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(2, 44, 234, 0.4);
        }
        
        .stat-label {
            font-size: 0.9em;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        
        .stat-value {
            font-size: 2em;
            font-weight: bold;
        }
        
        .exp-bar {
            width: 100%;
            height: 10px;
            background: rgba(255,255,255,0.3);
            border-radius: 5px;
            margin-top: 10px;
            overflow: hidden;
        }
        
        .exp-fill {
            height: 100%;
            background: #ffd700;
            transition: width 0.5s ease;
        }
        
        .shop {
            background: #f7fafc;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .shop h2 {
            color: #2d3748;
            margin-bottom: 20px;
        }
        
        .shop-items {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
        }
        
        .shop-item {
            background: white;
            padding: 20px;
            border-radius: 12px;
            border: 3px solid #e2e8f0;
            text-align: center;
            transition: all 0.3s;
        }
        
        .shop-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .shop-item.locked {
            opacity: 0.5;
        }
        
        .item-emoji {
            font-size: 3em;
            margin: 10px 0;
        }
        
        .item-name {
            font-size: 1.3em;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .price {
            color: #e53e3e;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .price.sell {
            color: #38a169;
        }
        
        .level-badge {
            background: #ffa500;
            color: white;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.8em;
            display: inline-block;
            margin-top: 5px;
        }
        
        .lahan {
            background: #f7fafc;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 25px;
        }
        
        .lahan h2 {
            color: #2d3748;
            margin-bottom: 20px;
        }
        
        .lahan-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .tanaman-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .tanaman-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            border: 3px solid #e2e8f0;
            text-align: center;
            position: relative;
        }
        
        .tanaman-card.siap {
            border-color: #48bb78;
            background: #f0fff4;
            animation: glow 2s ease-in-out infinite;
        }
        
        @keyframes glow {
            0%, 100% { box-shadow: 0 0 10px rgba(72, 187, 120, 0.3); }
            50% { box-shadow: 0 0 20px rgba(72, 187, 120, 0.6); }
        }
        
        .tanaman-card.golden {
            border-color: #ffd700;
            background: #fffbea;
        }
        
        .tanaman-card.rainbow {
            border: 3px solid;
            border-image: linear-gradient(45deg, red, orange, yellow, green, blue, violet) 1;
            background: #f0f0ff;
        }
        
        .kualitas-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 10px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .kualitas-badge.golden {
            background: #ffd700;
            color: white;
        }
        
        .kualitas-badge.rainbow {
            background: linear-gradient(135deg, #ff0080, #7928ca);
            color: white;
        }
        
        .tanaman-emoji {
            font-size: 3em;
            margin: 10px 0;
        }
        
        .progress-bar {
            width: 100%;
            height: 22px;
            background: #e2e8f0;
            border-radius: 11px;
            overflow: hidden;
            margin: 12px 0;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #48bb78, #38a169);
            color: white;
            font-size: 0.85em;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
            transition: transform 0.2s;
        }
        
        button:hover {
            transform: translateY(-2px);
        }
        
        .panen-btn {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            width: 100%;
            margin-top: 10px;
        }
        
        .panen-btn:disabled {
            background: #cbd5e0;
            cursor: not-allowed;
        }
        
        .upgrade-btn {
            background: linear-gradient(135deg, #ffd700 0%, #ffa500 100%);
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .empty-lahan {
            text-align: center;
            padding: 60px 20px;
            color: #a0aec0;
        }
        
        .empty-lahan-emoji {
            font-size: 4em;
            margin-bottom: 15px;
        }
        
        .info-footer {
            text-align: center;
            color: #718096;
            margin-top: 30px;
            padding: 20px;
            background: #f7fafc;
            border-radius: 10px;
        }
        
        .info-footer strong {
            color: #2d3748;
            display: block;
            margin-bottom: 10px;
            font-size: 1.1em;
        }
        
        .info-footer p {
            margin: 5px 0;
        }
        
        /*animasi untuk tanaman tumbuh*/
        @keyframes grow {
            0% { transform: scale(0.8); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .tanaman-card.new {
            animation: grow 0.5s ease;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌱 Grow a Garden</h1>
        <p class="subtitle">Simulasi Bercocok Tamam Berdasarkan Game Grow a Garden</p>
        
        <?php if ($pesan): ?>
            <div class="pesan <?= $tipepesan ?>"><?= htmlspecialchars($pesan) ?></div>
        <?php endif; ?>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-label">👨‍🌾 Nama Petani</div>
                <div class="stat-value"><?= htmlspecialchars($petani->getNama()) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">💰 Uang</div>
                <div class="stat-value">Rp<?= number_format($petani->getUang()) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">⭐ Level</div>
                <div class="stat-value"><?= $petani->getLevel() ?></div>
                <div class="exp-bar">
                    <div class="exp-fill" style="width: <?= $petani->getExp() ?>%"></div>
                </div>
                <div class="stat-label" style="margin-top: 5px; font-size: 0.8em;">
                    EXP: <?= $petani->getExp() ?>/100
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-label">🏡 Kapasitas Lahan</div>
                <div class="stat-value"><?= count($petani->getLahan()->getTanaman()) ?>/<?= $petani->getLahan()->getKapasitas() ?></div>
            </div>
        </div>
        
        <div class="shop">
            <h2>🏪 Toko Bibit Tanaman</h2>
            <div class="shop-items">
                <?php foreach ($katalog as $key => $item): ?>
                    <?php $unlocked = $petani->getLevel() >= $item['levelRequired']; ?>
                    <div class="shop-item <?= !$unlocked ? 'locked' : '' ?>">
                        <div class="item-emoji"><?= $item['emoji'] ?></div>
                        <div class="item-name"><?= $item['nama'] ?></div>
                        <p class="price">💵 Beli: Rp<?= number_format($item['hargaBeli']) ?></p>
                        <p class="price sell">💰 Jual: Rp<?= number_format($item['hargaJual']) ?></p>
                        <p style="font-size: 0.9em; color: #718096;">⏱️ <?= $item['waktuPanen'] ?> detik</p>
                        
                        <?php if ($item['levelRequired'] > 1): ?>
                            <span class="level-badge">Level <?= $item['levelRequired'] ?></span>
                        <?php endif; ?>
                        
                        <?php if ($unlocked): ?>
                            <form method="POST" style="margin-top: 10px;">
                                <input type="hidden" name="action" value="beli">
                                <input type="hidden" name="jenis" value="<?= $key ?>">
                                <input type="hidden" name="form_token" value="<?= $_SESSION['form_token'] ?>">
                                <button type="submit">Beli & Tanam</button>
                            </form>
                        <?php else: ?>
                            <p style="color: #e53e3e; margin-top: 10px; font-size: 0.9em;">🔒 Unlock Level <?= $item['levelRequired'] ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="lahan">
            <div class="lahan-header">
                <h2>🌾 Lahan Pertanian</h2>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="upgrade">
                    <input type="hidden" name="form_token" value="<?= $_SESSION['form_token'] ?>">
                    <button type="submit" class="upgrade-btn">
                        ⬆️ Upgrade Lahan (+3 slot) - Rp<?= number_format($petani->getLahan()->getKapasitas() * 200) ?>
                    </button>
                </form>
            </div>
            
            <?php $tanaman_list = $petani->getLahan()->getTanaman(); ?>
            
            <?php if (empty($tanaman_list)): ?>
                <div class="empty-lahan">
                    <div class="empty-lahan-emoji">🌾</div>
                    <p>Lahan masih kosong</p>
                    <p style="font-size: 0.9em; margin-top: 10px;">Beli bibit dari toko untuk mulai bertani!</p>
                </div>
            <?php else: ?>
                <div class="tanaman-grid">
                    <?php foreach ($tanaman_list as $index => $tanaman): ?>
                        <?php 
                        $status = $tanaman->getStatus();
                        $kualitasClass = $status['kualitas'] == 'golden' ? 'golden' : ($status['kualitas'] == 'rainbow' ? 'rainbow' : '');
                        $cardClass = $status['siapPanen'] ? 'siap' : '';
                        ?>
                        <div class="tanaman-card <?= $cardClass ?> <?= $kualitasClass ?>">
                            <?php if ($status['kualitas'] != 'normal'): ?>
                                <div class="kualitas-badge <?= $status['kualitas'] ?>">
                                    <?= $status['kualitas'] == 'golden' ? '⭐ GOLDEN x5' : '🌈 RAINBOW x10' ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="tanaman-emoji"><?= $tanaman->getEmoji() ?></div>
                            <div class="tanaman-name"><?= htmlspecialchars($status['nama']) ?></div>
                            <p style="font-size: 0.85em; color: #718096;"><?= $tanaman->getJenis() ?></p>
                            
                            <?php if (!$status['sudahDipanen']): ?>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= $status['pertumbuhan'] ?>%">
                                        <?= round($status['pertumbuhan']) ?>%
                                    </div>
                                </div>
                                
                                <p class="status-text">💧 Air: <?= round($status['air']) ?>%</p>
                                <p class="status-text">💰 Nilai: Rp<?= number_format($status['hargaJual']) ?></p>
                                
                                <?php if ($status['siapPanen']): ?>
                                    <p style="color: #48bb78; font-weight: bold; margin: 10px 0;">✅ SIAP PANEN!</p>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="panen">
                                        <input type="hidden" name="index" value="<?= $index ?>">
                                        <input type="hidden" name="form_token" value="<?= $_SESSION['form_token'] ?>">
                                        <button type="submit" class="panen-btn">🌾 Panen Sekarang</button>
                                    </form>
                                <?php else: ?>
                                    <button class="panen-btn" disabled>⏳ Sedang Tumbuh</button>
                                <?php endif; ?>
                            <?php else: ?>
                                <p style="color: #a0aec0; margin-top: 15px;">Sudah dipanen</p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="action-buttons">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="siram">
                        <input type="hidden" name="form_token" value="<?= $_SESSION['form_token'] ?>">
                        <button type="submit">💧 Siram Semua Tanaman</button>
                    </form>
                    
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="reset">
                        <input type="hidden" name="form_token" value="<?= $_SESSION['form_token'] ?>">
                        <button type="submit" onclick="return confirm('Yakin ingin reset game? Semua progress akan hilang!')">🔄 Reset Game</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="info-footer">
            <strong>📖 Panduan Bermain:</strong>
            <p>🛒 Beli bibit dari toko sesuai level kamu</p>
            <p>🌱 Tanaman akan tumbuh otomatis berdasarkan waktu</p>
            <p>💧 Jangan lupa siram agar tanaman tidak layu</p>
            <p>🌾 Panen saat progress 100% untuk dapat uang dan EXP</p>
            <p>⭐ Ada chance dapat tanaman GOLDEN (x5) atau RAINBOW (x10)!</p>
            <p>📈 Naik level untuk membuka bibit premium</p>
            <p>⬆️ Upgrade lahan untuk menambah kapasitas tanam</p>
        </div>
    </div>
    
    <script>
        //auto refresh hanya untuk update progress, tidak scroll ke atas
        let lastScrollPos = window.scrollY;
        
        setTimeout(function() {
            lastScrollPos = window.scrollY;
            location.reload();
        }, 5000);
        
        //restore scroll position after reload
        window.addEventListener('load', function() {
            if (sessionStorage.getItem('scrollPos')) {
                window.scrollTo(0, parseInt(sessionStorage.getItem('scrollPos')));
            }
        });
        
        window.addEventListener('beforeunload', function() {
            sessionStorage.setItem('scrollPos', window.scrollY);
        });
    </script>
</body>
</html>