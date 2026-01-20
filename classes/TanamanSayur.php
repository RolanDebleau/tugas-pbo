<?php
require_once 'Tanaman.php';

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
?>