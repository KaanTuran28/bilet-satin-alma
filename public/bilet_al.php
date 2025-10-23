<?php
require_once __DIR__ . '/../config/init.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php?status=login_required");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Geçersiz CSRF token! İşlem reddedildi.');
    }
}

$trip_id = $_GET['trip_id'] ?? null;
$user_id = $_SESSION['user_id'];
$message = '';
$coupon_code_input = trim($_POST['coupon_code'] ?? '');
$selected_seats_on_load = $_POST['seat_number'] ?? []; 
$applied_coupon = null;

if (!$trip_id) { header("Location: index.php"); exit(); }

try {
    $stmt = $pdo->prepare("SELECT t.*, c.name as company_name FROM trips t JOIN companies c ON t.company_id = c.id WHERE t.id = ?");
    $stmt->execute([$trip_id]);
    $trip = $stmt->fetch();
    if (!$trip) { header("Location: index.php"); exit(); }

    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $balance = $stmt->fetchColumn();
} catch (PDOException $e) { die("Sayfa verileri yüklenemedi: " . $e->getMessage()); }

$original_price_per_ticket = $trip['price'];
$price_to_pay_per_ticket = $original_price_per_ticket;

if (!empty($coupon_code_input)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ?");
        $stmt->execute([$coupon_code_input]);
        $coupon = $stmt->fetch();
        $today = date('Y-m-d');
        if (!$coupon) { $message = '<div class="alert alert-danger">Geçersiz kupon kodu.</div>';
        } elseif ($coupon['usage_limit'] <= 0) { $message = '<div class="alert alert-danger">Bu kuponun kullanım limiti dolmuştur.</div>';
        } elseif ($today > $coupon['expiration_date']) { $message = '<div class="alert alert-danger">Bu kuponun süresi dolmuştur.</div>';
        } elseif ($coupon['company_id'] !== null && $coupon['company_id'] != $trip['company_id']) { $message = '<div class="alert alert-danger">Bu kupon bu firma için geçerli değildir.</div>';
        } else {
            $applied_coupon = $coupon;
            $price_to_pay_per_ticket = $original_price_per_ticket * (1 - $applied_coupon['discount_rate']);
            if (isset($_POST['apply_coupon'])) { $message = '<div class="alert alert-success">Kupon başarıyla uygulandı!</div>'; }
        }
    } catch (PDOException $e) { $message = '<div class="alert alert-danger">Kupon kontrolü sırasında bir hata oluştu.</div>'; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy_ticket'])) {
    $selected_seats = $_POST['seat_number'] ?? [];
    $coupon_id_to_use = !empty($_POST['coupon_id']) ? $_POST['coupon_id'] : null;
    if (empty($selected_seats)) { $message = '<div class="alert alert-danger">Lütfen en az bir koltuk seçin.</div>';
    } else {
        $total_price = $price_to_pay_per_ticket * count($selected_seats);
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user_balance = $stmt->fetchColumn();
            if ($user_balance < $total_price) { throw new Exception("Yetersiz bakiye. Gerekli tutar: {$total_price} TL"); }
            
            foreach ($selected_seats as $seat) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE trip_id = ? AND seat_number = ? AND status = 'active'");
                $stmt->execute([$trip_id, $seat]);
                if ($stmt->fetchColumn() > 0) { throw new Exception("Seçtiğiniz {$seat} numaralı koltuk siz işlem yaparken başkası tarafından satın alındı."); }
            }
            $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            $stmt->execute([$total_price, $user_id]);
            $insert_stmt = $pdo->prepare("INSERT INTO tickets (user_id, trip_id, seat_number, status, price_paid) VALUES (?, ?, ?, 'active', ?)");
            foreach ($selected_seats as $seat) { $insert_stmt->execute([$user_id, $trip_id, $seat, $price_to_pay_per_ticket]); }
            if ($coupon_id_to_use) {
                $stmt = $pdo->prepare("UPDATE coupons SET usage_limit = usage_limit - 1 WHERE id = ?");
                $stmt->execute([$coupon_id_to_use]);
            }
            $pdo->commit();
            $message = '<div class="alert alert-success">Biletleriniz başarıyla satın alındı! Hesabım sayfasına yönlendiriliyorsunuz...</div>';
            header("Refresh:3; url=hesabim.php");
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = '<div class="alert alert-danger">Bilet alınamadı: ' . $e->getMessage() . '</div>';
        }
    }
}

try {
    $stmt = $pdo->prepare("SELECT seat_number FROM tickets WHERE trip_id = ? AND status = 'active'");
    $stmt->execute([$trip_id]);
    $sold_seats = array_column($stmt->fetchAll(), 'seat_number');
} catch (PDOException $e) { die("Koltuk bilgileri yüklenemedi: " . $e->getMessage()); }
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilet Satın Al - Bilet Platformu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>

    .navbar {
        padding-top: 1.8rem;
        padding-bottom: 1.8rem;
    }

        .seat-layout { display: grid; grid-template-columns: 1fr 1fr 0.5fr 1fr; gap: 10px; max-width: 280px; margin: 20px auto; user-select: none; }
        .seat { width: 40px; height: 40px; display: flex; justify-content: center; align-items: center; border: 2px solid #dee2e6; border-radius: 8px; font-weight: bold; background-color: #fff; cursor: pointer; transition: all 0.2s ease; }
        .seat.aisle, .seat.driver { border: none; background: none; cursor: default; }
        .seat.driver i { font-size: 2.5rem; color: #6c757d; }
        .seat.sold { background-color: #dc3545; color: white; border-color: #dc3545; cursor: not-allowed; }
        .seat.selected { background-color: #198754; color: white; border-color: #198754; transform: scale(1.1); }
        .seat:not(.sold):not(.aisle):not(.driver):hover { transform: scale(1.1); border-color: #0d6efd; }
        .seat-checkbox { display: none; }
        .legend { list-style: none; padding: 0; display: flex; justify-content: center; gap: 20px; margin-top: 20px; }
        .legend li { display: flex; align-items: center; gap: 8px; }
        .legend .color-box { width: 20px; height: 20px; border-radius: 4px; border: 1px solid #ccc; }
</style>
</head>
<body class="page-bilet-al">    

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
    <form method="POST" action="bilet_al.php?trip_id=<?php echo $trip_id; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                             <div>
                                <h2 class="mb-0"><?php echo htmlspecialchars($trip['departure_location']); ?> &rarr; <?php echo htmlspecialchars($trip['arrival_location']); ?></h2>
                                <p class="lead text-muted mb-0"><?php echo htmlspecialchars($trip['company_name']); ?> / <?php echo date('d.m.Y H:i', strtotime($trip['departure_time'])); ?></p>
                            </div>
                            <a href="index.php" class="btn btn-outline-secondary d-none d-lg-block"><i class="bi bi-arrow-left"></i> Sefer Değiştir</a>
                        </div>
                        <hr>
                        <?php if (!empty($message)) echo $message; ?>
                        
                        <div class="seat-layout-container mt-4">
                             <div class="seat-layout">
                                <div class="seat driver"><i class="bi bi-person-workspace"></i></div>
                                <div class="seat aisle"></div><div class="seat aisle"></div><div class="seat aisle"></div>
                                <?php 
                                $seat_counter = 1;
                                for ($i = 1; $i <= $trip['seat_count']; $i++):
                                    $is_sold = in_array($seat_counter, $sold_seats);
                                    $is_selected = in_array($seat_counter, $selected_seats_on_load);
                                ?>
                                    <label class="seat <?php echo $is_sold ? 'sold' : ''; echo $is_selected ? ' selected' : ''; ?>" for="seat-<?php echo $seat_counter; ?>">
                                        <input type="checkbox" name="seat_number[]" value="<?php echo $seat_counter; ?>" id="seat-<?php echo $seat_counter; ?>" class="seat-checkbox" <?php echo $is_sold ? 'disabled' : ''; echo $is_selected ? ' checked' : ''; ?>>
                                        <?php echo $seat_counter; ?>
                                    </label>
                                <?php 
                                    if ($seat_counter % 3 == 2) { echo '<div class="seat aisle"></div>'; }
                                    $seat_counter++;
                                endfor; 
                                ?>
                            </div>
                            <ul class="legend">
                                <li><span class="color-box" style="background: #fff;"></span> Boş</li>
                                <li><span class="color-box" style="background: #198754;"></span> Seçili</li>
                                <li><span class="color-box" style="background: #dc3545;"></span> Dolu</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title">Yolculuk Özeti</h4>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span>Koltuk Başı Fiyat:</span>
                            <span><?php echo htmlspecialchars(number_format($price_to_pay_per_ticket, 2)); ?> TL</span>
                        </div>
                         <div class="mb-3">
                            <label for="coupon_code" class="form-label mt-3">İndirim Kuponu</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="coupon_code" name="coupon_code" placeholder="Kupon Kodu" value="<?php echo htmlspecialchars($coupon_code_input); ?>">
                                <button class="btn btn-secondary" type="submit" name="apply_coupon">Uygula</button>
                            </div>
                        </div>
                        <hr>
                        <h6>Seçilen Koltuklar</h6>
                        <ul id="selected-seats-list" class="list-group list-group-flush mb-3">
                            <li class="list-group-item text-muted">Lütfen koltuk seçin...</li>
                        </ul>
                        <hr>
                        <div class="d-flex justify-content-between fs-4 fw-bold">
                            <span>Toplam Tutar:</span>
                            <span class="text-primary" id="total-amount">0,00 TL</span>
                        </div>
                        <div class="d-grid mt-3">
                            <button type="submit" name="buy_ticket" class="btn btn-success btn-lg"><i class="bi bi-lock-fill"></i> Güvenli Ödeme Yap</button>
                        </div>
                    </div>
                </div>
                 <div class="card mt-3">
                    <div class="card-body text-center">
                        <p class="mb-1">Bakiyeniz: <strong class="text-success fs-5"><?php echo htmlspecialchars(number_format($balance, 2)); ?> TL</strong></p>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="coupon_id" value="<?php echo $applied_coupon['id'] ?? ''; ?>">
    </form>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const seatCheckboxes = document.querySelectorAll('.seat-checkbox');
    const totalPriceDisplay = document.getElementById('total-amount');
    const selectedSeatsList = document.getElementById('selected-seats-list');
    const pricePerTicket = <?php echo $price_to_pay_per_ticket; ?>;

    function updateTotal() {
        const selectedSeats = document.querySelectorAll('.seat-checkbox:checked');
        const selectedCount = selectedSeats.length;
        const totalAmount = selectedCount * pricePerTicket;

        totalPriceDisplay.textContent = totalAmount.toLocaleString('tr-TR', { minimumFractionDigits: 2 }) + ' TL';

        selectedSeatsList.innerHTML = '';
        if (selectedCount === 0) {
            selectedSeatsList.innerHTML = '<li class="list-group-item text-muted">Lütfen koltuk seçin...</li>';
        } else {
            selectedSeats.forEach(seat => {
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between';
                li.innerHTML = `<span>Koltuk No: <strong>${seat.value}</strong></span> <span>${pricePerTicket.toLocaleString('tr-TR', { minimumFractionDigits: 2 })} TL</span>`;
                selectedSeatsList.appendChild(li);
            });
        }
    }

    seatCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            checkbox.parentElement.classList.toggle('selected', checkbox.checked);
            updateTotal();
        });
    });

    updateTotal();
});
</script>
</body>
</html>