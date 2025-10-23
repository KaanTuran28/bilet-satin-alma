<?php 
require_once __DIR__ . '/../config/init.php'; 

$departure_search = trim($_GET['departure'] ?? '');
$arrival_search = trim($_GET['arrival'] ?? '');
try {
    $sql = "SELECT trips.*, companies.name as company_name FROM trips JOIN companies ON trips.company_id = companies.id";
    $params = [];
    $where_clauses = [];
    if (!empty($departure_search)) {
        $where_clauses[] = "departure_location LIKE ?";
        $params[] = '%' . $departure_search . '%';
    }
    if (!empty($arrival_search)) {
        $where_clauses[] = "arrival_location LIKE ?";
        $params[] = '%' . $arrival_search . '%';
    }
    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(' AND ', $where_clauses);
    }
    $sql .= " ORDER BY departure_time ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
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
    <title>Ana Sayfa - Bilet Platformu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="page-index">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">Bilet Platformu</a>
    <div class="ms-auto">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="hesabim.php" class="btn btn-outline-light me-2"><i class="bi bi-person"></i> Hesabım</a>
            <a href="logout.php" class="btn btn-danger"><i class="bi bi-box-arrow-right"></i> Çıkış Yap</a>
        <?php else: ?>
            <a href="login.php" class="btn btn-outline-light me-2">Giriş Yap</a>
            <a href="register.php" class="btn btn-primary">Kayıt Ol</a>
        <?php endif; ?>
    </div>
  </div>
</nav>

<main class="container my-4">
    <div class="card shadow-sm mb-4">
        <div class="card-body p-4">
            <h2 class="card-title mb-3">Sefer Ara</h2>
            <form action="index.php" method="GET" class="row g-3 align-items-center">
                <div class="col-md">
                    <input type="text" class="form-control form-control-lg" name="departure" placeholder="Nereden?" value="<?php echo htmlspecialchars($departure_search); ?>">
                </div>
                <div class="col-md">
                    <input type="text" class="form-control form-control-lg" name="arrival" placeholder="Nereye?" value="<?php echo htmlspecialchars($arrival_search); ?>">
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-danger btn-lg w-100"><i class="bi bi-search"></i> Sefer Bul</button>
                </div>
            </form>
        </div>
    </div>

    <h1 class="mb-4">Uygun Seferler</h1>
    <?php if (empty($trips)): ?>
        <div class="alert alert-warning">Aradığınız kriterlere uygun sefer bulunamadı.</div>
    <?php else: ?>
        <?php foreach ($trips as $trip): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="text-primary"><?php echo htmlspecialchars($trip['company_name']); ?></h4>
                            <a href="sefer_detay.php?id=<?php echo $trip['id']; ?>" class="text-decoration-none text-dark">
                                <p class="fs-5 mb-1">
                                    <strong><?php echo htmlspecialchars($trip['departure_location']); ?></strong> 
                                    <i class="bi bi-arrow-right mx-2"></i> 
                                    <strong><?php echo htmlspecialchars($trip['arrival_location']); ?></strong>
                                </p>
                            </a>
                            <p class="text-muted">
                                <i class="bi bi-calendar-event"></i> Kalkış: <?php echo date('d/m/Y H:i', strtotime($trip['departure_time'])); ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <h3 class="text-success mb-2"><?php echo htmlspecialchars($trip['price']); ?> TL</h3>
                            <a href="sefer_detay.php?id=<?php echo $trip['id']; ?>" class="btn btn-outline-primary">
                                <i class="bi bi-info-circle"></i> Sefer Detayları
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>