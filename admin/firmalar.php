<?php
require_once 'auth_check.php';

$message = '';
$companies = [];

if (isset($_GET['status'])) {
    if ($_GET['status'] === 'updated') $message = '<div class="alert alert-success">Firma başarıyla güncellendi.</div>';
    if ($_GET['status'] === 'notfound') $message = '<div class="alert alert-danger">Firma bulunamadı.</div>';
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $company_id_to_delete = $_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM companies WHERE id = ?");
        $stmt->execute([$company_id_to_delete]);
        $message = '<div class="alert alert-success">Firma başarıyla silindi.</div>';
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Firma silinemedi. Bu firmaya ait seferler olabilir.</div>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_company'])) {
    $company_name = trim($_POST['name']);
    if (empty($company_name)) {
        $message = '<div class="alert alert-danger">Firma adı boş olamaz.</div>';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM companies WHERE name = ?");
            $stmt->execute([$company_name]);
            if ($stmt->fetchColumn() > 0) {
                $message = '<div class="alert alert-warning">Bu isimde bir firma zaten mevcut.</div>';
            } else {
                $stmt = $pdo->prepare("INSERT INTO companies (name) VALUES (?)");
                $stmt->execute([$company_name]);
                $message = '<div class="alert alert-success">Firma başarıyla eklendi.</div>';
            }
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Veritabanı hatası: ' . $e->getMessage() . '</div>';
        }
    }
}

try {
    $companies_stmt = $pdo->query("SELECT * FROM companies ORDER BY name ASC");
    $companies = $companies_stmt->fetchAll();
} catch (PDOException $e) {
    die("Firmalar getirilemedi: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Firma Yönetimi - Admin Paneli</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 bg-dark text-white min-vh-100 p-3">
            <h3 class="text-center">Admin Paneli</h3>
            <ul class="nav flex-column">
                <li class="nav-item"><a href="index.php" class="nav-link text-white">Ana Sayfa</a></li>
                <li class="nav-item"><a href="firmalar.php" class="nav-link text-white active">Firma Yönetimi</a></li>
                <li class="nav-item"><a href="firma_adminleri.php" class="nav-link text-white">Firma Admin Yönetimi</a></li>
                <li class="nav-item"><a href="kuponlar.php" class="nav-link text-white">Kupon Yönetimi</a></li>
                <li class="nav-item"><a href="../public/index.php" target="_blank" class="nav-link text-white">Siteyi Görüntüle</a></li>
                <li class="nav-item"><a href="../public/logout.php" class="nav-link text-white">Çıkış Yap</a></li>
            </ul>
        </nav>
        <main class="col-md-10 p-4">
            <h1>Firma Yönetimi</h1>
            <hr>
            <?php echo $message; ?>
            <div class="card mb-4">
                <div class="card-header">Yeni Firma Ekle</div>
                <div class="card-body">
                    <form method="POST" action="firmalar.php">
                        <div class="input-group mb-3">
                            <input type="text" name="name" class="form-control" placeholder="Firma Adı" required>
                            <button class="btn btn-primary" type="submit" name="add_company">Ekle</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-header">Mevcut Firmalar</div>
                <div class="card-body">
                    <table class="table table-bordered table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Firma Adı</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($companies)): ?>
                                <tr><td colspan="3" class="text-center">Henüz hiç firma eklenmemiş.</td></tr>
                            <?php else: ?>
                                <?php foreach ($companies as $company): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($company['id']); ?></td>
                                    <td><?php echo htmlspecialchars($company['name']); ?></td>
                                    <td>
                                        <a href="firma_duzenle.php?id=<?php echo $company['id']; ?>" class="btn btn-sm btn-warning">Düzenle</a>
                                        <a href="firmalar.php?action=delete&id=<?php echo $company['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu firmayı silmek istediğinizden emin misiniz?');">Sil</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
