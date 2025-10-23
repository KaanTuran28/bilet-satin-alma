<?php
require_once 'auth_check.php';

$message = '';
$company_id = $_SESSION['company_id'];
$trip_id = $_GET['id'] ?? null;

if (!$trip_id || !is_numeric($trip_id)) {
    header("Location: seferler.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $departure = trim($_POST['departure_location']);
    $arrival = trim($_POST['arrival_location']);
    $departure_time = $_POST['departure_time'];
    $price = $_POST['price'];
    $seat_count = $_POST['seat_count'];
    
    if (empty($departure) || empty($arrival) || empty($departure_time) || empty($price) || empty($seat_count)) {
        $message = '<div class="alert alert-danger">Tüm alanlar zorunludur.</div>';
    } else {
        try {
            $arrival_time = date('Y-m-d H:i:s', strtotime($departure_time . ' +3 hours'));
            
            $stmt = $pdo->prepare(
                "UPDATE trips SET departure_location = ?, arrival_location = ?, departure_time = ?, arrival_time = ?, price = ?, seat_count = ?
                 WHERE id = ? AND company_id = ?"
            );
            $stmt->execute([$departure, $arrival, $departure_time, $arrival_time, $price, $seat_count, $trip_id, $company_id]);

            if ($stmt->rowCount() > 0) {
                 $message = '<div class="alert alert-success">Sefer başarıyla güncellendi.</div>';
            } else {
                 $message = '<div class="alert alert-warning">Hiçbir değişiklik yapılmadı.</div>';
            }
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Güncelleme sırasında bir hata oluştu: ' . $e->getMessage() . '</div>';
        }
    }
}

try {
    $stmt = $pdo->prepare("SELECT * FROM trips WHERE id = ? AND company_id = ?");
    $stmt->execute([$trip_id, $company_id]);
    $trip = $stmt->fetch();

    if (!$trip) {
        header("Location: seferler.php");
        exit();
    }
} catch (PDOException $e) {
    die("Sefer bilgileri getirilemedi: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sefer Düzenle - Firma Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../public/assets/css/admin.css">
</head>
<body class="page-form">

<div class="sidebar">
    <h3>Firma Paneli</h3>
    <a href="index.php"><i class="bi bi-house-door-fill"></i> Ana Sayfa</a>
    <a href="seferler.php" class="active"><i class="bi bi-sign-turn-right-fill"></i> Sefer Yönetimi</a>
    <a href="kuponlar.php"><i class="bi bi-tag-fill"></i> Kupon Yönetimi</a>
    <a href="../public/logout.php"><i class="bi bi-box-arrow-right"></i> Çıkış Yap</a>
</div>

<div class="main-content">
    <div class="header">
        <h1>Sefer Düzenle</h1>
    </div>
    
    <?php if (!empty($message)) echo $message; ?>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Sefer Bilgilerini Güncelle</h5>
            <hr>
            <form method="POST">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="departure_location" class="form-label">Kalkış Noktası</label>
                        <input type="text" class="form-control" id="departure_location" name="departure_location" value="<?php echo htmlspecialchars($trip['departure_location']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="arrival_location" class="form-label">Varış Noktası</label>
                        <input type="text" class="form-control" id="arrival_location" name="arrival_location" value="<?php echo htmlspecialchars($trip['arrival_location']); ?>" required>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="departure_time" class="form-label">Kalkış Tarihi ve Saati</label>
                        <input type="datetime-local" class="form-control" id="departure_time" name="departure_time" value="<?php echo date('Y-m-d\TH:i', strtotime($trip['departure_time'])); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="price" class="form-label">Fiyat</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($trip['price']); ?>" step="0.01" required>
                            <span class="input-group-text">TL</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="seat_count" class="form-label">Koltuk Sayısı</label>
                        <input type="number" class="form-control" id="seat_count" name="seat_count" value="<?php echo htmlspecialchars($trip['seat_count']); ?>" required>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Değişiklikleri Kaydet</button>
                    <a href="seferler.php" class="btn btn-secondary ms-2">Listeye Geri Dön</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>