<?php
require_once 'config.php';

require_once 'classes/Tanaman.php';
require_once 'classes/TanamanSayur.php';
require_once 'classes/TanamanBuah.php';
require_once 'classes/TanamanPremium.php';
require_once 'classes/Lahan.php';
require_once 'classes/Petani.php';

if (!isset($_SESSION['petani'])) {
    $petani = new Petani("Farmer");
    $_SESSION['petani'] = serialize($petani);
    $_SESSION['pesan'] = "Selamat datang! Mulai bermain!";
    $_SESSION['tipepesan'] = "info";
    
    header('Location: index.php');
    exit;
}

try {
    $petani = unserialize($_SESSION['petani']);
    
    if (!$petani instanceof Petani) {
        throw new Exception("Invalid petani object");
    }
} catch (Exception $e) {
    $petani = new Petani("Farmer");
    $_SESSION['petani'] = serialize($petani);
    $_SESSION['pesan'] = "Session diperbarui. Game dimulai ulang.";
    $_SESSION['tipepesan'] = "info";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['ajax'])) {
    require_once 'actions.php';
}

//Ambil pesan
$pesan = $_SESSION['pesan'] ?? '';
$tipepesan = $_SESSION['tipepesan'] ?? 'info';

unset($_SESSION['pesan'], $_SESSION['tipepesan']);

//Data untuk tampilan
$katalog = $petani->getKatalogTanaman($petani->getLevel());
$allKatalog = $petani->getAllKatalogTanaman();
$tanaman_list = $petani->getLahan()->getTanaman();
$lahan_tersedia = $petani->getLahan()->getKapasitasTersedia();

$scrollPos = $_SESSION['scroll_pos'] ?? 0;
unset($_SESSION['scroll_pos']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌱 Grow a Garden</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            z-index: 1000;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
            border-left: 4px solid;
        }
        .toast.show {
            opacity: 1;
            transform: translateX(0);
        }
        .toast.success { border-left-color: #2ecc71; background: #d4edda; color: #155724; }
        .toast.error { border-left-color: #e74c3c; background: #f8d7da; color: #721c24; }
        .toast.info { border-left-color: #3498db; background: #d1ecf1; color: #0c5460; }
        .toast.warning { border-left-color: #f39c12; background: #fff3cd; color: #856404; }
        
        .loading-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            display: none;
            justify-content: center;
            align-items: center;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 10px 0;
            gap: 5px;
        }
        .qty-btn {
            width: 30px;
            height: 30px;
            border: none;
            background: #2ecc71;
            color: white;
            border-radius: 50%;
            cursor: pointer;
            font-weight: bold;
        }
        .qty-input {
            width: 50px;
            text-align: center;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body data-scroll-pos="<?= $scrollPos ?>">
    <div id="toast-container"></div>
    
    <div class="container">
        <!--
        <div class="debug-info">
            <strong>Debug Info:</strong> 
            Token: <?= substr($_SESSION['form_token'] ?? 'none', 0, 10) ?>... | 
            Session ID: <?= session_id() ?> | 
            Petani in Session: <?= isset($_SESSION['petani']) ? 'Yes' : 'No' ?>
        </div>
        -->
        
        <header class="header">
            <h1><i class="fas fa-seedling"></i> Grow a Garden</h1>
            <p class="subtitle">Simulasi Bercocok Tanam</p>
        </header>

        <?php if ($pesan): ?>
            <div class="pesan <?= htmlspecialchars($tipepesan) ?>">
                <i class="fas fa-<?= 
                    $tipepesan == 'success' ? 'check-circle' : 
                    ($tipepesan == 'error' ? 'exclamation-circle' : 
                    ($tipepesan == 'warning' ? 'exclamation-triangle' : 'info-circle'))
                ?>"></i>
                <?= htmlspecialchars($pesan) ?>
            </div>
        <?php endif; ?>

        <!-- STATS -->
        <div class="stats-section">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-user"></i></div>
                    <div class="stat-content">
                        <div class="stat-label">Nama Petani</div>
                        <div class="stat-value"><?= htmlspecialchars($petani->getNama()) ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                    <div class="stat-content">
                        <div class="stat-label">Uang</div>
                        <div class="stat-value">Rp<?= number_format($petani->getUang()) ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="stat-content">
                        <div class="stat-label">Level</div>
                        <div class="stat-value"><?= $petani->getLevel() ?></div>
                        <div class="exp-bar">
                            <div class="exp-fill" style="width: <?= $petani->getExp() ?>%"></div>
                        </div>
                        <div class="exp-text">EXP: <?= $petani->getExp() ?>/100</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-tractor"></i></div>
                    <div class="stat-content">
                        <div class="stat-label">Lahan</div>
                        <div class="stat-value">
                            <?= count($tanaman_list) ?>/<?= $petani->getLahan()->getKapasitas() ?>
                            (Tersedia: <?= $lahan_tersedia ?>)
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TOKO BIBIT -->
        <div class="shop-section">
            <h2 class="section-title"><i class="fas fa-store"></i> Toko Bibit</h2>
            
            <div class="lahan-info" style="background: #e8f5e8; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                <i class="fas fa-info-circle"></i> 
                <strong>Lahan tersedia:</strong> <?= $lahan_tersedia ?> slot | 
                <strong>Uang:</strong> Rp<?= number_format($petani->getUang()) ?>
            </div>
            
            <div class="shop-grid">
                <?php foreach ($allKatalog as $key => $item): ?>
                    <?php 
                    $unlocked = $petani->getLevel() >= $item['levelRequired'];
                    $maxBeli = $petani->getMaxBeli($key);
                    ?>
                    <div class="shop-item <?= !$unlocked ? 'locked' : '' ?>">
                        <div class="shop-item-header">
                            <div class="plant-emoji"><?= $item['emoji'] ?></div>
                            <div class="plant-name"><?= $item['nama'] ?></div>
                            <?php if ($item['levelRequired'] > 1): ?>
                                <div class="level-badge">Level <?= $item['levelRequired'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="shop-item-details">
                            <div class="price-row">
                                <span>Beli:</span>
                                <span class="price buy">Rp<?= number_format($item['hargaBeli']) ?></span>
                            </div>
                            <div class="price-row">
                                <span>Jual:</span>
                                <span class="price sell">Rp<?= number_format($item['hargaJual']) ?></span>
                            </div>
                            <div class="time-row">
                                <i class="far fa-clock"></i>
                                <span><?= $item['waktuPanen'] ?> detik</span>
                            </div>
                            
                            <?php if ($unlocked): ?>
                                <div class="quantity-selector">
                                    <input type="number" 
                                            name="jumlah" 
                                            value="1" 
                                            min="1" 
                                            max="<?= $maxBeli ?>"
                                            style="width: 60px; text-align: center;"
                                            onchange="updateTotal(this, <?= $item['hargaBeli'] ?>)">
                                </div>
                                <div style="text-align: center; font-size: 0.9rem; color: #666; margin: 5px 0;">
                                    Maks: <?= $maxBeli ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!$unlocked): ?>
                            <div class="locked-overlay">
                                <i class="fas fa-lock"></i>
                                <span>Level <?= $item['levelRequired'] ?>+</span>
                            </div>
                        <?php elseif ($maxBeli > 0): ?>
                            <form method="POST" class="buy-form" onsubmit="saveScrollPosition()">
                                <input type="hidden" name="action" value="beli">
                                <input type="hidden" name="jenis" value="<?= $key ?>">
                                <input type="hidden" name="form_token" value="<?= $_SESSION['form_token'] ?>">
                                <input type="hidden" name="scroll_pos" class="scroll-pos-input">
                                <div style="display: flex; gap: 5px;">
                                    <input type="number" 
                                            name="jumlah" 
                                            value="1" 
                                            min="1" 
                                            max="<?= $maxBeli ?>"
                                            style="flex: 1; text-align: center;"
                                            required>
                                    <button type="submit" class="buy-btn">
                                        <i class="fas fa-shopping-cart"></i> Beli
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div style="text-align: center; color: #e74c3c; padding: 10px; font-size: 0.9rem;">
                                <i class="fas fa-exclamation-triangle"></i>
                                Tidak bisa membeli
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- LAHAN PERTANIAN -->
        <div class="farm-section">
            <div class="section-header">
                <h2 class="section-title"><i class="fas fa-tractor"></i> Lahan Pertanian</h2>
                <div class="farm-actions">
                    <!-- TOMBOL PANEN SEMUA -->
                    <?php if (!empty($tanaman_list)): ?>
                        <form method="POST" onsubmit="saveScrollPosition()" style="display: inline;">
                            <input type="hidden" name="action" value="panen_semua">
                            <input type="hidden" name="form_token" value="<?= $_SESSION['form_token'] ?>">
                            <input type="hidden" name="scroll_pos" class="scroll-pos-input">
                            <button type="submit" class="action-btn harvest-all-btn">
                                <i class="fas fa-sickle"></i> Panen Semua
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <!-- TOMBOL SIRAM SEMUA -->
                    <form method="POST" onsubmit="saveScrollPosition()" style="display: inline;">
                        <input type="hidden" name="action" value="siram">
                        <input type="hidden" name="form_token" value="<?= $_SESSION['form_token'] ?>">
                        <input type="hidden" name="scroll_pos" class="scroll-pos-input">
                        <button type="submit" class="action-btn water-btn">
                            <i class="fas fa-tint"></i> Siram Semua
                        </button>
                    </form>
                    
                    <!-- TOMBOL UPGRADE -->
                    <form method="POST" onsubmit="saveScrollPosition()" style="display: inline;">
                        <input type="hidden" name="action" value="upgrade">
                        <input type="hidden" name="form_token" value="<?= $_SESSION['form_token'] ?>">
                        <input type="hidden" name="scroll_pos" class="scroll-pos-input">
                        <button type="submit" class="action-btn upgrade-btn">
                            <i class="fas fa-expand-alt"></i> 
                            <span class="upgrade-text">Upgrade Lahan</span>
                            <span class="price-tag">
                                Rp<?= number_format($petani->getLahan()->getKapasitas() * 200) ?>
                            </span>
                        </button>
                    </form>
                </div>
            </div>
            
            <?php if (empty($tanaman_list)): ?>
                <div class="empty-farm">
                    <div class="empty-icon">
                        <i class="fas fa-seedling"></i>
                    </div>
                    <p class="empty-text">Lahan masih kosong</p>
                    <p class="empty-subtext">Beli bibit dari toko untuk mulai bertani!</p>
                </div>
            <?php else: ?>
                <div class="farm-grid">
                    <?php foreach ($tanaman_list as $index => $tanaman): ?>
                        <?php $status = $tanaman->getStatus(); ?>
                        <div class="plant-card <?= $status['siapPanen'] ? 'ready' : '' ?> <?= $status['sudahDipanen'] ? 'harvested' : '' ?>">
                            <div class="plant-card-header">
                                <div class="plant-emoji-large"><?= $tanaman->getEmoji() ?></div>
                                <div class="plant-info">
                                    <div class="plant-name-large"><?= $status['nama'] ?></div>
                                    <div class="plant-type"><?= $tanaman->getJenis() ?></div>
                                </div>
                                <?php if ($status['kualitas'] != 'normal'): ?>
                                    <div class="quality-badge <?= $status['kualitas'] ?>">
                                        <?= $status['kualitas'] == 'golden' ? 'GOLDEN' : 'RAINBOW' ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="progress-container">
                                <div class="progress-label">
                                    <span>Pertumbuhan</span>
                                    <span><?= round($status['pertumbuhan']) ?>%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= $status['pertumbuhan'] ?>%"></div>
                                </div>
                            </div>
                            
                            <div class="water-container">
                                <div class="water-label">
                                    <i class="fas fa-tint"></i>
                                    <span>Air: <?= round($status['air']) ?>%</span>
                                </div>
                                <div class="water-bar">
                                    <div class="water-fill" style="width: <?= $status['air'] ?>%"></div>
                                </div>
                            </div>
                            
                            <div class="plant-status">
                                <?php if ($status['siapPanen']): ?>
                                    <div class="ready-to-harvest">
                                        <i class="fas fa-check-circle"></i>
                                        <span>Siap Panen!</span>
                                    </div>
                                    <div class="harvest-value">
                                        Rp<?= number_format($status['hargaAktual']) ?>
                                    </div>
                                    <form method="POST" onsubmit="saveScrollPosition()">
                                        <input type="hidden" name="action" value="panen">
                                        <input type="hidden" name="index" value="<?= $index ?>">
                                        <input type="hidden" name="form_token" value="<?= $_SESSION['form_token'] ?>">
                                        <input type="hidden" name="scroll_pos" class="scroll-pos-input">
                                        <button type="submit" class="harvest-btn">
                                            <i class="fas fa-sickle"></i> Panen Sekarang
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <div class="growing">
                                        <i class="fas fa-hourglass-half"></i>
                                        <span><?= round($status['waktuTersisa']) ?> detik lagi</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- RESET BUTTON -->
        <div class="reset-section">
            <form method="POST" id="reset-form" onsubmit="return confirmReset()">
                <input type="hidden" name="action" value="reset">
                <input type="hidden" name="form_token" value="<?= $_SESSION['form_token'] ?>">
                <input type="hidden" name="scroll_pos" value="0">
                <button type="submit" class="reset-btn">
                    <i class="fas fa-redo"></i> Reset Game
                </button>
            </form>
        </div>

        <footer class="footer">
            <p>🌱 Grow a Garden - Simulasi Bercocok Tanam</p>
        </footer>
    </div>

    <script>
        function saveScrollPosition() {
            const scrollPos = window.scrollY;
            document.querySelectorAll('.scroll-pos-input').forEach(input => {
                input.value = scrollPos;
            });
            return true;
        }
        
        //Update total harga
        function updateTotal(input, harga) {
            const value = parseInt(input.value) || 1;
            const maxBeli = parseInt(input.max);
            if (value > maxBeli) {
                input.value = maxBeli;
                showToast(`Maksimal pembelian: ${maxBeli}`, 'warning');
            }
        }
        
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            const icon = type === 'success' ? 'check-circle' : 
                        type === 'error' ? 'exclamation-circle' : 
                        type === 'warning' ? 'exclamation-triangle' : 'info-circle';
            toast.innerHTML = `<i class="fas fa-${icon}"></i> ${message}`;
            
            document.getElementById('toast-container').appendChild(toast);
            
            setTimeout(() => toast.classList.add('show'), 10);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
        
        function confirmReset() {
            if (!confirm('Yakin ingin reset game?\nSemua progress akan hilang!')) {
                return false;
            }
            saveScrollPosition();
            return true;
        }
        
        //Auto refresh 
        setInterval(() => {
            const scrollPos = window.scrollY;
            sessionStorage.setItem('lastScrollPos', scrollPos);
            window.location.reload();
        }, 10000);
        
        document.addEventListener('DOMContentLoaded', function() {
            const savedScroll = document.body.dataset.scrollPos;
            const sessionScroll = sessionStorage.getItem('lastScrollPos');
            
            if (sessionScroll) {
                setTimeout(() => window.scrollTo(0, parseInt(sessionScroll)), 100);
                sessionStorage.removeItem('lastScrollPos');
            } else if (savedScroll > 0) {
                setTimeout(() => window.scrollTo(0, parseInt(savedScroll)), 100);
            }
        });
    </script>
</body>
</html>