<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('Asia/Jakarta');

//Buat autoloader 
spl_autoload_register(function ($class_name) {
    $file = 'classes/' . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['form_token'])) {
    $_SESSION['form_token'] = bin2hex(random_bytes(16));
}

if (isset($_POST['scroll_pos'])) {
    $_SESSION['scroll_pos'] = intval($_POST['scroll_pos']);
}

header('Content-Type: text/html; charset=utf-8');
?>