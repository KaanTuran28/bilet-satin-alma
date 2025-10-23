<?php
require_once 'auth_check.php';

$message = '';

if (isset($_GET['status'])) {
    if ($_GET['status'] === 'deleted') $message = '<div class="alert alert-success">Kupon başarıyla silindi.</div>';
    if ($_GET['status'] === 'updated') $message = '<div class="alert alert-success">Kupon başarıyla güncellendi.</div>';
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ? AND company_id IS NULL");
        $stmt->execute([$_GET['id']]);
        header("Location: kuponlar.php?status=deleted");
        exit();
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Kupon silinemedi: ' . $e->getMessage() . '</div>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_coupon'])) {
    $code = trim($_POST['code']);
    $rate = str_replace(',', '.', $_POST['discount_rate']) / 100;
    $limit = $_POST['usage_limit'];
    $expire = $_POST['expiration_date'];

    if (empty($code) || empty($rate) || empty($limit) || empty($expire)) {
        $message = '<div class="alert alert-danger">Tüm alanlar zorunludur.</div>';
    } else {
        try {

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM coupons WHERE code = ?");
            $stmt->execute([$code]);
            if ($stmt->fetchColumn() > 0) {
                $message = '<div class="alert alert-warning">Bu kupon kodu zaten kullanılıyor. Lütfen farklı bir kod girin.</div>';
            } else {

                $stmt = $pdo->prepare("INSERT INTO coupons (code, discount_rate, usage_limit, expiration_date, company_id) VALUES (?, ?, ?, ?, NULL)");
                $stmt->execute([$code, $rate, $limit, $expire]);
                $message = '<div class="alert alert-success">Global kupon başarıyla oluşturuldu.</div>';
            }
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Kupon eklenemedi: ' . $e->getMessage() . '</div>';
        }
    }
}

try {
    $coupons_stmt = $pdo->query("SELECT * FROM coupons WHERE company_id IS NULL ORDER BY expiration_date ASC");
    $coupons = $coupons_stmt->fetchAll();
} catch (PDOException $e) {
    die("Kuponlar getirilemedi: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Global Kupon Yönetimi - Admin Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid">
    <div class="row">

        <nav class="col-md-2 bg-dark text-white min-vh-100 p-3">
            <h3 class="text-center mb-4">Admin Paneli</h3>
            <ul class="nav flex-column">
                <li class="nav-item"><a href="index.php" class="nav-link text-white">Ana Sayfa</a></li>
                <li class="nav-item"><a href="firmalar.php" class="nav-link text-white">Firma Yönetimi</a></li>
                <li class="nav-item"><a href="firma_adminleri.php" class="nav-link text-white">Firma Admin Yönetimi</a></li>
                <li class="nav-item"><a href="kuponlar.php" class="nav-link text-white active">Kupon Yönetimi</a></li>
                <li class="nav-item"><a href="../public/index.php" target="_blank" class="nav-link text-white">Siteyi Görüntüle</a></li>
                <li class="nav-item"><a href="../public/logout.php" class="nav-link text-white">Çıkış Yap</a></li>
            </ul>
        </nav>

        <main class="col-md-10 p-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h1 class="card-title">Global Kupon Yönetimi</h1>
                    <hr>
                    <?php echo $message; ?>

                    <div class="mb-4">
                        <h4>Yeni Global Kupon Ekle</h4>
                        <form method="POST" class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Kupon Kodu</label>
                                <input type="text" name="code" class="form-control" placeholder="Örn: YAZ2025" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">İndirim Oranı (0-1)</label>
                                <input type="number" name="discount_rate" class="form-control" step="0.01" min="0.01" max="1.00" placeholder="0.15 for %15" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kullanım Limiti</label>
                                <input type="number" name="usage_limit" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Son Kullanma Tarihi</label>
                                <input type="date" name="expiration_date" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="add_coupon" class="btn btn-primary">Ekle</button>
                            </div>
                        </form>
                    </div>

                    <h4>Mevcut Global Kuponlar</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Kod</th>
                                    <th>Oran</th>
                                    <th>Limit</th>
                                    <th>Son Tarih</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($coupons)): ?>
                                    <tr><td colspan="5" class="text-center">Henüz kupon eklenmemiş.</td></tr>
                                <?php else: ?>
                                    <?php foreach($coupons as $coupon): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($coupon['code']); ?></td>
                                            <td>%<?php echo htmlspecialchars($coupon['discount_rate'] * 100); ?></td>
                                            <td><?php echo htmlspecialchars($coupon['usage_limit']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($coupon['expiration_date'])); ?></td>
                                            <td>
                                                <a href="kupon_duzenle.php?id=<?php echo $coupon['id']; ?>" class="btn btn-sm btn-warning me-2">Düzenle</a>
                                                <a href="kuponlar.php?action=delete&id=<?php echo $coupon['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu kuponu silmek istediğinizden emin misiniz?');">Sil</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
