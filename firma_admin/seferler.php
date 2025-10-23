<?php
require_once 'auth_check.php';

$message = '';
$company_id = $_SESSION['company_id'];

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $trip_id_to_delete = $_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM trips WHERE id = ? AND company_id = ?");
        $stmt->execute([$trip_id_to_delete, $company_id]);
        
        if ($stmt->rowCount() > 0) {
            $message = '<div class="alert alert-success">Sefer başarıyla silindi.</div>';
        } else {
            $message = '<div class="alert alert-danger">İşlem başarısız. Bu sefere müdahale etme yetkiniz olmayabilir.</div>';
        }
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Sefer silinemedi. Bu sefere ait satılmış biletler olabilir.</div>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_trip'])) {
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
                "INSERT INTO trips (company_id, departure_location, arrival_location, departure_time, arrival_time, price, seat_count) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$company_id, $departure, $arrival, $departure_time, $arrival_time, $price, $seat_count]);
            $message = '<div class="alert alert-success">Yeni sefer başarıyla eklendi.</div>';
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Sefer eklenemedi: ' . $e->getMessage() . '</div>';
        }
    }
}

try {
    $stmt = $pdo->prepare("SELECT * FROM trips WHERE company_id = ? ORDER BY departure_time DESC");
    $stmt->execute([$company_id]);
    $trips = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Seferler getirilemedi: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sefer Yönetimi - Firma Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../public/assets/css/admin.css">
</head>
<body class="page-list">

<div class="sidebar">
    <h3>Firma Paneli</h3>
    <a href="index.php"><i class="bi bi-house-door-fill"></i> Ana Sayfa</a>
    <a href="seferler.php" class="active"><i class="bi bi-sign-turn-right-fill"></i> Sefer Yönetimi</a>
    <a href="kuponlar.php"><i class="bi bi-tag-fill"></i> Kupon Yönetimi</a>
    <a href="../public/logout.php"><i class="bi bi-box-arrow-right"></i> Çıkış Yap</a>
</div>

<div class="main-content">
    <div class="header">
        <h1>Sefer Yönetimi</h1>
    </div>
    
    <?php if (!empty($message)) echo $message; ?>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Yeni Sefer Ekle</h5>
            <hr>
            <form method="POST">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="departure_location" class="form-label">Kalkış Noktası</label>
                        <input type="text" class="form-control" id="departure_location" name="departure_location" required>
                    </div>
                    <div class="col-md-6">
                        <label for="arrival_location" class="form-label">Varış Noktası</label>
                        <input type="text" class="form-control" id="arrival_location" name="arrival_location" required>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="departure_time" class="form-label">Kalkış Tarihi ve Saati</label>
                        <input type="datetime-local" class="form-control" id="departure_time" name="departure_time" required>
                    </div>
                    <div class="col-md-4">
                        <label for="price" class="form-label">Fiyat</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                            <span class="input-group-text">TL</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="seat_count" class="form-label">Koltuk Sayısı</label>
                        <input type="number" class="form-control" id="seat_count" name="seat_count" required>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" name="add_trip" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Yeni Sefer Ekle</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Mevcut Seferleriniz</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mt-3">
                    <thead class="table-light">
                        <tr><th>Kalkış</th><th>Varış</th><th>Kalkış Zamanı</th><th>Fiyat</th><th>Koltuk</th><th>İşlemler</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($trips)): ?>
                            <tr><td colspan="6" class="text-center p-4">Henüz hiç sefer eklemediniz.</td></tr>
                        <?php else: ?>
                            <?php foreach($trips as $trip): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($trip['departure_location']); ?></td>
                                <td><?php echo htmlspecialchars($trip['arrival_location']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($trip['departure_time'])); ?></td>
                                <td><?php echo htmlspecialchars($trip['price']); ?> TL</td>
                                <td><?php echo htmlspecialchars($trip['seat_count']); ?></td>
                                <td>
                                    <a href="sefer_duzenle.php?id=<?php echo $trip['id']; ?>" class="btn btn-warning btn-sm">Düzenle</a>
                                    <a href="seferler.php?action=delete&id=<?php echo $trip['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bu seferi silmek istediğinizden emin misiniz?');">Sil</a>
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