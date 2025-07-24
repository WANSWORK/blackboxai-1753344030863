<?php
// video.php - Individual video page with like and comment functionality
// This page displays a single video with its details, likes, and comments

$page_title = "Video Detail";
require_once 'db.php';
require_once 'includes/auth.php';

// Get video ID from URL
$video_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($video_id <= 0) {
    header("Location: /index.php?error=video_not_found");
    exit;
}

// Get video details
$videoQuery = "
    SELECT v.*, c.name as category_name, u.email as uploader_email, u.is_premium as uploader_premium
    FROM videos v 
    JOIN categories c ON v.category_id = c.id 
    JOIN users u ON v.user_id = u.id 
    WHERE v.id = ? AND v.approved = 1
";
$videoStmt = mysqli_prepare($conn, $videoQuery);
mysqli_stmt_bind_param($videoStmt, "i", $video_id);
mysqli_stmt_execute($videoStmt);
$videoResult = mysqli_stmt_get_result($videoStmt);

if (!$videoResult || mysqli_num_rows($videoResult) == 0) {
    header("Location: /index.php?error=video_not_found");
    exit;
}

$video = mysqli_fetch_assoc($videoResult);
$page_title = "Video - " . $video['category_name'];

// Check if current user has liked this video
$userHasLiked = false;
if (isLoggedIn()) {
    $likeCheckQuery = "SELECT id FROM likes WHERE user_id = ? AND video_id = ?";
    $likeCheckStmt = mysqli_prepare($conn, $likeCheckQuery);
    mysqli_stmt_bind_param($likeCheckStmt, "ii", getCurrentUserId(), $video_id);
    mysqli_stmt_execute($likeCheckStmt);
    $likeCheckResult = mysqli_stmt_get_result($likeCheckStmt);
    $userHasLiked = mysqli_num_rows($likeCheckResult) > 0;
}

// Get comments for this video
$commentsQuery = "
    SELECT c.*, u.email as user_email, u.is_premium as user_premium
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.video_id = ? 
    ORDER BY c.created_at DESC
";
$commentsStmt = mysqli_prepare($conn, $commentsQuery);
mysqli_stmt_bind_param($commentsStmt, "i", $video_id);
mysqli_stmt_execute($commentsStmt);
$commentsResult = mysqli_stmt_get_result($commentsStmt);

// Get related videos (same category, excluding current video)
$relatedQuery = "
    SELECT v.*, c.name as category_name, u.email as uploader_email, u.is_premium as uploader_premium
    FROM videos v 
    JOIN categories c ON v.category_id = c.id 
    JOIN users u ON v.user_id = u.id 
    WHERE v.category_id = ? AND v.id != ? AND v.approved = 1 
    ORDER BY v.likes DESC, v.upload_time DESC 
    LIMIT 6
";
$relatedStmt = mysqli_prepare($conn, $relatedQuery);
mysqli_stmt_bind_param($relatedStmt, "ii", $video['category_id'], $video_id);
mysqli_stmt_execute($relatedStmt);
$relatedResult = mysqli_stmt_get_result($relatedStmt);

// Function to format time ago
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'baru saja';
    if ($time < 3600) return floor($time/60) . ' menit lalu';
    if ($time < 86400) return floor($time/3600) . ' jam lalu';
    if ($time < 2592000) return floor($time/86400) . ' hari lalu';
    if ($time < 31536000) return floor($time/2592000) . ' bulan lalu';
    return floor($time/31536000) . ' tahun lalu';
}

// Function to determine video label
function getVideoLabel($video) {
    if ($video['sponsor'] == 1) {
        return "🎯 Video Sponsor";
    }
    if ($video['likes'] > 10) {
        return "🔥 Best of the Week";
    }
    if ($video['likes'] > 5) {
        return "⭐ Top of the Month";
    }
    return "";
}

include 'includes/header.php';
?>

<div class="video-detail-page" style="padding: 2rem 0;">
    <div class="container">
        <!-- Breadcrumb -->
        <nav style="margin-bottom: 2rem; color: var(--text-secondary); font-size: 0.875rem;">
            <a href="/index.php" style="color: var(--accent-primary);">Home</a>
            <span style="margin: 0 0.5rem;">›</span>
            <a href="/index.php?category=<?php echo urlencode($video['category_name']); ?>" style="color: var(--accent-primary);">
                <?php echo htmlspecialchars($video['category_name']); ?>
            </a>
            <span style="margin: 0 0.5rem;">›</span>
            <span>Video Detail</span>
        </nav>
        
        <div style="display: grid; grid-template-columns: 1fr 300px; gap: 2rem; align-items: start;">
            <!-- Main Video Section -->
            <div class="main-video-section">
                <!-- Video Player -->
                <div class="video-player" style="background: var(--bg-secondary); border-radius: var(--radius-xl); overflow: hidden; position: relative;">
                    <?php $label = getVideoLabel($video); ?>
                    <?php if (!empty($label)): ?>
                        <div class="video-label" style="position: absolute; top: 1rem; left: 1rem; background: rgba(0, 0, 0, 0.8); color: white; padding: 0.5rem; border-radius: var(--radius-sm); font-size: 0.875rem; font-weight: 600; z-index: 10;">
                            <?php echo $label; ?>
                        </div>
                    <?php endif; ?>
                    
                    <video width="100%" height="400" controls controlsList="nodownload" style="display: block;">
                        <source src="/uploads/<?php echo htmlspecialchars($video['filename']); ?>" type="video/mp4">
                        Browser Anda tidak mendukung tag video.
                    </video>
                </div>
                
                <!-- Video Info -->
                <div class="video-info" style="background: var(--bg-secondary); border-radius: var(--radius-lg); padding: 1.5rem; margin-top: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                        <div>
                            <span class="video-category" style="background: var(--accent-primary); color: white; padding: 0.25rem 0.75rem; border-radius: var(--radius-sm); font-size: 0.875rem; font-weight: 600;">
                                <?php echo htmlspecialchars($video['category_name']); ?>
                            </span>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 1rem; color: var(--text-secondary); font-size: 0.875rem;">
                            <span>oleh <?php echo htmlspecialchars(explode('@', $video['uploader_email'])[0]); ?></span>
                            <?php if ($video['uploader_premium']): ?>
                                <span class="premium-badge">Premium</span>
                            <?php endif; ?>
                            <span><?php echo timeAgo($video['upload_time']); ?></span>
                        </div>
                    </div>
                    
                    <!-- Video Actions -->
                    <div class="video-actions" style="display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                        <div style="display: flex; align-items: center; gap: 2rem;">
                            <!-- Like Button -->
                            <?php if (isLoggedIn()): ?>
                                <form method="POST" action="/like_process.php" style="display: inline;">
                                    <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                                    <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['REQUEST_URI']; ?>">
                                    <button type="submit" class="like-btn" style="background: none; border: none; color: <?php echo $userHasLiked ? 'var(--error-color)' : 'var(--text-secondary)'; ?>; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-size: 1rem; transition: all 0.2s ease;">
                                        <span style="font-size: 1.25rem;"><?php echo $userHasLiked ? '❤️' : '🤍'; ?></span>
                                        <span><?php echo $video['likes']; ?> Like<?php echo $video['likes'] != 1 ? 's' : ''; ?></span>
                                    </button>
                                </form>
                            <?php else: ?>
                                <div style="color: var(--text-secondary); display: flex; align-items: center; gap: 0.5rem; font-size: 1rem;">
                                    <span style="font-size: 1.25rem;">🤍</span>
                                    <span><?php echo $video['likes']; ?> Like<?php echo $video['likes'] != 1 ? 's' : ''; ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Comment Count -->
                            <div style="color: var(--text-secondary); display: flex; align-items: center; gap: 0.5rem; font-size: 1rem;">
                                <span style="font-size: 1.25rem;">💬</span>
                                <span><?php echo mysqli_num_rows($commentsResult); ?> Komentar</span>
                            </div>
                        </div>
                        
                        <!-- Share Button -->
                        <button onclick="shareVideo()" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                            Bagikan
                        </button>
                    </div>
                </div>
                
                <!-- Comments Section -->
                <div class="comments-section" style="background: var(--bg-secondary); border-radius: var(--radius-lg); padding: 1.5rem; margin-top: 1rem;">
                    <h3 style="margin-bottom: 1.5rem;">Komentar (<?php echo mysqli_num_rows($commentsResult); ?>)</h3>
                    
                    <!-- Add Comment Form -->
                    <?php if (isLoggedIn()): ?>
                        <form method="POST" action="/comment_process.php" class="comment-form" style="margin-bottom: 2rem;">
                            <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                            <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['REQUEST_URI']; ?>">
                            
                            <div style="display: flex; gap: 1rem; align-items: start;">
                                <div style="width: 40px; height: 40px; background: var(--accent-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; flex-shrink: 0;">
                                    <?php echo strtoupper(substr($_SESSION['email'], 0, 1)); ?>
                                </div>
                                
                                <div style="flex: 1;">
                                    <textarea 
                                        name="comment" 
                                        placeholder="Tulis komentar Anda..." 
                                        required
                                        style="width: 100%; min-height: 80px; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius-md); background: var(--bg-tertiary); color: var(--text-primary); resize: vertical;"
                                    ></textarea>
                                    
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem;">
                                        <small style="color: var(--text-muted);">
                                            Gunakan bahasa yang sopan dan konstruktif
                                        </small>
                                        <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                            Kirim Komentar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php else: ?>
                        <div style="text-align: center; padding: 2rem; background: var(--bg-tertiary); border-radius: var(--radius-md); margin-bottom: 2rem;">
                            <p style="color: var(--text-secondary); margin-bottom: 1rem;">
                                Silakan login untuk memberikan komentar
                            </p>
                            <a href="/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn btn-primary">
                                Login
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Comments List -->
                    <div class="comments-list">
                        <?php if ($commentsResult && mysqli_num_rows($commentsResult) > 0): ?>
                            <?php while ($comment = mysqli_fetch_assoc($commentsResult)): ?>
                                <div class="comment-item" style="display: flex; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                                    <div style="width: 40px; height: 40px; background: var(--accent-secondary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; flex-shrink: 0;">
                                        <?php echo strtoupper(substr($comment['user_email'], 0, 1)); ?>
                                    </div>
                                    
                                    <div style="flex: 1;">
                                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                                            <span style="font-weight: 600; color: var(--text-primary);">
                                                <?php echo htmlspecialchars(explode('@', $comment['user_email'])[0]); ?>
                                            </span>
                                            
                                            <?php if ($comment['user_premium']): ?>
                                                <span class="premium-badge" style="font-size: 0.75rem;">Premium</span>
                                            <?php endif; ?>
                                            
                                            <span style="color: var(--text-muted); font-size: 0.875rem;">
                                                <?php echo timeAgo($comment['created_at']); ?>
                                            </span>
                                        </div>
                                        
                                        <p style="color: var(--text-secondary); line-height: 1.5; margin: 0;">
                                            <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                                <p>Belum ada komentar. Jadilah yang pertama berkomentar!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Related Videos -->
                <div class="related-videos" style="background: var(--bg-secondary); border-radius: var(--radius-lg); padding: 1.5rem;">
                    <h4 style="margin-bottom: 1rem; color: var(--text-primary);">Video Terkait</h4>
                    
                    <?php if ($relatedResult && mysqli_num_rows($relatedResult) > 0): ?>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <?php while ($relatedVideo = mysqli_fetch_assoc($relatedResult)): ?>
                                <a href="/video.php?id=<?php echo $relatedVideo['id']; ?>" class="related-video-item" style="display: flex; gap: 0.75rem; padding: 0.75rem; border-radius: var(--radius-md); transition: background-color 0.2s ease; text-decoration: none;">
                                    <video width="80" height="60" style="border-radius: var(--radius-sm); object-fit: cover; flex-shrink: 0;">
                                        <source src="/uploads/<?php echo htmlspecialchars($relatedVideo['filename']); ?>" type="video/mp4">
                                    </video>
                                    
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="font-size: 0.875rem; color: var(--text-primary); font-weight: 500; line-height: 1.3; margin-bottom: 0.25rem; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                            <?php echo htmlspecialchars($relatedVideo['category_name']); ?>
                                        </div>
                                        
                                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">
                                            <?php echo htmlspecialchars(explode('@', $relatedVideo['uploader_email'])[0]); ?>
                                            <?php if ($relatedVideo['uploader_premium']): ?>
                                                <span class="premium-badge" style="font-size: 0.625rem; margin-left: 0.25rem;">Premium</span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div style="font-size: 0.75rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem;">
                                            <span>❤️ <?php echo $relatedVideo['likes']; ?></span>
                                            <span>•</span>
                                            <span><?php echo timeAgo($relatedVideo['upload_time']); ?></span>
                                        </div>
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        </div>
                        
                        <div style="text-align: center; margin-top: 1rem;">
                            <a href="/index.php?category=<?php echo urlencode($video['category_name']); ?>" class="btn btn-secondary" style="font-size: 0.875rem; padding: 0.5rem 1rem;">
                                Lihat Semua <?php echo htmlspecialchars($video['category_name']); ?>
                            </a>
                        </div>
                    <?php else: ?>
                        <p style="color: var(--text-secondary); font-size: 0.875rem; text-align: center;">
                            Tidak ada video terkait
                        </p>
                    <?php endif; ?>
                </div>
                
                <!-- Video Stats -->
                <div class="video-stats" style="background: var(--bg-secondary); border-radius: var(--radius-lg); padding: 1.5rem; margin-top: 1rem;">
                    <h4 style="margin-bottom: 1rem; color: var(--text-primary);">Statistik Video</h4>
                    
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-secondary); font-size: 0.875rem;">Upload:</span>
                            <span style="color: var(--text-primary); font-size: 0.875rem; font-weight: 500;">
                                <?php echo date('d M Y', strtotime($video['upload_time'])); ?>
                            </span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-secondary); font-size: 0.875rem;">Kategori:</span>
                            <span style="color: var(--text-primary); font-size: 0.875rem; font-weight: 500;">
                                <?php echo htmlspecialchars($video['category_name']); ?>
                            </span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-secondary); font-size: 0.875rem;">Total Like:</span>
                            <span style="color: var(--text-primary); font-size: 0.875rem; font-weight: 500;">
                                <?php echo number_format($video['likes']); ?>
                            </span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-secondary); font-size: 0.875rem;">Total Komentar:</span>
                            <span style="color: var(--text-primary); font-size: 0.875rem; font-weight: 500;">
                                <?php echo mysqli_num_rows($commentsResult); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Video detail page specific styles */
.related-video-item:hover {
    background-color: var(--bg-tertiary);
}

.like-btn:hover {
    transform: scale(1.05);
}

.comment-form textarea:focus {
    border-color: var(--accent-primary);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

/* Mobile responsive */
@media (max-width: 768px) {
    .video-detail-page .container > div {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .sidebar {
        order: -1;
    }
    
    .video-actions {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch !important;
    }
    
    .comment-item {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .comment-form > div {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>

<script>
// Share video function
function shareVideo() {
    const url = window.location.href;
    const title = 'Video - <?php echo addslashes($video['category_name']); ?> | Stralentech.ai';
    
    if (navigator.share) {
        navigator.share({
            title: title,
            url: url
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(url).then(function() {
            alert('Link video berhasil disalin ke clipboard!');
        });
    }
}

// Auto-expand textarea
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.querySelector('textarea[name="comment"]');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    }
});

// Like button animation
document.querySelector('.like-btn')?.addEventListener('click', function() {
    this.style.transform = 'scale(0.95)';
    setTimeout(() => {
        this.style.transform = 'scale(1)';
    }, 150);
});

// Lazy load related videos
document.addEventListener('DOMContentLoaded', function() {
    const relatedVideos = document.querySelectorAll('.related-video-item video');
    
    const videoObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const video = entry.target;
                video.load();
            }
        });
    });
    
    relatedVideos.forEach(video => {
        videoObserver.observe(video);
    });
});
</script>

<?php include 'includes/footer.php'; ?>
