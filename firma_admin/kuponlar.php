<?php
require_once 'auth_check.php';

$message = '';
$company_id = $_SESSION['company_id'];

if (isset($_GET['status'])) {
    if ($_GET['status'] === 'deleted') $message = '<div class="alert alert-success">Kupon başarıyla silindi.</div>';
    if ($_GET['status'] === 'updated') $message = '<div class="alert alert-success">Kupon başarıyla güncellendi.</div>';
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ? AND company_id = ?");
        $stmt->execute([$_GET['id'], $company_id]);
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

                $stmt = $pdo->prepare("INSERT INTO coupons (code, discount_rate, usage_limit, expiration_date, company_id) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$code, $rate, $limit, $expire, $company_id]);
                $message = '<div class="alert alert-success">Firmaya özel kupon başarıyla oluşturuldu.</div>';
            }
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Kupon eklenemedi: ' . $e->getMessage() . '</div>';
        }
    }
}

try {
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE company_id = ? ORDER BY expiration_date ASC");
    $stmt->execute([$company_id]);
    $coupons = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Kuponlar getirilemedi: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kupon Yönetimi - Firma Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../public/assets/css/admin.css">
</head>
<body class="page-list">

<div class="sidebar">
    <h3>Firma Paneli</h3>
    <a href="index.php"><i class="bi bi-house-door-fill"></i> Ana Sayfa</a>
    <a href="seferler.php"><i class="bi bi-sign-turn-right-fill"></i> Sefer Yönetimi</a>
    <a href="kuponlar.php" class="active"><i class="bi bi-tag-fill"></i> Kupon Yönetimi</a>
    <a href="../public/logout.php"><i class="bi bi-box-arrow-right"></i> Çıkış Yap</a>
</div>

<div class="main-content">
    <div class="header">
        <h1>Firmaya Özel Kupon Yönetimi</h1>
    </div>
    
    <?php if (!empty($message)) echo $message; ?>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Yeni Kupon Ekle</h5>
            <hr>
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="code" class="form-label">Kupon Kodu</label>
                        <input type="text" class="form-control" id="code" name="code" placeholder="Örn: SONBAHAR10" required>
                    </div>
                    <div class="col-md-6">
                        <label for="discount_rate" class="form-label">İndirim Oranı</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="discount_rate" name="discount_rate" placeholder="Örn: 10" step="1" min="1" max="100" required>
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="usage_limit" class="form-label">Kullanım Limiti</label>
                        <input type="number" class="form-control" id="usage_limit" name="usage_limit" placeholder="Örn: 100" required>
                    </div>
                    <div class="col-md-6">
                        <label for="expiration_date" class="form-label">Son Kullanma Tarihi</label>
                        <input type="date" class="form-control" id="expiration_date" name="expiration_date" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" name="add_coupon" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Ekle</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Mevcut Kuponlarınız</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mt-3">
                    <thead class="table-light">
                        <tr><th>Kod</th><th>Oran</th><th>Kalan Limit</th><th>Son Tarih</th><th>İşlemler</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($coupons)): ?>
                            <tr><td colspan="5" class="text-center p-4">Henüz hiç kupon oluşturmadınız.</td></tr>
                        <?php else: ?>
                            <?php foreach($coupons as $coupon): ?>
                            <tr>
                                <td><strong class="font-monospace"><?php echo htmlspecialchars($coupon['code']); ?></strong></td>
                                <td>%<?php echo htmlspecialchars($coupon['discount_rate'] * 100); ?></td>
                                <td><?php echo htmlspecialchars($coupon['usage_limit']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($coupon['expiration_date'])); ?></td>
                                <td>
                                    <a href="kupon_duzenle.php?id=<?php echo $coupon['id']; ?>" class="btn btn-warning btn-sm">Düzenle</a>
                                    <a href="kuponlar.php?action=delete&id=<?php echo $coupon['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bu kuponu silmek istediğinizden emin misiniz?');">Sil</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>