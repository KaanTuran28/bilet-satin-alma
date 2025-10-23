<?php
require_once 'auth_check.php';

$message = '';
$coupon_id = $_GET['id'] ?? null;

if (!$coupon_id || !is_numeric($coupon_id)) {
    header("Location: kuponlar.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code']);
    $rate = $_POST['discount_rate'];
    $limit = $_POST['usage_limit'];
    $expire = $_POST['expiration_date'];

    if (empty($code) || empty($rate) || empty($limit) || empty($expire)) {
        $message = '<div class="alert alert-danger">Tüm alanlar zorunludur.</div>';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE coupons SET code = ?, discount_rate = ?, usage_limit = ?, expiration_date = ? WHERE id = ? AND company_id IS NULL");
            $stmt->execute([$code, $rate, $limit, $expire, $coupon_id]);
            header("Location: kuponlar.php?status=updated");
            exit();
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Kupon güncellenemedi: ' . $e->getMessage() . '</div>';
        }
    }
}

try {
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE id = ? AND company_id IS NULL");
    $stmt->execute([$coupon_id]);
    $coupon = $stmt->fetch();
    if (!$coupon) {
        header("Location: kuponlar.php");
        exit();
    }
} catch (PDOException $e) {
    die("Kupon bilgileri getirilemedi: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global Kupon Düzenle - Admin Paneli</title>
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
            <div class="card">
                <div class="card-body">
                    <h1 class="card-title">Global Kupon Düzenle</h1>
                    <hr>
                    <?php echo $message; ?>
                    <form method="POST">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="code" class="form-label">Kupon Kodu</label>
                                <input type="text" class="form-control" id="code" name="code" value="<?php echo htmlspecialchars($coupon['code']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="discount_rate" class="form-label">İndirim Oranı (0-1)</label>
                                <input type="number" step="0.01" min="0.01" max="1.00" class="form-control" id="discount_rate" name="discount_rate" value="<?php echo htmlspecialchars($coupon['discount_rate']); ?>" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="usage_limit" class="form-label">Kullanım Limiti</label>
                                <input type="number" class="form-control" id="usage_limit" name="usage_limit" value="<?php echo htmlspecialchars($coupon['usage_limit']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="expiration_date" class="form-label">Bitiş Tarihi</label>
                                <input type="date" class="form-control" id="expiration_date" name="expiration_date" value="<?php echo htmlspecialchars($coupon['expiration_date']); ?>" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Güncelle</button>
                        <a href="kuponlar.php" class="btn btn-secondary ms-2">Listeye Geri Dön</a>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
