<?php
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
    
    public function panenSemua() {
        $totalHasil = 0;
        $tanamanDipanen = 0;
        
        for ($i = count($this->tanaman) - 1; $i >= 0; $i--) {
            if (isset($this->tanaman[$i])) {
                $hasil = $this->tanaman[$i]->panen();
                if ($hasil > 0) {
                    $totalHasil += $hasil;
                    unset($this->tanaman[$i]);
                    $tanamanDipanen++;
                }
            }
        }
        
        $this->tanaman = array_values($this->tanaman);
        
        return ['total' => $totalHasil, 'jumlah' => $tanamanDipanen];
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
?>