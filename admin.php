<?php
// admin.php - Admin panel for managing users, videos, comments, and categories
// This page provides comprehensive admin functionality

$page_title = "Admin Panel";
require_once 'db.php';
require_once 'includes/auth.php';

// Require admin access
requireAdmin();

$message = '';
$error = '';

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'approve_video':
            $video_id = intval($_POST['video_id']);
            $updateQuery = "UPDATE videos SET approved = 1 WHERE id = ?";
            $stmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($stmt, "i", $video_id);
            if (mysqli_stmt_execute($stmt)) {
                $message = "Video berhasil disetujui.";
            } else {
                $error = "Gagal menyetujui video.";
            }
            break;
            
        case 'reject_video':
            $video_id = intval($_POST['video_id']);
            $updateQuery = "UPDATE videos SET approved = -1 WHERE id = ?";
            $stmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($stmt, "i", $video_id);
            if (mysqli_stmt_execute($stmt)) {
                $message = "Video berhasil ditolak.";
            } else {
                $error = "Gagal menolak video.";
            }
            break;
            
        case 'delete_video':
            $video_id = intval($_POST['video_id']);
            
            // Get video filename first
            $getVideoQuery = "SELECT filename FROM videos WHERE id = ?";
            $getStmt = mysqli_prepare($conn, $getVideoQuery);
            mysqli_stmt_bind_param($getStmt, "i", $video_id);
            mysqli_stmt_execute($getStmt);
            $result = mysqli_stmt_get_result($getStmt);
            $video = mysqli_fetch_assoc($result);
            
            if ($video) {
                // Delete video file
                $filePath = 'uploads/' . $video['filename'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                
                // Delete from database
                $deleteQuery = "DELETE FROM videos WHERE id = ?";
                $deleteStmt = mysqli_prepare($conn, $deleteQuery);
                mysqli_stmt_bind_param($deleteStmt, "i", $video_id);
                if (mysqli_stmt_execute($deleteStmt)) {
                    $message = "Video berhasil dihapus.";
                } else {
                    $error = "Gagal menghapus video dari database.";
                }
            }
            break;
            
        case 'toggle_user_status':
            $user_id = intval($_POST['user_id']);
            $current_status = $_POST['current_status'];
            $new_status = ($current_status === 'active') ? 'blocked' : 'active';
            
            $updateQuery = "UPDATE users SET status = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($stmt, "si", $new_status, $user_id);
            if (mysqli_stmt_execute($stmt)) {
                $message = "Status user berhasil diubah.";
            } else {
                $error = "Gagal mengubah status user.";
            }
            break;
            
        case 'toggle_premium':
            $user_id = intval($_POST['user_id']);
            $current_premium = intval($_POST['current_premium']);
            $new_premium = $current_premium ? 0 : 1;
            
            $updateQuery = "UPDATE users SET is_premium = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($stmt, "ii", $new_premium, $user_id);
            if (mysqli_stmt_execute($stmt)) {
                $message = "Status premium user berhasil diubah.";
            } else {
                $error = "Gagal mengubah status premium user.";
            }
            break;
            
        case 'delete_comment':
            $comment_id = intval($_POST['comment_id']);
            $deleteQuery = "DELETE FROM comments WHERE id = ?";
            $stmt = mysqli_prepare($conn, $deleteQuery);
            mysqli_stmt_bind_param($stmt, "i", $comment_id);
            if (mysqli_stmt_execute($stmt)) {
                $message = "Komentar berhasil dihapus.";
            } else {
                $error = "Gagal menghapus komentar.";
            }
            break;
            
        case 'add_category':
            $category_name = trim($_POST['category_name']);
            if (!empty($category_name)) {
                $insertQuery = "INSERT INTO categories (name) VALUES (?)";
                $stmt = mysqli_prepare($conn, $insertQuery);
                mysqli_stmt_bind_param($stmt, "s", $category_name);
                if (mysqli_stmt_execute($stmt)) {
                    $message = "Kategori berhasil ditambahkan.";
                } else {
                    $error = "Gagal menambahkan kategori.";
                }
            }
            break;
            
        case 'delete_category':
            $category_id = intval($_POST['category_id']);
            // Check if category has videos
            $checkQuery = "SELECT COUNT(*) as count FROM videos WHERE category_id = ?";
            $checkStmt = mysqli_prepare($conn, $checkQuery);
            mysqli_stmt_bind_param($checkStmt, "i", $category_id);
            mysqli_stmt_execute($checkStmt);
            $checkResult = mysqli_stmt_get_result($checkStmt);
            $count = mysqli_fetch_assoc($checkResult)['count'];
            
            if ($count > 0) {
                $error = "Tidak dapat menghapus kategori yang masih memiliki video.";
            } else {
                $deleteQuery = "DELETE FROM categories WHERE id = ?";
                $stmt = mysqli_prepare($conn, $deleteQuery);
                mysqli_stmt_bind_param($stmt, "i", $category_id);
                if (mysqli_stmt_execute($stmt)) {
                    $message = "Kategori berhasil dihapus.";
                } else {
                    $error = "Gagal menghapus kategori.";
                }
            }
            break;
            
        case 'set_sponsor':
            $video_id = intval($_POST['video_id']);
            $sponsor_status = intval($_POST['sponsor_status']);
            $updateQuery = "UPDATE videos SET sponsor = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($stmt, "ii", $sponsor_status, $video_id);
            if (mysqli_stmt_execute($stmt)) {
                $message = "Status sponsor video berhasil diubah.";
            } else {
                $error = "Gagal mengubah status sponsor video.";
            }
            break;
    }
}

// Get statistics
$statsQuery = "
    SELECT 
        (SELECT COUNT(*) FROM users WHERE role = 'member') as total_members,
        (SELECT COUNT(*) FROM users WHERE role = 'member' AND is_premium = 1) as premium_members,
        (SELECT COUNT(*) FROM videos) as total_videos,
        (SELECT COUNT(*) FROM videos WHERE approved = 1) as approved_videos,
        (SELECT COUNT(*) FROM videos WHERE approved = 0) as pending_videos,
        (SELECT COUNT(*) FROM videos WHERE approved = -1) as rejected_videos,
        (SELECT COUNT(*) FROM comments) as total_comments,
        (SELECT COUNT(*) FROM likes) as total_likes
";
$statsResult = mysqli_query($conn, $statsQuery);
$stats = mysqli_fetch_assoc($statsResult);

// Get pending videos
$pendingVideosQuery = "
    SELECT v.*, c.name as category_name, u.email as uploader_email 
    FROM videos v 
    JOIN categories c ON v.category_id = c.id 
    JOIN users u ON v.user_id = u.id 
    WHERE v.approved = 0 
    ORDER BY v.upload_time ASC
";
$pendingVideosResult = mysqli_query($conn, $pendingVideosQuery);

// Get all users
$usersQuery = "SELECT * FROM users WHERE role = 'member' ORDER BY created_at DESC";
$usersResult = mysqli_query($conn, $usersQuery);

// Get recent comments
$commentsQuery = "
    SELECT c.*, u.email as user_email, v.filename as video_filename 
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    JOIN videos v ON c.video_id = v.id 
    ORDER BY c.created_at DESC 
    LIMIT 20
";
$commentsResult = mysqli_query($conn, $commentsQuery);

// Get categories
$categoriesQuery = "SELECT c.*, COUNT(v.id) as video_count FROM categories c LEFT JOIN videos v ON c.id = v.category_id GROUP BY c.id ORDER BY c.name";
$categoriesResult = mysqli_query($conn, $categoriesQuery);

// Get top videos
$topVideosQuery = "
    SELECT v.*, c.name as category_name, u.email as uploader_email 
    FROM videos v 
    JOIN categories c ON v.category_id = c.id 
    JOIN users u ON v.user_id = u.id 
    WHERE v.approved = 1 
    ORDER BY v.likes DESC 
    LIMIT 10
";
$topVideosResult = mysqli_query($conn, $topVideosQuery);

include 'includes/header.php';
?>

<div class="admin-panel">
    <div class="container">
        <!-- Admin Header -->
        <div class="dashboard-header">
            <h2>Admin Panel</h2>
            <p style="color: var(--text-secondary);">
                Kelola platform Stralentech.ai
            </p>
        </div>
        
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
        
        <!-- Statistics -->
        <div class="dashboard-section">
            <h3>Statistik Platform</h3>
            <div class="admin-stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['total_members']); ?></div>
                    <div class="stat-label">Total Member</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['premium_members']); ?></div>
                    <div class="stat-label">Premium Member</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['total_videos']); ?></div>
                    <div class="stat-label">Total Video</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['approved_videos']); ?></div>
                    <div class="stat-label">Video Approved</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['pending_videos']); ?></div>
                    <div class="stat-label">Video Pending</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['total_likes']); ?></div>
                    <div class="stat-label">Total Like</div>
                </div>
            </div>
        </div>
        
        <!-- Pending Videos -->
        <div class="dashboard-section">
            <h3>Video Menunggu Persetujuan (<?php echo $stats['pending_videos']; ?>)</h3>
            
            <?php if ($pendingVideosResult && mysqli_num_rows($pendingVideosResult) > 0): ?>
                <div class="admin-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Video</th>
                                <th>Uploader</th>
                                <th>Kategori</th>
                                <th>Upload Time</th>
                                <th>Preview</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($video = mysqli_fetch_assoc($pendingVideosResult)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($video['filename']); ?></td>
                                    <td><?php echo htmlspecialchars($video['uploader_email']); ?></td>
                                    <td><?php echo htmlspecialchars($video['category_name']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($video['upload_time'])); ?></td>
                                    <td>
                                        <video width="100" height="60" controls>
                                            <source src="/uploads/<?php echo htmlspecialchars($video['filename']); ?>" type="video/mp4">
                                        </video>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="approve_video">
                                                <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                                                <button type="submit" class="btn-approve" onclick="return confirm('Setujui video ini?')">
                                                    Approve
                                                </button>
                                            </form>
                                            
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="reject_video">
                                                <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                                                <button type="submit" class="btn-reject" onclick="return confirm('Tolak video ini?')">
                                                    Reject
                                                </button>
                                            </form>
                                            
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="delete_video">
                                                <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                                                <button type="submit" class="btn-reject" onclick="return confirm('Hapus video ini secara permanen?')">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: var(--text-secondary); padding: 2rem;">
                    Tidak ada video yang menunggu persetujuan.
                </p>
            <?php endif; ?>
        </div>
        
        <!-- User Management -->
        <div class="dashboard-section">
            <h3>Manajemen User</h3>
            
            <?php if ($usersResult && mysqli_num_rows($usersResult) > 0): ?>
                <div class="admin-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Premium</th>
                                <th>Bergabung</th>
                                <th>Total Video</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = mysqli_fetch_assoc($usersResult)): ?>
                                <?php
                                // Get user video count
                                $videoCountQuery = "SELECT COUNT(*) as count FROM videos WHERE user_id = ?";
                                $videoCountStmt = mysqli_prepare($conn, $videoCountQuery);
                                mysqli_stmt_bind_param($videoCountStmt, "i", $user['id']);
                                mysqli_stmt_execute($videoCountStmt);
                                $videoCountResult = mysqli_stmt_get_result($videoCountStmt);
                                $videoCount = mysqli_fetch_assoc($videoCountResult)['count'];
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="alert alert-<?php echo $user['status'] === 'active' ? 'success' : 'error'; ?>" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                            <?php echo $user['status'] === 'active' ? 'Aktif' : 'Diblokir'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['is_premium']): ?>
                                            <span class="premium-badge">Premium</span>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted);">Regular</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                    <td><?php echo $videoCount; ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="toggle_user_status">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="current_status" value="<?php echo $user['status']; ?>">
                                                <button type="submit" class="btn-block" onclick="return confirm('Ubah status user ini?')">
                                                    <?php echo $user['status'] === 'active' ? 'Blokir' : 'Aktifkan'; ?>
                                                </button>
                                            </form>
                                            
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="toggle_premium">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="current_premium" value="<?php echo $user['is_premium']; ?>">
                                                <button type="submit" class="btn-approve" onclick="return confirm('Ubah status premium user ini?')">
                                                    <?php echo $user['is_premium'] ? 'Remove Premium' : 'Set Premium'; ?>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Category Management -->
        <div class="dashboard-section">
            <h3>Manajemen Kategori</h3>
            
            <!-- Add Category Form -->
            <form method="POST" style="margin-bottom: 2rem; display: flex; gap: 1rem; align-items: end;">
                <div style="flex: 1;">
                    <label for="category_name">Nama Kategori Baru</label>
                    <input type="text" id="category_name" name="category_name" required placeholder="Masukkan nama kategori">
                </div>
                <input type="hidden" name="action" value="add_category">
                <button type="submit" class="btn btn-primary">Tambah Kategori</button>
            </form>
            
            <!-- Categories List -->
            <?php if ($categoriesResult && mysqli_num_rows($categoriesResult) > 0): ?>
                <div class="admin-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama Kategori</th>
                                <th>Jumlah Video</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($category = mysqli_fetch_assoc($categoriesResult)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                                    <td><?php echo $category['video_count']; ?></td>
                                    <td>
                                        <?php if ($category['video_count'] == 0): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="delete_category">
                                                <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                                <button type="submit" class="btn-reject" onclick="return confirm('Hapus kategori ini?')">
                                                    Hapus
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted); font-size: 0.875rem;">Tidak dapat dihapus (ada video)</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Top Videos -->
        <div class="dashboard-section">
            <h3>Video Terpopuler</h3>
            
            <?php if ($topVideosResult && mysqli_num_rows($topVideosResult) > 0): ?>
                <div class="admin-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Video</th>
                                <th>Uploader</th>
                                <th>Kategori</th>
                                <th>Likes</th>
                                <th>Sponsor</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($video = mysqli_fetch_assoc($topVideosResult)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($video['filename']); ?></td>
                                    <td><?php echo htmlspecialchars($video['uploader_email']); ?></td>
                                    <td><?php echo htmlspecialchars($video['category_name']); ?></td>
                                    <td><?php echo $video['likes']; ?></td>
                                    <td>
                                        <?php if ($video['sponsor']): ?>
                                            <span class="alert alert-warning" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">Sponsor</span>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted);">Regular</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="set_sponsor">
                                            <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                                            <input type="hidden" name="sponsor_status" value="<?php echo $video['sponsor'] ? 0 : 1; ?>">
                                            <button type="submit" class="btn-approve" onclick="return confirm('Ubah status sponsor video ini?')">
                                                <?php echo $video['sponsor'] ? 'Remove Sponsor' : 'Set Sponsor'; ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Recent Comments -->
        <div class="dashboard-section">
            <h3>Komentar Terbaru</h3>
            
            <?php if ($commentsResult && mysqli_num_rows($commentsResult) > 0): ?>
                <div class="admin-table">
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Video</th>
                                <th>Komentar</th>
                                <th>Waktu</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($comment = mysqli_fetch_assoc($commentsResult)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($comment['user_email']); ?></td>
                                    <td><?php echo htmlspecialchars($comment['video_filename']); ?></td>
                                    <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">
                                        <?php echo htmlspecialchars(substr($comment['comment'], 0, 100)); ?>
                                        <?php if (strlen($comment['comment']) > 100) echo '...'; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="delete_comment">
                                            <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                            <button type="submit" class="btn-reject" onclick="return confirm('Hapus komentar ini?')">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Auto-refresh admin panel every 60 seconds
setInterval(function() {
    if (document.hidden === false) {
        const lastActivity = localStorage.getItem('lastAdminActivity') || 0;
        const currentTime = Date.now();
        
        if (currentTime - lastActivity > 60000) { // 60 seconds
            window.location.reload();
        }
    }
}, 60000);

// Track admin activity
document.addEventListener('mousemove', function() {
    localStorage.setItem('lastAdminActivity', Date.now());
});

document.addEventListener('keypress', function() {
    localStorage.setItem('lastAdminActivity', Date.now());
});

// Confirm actions
document.querySelectorAll('form button').forEach(button => {
    if (button.textContent.includes('Delete') || button.textContent.includes('Hapus')) {
        button.addEventListener('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin menghapus ini? Tindakan ini tidak dapat dibatalkan.')) {
                e.preventDefault();
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
