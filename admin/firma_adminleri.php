<?php
require_once 'auth_check.php';

$message = '';

if (isset($_GET['status'])) {
    if ($_GET['status'] === 'updated') {
        $message = '<div class="alert alert-success">Firma Admin bilgileri başarıyla güncellendi.</div>';
    }
    if ($_GET['status'] === 'deleted') {
        $message = '<div class="alert alert-success">Firma Admin başarıyla silindi.</div>';
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {

    if ($_GET['id'] == $_SESSION['user_id']) {
        $message = '<div class="alert alert-danger">Kendi hesabınızı silemezsiniz.</div>';
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'firma_admin'");
            $stmt->execute([$_GET['id']]);
            header("Location: firma_adminleri.php?status=deleted");
            exit();
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Kullanıcı silinemedi: ' . $e->getMessage() . '</div>';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_firma_admin'])) {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $company_id = $_POST['company_id'];

    if (empty($fullname) || empty($email) || empty($password) || empty($company_id)) {
        $message = '<div class="alert alert-danger">Tüm alanların doldurulması zorunludur.</div>';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $message = '<div class="alert alert-warning">Bu e-posta adresi zaten kullanılıyor.</div>';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password, role, company_id) VALUES (?, ?, ?, 'firma_admin', ?)");
                $stmt->execute([$fullname, $email, $hashed_password, $company_id]);
                $message = '<div class="alert alert-success">Firma Admin başarıyla oluşturuldu.</div>';
            }
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Veritabanı hatası: ' . $e->getMessage() . '</div>';
        }
    }
}

try {
    $companies_stmt = $pdo->query("SELECT id, name FROM companies ORDER BY name ASC");
    $companies = $companies_stmt->fetchAll();
    $admins_stmt = $pdo->query("
        SELECT users.id, users.fullname, users.email, companies.name AS company_name 
        FROM users 
        JOIN companies ON users.company_id = companies.id 
        WHERE users.role = 'firma_admin' 
        ORDER BY users.fullname ASC
    ");
    $firma_admins = $admins_stmt->fetchAll();
} catch (PDOException $e) {
    die("Veri çekme hatası: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Firma Admin Yönetimi - Admin Paneli</title>
    <link rel="stylesheet" href="../public/assets/css/admin.css">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="page-list">

<div class="sidebar">
    <h3 style="color: white; text-align: center;">Admin Paneli</h3>
    <a href="index.php">Ana Sayfa</a>
    <a href="firmalar.php">Firma Yönetimi</a>
    <a href="firma_adminleri.php" class="active">Firma Admin Yönetimi</a>
    <a href="kuponlar.php">Kupon Yönetimi</a>
    <a href="../public/index.php" target="_blank">Siteyi Görüntüle</a>
    <a href="../public/logout.php">Çıkış Yap</a>
</div>

<div class="main-content container mt-4">
    <h1>Firma Admin Yönetimi</h1><hr>
    <?php echo $message; ?>

    <div class="form-section mb-4">
        <h2>Yeni Firma Admin Ekle</h2>
        <form action="firma_adminleri.php" method="POST" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="fullname" class="form-control" placeholder="Ad Soyad" required>
            </div>
            <div class="col-md-3">
                <input type="email" name="email" class="form-control" placeholder="E-posta" required>
            </div>
            <div class="col-md-3">
                <input type="password" name="password" class="form-control" placeholder="Şifre" required>
            </div>
            <div class="col-md-2">
                <select name="company_id" class="form-select" required>
                    <option value="">Firma Seçin</option>
                    <?php foreach ($companies as $company): ?>
                        <option value="<?php echo $company['id']; ?>"><?php echo htmlspecialchars($company['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1 d-grid">
                <button type="submit" name="add_firma_admin" class="btn btn-primary">Ekle</button>
            </div>
        </form>
    </div>

    <div class="table-section">
        <h2>Mevcut Firma Adminleri</h2>
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Ad Soyad</th>
                    <th>E-posta</th>
                    <th>Atandığı Firma</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($firma_admins as $admin): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($admin['id']); ?></td>
                        <td><?php echo htmlspecialchars($admin['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($admin['email']); ?></td>
                        <td><?php echo htmlspecialchars($admin['company_name']); ?></td>
                        <td>
                            <a href="firma_admin_duzenle.php?id=<?php echo $admin['id']; ?>" class="btn btn-sm btn-warning">Düzenle</a>
                            <a href="firma_adminleri.php?action=delete&id=<?php echo $admin['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?');">Sil</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
