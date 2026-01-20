<?php
require_once 'Tanaman.php';

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
?>