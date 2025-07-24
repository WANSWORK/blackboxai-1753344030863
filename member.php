<?php
// member.php - Member dashboard for video upload and management
// This page allows members to upload videos and view their upload status

$page_title = "Dashboard Member";
require_once 'db.php';
require_once 'includes/auth.php';

// Require member login
requireMember();

$user_id = getCurrentUserId();
$error = '';
$success = '';

// Get user data to check premium status
$userQuery = "SELECT * FROM users WHERE id = ?";
$userStmt = mysqli_prepare($conn, $userQuery);
mysqli_stmt_bind_param($userStmt, "i", $user_id);
mysqli_stmt_execute($userStmt);
$userResult = mysqli_stmt_get_result($userStmt);
$userData = mysqli_fetch_assoc($userResult);
$isPremiumUser = $userData['is_premium'] == 1;

// Process video upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['video'])) {
    $category_id = intval($_POST['category_id']);
    $uploadedFile = $_FILES['video'];
    
    // Validation
    $allowedType = 'video/mp4';
    $maxSize = MAX_FILE_SIZE; // 50MB from .env.php
    
    if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
        $error = "Terjadi kesalahan saat mengupload file.";
    } elseif ($uploadedFile['type'] !== $allowedType) {
        $error = "Format video harus MP4.";
    } elseif ($uploadedFile['size'] > $maxSize) {
        $error = "Ukuran video tidak boleh melebihi 50MB.";
    } elseif (empty($category_id)) {
        $error = "Silakan pilih kategori video.";
    } else {
        // Check daily upload limit for non-premium users
        if (!$isPremiumUser) {
            $today = date('Y-m-d');
            $uploadCountQuery = "SELECT COUNT(*) as count FROM videos WHERE user_id = ? AND DATE(upload_time) = ?";
            $uploadCountStmt = mysqli_prepare($conn, $uploadCountQuery);
            mysqli_stmt_bind_param($uploadCountStmt, "is", $user_id, $today);
            mysqli_stmt_execute($uploadCountStmt);
            $uploadCountResult = mysqli_stmt_get_result($uploadCountStmt);
            $uploadCountData = mysqli_fetch_assoc($uploadCountResult);
            
            if ($uploadCountData['count'] >= MAX_DAILY_UPLOADS) {
                $error = "Batas upload harian tercapai (" . MAX_DAILY_UPLOADS . " video per hari). <a href='/upgrade.php'>Upgrade ke premium</a> untuk upload tanpa batas.";
            }
        }
        
        if (empty($error)) {
            // Create uploads directory if it doesn't exist
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $fileExtension = pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);
            $newFileName = time() . '_' . uniqid() . '.' . $fileExtension;
            $uploadPath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($uploadedFile['tmp_name'], $uploadPath)) {
                // Insert video record into database
                $insertVideoQuery = "INSERT INTO videos (user_id, filename, category_id, likes, approved, upload_time, sponsor) VALUES (?, ?, ?, 0, 0, NOW(), 0)";
                $insertStmt = mysqli_prepare($conn, $insertVideoQuery);
                mysqli_stmt_bind_param($insertStmt, "isi", $user_id, $newFileName, $category_id);
                
                if (mysqli_stmt_execute($insertStmt)) {
                    $success = "Video berhasil diupload dan menunggu persetujuan admin.";
                    
                    // Log the upload
                    error_log("Video uploaded by user ID $user_id: $newFileName");
                } else {
                    $error = "Terjadi kesalahan saat menyimpan data video.";
                    // Delete uploaded file if database insert failed
                    unlink($uploadPath);
                }
            } else {
                $error = "Gagal mengupload video. Pastikan folder uploads memiliki permission yang benar.";
            }
        }
    }
}

// Fetch user's uploaded videos
$userVideosQuery = "
    SELECT v.*, c.name as category_name 
    FROM videos v 
    JOIN categories c ON v.category_id = c.id 
    WHERE v.user_id = ? 
    ORDER BY v.upload_time DESC
";
$userVideosStmt = mysqli_prepare($conn, $userVideosQuery);
mysqli_stmt_bind_param($userVideosStmt, "i", $user_id);
mysqli_stmt_execute($userVideosStmt);
$userVideosResult = mysqli_stmt_get_result($userVideosStmt);

// Fetch categories for dropdown
$categoriesQuery = "SELECT * FROM categories ORDER BY name";
$categoriesResult = mysqli_query($conn, $categoriesQuery);

// Get today's upload count
$today = date('Y-m-d');
$todayUploadQuery = "SELECT COUNT(*) as count FROM videos WHERE user_id = ? AND DATE(upload_time) = ?";
$todayUploadStmt = mysqli_prepare($conn, $todayUploadQuery);
mysqli_stmt_bind_param($todayUploadStmt, "is", $user_id, $today);
mysqli_stmt_execute($todayUploadStmt);
$todayUploadResult = mysqli_stmt_get_result($todayUploadStmt);
$todayUploadCount = mysqli_fetch_assoc($todayUploadResult)['count'];

include 'includes/header.php';
?>

<div class="dashboard">
    <div class="container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h2>Dashboard Member</h2>
            <p style="color: var(--text-secondary);">
                Selamat datang, <?php echo htmlspecialchars(explode('@', $userData['email'])[0]); ?>!
                <?php if ($isPremiumUser): ?>
                    <span class="premium-badge">Premium Member</span>
                <?php endif; ?>
            </p>
        </div>
        
        <!-- Upload Statistics -->
        <div class="dashboard-section">
            <h3>Statistik Upload Hari Ini</h3>
            <div class="admin-stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $todayUploadCount; ?></div>
                    <div class="stat-label">Video Diupload Hari Ini</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        <?php echo $isPremiumUser ? '∞' : (MAX_DAILY_UPLOADS - $todayUploadCount); ?>
                    </div>
                    <div class="stat-label">
                        <?php echo $isPremiumUser ? 'Upload Unlimited' : 'Sisa Upload Hari Ini'; ?>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        <?php 
                        $totalUserVideos = mysqli_num_rows($userVideosResult);
                        mysqli_data_seek($userVideosResult, 0); // Reset pointer
                        echo $totalUserVideos;
                        ?>
                    </div>
                    <div class="stat-label">Total Video Saya</div>
                </div>
            </div>
        </div>
        
        <!-- Video Upload Form -->
        <div class="dashboard-section">
            <h3>Upload Video Baru</h3>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!$isPremiumUser && $todayUploadCount >= MAX_DAILY_UPLOADS): ?>
                <div class="alert alert-warning">
                    Anda telah mencapai batas upload harian (<?php echo MAX_DAILY_UPLOADS; ?> video). 
                    <a href="/upgrade.php" style="color: var(--accent-primary); font-weight: 600;">Upgrade ke Premium</a> 
                    untuk upload tanpa batas!
                </div>
            <?php else: ?>
                <form action="member.php" method="POST" enctype="multipart/form-data" id="uploadForm">
                    <div style="display: grid; gap: 1.5rem;">
                        <div>
                            <label for="video">Pilih Video (MP4, Max 50MB, Max 1 menit)</label>
                            <input 
                                type="file" 
                                id="video" 
                                name="video" 
                                accept="video/mp4" 
                                required
                                style="padding: 1rem; border: 2px dashed var(--border-color); border-radius: var(--radius-md); background: var(--bg-tertiary);"
                            >
                            <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">
                                Format: MP4 | Ukuran: Maksimal 50MB | Durasi: Maksimal 1 menit
                            </small>
                        </div>
                        
                        <div>
                            <label for="category_id">Kategori Video</label>
                            <select id="category_id" name="category_id" required>
                                <option value="">Pilih Kategori</option>
                                <?php
                                if ($categoriesResult && mysqli_num_rows($categoriesResult) > 0) {
                                    while ($category = mysqli_fetch_assoc($categoriesResult)) {
                                        echo "<option value='" . $category['id'] . "'>" . htmlspecialchars($category['name']) . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div id="videoPreview" style="display: none;">
                            <label>Preview Video</label>
                            <video id="previewVideo" width="100%" height="200" controls style="border-radius: var(--radius-md);">
                                Browser Anda tidak mendukung tag video.
                            </video>
                            <div id="videoInfo" style="margin-top: 0.5rem; font-size: 0.875rem; color: var(--text-secondary);"></div>
                        </div>
                        
                        <button type="submit" id="uploadBtn" class="btn btn-primary">
                            Upload Video
                        </button>
                    </div>
                </form>
            <?php endif; ?>
            
            <?php if (!$isPremiumUser): ?>
                <div style="margin-top: 2rem; padding: 1.5rem; background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary)); border-radius: var(--radius-lg); color: white;">
                    <h4 style="margin-bottom: 1rem; color: white;">Upgrade ke Premium</h4>
                    <ul style="list-style: none; padding: 0; margin-bottom: 1.5rem;">
                        <li style="margin-bottom: 0.5rem;">✅ Upload video tanpa batas harian</li>
                        <li style="margin-bottom: 0.5rem;">✅ Badge Premium di profil dan video</li>
                        <li style="margin-bottom: 0.5rem;">✅ Prioritas review dari admin</li>
                        <li style="margin-bottom: 0.5rem;">✅ Akses fitur eksklusif</li>
                    </ul>
                    <a href="/upgrade.php" class="btn btn-secondary" style="background: white; color: var(--accent-primary);">
                        Upgrade Sekarang
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- User Videos List -->
        <div class="dashboard-section">
            <h3>Video Saya</h3>
            
            <?php if ($userVideosResult && mysqli_num_rows($userVideosResult) > 0): ?>
                <div class="user-videos">
                    <?php while ($video = mysqli_fetch_assoc($userVideosResult)): ?>
                        <div class="video-item">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                <div>
                                    <h4 style="margin: 0 0 0.5rem 0; color: var(--text-primary);">
                                        <?php echo htmlspecialchars($video['filename']); ?>
                                    </h4>
                                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.875rem;">
                                        Kategori: <?php echo htmlspecialchars($video['category_name']); ?>
                                    </p>
                                </div>
                                
                                <div style="text-align: right;">
                                    <?php
                                    $statusClass = '';
                                    $statusText = '';
                                    switch ($video['approved']) {
                                        case 1:
                                            $statusClass = 'success';
                                            $statusText = '✅ Approved';
                                            break;
                                        case -1:
                                            $statusClass = 'error';
                                            $statusText = '❌ Rejected';
                                            break;
                                        default:
                                            $statusClass = 'warning';
                                            $statusText = '⏳ Pending';
                                    }
                                    ?>
                                    <span class="alert alert-<?php echo $statusClass; ?>" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; display: inline-block;">
                                        <?php echo $statusText; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                                <div style="color: var(--text-muted); font-size: 0.875rem;">
                                    <span>Upload: <?php echo date('d/m/Y H:i', strtotime($video['upload_time'])); ?></span>
                                    <span style="margin-left: 1rem;">Likes: <?php echo $video['likes']; ?></span>
                                </div>
                                
                                <div style="display: flex; gap: 0.5rem;">
                                    <?php if ($video['approved'] == 1): ?>
                                        <a href="/video.php?id=<?php echo $video['id']; ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                            Lihat Video
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($video['approved'] == 0): ?>
                                        <span style="color: var(--text-muted); font-size: 0.875rem;">Menunggu review admin</span>
                                    <?php elseif ($video['approved'] == -1): ?>
                                        <span style="color: var(--error-color); font-size: 0.875rem;">Ditolak admin</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                    <h4>Belum ada video yang diupload</h4>
                    <p>Upload video pertama Anda untuk memulai berbagi dengan komunitas!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Video upload form enhancement
document.addEventListener('DOMContentLoaded', function() {
    const videoInput = document.getElementById('video');
    const uploadForm = document.getElementById('uploadForm');
    const uploadBtn = document.getElementById('uploadBtn');
    const videoPreview = document.getElementById('videoPreview');
    const previewVideo = document.getElementById('previewVideo');
    const videoInfo = document.getElementById('videoInfo');
    
    // Video file selection handler
    videoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file type
            if (file.type !== 'video/mp4') {
                alert('Format video harus MP4');
                this.value = '';
                return;
            }
            
            // Validate file size (50MB)
            const maxSize = 50 * 1024 * 1024;
            if (file.size > maxSize) {
                alert('Ukuran video tidak boleh melebihi 50MB');
                this.value = '';
                return;
            }
            
            // Show preview
            const url = URL.createObjectURL(file);
            previewVideo.src = url;
            videoPreview.style.display = 'block';
            
            // Show file info
            const sizeInMB = (file.size / (1024 * 1024)).toFixed(2);
            videoInfo.innerHTML = `
                <strong>File:</strong> ${file.name}<br>
                <strong>Ukuran:</strong> ${sizeInMB} MB<br>
                <strong>Type:</strong> ${file.type}
            `;
            
            // Validate duration when metadata loads
            previewVideo.addEventListener('loadedmetadata', function() {
                const duration = this.duration;
                const minutes = Math.floor(duration / 60);
                const seconds = Math.floor(duration % 60);
                
                videoInfo.innerHTML += `<br><strong>Durasi:</strong> ${minutes}:${seconds.toString().padStart(2, '0')}`;
                
                if (duration > 60) {
                    alert('Durasi video tidak boleh melebihi 1 menit (60 detik)');
                    videoInput.value = '';
                    videoPreview.style.display = 'none';
                    URL.revokeObjectURL(url);
                }
            });
        } else {
            videoPreview.style.display = 'none';
        }
    });
    
    // Form submission
    uploadForm.addEventListener('submit', function(e) {
        const file = videoInput.files[0];
        const category = document.getElementById('category_id').value;
        
        if (!file) {
            e.preventDefault();
            alert('Silakan pilih file video');
            return;
        }
        
        if (!category) {
            e.preventDefault();
            alert('Silakan pilih kategori video');
            return;
        }
        
        // Show loading state
        uploadBtn.innerHTML = 'Mengupload... <span class="loading"></span>';
        uploadBtn.disabled = true;
        
        // Show progress (simulate)
        let progress = 0;
        const progressInterval = setInterval(function() {
            progress += Math.random() * 10;
            if (progress >= 90) {
                clearInterval(progressInterval);
            }
            uploadBtn.innerHTML = `Mengupload... ${Math.min(Math.round(progress), 90)}%`;
        }, 200);
    });
});

// Auto-refresh page every 30 seconds to check for approval status updates
setInterval(function() {
    // Only refresh if user is not actively interacting
    if (document.hidden === false) {
        const lastActivity = localStorage.getItem('lastMemberActivity') || 0;
        const currentTime = Date.now();
        
        if (currentTime - lastActivity > 30000) { // 30 seconds
            window.location.reload();
        }
    }
}, 30000);

// Track user activity
document.addEventListener('mousemove', function() {
    localStorage.setItem('lastMemberActivity', Date.now());
});

document.addEventListener('keypress', function() {
    localStorage.setItem('lastMemberActivity', Date.now());
});
</script>

<?php include 'includes/footer.php'; ?>
