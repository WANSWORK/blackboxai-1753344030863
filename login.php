<?php
// login.php - User login page
// Allows members and admin to log into their accounts

$page_title = "Login";
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
$message = '';

// Handle messages from URL parameters
if (isset($_GET['message'])) {
    switch ($_GET['message']) {
        case 'logged_out':
            $message = 'Anda telah berhasil logout.';
            break;
        case 'registered':
            $message = 'Registrasi berhasil! Silakan login dengan akun baru Anda.';
            break;
        case 'session_expired':
            $message = 'Sesi Anda telah berakhir. Silakan login kembali.';
            break;
    }
}

// Handle errors from URL parameters
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'access_denied':
            $error = 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman tersebut.';
            break;
        case 'login_required':
            $error = 'Silakan login terlebih dahulu untuk mengakses halaman tersebut.';
            break;
    }
}

// Get redirect URL if provided
$redirectUrl = isset($_GET['redirect']) ? $_GET['redirect'] : '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize form data
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validation
    if (empty($email) || empty($password)) {
        $error = "Email dan password harus diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } else {
        // Check user credentials
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            
            // Check if account is active
            if ($user['status'] !== 'active') {
                $error = "Akun Anda telah diblokir. Hubungi administrator untuk informasi lebih lanjut.";
            } elseif (password_verify($password, $user['password'])) {
                // Login successful
                setUserSession($user);
                
                // Set remember me cookie if requested
                if ($remember) {
                    $cookieValue = base64_encode($user['id'] . ':' . $user['email']);
                    setcookie('remember_user', $cookieValue, time() + (30 * 24 * 60 * 60), '/'); // 30 days
                }
                
                // Log successful login
                error_log("User logged in: " . $email . " (Role: " . $user['role'] . ")");
                
                // Redirect based on role or redirect URL
                if (!empty($redirectUrl)) {
                    header("Location: " . $redirectUrl);
                } elseif ($user['role'] == 'admin') {
                    header("Location: /admin.php");
                } else {
                    header("Location: /member.php");
                }
                exit;
            } else {
                $error = "Password salah.";
                // Log failed login attempt
                error_log("Failed login attempt for: " . $email);
            }
        } else {
            $error = "Email tidak ditemukan.";
            // Log failed login attempt
            error_log("Failed login attempt for non-existent email: " . $email);
        }
    }
}

// Check for remember me cookie
$rememberedEmail = '';
if (isset($_COOKIE['remember_user']) && empty($_POST['email'])) {
    $cookieData = base64_decode($_COOKIE['remember_user']);
    $parts = explode(':', $cookieData);
    if (count($parts) == 2) {
        $rememberedEmail = $parts[1];
    }
}

include 'includes/header.php';
?>

<div class="form-container">
    <h2>Masuk ke Akun</h2>
    <p style="text-align: center; color: var(--text-secondary); margin-bottom: 2rem;">
        Selamat datang kembali di Stralentech.ai
    </p>
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-success">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <!-- Login Form -->
    <form action="login.php<?php echo !empty($redirectUrl) ? '?redirect=' . urlencode($redirectUrl) : ''; ?>" method="POST" id="loginForm">
        <div style="margin-bottom: 1.5rem;">
            <label for="email">Email Address</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                required 
                placeholder="contoh@email.com"
                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : htmlspecialchars($rememberedEmail); ?>"
                autocomplete="email"
                autofocus
            >
        </div>
        
        <div style="margin-bottom: 1.5rem;">
            <label for="password">Password</label>
            <div style="position: relative;">
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    placeholder="Masukkan password"
                    autocomplete="current-password"
                >
                <button 
                    type="button" 
                    id="togglePassword" 
                    style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer;"
                    onclick="togglePasswordVisibility()"
                >
                    👁️
                </button>
            </div>
        </div>
        
        <!-- Remember Me and Forgot Password -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" name="remember" <?php echo !empty($rememberedEmail) ? 'checked' : ''; ?>>
                <span style="font-size: 0.875rem; color: var(--text-secondary);">Ingat saya</span>
            </label>
            
            <a href="#" onclick="showForgotPassword(); return false;" style="font-size: 0.875rem; color: var(--accent-primary);">
                Lupa password?
            </a>
        </div>
        
        <button type="submit" id="submitBtn" class="btn btn-primary" style="width: 100%;">
            Masuk
        </button>
    </form>
    
    <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
        <p style="color: var(--text-secondary); margin-bottom: 1rem;">
            Belum punya akun?
        </p>
        <a href="/register.php" class="btn btn-secondary">
            Daftar Sekarang
        </a>
    </div>
    
    <!-- Demo Accounts Info -->
    <div style="margin-top: 2rem; padding: 1.5rem; background: var(--bg-tertiary); border-radius: var(--radius-lg);">
        <h4 style="margin-bottom: 1rem; color: var(--text-primary);">Demo Accounts:</h4>
        <div style="color: var(--text-secondary); font-size: 0.875rem;">
            <p style="margin-bottom: 0.5rem;">
                <strong>Admin:</strong> admin@stralentech.ai / admin123
            </p>
            <p style="margin-bottom: 0;">
                <strong>Member:</strong> Daftar akun baru atau gunakan akun yang sudah dibuat
            </p>
        </div>
    </div>
</div>

<!-- Forgot Password Modal -->
<div id="forgotPasswordModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeForgotPassword()">&times;</span>
        <h2>Reset Password</h2>
        <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
            Masukkan email Anda untuk mendapatkan instruksi reset password.
        </p>
        
        <form id="forgotPasswordForm">
            <div style="margin-bottom: 1.5rem;">
                <label for="resetEmail">Email Address</label>
                <input 
                    type="email" 
                    id="resetEmail" 
                    name="resetEmail" 
                    required 
                    placeholder="contoh@email.com"
                >
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                Kirim Instruksi Reset
            </button>
        </form>
        
        <div id="resetMessage" style="margin-top: 1rem;"></div>
    </div>
</div>

<script>
// Form enhancement and validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');
    
    // Form submission
    form.addEventListener('submit', function(e) {
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        
        if (!email || !password) {
            e.preventDefault();
            alert('Email dan password harus diisi');
            return;
        }
        
        // Show loading state
        submitBtn.innerHTML = 'Masuk... <span class="loading"></span>';
        submitBtn.disabled = true;
    });
    
    // Auto-focus on password if email is pre-filled
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    
    if (emailInput.value) {
        passwordInput.focus();
    }
});

// Toggle password visibility
function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
    const toggleBtn = document.getElementById('togglePassword');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleBtn.innerHTML = '🙈';
    } else {
        passwordInput.type = 'password';
        toggleBtn.innerHTML = '👁️';
    }
}

// Forgot password functionality
function showForgotPassword() {
    document.getElementById('forgotPasswordModal').style.display = 'block';
    document.getElementById('resetEmail').focus();
}

function closeForgotPassword() {
    document.getElementById('forgotPasswordModal').style.display = 'none';
    document.getElementById('resetMessage').innerHTML = '';
}

// Handle forgot password form
document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = document.getElementById('resetEmail').value;
    const messageDiv = document.getElementById('resetMessage');
    
    // Simulate password reset (in production, this would send an actual email)
    messageDiv.innerHTML = `
        <div class="alert alert-success">
            Jika email ${email} terdaftar, instruksi reset password telah dikirim ke email Anda.
            <br><br>
            <small>Catatan: Ini adalah demo. Dalam implementasi nyata, email reset akan dikirim.</small>
        </div>
    `;
    
    // Clear form
    document.getElementById('resetEmail').value = '';
});

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('forgotPasswordModal');
    if (event.target == modal) {
        closeForgotPassword();
    }
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Escape key closes modal
    if (e.key === 'Escape') {
        closeForgotPassword();
    }
    
    // Enter key submits form
    if (e.key === 'Enter' && e.target.tagName !== 'BUTTON') {
        const form = document.getElementById('loginForm');
        if (form.checkValidity()) {
            form.submit();
        }
    }
});

// Auto-clear error messages after 10 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert-error');
    alerts.forEach(function(alert) {
        alert.style.opacity = '0';
        setTimeout(function() {
            alert.style.display = 'none';
        }, 300);
    });
}, 10000);

// Check for caps lock
document.getElementById('password').addEventListener('keyup', function(e) {
    const capsLockOn = e.getModifierState && e.getModifierState('CapsLock');
    const warningDiv = document.getElementById('capsLockWarning');
    
    if (capsLockOn) {
        if (!warningDiv) {
            const warning = document.createElement('div');
            warning.id = 'capsLockWarning';
            warning.style.cssText = 'color: var(--warning-color); font-size: 0.875rem; margin-top: 0.5rem;';
            warning.innerHTML = '⚠️ Caps Lock aktif';
            this.parentNode.appendChild(warning);
        }
    } else {
        if (warningDiv) {
            warningDiv.remove();
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>
