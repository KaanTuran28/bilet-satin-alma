<?php
require_once __DIR__ . '/../config/init.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Geçersiz CSRF token! İşlem reddedildi.');
    }

    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $error_messages = [];

    if (empty($fullname) || empty($email) || empty($password)) {
        $error_messages[] = "Lütfen tüm alanları doldurun.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_messages[] = "Lütfen geçerli bir e-posta adresi girin.";
    }
    if ($password !== $password_confirm) {
        $error_messages[] = "Girdiğiniz şifreler uyuşmuyor.";
    }
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
        $error_messages[] = "Şifre, belirtilen kurallara uymuyor.";
    }

    if (empty($error_messages)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->execute([$fullname, $email, $hashed_password]);
            header("Location: login.php?status=success");
            exit();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { $error = "Bu e-posta adresi zaten kayıtlı.";
            } else { $error = "Bir veritabanı hatası oluştu: " . $e->getMessage(); }
        }
    } else {
        $error = implode('<br>', $error_messages);
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - Bilet Platformu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="page-auth">

<div class="container" style="max-width: 480px;">
    <div class="card shadow-lg border-0">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <i class="bi bi-bus-front h1 text-primary"></i>
                <h1 class="h3 mb-3 fw-normal">Yeni Hesap Oluştur</h1>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger small"><?php echo $error; ?></div>
            <?php endif; ?>

            <form action="register.php" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                
                <div class="mb-3">
                    <label for="fullname" class="form-label">Ad Soyad</label>
                    <input type="text" class="form-control" id="fullname" name="fullname" required>
                </div>

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

                <div class="mb-3">
                    <label for="password_confirm" class="form-label">Şifre Tekrar</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                         <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                    <div id="password-match-status" class="form-text mt-1"></div>
                </div>

                <div id="password-requirements" class="mb-3 p-3 bg-light rounded" style="font-size: 0.9em;">
                    <p class="mb-1 small">Şifreniz şunları içermelidir:</p>
                    <ul class="list-unstyled mb-0 small">
                        <li id="req-length" class="text-danger"><i class="bi bi-x-circle me-1"></i> En az 8 karakter</li>
                        <li id="req-lowercase" class="text-danger"><i class="bi bi-x-circle me-1"></i> En az bir küçük harf</li>
                        <li id="req-uppercase" class="text-danger"><i class="bi bi-x-circle me-1"></i> En az bir büyük harf</li>
                        <li id="req-number" class="text-danger"><i class="bi bi-x-circle me-1"></i> En az bir rakam</li>
                    </ul>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-lg fw-bold">Hesap Oluştur</button>
                </div>
            </form>
            <div class="text-center mt-4">
                <p class="text-muted">Zaten bir hesabınız var mı? <a href="login.php" class="fw-bold">Giriş Yap</a></p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    function togglePasswordVisibility(inputId, toggleButtonId) {
        const passwordInput = document.getElementById(inputId);
        const toggleButton = document.getElementById(toggleButtonId);
        if(!passwordInput || !toggleButton) return;
        const icon = toggleButton.querySelector('i');

        toggleButton.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        });
    }
    togglePasswordVisibility('password', 'togglePassword');
    togglePasswordVisibility('password_confirm', 'togglePasswordConfirm');

    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirm');
    const matchStatus = document.getElementById('password-match-status');
    const reqLength = document.getElementById('req-length');
    const reqLowercase = document.getElementById('req-lowercase');
    const reqUppercase = document.getElementById('req-uppercase');
    const reqNumber = document.getElementById('req-number');

    function validatePasswords() {
        if (!matchStatus || !passwordConfirmInput || !passwordInput) return;
        if (passwordConfirmInput.value.length > 0) {
            if (passwordInput.value === passwordConfirmInput.value) {
                matchStatus.textContent = '✓ Şifreler uyuşuyor.';
                matchStatus.className = 'form-text text-success fw-bold';
            } else {
                matchStatus.textContent = '✗ Şifreler uyuşmuyor.';
                matchStatus.className = 'form-text text-danger fw-bold';
            }
        } else {
            matchStatus.textContent = '';
        }
    }

    function validateStrength() {
        if(!passwordInput) return;
        const pass = passwordInput.value;
        updateRequirement(reqLength, pass.length >= 8);
        updateRequirement(reqLowercase, /[a-z]/.test(pass));
        updateRequirement(reqUppercase, /[A-Z]/.test(pass));
        updateRequirement(reqNumber, /\d/.test(pass));
    }

    function updateRequirement(element, met) {
        if (!element) return;
        const icon = element.querySelector('i');
        
        if (met) {
            icon.className = 'bi bi-check-circle me-1';
            element.className = 'text-success';
        } else {
            icon.className = 'bi bi-x-circle me-1';
            element.className = 'text-danger';
        }
    }

    if(passwordInput) passwordInput.addEventListener('input', validateStrength);
    if(passwordConfirmInput) passwordConfirmInput.addEventListener('input', validatePasswords);
    if(passwordInput) passwordInput.addEventListener('input', validatePasswords);
});
</script>
</body>
</html>