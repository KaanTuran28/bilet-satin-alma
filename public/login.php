<?php
require_once __DIR__ . '/../config/init.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Geçersiz CSRF token! İşlem reddedildi.');
    }
    
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Lütfen e-posta ve şifrenizi girin.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true); 
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['role'] = $user['role'];

                if (isset($_POST['redirect_url']) && !empty($_POST['redirect_url'])) {
                    header("Location: " . $_POST['redirect_url']);
                }
                elseif ($_SESSION['role'] === 'admin') {
                    header("Location: ../admin/index.php");
                } elseif ($_SESSION['role'] === 'firma_admin') {
                    $_SESSION['company_id'] = $user['company_id'];
                    header("Location: ../firma_admin/index.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $error = "E-posta veya şifre hatalı.";
            }
        } catch (PDOException $e) {
            $error = "Bir veritabanı hatası oluştu: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - Bilet Platformu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="page-auth">

<div class="container" style="max-width: 450px;">
    <div class="card shadow-lg border-0">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <i class="bi bi-bus-front h1 text-primary"></i>
                <h1 class="h3 mb-3 fw-normal">Giriş Yap</h1>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php elseif (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
                <div class="alert alert-success">Kayıt başarılı! Lütfen giriş yapın.</div>
            <?php elseif (isset($_GET['status']) && $_GET['status'] === 'logged_out'): ?>
                <div class="alert alert-info">Başarıyla çıkış yaptınız.</div>
            <?php elseif (isset($_GET['status']) && $_GET['status'] === 'login_required'): ?>
                <div class="alert alert-warning">Bu işlemi yapabilmek için lütfen giriş yapın.</div>
            <?php endif; ?>

            <form action="login.php" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="redirect_url" value="<?php echo htmlspecialchars($_GET['redirect_url'] ?? ''); ?>">

                <div class="mb-3">
                    <label for="email" class="form-label">E-posta Adresi</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Şifre</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                </div>
                
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-lg fw-bold">Giriş Yap</button>
                </div>
            </form>
            <div class="text-center mt-4">
                <p class="text-muted">Hesabınız yok mu? <a href="register.php" class="fw-bold">Hemen Kayıt Olun</a></p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const toggleButton = document.getElementById('togglePassword');
    
    if (passwordInput && toggleButton) {
        const icon = toggleButton.querySelector('i');
        toggleButton.addEventListener('click', function() {

            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        });
    }
});
</script>
</body>
</html>