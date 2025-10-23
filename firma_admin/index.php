<?php
require_once 'auth_check.php';

try {
    $company_id = $_SESSION['company_id'];

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM trips WHERE company_id = ?");
    $stmt->execute([$company_id]);
    $total_trips = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM coupons WHERE company_id = ?");
    $stmt->execute([$company_id]);
    $total_coupons = $stmt->fetchColumn();

} catch (PDOException $e) {
    $total_trips = 'N/A';
    $total_coupons = 'N/A';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../public/assets/css/admin.css">
</head>
<body class="page-dashboard">

<div class="sidebar">
    <h3>Firma Paneli</h3>
    <a href="index.php" class="active"><i class="bi bi-house-door-fill"></i> Ana Sayfa</a>
    <a href="seferler.php"><i class="bi bi-sign-turn-right-fill"></i> Sefer Yönetimi</a>
    <a href="kuponlar.php"><i class="bi bi-tag-fill"></i> Kupon Yönetimi</a>
    <a href="../public/logout.php"><i class="bi bi-box-arrow-right"></i> Çıkış Yap</a>
</div>

<div class="main-content">
    <div class="header">
        <h1>Kontrol Paneli</h1>
        <p class="lead text-muted">Hoş geldiniz, <strong><?php echo htmlspecialchars($_SESSION['fullname']); ?></strong></p>
    </div>
    
    <div class="row">
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card text-white bg-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title fs-1 fw-bold"><?php echo $total_trips; ?></h3>
                            <p class="card-text fs-5">Toplam Sefer</p>
                        </div>
                        <i class="bi bi-sign-turn-right-fill" style="font-size: 4rem; opacity: 0.5;"></i>
                    </div>
                </div>
                <a href="seferler.php" class="card-footer text-white text-decoration-none">
                    Detayları Görüntüle <i class="bi bi-arrow-right-circle-fill"></i>
                </a>
            </div>
        </div>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card text-white bg-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title fs-1 fw-bold"><?php echo $total_coupons; ?></h3>
                            <p class="card-text fs-5">Tanımlı Kupon</p>
                        </div>
                        <i class="bi bi-tag-fill" style="font-size: 4rem; opacity: 0.5;"></i>
                    </div>
                </div>
                <a href="kuponlar.php" class="card-footer text-white text-decoration-none">
                    Detayları Görüntüle <i class="bi bi-arrow-right-circle-fill"></i>
                </a>
            </div>
        </div>
        </div>

    <div class="mt-4">
        <h2>Hızlı İşlemler</h2>
        <hr>
        <a href="seferler.php" class="btn btn-lg btn-secondary me-2"><i class="bi bi-plus-circle"></i> Yeni Sefer Ekle</a>
        <a href="kuponlar.php" class="btn btn-lg btn-secondary"><i class="bi bi-plus-circle"></i> Yeni Kupon Ekle</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>