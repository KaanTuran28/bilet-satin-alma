<?php
require_once 'auth_check.php';

try {

    $stmt_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
    $total_users = $stmt_users->fetchColumn();

    $stmt_companies = $pdo->query("SELECT COUNT(*) FROM companies");
    $total_companies = $stmt_companies->fetchColumn();

    $stmt_trips = $pdo->query("SELECT COUNT(*) FROM trips");
    $total_trips = $stmt_trips->fetchColumn();
    
    $stmt_tickets = $pdo->query("SELECT COUNT(*) FROM tickets WHERE status = 'active'");
    $total_tickets = $stmt_tickets->fetchColumn();

} catch (PDOException $e) {

    $total_users = $total_companies = $total_trips = $total_tickets = 'Hata';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../public/assets/css/admin.css">
</head>
<body class="page-dashboard">

<div class="sidebar">
    <h3 style="color: white; text-align: center;">Admin Paneli</h3>
    <a href="index.php" class="active"><i class="bi bi-house-door-fill"></i> Ana Sayfa</a>
    <a href="firmalar.php"><i class="bi bi-building"></i> Firma Yönetimi</a>
    <a href="firma_adminleri.php"><i class="bi bi-people-fill"></i> Firma Admin Yönetimi</a>
    <a href="kuponlar.php"><i class="bi bi-tag-fill"></i> Kupon Yönetimi</a>
    <a href="../public/index.php" target="_blank"><i class="bi bi-eye-fill"></i> Siteyi Görüntüle</a>
    <a href="../public/logout.php"><i class="bi bi-box-arrow-right"></i> Çıkış Yap</a>
</div>

<div class="main-content">
    <div class="header">
        <h1>Kontrol Paneli</h1>
        <p class="lead text-muted">Hoş geldiniz, <strong><?php echo htmlspecialchars($_SESSION['fullname']); ?></strong></p>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card text-white bg-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-1 fw-bold"><?php echo $total_users; ?></div>
                            <div class="fs-5">Toplam Yolcu</div>
                        </div>
                        <i class="bi bi-person" style="font-size: 4rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card text-white bg-warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-1 fw-bold"><?php echo $total_companies; ?></div>
                            <div class="fs-5">Toplam Firma</div>
                        </div>
                        <i class="bi bi-building" style="font-size: 4rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card text-white bg-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-1 fw-bold"><?php echo $total_trips; ?></div>
                            <div class="fs-5">Toplam Sefer</div>
                        </div>
                        <i class="bi bi-sign-turn-right-fill" style="font-size: 4rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card text-white bg-danger h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-1 fw-bold"><?php echo $total_tickets; ?></div>
                            <div class="fs-5">Aktif Bilet</div>
                        </div>
                        <i class="bi bi-ticket-detailed-fill" style="font-size: 4rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <h2>Hızlı İşlemler</h2>
        <hr>
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-building-add" style="font-size: 3rem; color: var(--primary-color);"></i>
                        <h5 class="card-title mt-2">Yeni Firma Ekle</h5>
                        <p class="card-text text-muted">Sisteme yeni bir otobüs firması ekleyin.</p>
                        <a href="firmalar.php" class="btn btn-primary">Git</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-person-plus-fill" style="font-size: 3rem; color: var(--success-color);"></i>
                        <h5 class="card-title mt-2">Yeni Firma Admini Ekle</h5>
                        <p class="card-text text-muted">Bir firmayı yönetmesi için yeni bir yetkili atayın.</p>
                        <a href="firma_adminleri.php" class="btn btn-success">Git</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>