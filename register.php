<?php
// register.php - User registration page
// Allows new users to create an account

$page_title = "Daftar";
require_once 'db.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        header("Location: /admin.php");
    } else {
        header("Location: /member.php");
    }
    exit;
}

$error = '';
$success = '';

// Process registration form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize form data
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);
    
    // Validation
    if (empty($email) || empty($password) || empty($confirmPassword)) {
        $error = "Semua field harus diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } elseif ($password !== $confirmPassword) {
        $error = "Konfirmasi password tidak cocok.";
    } else {
        // Check if email already exists
        $checkQuery = "SELECT id FROM users WHERE email = ?";
        $checkStmt = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($checkStmt, "s", $email);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);
        
        if (mysqli_num_rows($checkResult) > 0) {
            $error = "Email sudah terdaftar. Silakan gunakan email lain atau <a href='/login.php'>login</a>.";
        } else {
            // Hash password and insert new user
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            $insertQuery = "INSERT INTO users (email, password, role, is_premium, status, created_at) VALUES (?, ?, 'member', 0, 'active', NOW())";
            $insertStmt = mysqli_prepare($conn, $insertQuery);
            mysqli_stmt_bind_param($insertStmt, "ss", $email, $passwordHash);
            
            if (mysqli_stmt_execute($insertStmt)) {
                $success = "Registrasi berhasil! Silakan <a href='/login.php'>login</a> untuk melanjutkan.";
                
                // Log the registration
                error_log("New user registered: " . $email);
                
                // Optional: Auto-login after registration
                // Uncomment the following lines if you want to auto-login users after registration
                /*
                $userId = mysqli_insert_id($conn);
                $userQuery = "SELECT * FROM users WHERE id = ?";
                $userStmt = mysqli_prepare($conn, $userQuery);
                mysqli_stmt_bind_param($userStmt, "i", $userId);
                mysqli_stmt_execute($userStmt);
                $userResult = mysqli_stmt_get_result($userStmt);
                $userData = mysqli_fetch_assoc($userResult);
                
                setUserSession($userData);
                header("Location: /member.php?message=welcome");
                exit;
                */
            } else {
                $error = "Terjadi kesalahan saat mendaftarkan akun. Silakan coba lagi.";
                error_log("Registration error: " . mysqli_error($conn));
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="form-container">
    <h2>Daftar Akun Baru</h2>
    <p style="text-align: center; color: var(--text-secondary); margin-bottom: 2rem;">
        Bergabunglah dengan komunitas kreator video AI
    </p>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <?php echo $success; ?>
        </div>
    <?php else: ?>
        <!-- Registration Form -->
        <form action="register.php" method="POST" id="registerForm">
            <div style="margin-bottom: 1.5rem;">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    placeholder="contoh@email.com"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    autocomplete="email"
                >
                <small style="color: var(--text-muted); font-size: 0.875rem;">
                    Gunakan email yang valid untuk verifikasi akun
                </small>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    placeholder="Minimal 6 karakter"
                    minlength="6"
                    autocomplete="new-password"
                >
                <div id="passwordStrength" style="margin-top: 0.5rem; font-size: 0.875rem;"></div>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label for="confirm_password">Konfirmasi Password</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    required 
                    placeholder="Ulangi password"
                    autocomplete="new-password"
                >
                <div id="passwordMatch" style="margin-top: 0.5rem; font-size: 0.875rem;"></div>
            </div>
            
            <!-- Terms and Conditions -->
            <div style="margin-bottom: 1.5rem;">
                <label style="display: flex; align-items: start; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" id="terms" name="terms" required style="margin-top: 0.25rem;">
                    <span style="font-size: 0.875rem; color: var(--text-secondary);">
                        Saya setuju dengan <a href="#" onclick="showTermsOfService(); return false;">Syarat dan Ketentuan</a> 
                        serta <a href="#" onclick="showPrivacyPolicy(); return false;">Kebijakan Privasi</a> Stralentech.ai
                    </span>
                </label>
            </div>
            
            <button type="submit" id="submitBtn" class="btn btn-primary" style="width: 100%;">
                Daftar Sekarang
            </button>
        </form>
    <?php endif; ?>
    
    <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
        <p style="color: var(--text-secondary); margin-bottom: 1rem;">
            Sudah punya akun?
        </p>
        <a href="/login.php" class="btn btn-secondary">
            Masuk ke Akun
        </a>
    </div>
    
    <!-- Benefits Section -->
    <div style="margin-top: 2rem; padding: 1.5rem; background: var(--bg-tertiary); border-radius: var(--radius-lg);">
        <h4 style="margin-bottom: 1rem; color: var(--text-primary);">Keuntungan Bergabung:</h4>
        <ul style="list-style: none; padding: 0; color: var(--text-secondary);">
            <li style="margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                ✅ Upload video AI hingga 2 video per hari
            </li>
            <li style="margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                ✅ Like dan komentar pada video komunitas
            </li>
            <li style="margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                ✅ Akses ke semua kategori video
            </li>
            <li style="margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                ✅ Bergabung dengan komunitas global
            </li>
            <li style="margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                🎯 Upgrade ke Premium untuk upload unlimited
            </li>
        </ul>
    </div>
</div>

<script>
// Form validation and enhancement
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const passwordStrength = document.getElementById('passwordStrength');
    const passwordMatch = document.getElementById('passwordMatch');
    const submitBtn = document.getElementById('submitBtn');
    
    // Password strength checker
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        const strength = checkPasswordStrength(password);
        
        passwordStrength.innerHTML = `
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <div style="flex: 1; height: 4px; background: var(--bg-tertiary); border-radius: 2px; overflow: hidden;">
                    <div style="height: 100%; background: ${strength.color}; width: ${strength.percentage}%; transition: all 0.3s ease;"></div>
                </div>
                <span style="color: ${strength.color}; font-weight: 500;">${strength.text}</span>
            </div>
        `;
    });
    
    // Password match checker
    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (confirmPassword.length > 0) {
            if (password === confirmPassword) {
                passwordMatch.innerHTML = '<span style="color: var(--success-color);">✅ Password cocok</span>';
                return true;
            } else {
                passwordMatch.innerHTML = '<span style="color: var(--error-color);">❌ Password tidak cocok</span>';
                return false;
            }
        } else {
            passwordMatch.innerHTML = '';
            return false;
        }
    }
    
    confirmPasswordInput.addEventListener('input', checkPasswordMatch);
    passwordInput.addEventListener('input', checkPasswordMatch);
    
    // Form submission
    form.addEventListener('submit', function(e) {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        const terms = document.getElementById('terms').checked;
        
        if (password.length < 6) {
            e.preventDefault();
            alert('Password minimal 6 karakter');
            return;
        }
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Konfirmasi password tidak cocok');
            return;
        }
        
        if (!terms) {
            e.preventDefault();
            alert('Anda harus menyetujui syarat dan ketentuan');
            return;
        }
        
        // Show loading state
        submitBtn.innerHTML = 'Mendaftar... <span class="loading"></span>';
        submitBtn.disabled = true;
    });
});

// Password strength checker function
function checkPasswordStrength(password) {
    let score = 0;
    let feedback = [];
    
    if (password.length >= 6) score += 1;
    if (password.length >= 8) score += 1;
    if (/[a-z]/.test(password)) score += 1;
    if (/[A-Z]/.test(password)) score += 1;
    if (/[0-9]/.test(password)) score += 1;
    if (/[^A-Za-z0-9]/.test(password)) score += 1;
    
    if (score < 2) {
        return { color: 'var(--error-color)', percentage: 20, text: 'Lemah' };
    } else if (score < 4) {
        return { color: 'var(--warning-color)', percentage: 50, text: 'Sedang' };
    } else if (score < 6) {
        return { color: 'var(--success-color)', percentage: 80, text: 'Kuat' };
    } else {
        return { color: 'var(--success-color)', percentage: 100, text: 'Sangat Kuat' };
    }
}

// Email validation
document.getElementById('email').addEventListener('blur', function() {
    const email = this.value;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (email && !emailRegex.test(email)) {
        this.style.borderColor = 'var(--error-color)';
        this.setCustomValidity('Format email tidak valid');
    } else {
        this.style.borderColor = '';
        this.setCustomValidity('');
    }
});
</script>

<?php include 'includes/footer.php'; ?>
