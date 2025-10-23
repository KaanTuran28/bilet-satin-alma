<?php
require_once __DIR__ . '/../config/init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

if (isset($_GET['action']) && $_GET['action'] === 'cancel' && isset($_GET['ticket_id'])) {

    if (!validate_csrf_token($_GET['csrf_token'] ?? '')) {
        die('Geçersiz CSRF token! İşlem reddedildi.');
    }

    $ticket_id_to_cancel = $_GET['ticket_id'];
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("SELECT t.id as ticket_id, t.price_paid, tr.departure_time FROM tickets t JOIN trips tr ON t.trip_id = tr.id WHERE t.id = ? AND t.user_id = ? AND t.status = 'active'");
        $stmt->execute([$ticket_id_to_cancel, $user_id]);
        $ticket_info = $stmt->fetch();

        if (!$ticket_info) { throw new Exception("İptal edilecek bilet bulunamadı veya bu bilete erişim yetkiniz yok."); }

        $hours_until_departure = (strtotime($ticket_info['departure_time']) - time()) / 3600;
        if ($hours_until_departure <= 1) { throw new Exception("Kalkışa 1 saat veya daha az bir süre kaldığı için bilet iptal edilemez."); }

        $stmt = $pdo->prepare("UPDATE tickets SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$ticket_id_to_cancel]);

        $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt->execute([$ticket_info['price_paid'], $user_id]);

        $pdo->commit();
        $message = '<div class="alert alert-success">Biletiniz başarıyla iptal edildi ve ücreti hesabınıza iade edildi.</div>';
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = '<div class="alert alert-danger">İptal işlemi başarısız: ' . $e->getMessage() . '</div>';
    }
}

try {
    $stmt = $pdo->prepare("SELECT fullname, email, balance FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT t.id as ticket_id, t.seat_number, t.status, t.purchase_time, t.price_paid, tr.departure_location, tr.arrival_location, tr.departure_time, c.name as company_name FROM tickets t JOIN trips tr ON t.trip_id = tr.id JOIN companies c ON tr.company_id = c.id WHERE t.user_id = ? ORDER BY tr.departure_time DESC");
    $stmt->execute([$user_id]);
    $tickets = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Bilgiler getirilemedi: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Hesabım - Bilet Platformu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="page-hesabim">

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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Hesabım</h1>
        <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Ana Sayfaya Dön</a>
    </div>

    <?php if (!empty($message)) echo $message; ?>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title"><i class="bi bi-person-circle"></i> Profil Bilgileri</h5><hr>
            <div class="row align-items-center">
                <div class="col-md-8">
                    <p class="mb-2"><strong>Ad Soyad:</strong> <?php echo htmlspecialchars($user['fullname']); ?></p>
                    <p class="mb-0"><strong>E-posta:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                <div class="col-md-4 text-md-end">
                    <p class="fs-4 mb-0"><strong>Bakiye:</strong><br><span class="text-success fw-bold"><?php echo htmlspecialchars(number_format($user['balance'], 2)); ?> TL</span></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><i class="bi bi-ticket-detailed"></i> Biletlerim</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mt-3">
                    <thead class="table-light">
                        <tr><th>Firma</th><th>Güzergah</th><th>Kalkış</th><th>Koltuk</th><th>Tutar</th><th>Durum</th><th>İşlemler</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tickets)): ?>
                            <tr><td colspan="7" class="text-center p-4">Henüz hiç bilet satın almadınız.</td></tr>
                        <?php else: ?>
                            <?php foreach($tickets as $ticket): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($ticket['company_name']); ?></td>
                                    <td><?php echo htmlspecialchars($ticket['departure_location']); ?><br>&rarr; <?php echo htmlspecialchars($ticket['arrival_location']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($ticket['departure_time'])); ?></td>
                                    <td><span class="badge bg-dark fs-6"><?php echo htmlspecialchars($ticket['seat_number']); ?></span></td>
                                    <td><?php echo htmlspecialchars(number_format($ticket['price_paid'], 2)); ?> TL</td>
                                    <td>
                                        <?php if ($ticket['status'] === 'active'): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">İptal</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($ticket['status'] === 'active'):
                                            $departure_iso = date('Y-m-d\TH:i:s', strtotime($ticket['departure_time']));
                                            $cancel_url = "hesabim.php?action=cancel&ticket_id={$ticket['ticket_id']}&csrf_token=" . generate_csrf_token();
                                        ?>
                                            <a href="<?php echo $cancel_url; ?>" 
                                               class="btn btn-danger btn-sm cancel-button" 
                                               data-departure-time="<?php echo $departure_iso; ?>">
                                               İptal Et
                                            </a>
                                        <?php endif; ?>
                                        <a href="bilet_pdf.php?ticket_id=<?php echo $ticket['ticket_id']; ?>" class="btn btn-primary btn-sm" target="_blank">PDF</a>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cancelButtons = document.querySelectorAll('.cancel-button');
    cancelButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault(); 
            const departureTime = this.getAttribute('data-departure-time');
            const cancelUrl = this.href; 
            const departureTimestamp = new Date(departureTime).getTime();
            const currentTimestamp = new Date().getTime();
            const hoursUntilDeparture = (departureTimestamp - currentTimestamp) / (1000 * 60 * 60);

            if (hoursUntilDeparture <= 1) {
                alert('Kalkışa 1 saat veya daha az bir süre kaldığı için bu bilet iptal edilemez.');
            } else {
                if (confirm('Bu bileti iptal etmek istediğinizden emin misiniz? Ücreti hesabınıza iade edilecektir.')) {
                    window.location.href = cancelUrl;
                }
            }
        });
    });
});
</script>
</body>
</html>