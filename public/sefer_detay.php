<?php
require_once __DIR__ . '/../config/init.php';

$trip_id = $_GET['id'] ?? null;

if (!$trip_id || !is_numeric($trip_id)) {

    header("Location: index.php");
    exit();
}

try {

    $stmt = $pdo->prepare("
        SELECT trips.*, companies.name as company_name 
        FROM trips 
        JOIN companies ON trips.company_id = companies.id 
        WHERE trips.id = ?
    ");
    $stmt->execute([$trip_id]);
    $trip = $stmt->fetch();

    if (!$trip) {

        die("Belirtilen sefer bulunamadı.");
    }

    $stmt_seats = $pdo->prepare("SELECT seat_number FROM tickets WHERE trip_id = ? AND status = 'active'");
    $stmt_seats->execute([$trip_id]);
    $sold_seats = array_column($stmt_seats->fetchAll(), 'seat_number');

    $dolu_koltuk_sayisi = count($sold_seats);
    $bos_koltuk_sayisi = $trip['seat_count'] - $dolu_koltuk_sayisi;

} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}

$page_title = 'Sefer Detayları';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Bilet Platformu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .seat-layout { display: grid; grid-template-columns: 1fr 1fr 0.5fr 1fr; gap: 10px; max-width: 280px; margin: 20px auto; }
        .seat { width: 40px; height: 40px; display: flex; justify-content: center; align-items: center; border: 2px solid #dee2e6; border-radius: 8px; font-weight: bold; background-color: #fff; }
        .seat.aisle, .seat.driver { border: none; background: none; }
        .seat.driver i { font-size: 2.5rem; color: #6c757d; }
        .seat.sold { background-color: #dc3545; color: white; border-color: #dc3545; }
        .legend { list-style: none; padding: 0; display: flex; justify-content: center; gap: 20px; margin-top: 20px; }
        .legend li { display: flex; align-items: center; gap: 8px; }
        .legend .color-box { width: 20px; height: 20px; border-radius: 4px; border: 1px solid #ccc; }
    </style>
</head>
<body>

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
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0"><?php echo htmlspecialchars($trip['company_name']); ?></h2>
        </div>
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-5 text-center"><h3 class="fw-light"><?php echo htmlspecialchars($trip['departure_location']); ?></h3></div>
                <div class="col-md-2 text-center"><i class="bi bi-arrow-right-circle" style="font-size: 2.5rem; color: var(--primary-color);"></i></div>
                <div class="col-md-5 text-center"><h3 class="fw-light"><?php echo htmlspecialchars($trip['arrival_location']); ?></h3></div>
            </div>
            <hr class="my-4">
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between"><strong>Kalkış Tarihi ve Saati:</strong> <span><?php echo date('d F Y, H:i', strtotime($trip['departure_time'])); ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><strong>Tahmini Varış Tarihi ve Saati:</strong> <span><?php echo date('d F Y, H:i', strtotime($trip['arrival_time'])); ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><strong>Toplam Koltuk Sayısı:</strong> <span><?php echo htmlspecialchars($trip['seat_count']); ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><strong>Dolu Koltuk Sayısı:</strong> <span class="fw-bold text-danger"><?php echo $dolu_koltuk_sayisi; ?></span></li>
                <li class="list-group-item d-flex justify-content-between"><strong>Boş Koltuk Sayısı:</strong> <span class="fw-bold text-success"><?php echo $bos_koltuk_sayisi; ?></span></li>
                <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Fiyat:</strong> <span class="fs-3 fw-bold text-success"><?php echo htmlspecialchars(number_format($trip['price'], 2)); ?> TL</span></li>
            </ul>

            <div class="mt-4 pt-3 border-top">
                <h4 class="text-center mb-3">Koltuk Durumu</h4>
                <div class="seat-layout">
                    <div class="seat driver"><i class="bi bi-person-workspace"></i></div>
                    <div class="seat aisle"></div><div class="seat aisle"></div><div class="seat aisle"></div>
                    <?php 
                    $seat_counter = 1;
                    for ($i = 1; $i <= $trip['seat_count']; $i++):
                        $is_sold = in_array($seat_counter, $sold_seats);
                    ?>
                        <div class="seat <?php echo $is_sold ? 'sold' : ''; ?>">
                            <?php echo $seat_counter; ?>
                        </div>
                    <?php 
                        if ($seat_counter % 3 == 2) { echo '<div class="seat aisle"></div>'; }
                        $seat_counter++;
                    endfor; 
                    ?>
                </div>
                <ul class="legend">
                    <li><span class="color-box" style="background: #fff;"></span> Boş</li>
                    <li><span class="color-box" style="background: #dc3545;"></span> Dolu</li>
                </ul>
            </div>
            
            <div class="text-center mt-4 border-top pt-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="bilet_al.php?trip_id=<?php echo $trip['id']; ?>&csrf_token=<?php echo generate_csrf_token(); ?>" class="btn btn-success btn-lg"><i class="bi bi-cart-check"></i> Bilet Al</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-success btn-lg visitor-buy-btn"><i class="bi bi-cart-check"></i> Bilet Al</a>
                <?php endif; ?>
                <a href="index.php" class="btn btn-secondary btn-lg ms-2"><i class="bi bi-arrow-left"></i> Geri Dön</a>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const visitorButton = document.querySelector('.visitor-buy-btn');
    if (visitorButton) {
        visitorButton.addEventListener('click', function(event) {
            event.preventDefault(); 
            alert('Bilet alabilmek için lütfen giriş yapınız.');
            window.location.href = this.href;
        });
    }
});
</script>
</body>
</html>