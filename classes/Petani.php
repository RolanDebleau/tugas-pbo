<?php
require_once 'Lahan.php';

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
    
    public function getNama() { return $this->nama; }
    public function getUang() { return $this->uang; }
    public function getLevel() { return $this->level; }
    public function getExp() { return $this->exp; }
    public function getLahan() { return $this->lahan; }
    
    public function beliTanaman($jenis, $level) {
        $katalog = $this->getKatalogTanaman($level);
        if (!isset($katalog[$jenis])) {
            return null;
        }
        
        $data = $katalog[$jenis];
        
        if ($level < $data['levelRequired']) {
            return null;
        }
        
        if ($this->uang < $data['hargaBeli']) {
            return null;
        }
        
        if ($this->lahan->getKapasitasTersedia() <= 0) {
            return null;
        }
        
        $this->uang -= $data['hargaBeli'];
        
        switch($data['tipe']) {
            case 'sayur':
                require_once 'TanamanSayur.php';
                return new TanamanSayur($data['nama'], $data['waktuPanen'], $data['hargaBeli'], 
                                        $data['hargaJual'], $data['vitamin'], $data['emoji']);
            case 'buah':
                require_once 'TanamanBuah.php';
                return new TanamanBuah($data['nama'], $data['waktuPanen'], $data['hargaBeli'], 
                                        $data['hargaJual'], $data['rasa'], $data['emoji']);
            case 'premium':
                require_once 'TanamanPremium.php';
                return new TanamanPremium($data['nama'], $data['waktuPanen'], $data['hargaBeli'], 
                                        $data['hargaJual'], $data['emoji'], $data['levelRequired']);
        }
        return null;
    }
    
    public function getKatalogTanaman($level) {
        $allKatalog = $this->getAllKatalogTanaman();
        $filteredKatalog = [];
        
        foreach ($allKatalog as $key => $item) {
            if ($level >= $item['levelRequired']) {
                $filteredKatalog[$key] = $item;
            }
        }
        
        return $filteredKatalog;
    }
    
    public function getAllKatalogTanaman() {
        return [
            'wortel' => [
                'nama' => 'Wortel',
                'tipe' => 'sayur',
                'hargaBeli' => 50,
                'hargaJual' => 100,
                'waktuPanen' => 30,
                'vitamin' => 'Vitamin A',
                'emoji' => '🥕',
                'levelRequired' => 1
            ],
            'tomat' => [
                'nama' => 'Tomat',
                'tipe' => 'sayur',
                'hargaBeli' => 100,
                'hargaJual' => 200,
                'waktuPanen' => 45,
                'vitamin' => 'Vitamin C',
                'emoji' => '🍅',
                'levelRequired' => 1
            ],
            'strawberry' => [
                'nama' => 'Strawberry',
                'tipe' => 'buah',
                'hargaBeli' => 150,
                'hargaJual' => 300,
                'waktuPanen' => 60,
                'rasa' => 'Manis',
                'emoji' => '🍓',
                'levelRequired' => 1
            ],
            'jagung' => [
                'nama' => 'Jagung',
                'tipe' => 'sayur',
                'hargaBeli' => 200,
                'hargaJual' => 400,
                'waktuPanen' => 50,
                'vitamin' => 'Vitamin B',
                'emoji' => '🌽',
                'levelRequired' => 3
            ],
            'semangka' => [
                'nama' => 'Semangka',
                'tipe' => 'buah',
                'hargaBeli' => 300,
                'hargaJual' => 600,
                'waktuPanen' => 80,
                'rasa' => 'Segar',
                'emoji' => '🍉',
                'levelRequired' => 5
            ],
            'nanas' => [
                'nama' => 'Nanas',
                'tipe' => 'premium',
                'hargaBeli' => 500,
                'hargaJual' => 1000,
                'waktuPanen' => 100,
                'emoji' => '🍍',
                'levelRequired' => 7
            ],
            'durian' => [
                'nama' => 'Durian',
                'tipe' => 'premium',
                'hargaBeli' => 800,
                'hargaJual' => 1600,
                'waktuPanen' => 120,
                'emoji' => '🍈',
                'levelRequired' => 10
            ],
            'alpukat' => [
                'nama' => 'Alpukat',
                'tipe' => 'premium',
                'hargaBeli' => 1000,
                'hargaJual' => 2000,
                'waktuPanen' => 150,
                'emoji' => '🥑',
                'levelRequired' => 12
            ],
            'mangga' => [
                'nama' => 'Mangga',
                'tipe' => 'premium',
                'hargaBeli' => 1500,
                'hargaJual' => 3000,
                'waktuPanen' => 200,
                'emoji' => '🥭',
                'levelRequired' => 15
            ],
            'pisang' => [
                'nama' => 'Pisang',
                'tipe' => 'premium',
                'hargaBeli' => 2000,
                'hargaJual' => 4000,
                'waktuPanen' => 250,
                'emoji' => '🍌',
                'levelRequired' => 18
            ]
        ];
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
    public function getMaxBeli($jenis) {
        $katalog = $this->getAllKatalogTanaman();
        if (!isset($katalog[$jenis])) {
            return 0;
        }
        
        $item = $katalog[$jenis];
        
        if ($this->level < $item['levelRequired']) {
            return 0;
        }
        
        $maxByMoney = floor($this->uang / $item['hargaBeli']);
        
        $maxByLahan = $this->lahan->getKapasitasTersedia();
        
        return min($maxByMoney, $maxByLahan, 10);
    }
    
}
?>