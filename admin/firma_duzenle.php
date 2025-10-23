<?php
require_once 'auth_check.php';

$message = '';
$company_name = '';
$company_id = $_GET['id'] ?? null;

if (!$company_id || !is_numeric($company_id)) {
    header("Location: firmalar.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_company'])) {
    $new_company_name = trim($_POST['name']);
    $company_id_to_update = $_POST['id'];

    if (empty($new_company_name)) {
        $message = '<div class="alert alert-danger">Firma adı boş olamaz.</div>';
        $company_name = '';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE companies SET name = ? WHERE id = ?");
            $stmt->execute([$new_company_name, $company_id_to_update]);
            header("Location: firmalar.php?status=updated");
            exit();
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Firma güncellenemedi: ' . $e->getMessage() . '</div>';
            $company_name = $new_company_name; 
        }
    }
} else {

    try {
        $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ?");
        $stmt->execute([$company_id]);
        $company = $stmt->fetch();
        if ($company) {
            $company_name = $company['name'];
        } else {
            header("Location: firmalar.php?status=notfound");
            exit();
        }
    } catch (PDOException $e) {
        die("Firma bilgileri getirilemedi: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Düzenle - Admin Paneli</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="../public/assets/css/admin.css">
</head>
<body>

<div class="container-fluid">
    <div class="row">

        <nav class="col-md-2 bg-dark text-white min-vh-100 p-3">
            <h3 class="text-center mb-4">Admin Paneli</h3>
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
            <h1 class="mb-4">Firma Düzenle</h1>
            <?php echo $message; ?>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="firma_duzenle.php?id=<?php echo htmlspecialchars($company_id); ?>" method="POST" class="row g-3">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($company_id); ?>">

                        <div class="col-md-6">
                            <label for="name" class="form-label">Firma Adı</label>
                            <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($company_name); ?>" required>
                        </div>

                        <div class="col-12 d-flex justify-content-between mt-3">
                            <button type="submit" name="update_company" class="btn btn-primary">Güncelle</button>
                            <a href="firmalar.php" class="btn btn-secondary">Geri Dön</a>
                        </div>
                    </form>
                </div>
            </div>

        </main>
    </div>
</div>

</body>
</html>
