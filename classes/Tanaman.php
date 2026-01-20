<?php
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
    
    public function getNama() { return $this->nama; }
    public function getKualitas() { return $this->kualitas; }
    public function getMultiplier() { return $this->multiplier; }
    
    public function siram() {
        if (!$this->sudahDipanen) {
            $this->statusAir = 100;
            $this->waktuSiramTerakhir = time();
            return true;
        }
        return false;
    }
    
    public function updateStatusAir() {
        if ($this->sudahDipanen) return;
        $waktuSekarang = time();
        $detikBerlalu = $waktuSekarang - $this->waktuSiramTerakhir;
        $penurunan = floor($detikBerlalu / 2) * 5;
        $this->statusAir = max(0, 100 - $penurunan);
    }
    
    public function bisaDipanen() {
        $waktuSekarang = time();
        $waktuTumbuh = $waktuSekarang - $this->waktuTanam;
        $sudahWaktunya = ($waktuTumbuh >= $this->waktuPanen);
        
        $this->updateStatusAir();
        
        return ($sudahWaktunya && !$this->sudahDipanen);
    }
    
    public function panen() {
        if ($this->bisaDipanen()) {
            $this->sudahDipanen = true; 
            $bonusAir = ($this->statusAir > 80) ? 1.2 : 
                    (($this->statusAir > 50) ? 1.1 : 1.0);
            return $this->hargaJual * $this->multiplier * $bonusAir;
        }
        return 0;
    }
    
    public function getStatus() {
        $this->updateStatusAir();
        $waktuSekarang = time();
        $waktuTumbuh = $waktuSekarang - $this->waktuTanam;
        $persenTumbuh = min(100, ($waktuTumbuh / $this->waktuPanen) * 100);
        
        $siapPanen = $this->bisaDipanen();
        $waktuTersisa = max(0, $this->waktuPanen - $waktuTumbuh);
        
        return [
            'nama' => $this->nama,
            'pertumbuhan' => round($persenTumbuh, 1),
            'air' => round($this->statusAir, 1),
            'siapPanen' => $siapPanen,
            'sudahDipanen' => $this->sudahDipanen,
            'kualitas' => $this->kualitas,
            'multiplier' => $this->multiplier,
            'hargaJual' => $this->hargaJual * $this->multiplier,
            'waktuTersisa' => $waktuTersisa,
            'hargaAktual' => $this->hargaJual * $this->multiplier * 
                            (($this->statusAir > 80) ? 1.2 : 
                            (($this->statusAir > 50) ? 1.1 : 1.0))
        ];
    }
    
    abstract public function getJenis();
    abstract public function getEmoji();
}
?>