<?php
require_once 'Tanaman.php';

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
?>