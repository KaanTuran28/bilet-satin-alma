<?php
require_once 'auth_check.php';

$message = '';
$admin_id = $_GET['id'] ?? null;

if (!$admin_id || !is_numeric($admin_id)) {
    header("Location: firma_adminleri.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $company_id = $_POST['company_id'];
    $user_id = $_POST['id'];

    if (empty($fullname) || empty($email) || empty($company_id)) {
        $message = '<div class="alert alert-danger">Name, Email, and Company fields cannot be empty.</div>';
    } else {
        try {
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET fullname = ?, email = ?, password = ?, company_id = ? WHERE id = ?");
                $stmt->execute([$fullname, $email, $hashed_password, $company_id, $user_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET fullname = ?, email = ?, company_id = ? WHERE id = ?");
                $stmt->execute([$fullname, $email, $company_id, $user_id]);
            }
            header("Location: firma_adminleri.php?status=updated");
            exit();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'UNIQUE constraint failed')) {
                $message = '<div class="alert alert-danger">This email address is already in use by another user.</div>';
            } else {
                $message = '<div class="alert alert-danger">An error occurred during the update: ' . $e->getMessage() . '</div>';
            }
        }
    }
}

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'firma_admin'");
    $stmt->execute([$admin_id]);
    $admin_user = $stmt->fetch();

    if (!$admin_user) {
        header("Location: firma_adminleri.php");
        exit();
    }
    
    $companies_stmt = $pdo->query("SELECT id, name FROM companies ORDER BY name ASC");
    $companies = $companies_stmt->fetchAll();

} catch (PDOException $e) {
    die("Data retrieval error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Admin Düzenle - Admin Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../public/assets/css/admin.css">
</head>
<body class="page-form">

<div class="sidebar">
    <h3 style="color: white; text-align: center;">Admin Paneli</h3>
    <a href="index.php"><i class="bi bi-house-door-fill"></i> Ana Sayfa</a>
    <a href="firmalar.php"><i class="bi bi-building"></i> Firma Yönetimi</a>
    <a href="firma_adminleri.php" class="active"><i class="bi bi-people-fill"></i> Firma Admin Yönetimi</a>
    <a href="kuponlar.php"><i class="bi bi-tag-fill"></i> Kupon Yönetimi</a>
    <a href="../public/index.php" target="_blank"><i class="bi bi-eye-fill"></i> Siteyi Görüntüle</a>
    <a href="../public/logout.php"><i class="bi bi-box-arrow-right"></i> Çıkış Yap</a>
</div>

<div class="main-content">
    <div class="header">
        <h1>Firma Admin Düzenle</h1>
    </div>
    
    <?php if (!empty($message)) echo $message; ?>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Kullanıcı Bilgilerini Güncelle</h5>
            <hr>
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($admin_user['id']); ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="fullname" class="form-label">Ad Soyad</label>
                        <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlspecialchars($admin_user['fullname']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">E-posta</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($admin_user['email']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label">Yeni Şifre (Değiştirmek istemiyorsanız boş bırakın)</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Yeni Şifre">
                    </div>
                    <div class="col-md-6">
                        <label for="company_id" class="form-label">Atandığı Firma</label>
                        <select class="form-select" id="company_id" name="company_id" required>
                            <?php foreach ($companies as $company): ?>
                                <option value="<?php echo $company['id']; ?>" <?php if ($company['id'] == $admin_user['company_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($company['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Değişiklikleri Kaydet</button>
                    <a href="firma_adminleri.php" class="btn btn-secondary ms-2">Listeye Geri Dön</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>