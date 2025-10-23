<?php
require_once 'auth_check.php';

$message = '';
$company_id = $_SESSION['company_id'];
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
            $stmt = $pdo->prepare("UPDATE coupons SET code = ?, discount_rate = ?, usage_limit = ?, expiration_date = ? WHERE id = ? AND company_id = ?");
            $stmt->execute([$code, $rate, $limit, $expire, $coupon_id, $company_id]);
            header("Location: kuponlar.php?status=updated");
            exit();
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Kupon güncellenemedi: ' . $e->getMessage() . '</div>';
        }
    }
}

try {
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE id = ? AND company_id = ?");
    $stmt->execute([$coupon_id, $company_id]);
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
    <title>Kupon Düzenle - Firma Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../public/assets/css/admin.css">
</head>
<body class="page-form">

<div class="sidebar">
    <h3>Firma Paneli</h3>
    <a href="index.php"><i class="bi bi-house-door-fill"></i> Ana Sayfa</a>
    <a href="seferler.php"><i class="bi bi-sign-turn-right-fill"></i> Sefer Yönetimi</a>
    <a href="kuponlar.php" class="active"><i class="bi bi-tag-fill"></i> Kupon Yönetimi</a>
    <a href="../public/logout.php"><i class="bi bi-box-arrow-right"></i> Çıkış Yap</a>
</div>

<div class="main-content">
    <div class="header">
        <h1>Firmaya Özel Kupon Düzenle</h1>
    </div>
    
    <?php if (!empty($message)) echo $message; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="code" class="form-label">Kupon Kodu</label>
                        <input type="text" class="form-control" id="code" name="code" value="<?php echo htmlspecialchars($coupon['code']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="discount_rate" class="form-label">İndirim Oranı (% olarak)</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="discount_rate" name="discount_rate" value="<?php echo htmlspecialchars($coupon['discount_rate'] * 100); ?>" step="1" min="1" max="100" required>
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="usage_limit" class="form-label">Kullanım Limiti</label>
                        <input type="number" class="form-control" id="usage_limit" name="usage_limit" value="<?php echo htmlspecialchars($coupon['usage_limit']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="expiration_date" class="form-label">Son Kullanma Tarihi</label>
                        <input type="date" class="form-control" id="expiration_date" name="expiration_date" value="<?php echo htmlspecialchars($coupon['expiration_date']); ?>" required>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Güncelle</button>
                    <a href="kuponlar.php" class="btn btn-secondary ms-2">Listeye Geri Dön</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>